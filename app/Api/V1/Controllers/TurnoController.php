<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearTurnoRequest;
use App\Api\V1\Requests\ModificarTurnoRequest;
use App\Api\V1\Requests\MostrarTurnosRequest;
use App\Http\Controllers\Controller;
use App\Jobs\ConfirmacionTurnoJob;
use App\Mail\TurnoReasignado;
use App\Models\Consultorio;
use App\Models\EstadosTurno;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Turno;
use App\Utilities\Turnos as TurnosUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TurnoController extends Controller
{
    public function mostrarTurnos(MostrarTurnosRequest $request)
    {

        $turnos = null;
        $turnosQuery = null;

        $fechaDesdeStr = $request->fecha_desde;
        $fechaHastaStr = $request->fecha_hasta;

        $filtros = $request->only(['profesional_id', 'consultorio_id']);

        try {

            $turnosQuery = Turno::select()->with(['profesional', 'paciente', 'consultorio', 'estado']);

            foreach ($filtros as $filtro => $valor) {
                if (!empty($valor))
                    $turnosQuery->where($filtro, $valor);
            }

            if ($fechaDesdeStr !== null && $fechaHastaStr !== null) {

                $fechaDesde = new Carbon($fechaDesdeStr);
                $fechaHasta = new Carbon($fechaHastaStr);

                if ($fechaDesde > $fechaHasta) {
                    throw new HttpException(400, "Debe proporcionar un rango de fechas válido.");
                }

                $turnosQuery->whereBetween('fecha_hora_desde', [$fechaDesde, $fechaHasta->setTime(23, 59, 59)]);
            }

            $turnos = $turnosQuery->get();

            $turnos->transform(function ($item, $key) {
                $_item = $item;
                $_item['paciente_nombre_completo'] = $item['paciente']['apellido'] . " " . $item['paciente']['nombre'];
                $_item['profesional_nombre_completo'] = $item['profesional']['apellido'] . " " . $item['profesional']['nombre'];
                $_item['consultorio_nombre'] = $item['consultorio']['nombre'];
                $_item['estado_descripcion'] = $item['estado']['descripcion'];
                $_item['estado_color'] = $item['estado']['color'];
                unset($_item['profesional']);
                unset($_item['paciente']);
                unset($_item['consultorio']);
                unset($_item['estado']);

                return $_item;
            });
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'turnos' => $turnos
        ];
    }

    public function mostrarTurno($id)
    {
        $turno = null;

        try {
            $turno = Turno::select()->with(['profesional', 'paciente', 'consultorio', 'estado'])->find($id);
            if (empty($turno)) {
                throw new HttpException(400, "El turno requerido no existe.");
            }

            $horarioDesde = new Carbon($turno->fecha_hora_desde);

            $turno->editable = ($horarioDesde > Carbon::now());

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'turno' => $turno,
        ];
    }

    public function obtenerInfoFormulario()
    {

        $profesionales = null;
        $pacientes = null;
        $estados = null;
        $consultorios = null;

        try {
            $profesionales = Profesional::select('id', DB::raw("CONCAT(apellido, ' ', nombre) as nombre_completo"))->get();
            $pacientes = Paciente::select('id', DB::raw("CONCAT(apellido, ' ', nombre) as nombre_completo"))->get();
            $estados = EstadosTurno::select('id', 'descripcion', 'color')->get();
            $consultorios = Consultorio::select('id', 'nombre')->get();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'profesionales' => $profesionales,
            'pacientes' => $pacientes,
            'estados' => $estados,
            'consultorios' => $consultorios
        ];
    }

    public function crearTurno(CrearTurnoRequest $request)
    {
        $turno = null;

        $campos = ['profesional_id', 'paciente_id', 'consultorio_id', 'titulo', 'descripcion', 'fecha_hora_desde', 'fecha_hora_hasta'];
        $datos = $request->only($campos);

        try {

            $horarioDesde = new Carbon($datos['fecha_hora_desde']);
            $horarioHasta = new Carbon($datos['fecha_hora_hasta']);

            if ($horarioDesde >= $horarioHasta) {
                throw new HttpException(400, "Debe proporcionar un rango válido.");
            }

            if ($horarioDesde <= Carbon::now()){
                throw new HttpException(400, "La fecha y hora de inicio debe ser superior a la fecha y hora actual.");
            }

            TurnosUtility::evaluarInterseccionHorarios(
                $horarioDesde,
                $horarioHasta,
                $datos['consultorio_id'],
                $datos['profesional_id']
            );

            $turno = Turno::create(array_merge($datos, ['estado_id' => EstadosTurno::obtenerIdEstado('creado')]));

            if(!empty($turno->paciente->email)){
                $this->evaluarEnvioDeConfirmacion($turno);
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json(
            [
                'status' => 'ok',
                'mensaje' => 'Turno creado exitosamente.',
                'paciente' => $turno,
            ],
            201
        );
    }

    public function modificarTurno(ModificarTurnoRequest $request, $id)
    {
        $turno = null;

        $campos = ['profesional_id', 'consultorio_id', 'estado_id', 'titulo', 'descripcion', 'fecha_hora_desde', 'fecha_hora_hasta'];
        $datos = $request->only($campos);

        try {
            $turno = Turno::find($id);
            if (empty($turno)) {
                throw new HttpException(400, "El turno requerido no existe.");
            }

            $horarioDesde = new Carbon($datos['fecha_hora_desde']);
            $horarioHasta = new Carbon($datos['fecha_hora_hasta']);

            if ($horarioDesde >= $horarioHasta) {
                throw new HttpException(400, "Debe proporcionar un rango válido.");
            }

            if ($horarioDesde <= Carbon::now() && (
                $datos['estado_id'] == EstadosTurno::obtenerIdEstado('pendiente') ||
                $datos['estado_id'] == EstadosTurno::obtenerIdEstado('confirmado')
                )){
                throw new HttpException(400, "La fecha y hora de inicio debe ser superior a la fecha y hora actual.");
            }

            TurnosUtility::evaluarInterseccionHorarios(
                $horarioDesde,
                $horarioHasta,
                $datos['consultorio_id'],
                $datos['profesional_id'],
                $turno->id
            );



            if($horarioDesde > Carbon::now()){
                $turno->fill($datos);
            }else{
                $camposPermitidos = ['estado_id'];
                $camposNegados = array_diff($campos, $camposPermitidos);

                foreach($camposNegados as $campo){
                    if($turno[$campo] !== $datos[$campo]){
                        throw new HttpException(400, "El campo {$campo} no puede ser modificado.");
                    }
                }
                foreach($camposPermitidos as $campo){
                    $turno[$campo] = $datos[$campo];
                }
            }

            //Si cambia algo en el horario, envio mail de notificacion
            if(!($turno->fecha_hora_desde == $horarioDesde && $turno->fecha_hora_hasta == $horarioHasta) && !empty($turno->paciente->email)){
                if($datos['estado_id']== EstadosTurno::obtenerIdEstado('confirmado')){
                    Mail::to($turno->paciente->email)->queue(new TurnoReasignado($turno));
                }
            }

            $turno->save();

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Turno modificado con éxito.'
        ];
    }

    public function borrarTurno($id)
    {

        $turno = null;

        try {
            $turno = Turno::find($id);
            if (empty($turno)) {
                throw new HttpException(400, "El turno requerido no existe.");
            }

            $horarioDesde = new Carbon($turno->fecha_hora_desde);

            if($horarioDesde <= Carbon::now()){
                throw new HttpException(400, "Este turno no puede ser eliminado.");
            }

            $turno->delete();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Turno eliminado con éxito.'
        ];
    }

    public function confirmarRechazarTurno(Request $request){

        $turno = null;
        $id = $request->id;
        $confirmar = $request->confirmar;
        $mensaje = "";

        try {

            if(!empty($id) && $confirmar !== null){

                $turno = Turno::find(Crypt::decryptString($id));

                if(empty($turno)){
                    $mensaje = "Ha ocurrido un error. No se encuentra el turno asociado.";
                }else{

                    if($turno->estado_id == EstadosTurno::obtenerIdEstado('pendiente')){

                        if($confirmar){

                            $turno->estado_id = EstadosTurno::obtenerIdEstado('confirmado');
                            $mensaje = "Turno confirmado.";
                        }else{

                            $turno->estado_id = EstadosTurno::obtenerIdEstado('aca');
                            $mensaje = "Turno rechazado.";
                        }

                        $turno->save();

                    }else{
                        $mensaje = "Ups! La acción no pudo ser procesada.";
                    }

                }

            }

        } catch (\Exception $e) {
            throw $e;
        }

        return view('confirmacionTurnoExitosa', ['mensaje' => $mensaje]);
    }

    public function enviarMensajeTest()
    {
        $url = "https://graph.facebook.com/v15.0/100434602966612/messages";

        $body = [
            "messaging_product" => "whatsapp",
            "to" => "543425497474",
            "type" =>"template",
            "template"=> [ "name" => "hello_world", "language" => [ "code" =>"en_US" ] ]
        ];
        $token = "EAATFnZAOOGhgBABEx6ipTwZC52CZCsQpFg6fUR8ZCRDNxCYDI3WtH0ZCuZAcFkUPLdYf1YEA5P4TyImpBSMe3WVpkaA1aExMUMjPZADbjWXoQxZBHMsIYkArZCFZBjGaMoqImI1BSMjLHukWGdAjrZCTdJv2pXJAZCJwD3VGZBr5ZAzZBI0poTFoRSI7pok7ms6ZBeU0v89p35zhwZAqC3QZDZD";
        $response = Http::withToken($token)->acceptJson()->post($url, $body);

        Log::info("MANDE UN WSP");

        return [
            'status' => 'ok',
            'mensaje' => 'Mensaje enviado.',
            'response' => $response->json()
        ];
    }

    private function evaluarEnvioDeConfirmacion(Turno $turno){

        [$hora , $minutos] = explode(':', config('custom.horarioConfirmacionTurno'));

        $horarioDeEnvio = new Carbon($turno->fecha_hora_desde);
        $horarioDeEnvio->subDays(config('custom.diasAntelacionTurno'))->setTime($hora, $minutos)->subMinutes(1);

        if($horarioDeEnvio <= Carbon::now()){
            ConfirmacionTurnoJob::dispatch($turno);
        }

    }
}

<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearPacienteRequest;
use App\Api\V1\Requests\ModificarPacienteRequest;
use App\Api\V1\Requests\MostrarPacientesRequest;
use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PacienteController extends Controller
{
    public function mostrarPacientes(MostrarPacientesRequest $request)
    {

        $pacientes = null;
        $pacientesQuery = null;
        $fechaDesdeStr = $request->fecha_desde;
        $fechaHastaStr = $request->fecha_hasta;
        $mostrarPaginado = ($request->paginado === null) ? true : $request->paginado;

        $filtros = [];
        $filtrosLike = $request->only(['email', 'documento']);
        $filtroNombreCompleto = $request->nombre_completo;

        try {
            $pacientesQuery = Paciente::select('id', 'documento', 'email', 'telefono', 'direccion', 'created_at as fecha_alta', DB::raw("CONCAT(apellido, ' ', nombre) as nombre_completo"));

            foreach ($filtros as $filtro => $valor) {
                if (!empty($valor))
                    $pacientesQuery->where($filtro, $valor);
            }
            foreach ($filtrosLike as $filtro => $valor) {
                if (!empty($valor) && (strlen($valor) >= config('custom.filtroMinLenght')))
                    $pacientesQuery->where($filtro, 'like', '%' . $valor . '%');
            }

            if ($fechaDesdeStr !== null && $fechaHastaStr !== null) {

                $fechaDesde = new Carbon($fechaDesdeStr);
                $fechaHasta = new Carbon($fechaHastaStr);

                if ($fechaDesde > $fechaHasta) {
                    throw new HttpException(400, "Debe proporcionar un rango de fechas válido.");
                }

                $pacientesQuery->whereBetween('created_at', [$fechaDesde, $fechaHasta->setTime(23, 59, 59)]);
            }

            if(!empty($filtroNombreCompleto) && (strlen($filtroNombreCompleto) >= config('custom.filtroMinLenght'))){
                $pacientesQuery->where(DB::raw("CONCAT(nombre, ' ', apellido)"), 'like', '%' . $filtroNombreCompleto . '%');
            }

            if ($mostrarPaginado) {
                $pacientes = $pacientesQuery->paginate(config('custom.paginateNumber'));
            } else {
                $pacientes = $pacientesQuery->get();
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'pacientes' => $pacientes
        ];
    }

    public function mostrarPaciente($id)
    {

        $paciente = null;

        try {
            $paciente = Paciente::select()->find($id);
            if (empty($paciente)) {
                throw new HttpException(400, "El paciente requerido no existe.");
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'paciente' => $paciente
        ];
    }

    public function crearPaciente(CrearPacienteRequest $request)
    {
        $paciente = null;

        $campos = ['nombre', 'apellido', 'documento', 'email', 'telefono', 'direccion', 'observaciones'];
        $datos = $request->only($campos);

        try {
            $paciente = Paciente::create($datos);
        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json(
            [
                'status' => 'ok',
                'mensaje' => 'Paciente creado exitosamente.',
                'paciente' => $paciente,
            ],
            201
        );
    }

    public function modificarPaciente(ModificarPacienteRequest $request, $id)
    {

        $paciente = null;

        $campos = ['nombre', 'apellido', 'documento', 'email', 'telefono', 'direccion', 'observaciones'];
        $datos = $request->only($campos);

        try {
            $paciente = Paciente::find($id);
            if (empty($paciente)) {
                throw new HttpException(400, "El paciente requerido no existe.");
            }

            $paciente->fill($datos)->save();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Paciente modificado con éxito.'
        ];
    }

    public function borrarPaciente($id)
    {

        $paciente = null;

        try {
            $paciente = Paciente::find($id);
            if (empty($paciente)) {
                throw new HttpException(400, "El paciente requerido no existe.");
            }

            $paciente->delete();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Paciente eliminado con éxito.'
        ];
    }
}

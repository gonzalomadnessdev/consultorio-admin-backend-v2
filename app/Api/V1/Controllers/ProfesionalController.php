<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearProfesionalRequest;
use App\Api\V1\Requests\ModificarProfesionalRequest;
use App\Api\V1\Requests\MostrarProfesionalesRequest;
use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfesionalController extends Controller
{
    public function mostrarProfesionales(MostrarProfesionalesRequest $request)
    {

        $profesionales = null;
        $profesionalesQuery = null;
        $mostrarPaginado = ($request->paginado === null) ? true : $request->paginado;

        $filtros = [];
        $filtrosLike = $request->only(['email', 'documento']);

        try {
            $profesionalesQuery = Profesional::select('id', 'documento', 'matricula', 'email', 'telefono', 'created_at as fecha_alta', DB::raw("CONCAT(apellido, ' ', nombre) as nombre_completo"));

            foreach ($filtros as $filtro => $valor) {
                if (!empty($valor))
                    $profesionalesQuery->where($filtro, $valor);
            }
            foreach ($filtrosLike as $filtro => $valor) {
                if (!empty($valor) && (strlen($valor) >= config('custom.filtroMinLenght')))
                    $profesionalesQuery->where($filtro, 'like', '%' . $valor . '%');
            }

            if ($mostrarPaginado) {
                $profesionales = $profesionalesQuery->paginate(config('custom.paginateNumber'));
            } else {
                $profesionales = $profesionalesQuery->get();
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'profesionales' => $profesionales
        ];
    }

    public function mostrarProfesional($id)
    {

        $profesional = null;

        try {
            $profesional = Profesional::select()->find($id);
            if (empty($profesional)) {
                throw new HttpException(400, "El profesional requerido no existe.");
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'profesional' => $profesional
        ];
    }

    public function crearProfesional(CrearProfesionalRequest $request)
    {

        $profesional = null;

        $campos = ['nombre', 'apellido', 'documento', 'matricula', 'email', 'telefono'];
        $datos = $request->only($campos);

        try {
            $profesional = Profesional::create($datos);
        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json(
            [
                'status' => 'ok',
                'mensaje' => 'Profesional creado exitosamente.',
                'profesional' => $profesional,
            ],
            201
        );
    }

    public function modificarProfesional(ModificarProfesionalRequest $request, $id)
    {

        $profesional = null;

        $campos = ['nombre', 'apellido', 'documento', 'matricula', 'email', 'telefono'];
        $datos = $request->only($campos);

        try {

            $profesional = Profesional::find($id);
            if (empty($profesional)) {
                throw new HttpException(400, "El profesional requerido no existe.");
            }

            $profesional->fill($datos)->save();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Profesional modificado con éxito.'
        ];
    }

    public function borrarProfesional($id){

        $profesional = null;

        try {

            $profesional = Profesional::find($id);
            if (empty($profesional)) {
                throw new HttpException(400, "El profesional requerido no existe.");
            }

            $profesional->delete();

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Profesional eliminado con éxito.'
        ];
    }
}

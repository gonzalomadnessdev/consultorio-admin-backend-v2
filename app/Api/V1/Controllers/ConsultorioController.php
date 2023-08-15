<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearConsultorioRequest;
use App\Api\V1\Requests\ModificarConsultorioRequest;
use App\Http\Controllers\Controller;
use App\Models\Consultorio;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConsultorioController extends Controller
{
    public function mostrarConsultorios(){

        $consultorios = null;
        $consultoriosQuery = null;

        try {
            $consultoriosQuery = Consultorio::select('id', 'nombre', 'created_at as fecha_alta');
            $consultorios = $consultoriosQuery->paginate(config('custom.paginateNumber'));

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'consultorios' => $consultorios
        ];

    }

    public function mostrarConsultorio($id){

        $consultorio = null;

        try {
            $consultorio = Consultorio::select()->find($id);
            if (empty($consultorio)) {
                throw new HttpException(400, "El consultorio requerido no existe.");
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'consultorio' => $consultorio
        ];
    }

    public function crearConsultorio(CrearConsultorioRequest $request){
        $consultorio = null;

        $campos = ['nombre'];
        $datos = $request->only($campos);

        try {
            $consultorio = Consultorio::create($datos);
        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json(
            [
                'status' => 'ok',
                'mensaje' => 'Consultorio creado exitosamente.',
                'consultorio' => $consultorio,
            ],
            201
        );
    }

    public function modificarConsultorio(ModificarConsultorioRequest $request, $id){
        $consultorio = null;

        $campos = ['nombre'];
        $datos = $request->only($campos);

        try {
            $consultorio = Consultorio::find($id);
            if (empty($consultorio)) {
                throw new HttpException(400, "El consultorio requerido no existe.");
            }

            $consultorio->fill($datos)->save();

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Consultorio modificado con éxito.'
        ];
    }

    public function borrarConsultorio($id){

        $consultorio = null;

        try {
            $consultorio = Consultorio::find($id);
            if (empty($consultorio)) {
                throw new HttpException(400, "El consultorio requerido no existe.");
            }

            $consultorio->delete();

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Paciente consultorio con éxito.'
        ];
    }
}

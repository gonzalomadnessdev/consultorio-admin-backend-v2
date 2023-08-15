<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearRolRequest;
use App\Api\V1\Requests\ModificarRolRequest;
use App\Models\Role;
use App\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RolesYPermisosController extends Controller
{
    public function mostrarRoles()
    {
        $roles = null;

        try {
            $roles = Role::select('id', 'slug as rol', 'name as nombre', 'level as nivel', 'description as descripcion')
                ->with('permisos:id,slug as permiso,name as nombre')
                ->where('slug',  '<>', 'superadmin')->get();
        } catch (\Exception $e) {
            throw $e;
        }

        return  [
            'status' => 'ok',
            'roles' => $roles
        ];
    }

    public function mostrarRol($id){

        $rol = null;

        try {
            $rol = Role::select('id', 'slug as rol', 'name as nombre', 'level as nivel', 'description as descripcion')
            ->with('permisos:id,slug as permiso,name as nombre')
            ->where('slug',  '<>', 'superadmin')
            ->find($id);

            if(empty($rol)){
                throw new HttpException(400, "El rol requerido no existe.");
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return  [
            'status' => 'ok',
            'rol' => $rol
        ];

    }

    public function mostrarPermisos()
    {
        $permisos = null;

        try {
            $permisos = Permission::select('id', 'slug as permiso', 'name as nombre')->get();
        } catch (\Exception $e) {
            throw $e;
        }

        return  [
            'status' => 'ok',
            'permisos' => $permisos
        ];
    }

    public function crearRol(CrearRolRequest $request)
    {
        $rol = null;
        $datos = [];
        $mensaje = 'Rol creado exitosamente.';
        $slug = "";

        try {
            $slug = Str::slug($request->nombre, config('roles.separator'));

            if(Role::where('slug' , $slug)->count()){
                throw new HttpException(400, "El nombre de rol ya está en uso.");
            }

            $datos['slug'] = $request->nombre;
            $datos['name'] = $request->nombre;
            $datos['description'] = $request->descripcion;
            $datos['permisos'] = $request->permisos;
            $datos['level'] = Role::NIVEL_DEFAULT;

            $rol = Role::create($datos);
            $mensaje = $mensaje . $this->asignarPermisosARol($rol, $datos['permisos'], false);

        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json([
            'status' => 'ok',
            'mensaje' => $mensaje,
            'rol' => $rol
        ], 201);
    }

    public function modificarRol(ModificarRolRequest $request, $id){

        $rol = null;
        $datos = [];
        $mensaje = 'Rol modificado exitosamente.';
        $slug = "";

        try {
            $rol = Role::find($id);

            if (empty($rol)) {
                throw new HttpException(400, "El rol requerido no existe.");
            } else if ($rol->slug == 'superadmin') {
                throw new HttpException(400, "No se permite modificar este rol.");
            }

            $slug = Str::slug($request->nombre, config('roles.separator'));

            if(Role::where('slug' , $slug)->where('id','<>',$id)->count()){
                throw new HttpException(400, "El nombre de rol ya está en uso.");
            }

            $datos['name'] = $request->nombre;
            $datos['slug'] = $request->nombre;
            $datos['description'] = $request->descripcion;
            $datos['permisos'] = $request->permisos;
            $datos['level'] = Role::NIVEL_DEFAULT;

            $rol->fill($datos)->save();

            $mensaje = $mensaje . $this->asignarPermisosARol($rol, $datos['permisos'], true);

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => $mensaje
        ];

    }

    public function borrarRol($id)
    {
        $rol = null;

        try {
            $rol = Role::find($id);
            if (empty($rol)) {
                throw new HttpException(400, "El rol requerido no existe.");
            } else if ($rol->slug == 'superadmin') {
                throw new HttpException(400, "No se permite eliminar este rol.");
            }

            $rol->delete($id);
        } catch (\Exception $e) {
            throw $e;
        }
        return [
            'status' => 'ok',
            'mensaje_ok' => 'Rol eliminado con éxito.'
        ];
    }


    private function asignarPermisosARol($rol, $permisos, $modificar)
    {
        $mensaje = "";

        if($permisos !== null){
            if(count($permisos)){

                $permisos = array_unique($permisos);

                $permisos = Permission::whereIn('slug', $permisos)->get();
                $rol->syncPermissions($permisos);

                $mensaje = " Se asignaron permisos : ({$permisos->implode('slug', " , ")}) al rol.";

            } else if($modificar){
                $rol->detachAllPermissions();
                $mensaje = ' El rol ya no tiene permisos asignados.';
            }
        }

        return $mensaje;
    }

}

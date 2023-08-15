<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CrearUsuarioRequest;
use App\Api\V1\Requests\ModificarUsuarioRequest;
use App\Api\V1\Requests\MostrarUsuariosRequest;
use App\Api\V1\Requests\UsuarioCambiarContrasenaRequest;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{
    public function mostrarUsuarios(MostrarUsuariosRequest $request)
    {
        $usuarios = null;
        $usuariosQuery = null;
        $fechaDesdeStr = $request->fecha_desde;
        $fechaHastaStr = $request->fecha_hasta;
        $mostrarPaginado = ($request->paginado === null) ? true : $request->paginado;

        $filtros = [];
        $filtrosLike = $request->only(['email']);

        try {
            $usuariosQuery = User::where(function ($query) {
                $query->whereRelation('roles', 'slug', '<>', 'superadmin')
                    ->orDoesntHave('roles');
            });

            foreach ($filtros as $filtro => $valor) {
                if (!empty($valor))
                    $usuariosQuery->where($filtro, $valor);
            }
            foreach ($filtrosLike as $filtro => $valor) {
                if (!empty($valor) && (strlen($valor) >= config('custom.filtroMinLenght')))
                    $usuariosQuery->where($filtro, 'like', '%' . $valor . '%');
            }

            if ($fechaDesdeStr !== null && $fechaHastaStr !== null) {

                $fechaDesde = new Carbon($fechaDesdeStr);
                $fechaHasta = new Carbon($fechaHastaStr);

                if ($fechaDesde > $fechaHasta) {
                    throw new HttpException(400, "Debe proporcionar un rango de fechas válido.");
                }

                $usuariosQuery->whereBetween('created_at', [$fechaDesde, $fechaHasta->setTime(23, 59, 59)]);
            }

            $usuariosQuery->select('id', 'email', 'created_at as fecha_alta');

            if ($mostrarPaginado) {
                $usuarios = $usuariosQuery->paginate(config('custom.paginateNumber'));
            } else {
                $usuarios = $usuariosQuery->get();
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'usuarios' => $usuarios
        ];
    }

    public function mostrarUsuario($id)
    {
        $usuario = null;

        try {
            $usuario = User::select(['id', 'email', 'created_at as fecha_alta'])
                ->with('roles:id,slug as rol,name as nombre,level as nivel')
                ->find($id);

            if (empty($usuario)) {
                throw new HttpException(400, "El usuario requerido no existe.");
            } else {
                $esSuperAdmin = $usuario->roles->contains(function ($rol) {
                    return $rol->rol == 'superadmin';
                });

                if ($esSuperAdmin) throw new HttpException(400, "El usuario requerido no existe.");
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'usuario' => $usuario
        ];
    }

    public function crearUsuario(CrearUsuarioRequest $request)
    {
        $usuario = null;
        $campos = ['email', 'password', 'roles'];
        $datos = $request->only($campos);
        $mensaje = "Usuario creado exitosamente.";

        try {
            $usuario = User::create($datos);
            $mensaje = $mensaje . $this->asignarRolesAUsuario($usuario, $datos['roles'], false);
        } catch (\Exception $e) {
            throw $e;
        }

        return response()->json(
            [
                'status' => 'ok',
                'mensaje' => $mensaje,
                'usuario' => $usuario,
            ],
            201
        );
    }

    public function modificarUsuario(ModificarUsuarioRequest $request,  $id)
    {
        $usuario = null;
        $campos = ['email', 'roles'];
        $datos = $request->only($campos);
        $mensaje = "Usuario modificado con éxito.";

        try {
            $usuario = User::find($id);
            $this->evaluarExepciones($usuario, $id, 'modificar');

            $usuario->fill($datos)->save();

            $mensaje = $mensaje . $this->asignarRolesAUsuario($usuario, $datos['roles'], true);
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => $mensaje
        ];
    }

    public function borrarUsuario($id)
    {
        $usuario = null;
        try {
            $usuario = User::find($id);
            $this->evaluarExepciones($usuario, $id, 'eliminar');

            $usuario->delete($id);
        } catch (\Exception $e) {
            throw $e;
        }
        return [
            'status' => 'ok',
            'mensaje' => 'Usuario eliminado con éxito.'
        ];
    }

    public function cambiarContrasena(UsuarioCambiarContrasenaRequest $request)
    {

        $id = $request->usuario_id;
        $nuevaContrasena = $request->nueva_contrasena;
        $usuario = null;

        try {
            $usuario = User::find($id);
            $usuario->password = $nuevaContrasena;
            $usuario->save();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Contraseña modificada con éxito.'
        ];
    }

    private function evaluarExepciones($usuario, $id, $accion)
    {
        $usuarioActual = auth()->user();

        if (empty($usuario)) {
            throw new HttpException(400, "El usuario requerido no existe.");
        } else if ($usuarioActual->id == $id) {
            throw new HttpException(400, "No se permite {$accion} el propio usuario.");
        } else if ($usuario->hasRole('superadmin')) {
            throw new HttpException(400, "No se permite {$accion} este usuario.");
        }
    }

    private function asignarRolesAUsuario($usuario, $roles, $modificar)
    {
        $mensaje = "";

        if ($roles !== null) {
            if (count($roles)) {

                $roles = array_unique($roles);

                $roles = Role::whereIn('slug', $roles)
                    ->where('slug', '<>', 'superadmin')
                    ->get();

                $usuario->syncRoles($roles);

                $mensaje = " Se asignaron roles : ({$roles->implode('slug', " , ")}) al usuario.";
            } else if ($modificar) {
                $usuario->detachAllRoles();
                $mensaje = " El usuario ya no tiene roles asignados.";
            }
        }

        return $mensaje;
    }
}

<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CambiarContrasenaRequest;
use App\Api\V1\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credenciales = null;
        $token = null;

        try {
            $credenciales = $request->only(['email', 'password']);
            $token = auth()->attempt($credenciales);
            if (!$token) {
                return $this->response->error('Las credenciales no son válidas.', 401);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return  $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = null;
        $data = null;
        $objetoPermisos = [];

        try {
            $user = auth()->user();

            $permisosAsignados = $user->getPermissions()->map(function ($rol) {
                return $rol->slug;
            });

            $listaPermisosAll = Permission::get()->pluck('slug');
            $permisosNoAsignados = $listaPermisosAll->diff($permisosAsignados);

            foreach($permisosAsignados as $permiso){
                $objetoPermisos[str_replace(".","_",$permiso)] = true;
            }
            foreach($permisosNoAsignados as $permiso){
                $objetoPermisos[str_replace(".","_",$permiso)] = false;
            }

            $data = [
                "id"=>$user->id,
                "nombre"=>$user->nombre,
                "apellido"=>$user->apellido,
                "email"=>$user->email,
                "roles"=>$user->roles->map(function ($rol) {
                    return [
                        "rol"=> $rol->slug,
                        "nivel"=> $rol->level,
                    ];
                }),
                "permisos"=>$objetoPermisos,

            ];

        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status'=>'ok',
            'usuario'=>$data
        ];
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();
        } catch (\Exception $e) {
            throw $e;
        }
        return [
            'status'=>'ok',
            'mensaje'=>'Usuario deslogueado correctamente.'
        ];
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $expira_en_minutos = null;
        $fecha_expiracion = null;

        try {
            $expira_en_minutos = Auth::factory()->getTTL();
            $fecha_expiracion = Carbon::now()->addMinutes($expira_en_minutos)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status'=> 'ok',
            'data' => [
                'token' => $token,
                'tipo' => 'bearer',
                'expira_en' => $expira_en_minutos * 60,
                'fecha_expiracion' => $fecha_expiracion
            ]
        ];
    }


    public function cambiarContrasena(CambiarContrasenaRequest $request){

        $nuevaContrasena = $request->nueva_contrasena;
        $usuario = null;

        try {
            $usuario = auth()->user();
            $usuario->password = $nuevaContrasena;
            $usuario->save();
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
            'mensaje' => 'Cambió su contraseña con éxito.'
        ];
    }

    // public function register(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'nombre' => 'string',
    //         'apellido' => 'required|string',
    //         'email' => 'required|string|email:rfc|max:100|unique:users',
    //         'password' => 'required|string|min:6'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }

    //     $user = User::create(array_merge(
    //         $validator->validate(),
    //         ['password' => bcrypt($request->password)]
    //     ));

    //     return response()->json([
    //         'status' => 'ok',
    //         'mensaje' => 'Usuario registrado exitosamente!'
    //     ], 201);
    // }
}

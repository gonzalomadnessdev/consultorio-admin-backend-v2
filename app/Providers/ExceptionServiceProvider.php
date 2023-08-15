<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

        /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Validation\ValidationException $exception) {
            $erroresTodos = $exception->errors();
            $mensaje_error = [];
            foreach ($erroresTodos as $errores) {
                foreach ($errores as $error) {
                    $mensaje_error[] = $error;
                }
            }
            $mensaje = implode(" ", $mensaje_error);
            return response()->json([
                'status' => 'error',
                'mensaje' => $mensaje
            ], 400);
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Auth\AuthenticationException $exception) {
            return response()->json([
                'status' => 'error',
                'mensaje' => "No autorizado."
            ], 401);
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $exception) {
            $mensaje = null;
            if ($exception->getMessage() == "The token has been blacklisted") {
                $mensaje = "El Token ha sido deshabilitado.";
            } else if ($exception->getMessage() == "Token has expired") {
                $mensaje = "El Token ha expirado.";
            } else if ($exception->getMessage() == "Token not provided") {
                $mensaje = "El Token no ha sido proporcionado.";
            } else if ($exception->getMessage() == "Token Signature could not be verified.") {
                $mensaje = "El Token no pudo ser verificado.";
            } else if ($exception->getMessage() == "Wrong number of segments") {
                $mensaje = "El Token esta mal formado.";
            } else if (mb_strpos($exception->getMessage(), 'Could not decode token:') !== false) {
                $mensaje = "El Token no pudo ser verificado.";
            } else {
                $mensaje = $exception->getMessage();
            }

            return response()->json([
                'status' => 'error',
                'mensaje' => $mensaje
            ], 401);
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $exception) {
            $mensaje = null;
            $mensaje = $exception->getMessage();

            return response()->json([
                'status' => 'error',
                'mensaje' => $mensaje
            ], 403);
        });

        app('Dingo\Api\Exception\Handler')->register(function (HttpException $exception) {
            $mensaje = $exception->getMessage();
            $statusCode = $exception->getStatusCode();

            if ($statusCode == 404) {
                $mensaje = "No se ha encontrado la ruta";
            }

            return response()->json([
                'status' => 'error',
                'mensaje' => $mensaje
            ], $exception->getStatusCode());
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Throwable $exception) {
            $mensaje = 'Ha ocurrido un error.';
            return response()->json([
                'status' => 'error',
                'mensaje' => $mensaje
            ], 500);
        });
    }
}

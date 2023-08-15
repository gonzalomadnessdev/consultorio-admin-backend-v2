<?php

use Dingo\Api\Routing\Router;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

/** @var Router $api */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function (Router $api) {

    /*Prefix 'auth'*/
    $api->group(['prefix' => 'auth'], function (Router $api) {
        $api->post('login', 'App\Api\V1\Controllers\AuthController@login');

        $api->group(['middleware' => ['jwt.auth']], function (Router $api) {
            $api->post('logout', 'App\Api\V1\Controllers\AuthController@logout');
            $api->post('refresh', 'App\Api\V1\Controllers\AuthController@refresh');
            $api->get('me', 'App\Api\V1\Controllers\AuthController@me');
            $api->post('cambiar-contrasena', 'App\Api\V1\Controllers\AuthController@cambiarContrasena');
        });
    });

    $api->group(['middleware' => ['jwt.auth']], function (Router $api) {

        /*Usuarios*/
        $api->group(['middleware' => ['tienePermiso:usuarios.ver']], function (Router $api) {
            $api->get('usuarios', 'App\Api\V1\Controllers\UserController@mostrarUsuarios');
            $api->get('usuarios/{id}', 'App\Api\V1\Controllers\UserController@mostrarUsuario')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:usuarios.crear']], function (Router $api) {
            $api->post('usuarios', 'App\Api\V1\Controllers\UserController@crearUsuario');
        });

        $api->group(['middleware' => ['tienePermiso:usuarios.editar']], function (Router $api) {
            $api->put('usuarios/{id}', 'App\Api\V1\Controllers\UserController@modificarUsuario')->where('id', '[0-9]+');
            $api->post('usuarios/cambiar-contrasena', 'App\Api\V1\Controllers\UserController@cambiarContrasena');
        });

        $api->group(['middleware' => ['tienePermiso:usuarios.eliminar']], function (Router $api) {
            $api->delete('usuarios/{id}', 'App\Api\V1\Controllers\UserController@borrarUsuario')->where('id', '[0-9]+');
        });

        /*Roles*/
        $api->group(['middleware' => ['tienePermiso:roles.ver']], function (Router $api) {
            $api->get('roles', 'App\Api\V1\Controllers\RolesYPermisosController@mostrarRoles');
            $api->get('roles/{id}', 'App\Api\V1\Controllers\RolesYPermisosController@mostrarRol')->where('id', '[0-9]+');
            $api->get('permisos', 'App\Api\V1\Controllers\RolesYPermisosController@mostrarPermisos');
        });

        $api->group(['middleware' => ['tienePermiso:roles.crear']], function (Router $api) {
            $api->post('roles', 'App\Api\V1\Controllers\RolesYPermisosController@crearRol');
        });

        $api->group(['middleware' => ['tienePermiso:roles.editar']], function (Router $api) {
            $api->put('roles/{id}', 'App\Api\V1\Controllers\RolesYPermisosController@modificarRol')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:roles.eliminar']], function (Router $api) {
            $api->delete('roles/{id}', 'App\Api\V1\Controllers\RolesYPermisosController@borrarRol')->where('id', '[0-9]+');
        });

        /*Profesionales*/
        $api->group(['middleware' => ['tienePermiso:profesionales.ver']], function (Router $api) {
            $api->get('profesionales', 'App\Api\V1\Controllers\ProfesionalController@mostrarProfesionales');
            $api->get('profesionales/{id}', 'App\Api\V1\Controllers\ProfesionalController@mostrarProfesional')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:profesionales.crear']], function (Router $api) {
            $api->post('profesionales', 'App\Api\V1\Controllers\ProfesionalController@crearProfesional');
        });

        $api->group(['middleware' => ['tienePermiso:profesionales.editar']], function (Router $api) {
            $api->put('profesionales/{id}', 'App\Api\V1\Controllers\ProfesionalController@modificarProfesional')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:profesionales.eliminar']], function (Router $api) {
            $api->delete('profesionales/{id}', 'App\Api\V1\Controllers\ProfesionalController@borrarProfesional')->where('id', '[0-9]+');
        });

        /*Pacientes*/
        $api->group(['middleware' => ['tienePermiso:pacientes.ver']], function (Router $api) {
            $api->get('pacientes', 'App\Api\V1\Controllers\PacienteController@mostrarPacientes');
            $api->get('pacientes/{id}', 'App\Api\V1\Controllers\PacienteController@mostrarPaciente')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:pacientes.crear']], function (Router $api) {
            $api->post('pacientes', 'App\Api\V1\Controllers\PacienteController@crearPaciente');
        });

        $api->group(['middleware' => ['tienePermiso:pacientes.editar']], function (Router $api) {
            $api->put('pacientes/{id}', 'App\Api\V1\Controllers\PacienteController@modificarPaciente')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:pacientes.eliminar']], function (Router $api) {
            $api->delete('pacientes/{id}', 'App\Api\V1\Controllers\PacienteController@borrarPaciente')->where('id', '[0-9]+');
        });

        /* Consultorios */
        $api->group(['middleware' => ['tienePermiso:consultorios.ver']], function (Router $api) {
            $api->get('consultorios', 'App\Api\V1\Controllers\ConsultorioController@mostrarConsultorios');
            $api->get('consultorios/{id}', 'App\Api\V1\Controllers\ConsultorioController@mostrarConsultorio')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:consultorios.crear']], function (Router $api) {
            $api->post('consultorios', 'App\Api\V1\Controllers\ConsultorioController@crearConsultorio');
        });

        $api->group(['middleware' => ['tienePermiso:consultorios.editar']], function (Router $api) {
            $api->put('consultorios/{id}', 'App\Api\V1\Controllers\ConsultorioController@modificarConsultorio')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:consultorios.eliminar']], function (Router $api) {
            $api->delete('consultorios/{id}', 'App\Api\V1\Controllers\ConsultorioController@borrarConsultorio')->where('id', '[0-9]+');
        });

        /*Turnos*/
        $api->group(['middleware' => ['tienePermiso:turnos.ver']], function (Router $api) {
            $api->get('turnos', 'App\Api\V1\Controllers\TurnoController@mostrarTurnos');
            $api->get('turnos/formulario', 'App\Api\V1\Controllers\TurnoController@obtenerInfoFormulario');
            $api->get('turnos/{id}', 'App\Api\V1\Controllers\TurnoController@mostrarTurno')->where('id', '[0-9]+');
            $api->get('turnos/send-message', 'App\Api\V1\Controllers\TurnoController@enviarMensajeTest');
        });

        $api->group(['middleware' => ['tienePermiso:turnos.crear']], function (Router $api) {
            $api->post('turnos', 'App\Api\V1\Controllers\TurnoController@crearTurno');
        });

        $api->group(['middleware' => ['tienePermiso:turnos.editar']], function (Router $api) {
            $api->put('turnos/{id}', 'App\Api\V1\Controllers\TurnoController@modificarTurno')->where('id', '[0-9]+');
        });

        $api->group(['middleware' => ['tienePermiso:turnos.eliminar']], function (Router $api) {
            $api->delete('turnos/{id}', 'App\Api\V1\Controllers\TurnoController@borrarTurno')->where('id', '[0-9]+');
        });


    });

    /*Prefix 'test'*/
    $api->group(['prefix' => 'test'], function (Router $api) {
        $api->post('hash', 'App\Api\V1\Controllers\TestController@hash');
    });
});

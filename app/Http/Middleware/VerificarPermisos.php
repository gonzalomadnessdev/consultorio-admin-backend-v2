<?php

namespace App\Http\Middleware;

use jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException;

class VerificarPermisos extends VerifyPermission
{
/**
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request    $request
     * @param \Closure   $next
     * @param int|string $permission
     *
     * @throws \jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permission)
    {
        if(auth()->user()->hasAllPermissions($permission)){
            return $next($request);
        }
        $permission = join(',', $permission);
        throw new PermissionDeniedException($permission);
    }
}

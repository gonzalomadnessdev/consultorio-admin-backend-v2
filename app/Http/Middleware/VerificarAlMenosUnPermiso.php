<?php

namespace App\Http\Middleware;

use jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyPermission;
use Closure;
use Illuminate\Http\Request;
use jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException;

class VerificarAlMenosUnPermiso extends VerifyPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next , ...$permission)
    {
        if(auth()->user()->hasOnePermission($permission)){
            return $next($request);
        }
        $permission = join(',', $permission);
        throw new PermissionDeniedException($permission);
    }
}

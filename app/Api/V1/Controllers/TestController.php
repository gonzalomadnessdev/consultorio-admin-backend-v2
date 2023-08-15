<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestController extends Controller
{
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

        return response()->json(
            [
                'status' => 'ok',
                'profesional' => $profesional
            ]
        );
    }

    public function hash(Request $request){
        return Hash::make($request->value);
    }
}

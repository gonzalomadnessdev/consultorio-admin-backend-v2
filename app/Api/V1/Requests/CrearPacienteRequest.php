<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearPacienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre' => 'required|min:3|max:150|string',
            'apellido' => 'required|min:3|max:150|string',
            'documento' => 'required|integer|digits_between:5,8|unique:pacientes',
            'email' => 'present|nullable|string|email:rfc|max:100|unique:pacientes',
            'telefono' => 'present|nullable|integer|digits_between:6,20',
            'direccion' => 'present|nullable|min:3|max:150|string',
            'observaciones' => 'present|nullable|string',
        ];
    }
}

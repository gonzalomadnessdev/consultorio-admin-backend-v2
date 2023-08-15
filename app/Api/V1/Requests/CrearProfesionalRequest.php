<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearProfesionalRequest extends FormRequest
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
            'documento' => 'required|integer|digits_between:5,8|unique:profesionales',
            'matricula' => 'required|integer|digits_between:5,10|unique:profesionales',
            'email' => 'present|nullable|string|email:rfc|max:100|unique:profesionales',
            'telefono' => 'present|nullable|integer|digits_between:6,20',
        ];
    }
}

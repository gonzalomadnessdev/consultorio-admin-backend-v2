<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MostrarUsuariosRequest extends FormRequest
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
            'email' => 'nullable',
            'fecha_desde' => 'nullable|date_format:Y-m-d|required_with:fecha_hasta',
            'fecha_hasta' => 'nullable|date_format:Y-m-d|required_with:fecha_desde',
            'paginado' => 'nullable|boolean',
        ];
    }
}

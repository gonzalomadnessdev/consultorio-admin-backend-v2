<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearTurnoRequest extends FormRequest
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
            'profesional_id' => 'required|integer|exists:profesionales,id',
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'consultorio_id' => 'required|integer|exists:consultorios,id',
            'fecha_hora_desde' => 'required|date_format:Y-m-d H:i:s',
            'fecha_hora_hasta' => 'required|date_format:Y-m-d H:i:s',
            'titulo' => 'required|min:3|max:150|string',
            'descripcion' => 'present|nullable|string',
        ];
    }
}

<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModificarProfesionalRequest extends FormRequest
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
            'documento' => ['required','integer','digits_between:5,8',
                Rule::unique('profesionales')->ignore($this->id),
            ],
            'matricula' => ['required','integer','digits_between:5,10',
                Rule::unique('profesionales')->ignore($this->id),
            ],
            'email' => ['present','nullable','string','email:rfc','max:100',
                Rule::unique('profesionales')->ignore($this->id),
            ],
            'telefono' => 'present|nullable|integer|digits_between:6,20',
        ];
    }
}

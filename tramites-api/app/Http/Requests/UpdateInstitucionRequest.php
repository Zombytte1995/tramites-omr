<?php

namespace App\Http\Requests;

use App\Enums\TipoInstitucion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('institucion');

        return [
            'nombre' => [
                'sometimes', 'required', 'string', 'max:150',
                Rule::unique('instituciones', 'nombre')->ignore($id),
            ],
            'tipo' => [
                'sometimes', 'required', 'string',
                Rule::in(array_column(TipoInstitucion::cases(), 'value')),
            ],
        ];
    }

    public function messages(): array
    {
        $valores = implode(', ', array_column(TipoInstitucion::cases(), 'value'));

        return [
            'nombre.required' => 'El nombre de la institución es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 150 caracteres.',
            'nombre.unique' => 'Ya existe otra institución registrada con ese nombre.',
            'tipo.required' => 'El tipo de institución es obligatorio.',
            'tipo.in' => "El tipo debe ser uno de los siguientes valores: {$valores}.",
        ];
    }
}

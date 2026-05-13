<?php

namespace App\Http\Requests;

use App\Enums\TipoInstitucion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:instituciones,nombre'],
            // Rule::in() en lugar de Rule::enum() para que messages() lo pueda sobrescribir
            'tipo' => ['required', 'string', Rule::in(array_column(TipoInstitucion::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        $valores = implode(', ', array_column(TipoInstitucion::cases(), 'value'));

        return [
            'nombre.required' => 'El nombre de la institución es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 150 caracteres.',
            'nombre.unique' => 'Ya existe una institución registrada con ese nombre.',
            'tipo.required' => 'El tipo de institución es obligatorio.',
            'tipo.in' => "El tipo debe ser uno de los siguientes valores: {$valores}.",
        ];
    }
}

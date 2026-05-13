<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTramiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:20', 'unique:tramites,codigo'],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['required', 'string'],
            'institucion_id' => ['required', 'integer', 'exists:instituciones,id'],
            'dias_habiles' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del trámite es obligatorio.',
            'codigo.max' => 'El código no puede exceder los 20 caracteres.',
            'codigo.unique' => 'Ya existe un trámite con este código.',
            'nombre.required' => 'El nombre del trámite es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 200 caracteres.',
            'descripcion.required' => 'La descripción del trámite es obligatoria.',
            'institucion_id.required' => 'Debe indicar la institución responsable.',
            'institucion_id.integer' => 'El identificador de institución debe ser un número entero.',
            'institucion_id.exists' => 'La institución seleccionada no existe.',
            'dias_habiles.required' => 'Los días hábiles son obligatorios.',
            'dias_habiles.integer' => 'Los días hábiles deben ser un número entero.',
            'dias_habiles.min' => 'Los días hábiles deben ser al menos 1.',
            'dias_habiles.max' => 'Los días hábiles no pueden superar los 365.',
        ];
    }
}

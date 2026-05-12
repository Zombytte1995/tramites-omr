<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Institucion */
class InstitucionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'nombre' => $this->nombre,
            'tipo'   => [
                'valor'    => $this->tipo->value,
                'etiqueta' => $this->tipo->label(),
            ],
            'activo'     => $this->activo,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tramite extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'institucion_id',
        'dias_habiles',
        'activo',
    ];

    protected $casts = [
        'activo'      => 'boolean',
        'dias_habiles' => 'integer',
    ];

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeInstitucion($query, int $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }
}

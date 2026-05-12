<?php

namespace App\Models;

use App\Enums\TipoInstitucion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'tipo',
        'activo',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    protected $casts = [
        'tipo'   => TipoInstitucion::class,
        'activo' => 'boolean',
    ];

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class);
    }

    public function tramitesActivos(): HasMany
    {
        return $this->hasMany(Tramite::class)->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}

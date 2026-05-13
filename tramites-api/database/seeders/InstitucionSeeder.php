<?php

namespace Database\Seeders;

use App\Enums\TipoInstitucion;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class InstitucionSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = [
            [
                'nombre' => 'Ministerio de Salud',
                'tipo' => TipoInstitucion::MINISTERIO,
                'activo' => true,
            ],
            [
                'nombre' => 'Ministerio de Educación',
                'tipo' => TipoInstitucion::MINISTERIO,
                'activo' => true,
            ],
            [
                'nombre' => 'Alcaldía Municipal de San Salvador',
                'tipo' => TipoInstitucion::ALCALDIA,
                'activo' => true,
            ],
            [
                'nombre' => 'Corte Suprema de Justicia',
                'tipo' => TipoInstitucion::AUTONOMA,
                'activo' => true,
            ],
            [
                'nombre' => 'Instituto Salvadoreño de Seguro Social',
                'tipo' => TipoInstitucion::AUTONOMA,
                'activo' => true,
            ],
        ];

        foreach ($instituciones as $data) {
            Institucion::firstOrCreate(['nombre' => $data['nombre']], $data);
        }
    }
}

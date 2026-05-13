<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Tramite;
use Illuminate\Database\Eloquent\Factories\Factory;

class TramiteFactory extends Factory
{
    protected $model = Tramite::class;

    private static array $tramites = [
        ['nombre' => 'Registro de Nacimiento',             'dias' => 5],
        ['nombre' => 'Certificación de Acta de Nacimiento', 'dias' => 3],
        ['nombre' => 'Inscripción de Matrimonio',          'dias' => 10],
        ['nombre' => 'Declaración de Defunción',           'dias' => 3],
        ['nombre' => 'Solicitud de Permiso de Construcción', 'dias' => 30],
        ['nombre' => 'Licencia de Funcionamiento Comercial', 'dias' => 15],
        ['nombre' => 'Registro de Marca Comercial',        'dias' => 60],
        ['nombre' => 'Inscripción en el Registro de Comercio', 'dias' => 10],
        ['nombre' => 'Certificación de Antecedentes Penales', 'dias' => 2],
        ['nombre' => 'Solicitud de Pasaporte',             'dias' => 5],
        ['nombre' => 'Renovación de DUI',                  'dias' => 1],
        ['nombre' => 'Solvencia Municipal',                'dias' => 1],
        ['nombre' => 'Permiso de Importación',             'dias' => 20],
        ['nombre' => 'Registro Sanitario de Alimentos',    'dias' => 45],
        ['nombre' => 'Autorización de Apertura de Farmacia', 'dias' => 30],
        ['nombre' => 'Inscripción en el ISSS',             'dias' => 5],
        ['nombre' => 'Calificación de Discapacidad',       'dias' => 15],
        ['nombre' => 'Registro de ONG',                    'dias' => 30],
        ['nombre' => 'Certificación de Notas (MINED)',     'dias' => 5],
        ['nombre' => 'Reconocimiento de Títulos Extranjeros', 'dias' => 60],
    ];

    private static int $codigoCounter = 1;

    public function definition(): array
    {
        $tramite = $this->faker->randomElement(self::$tramites);
        $codigo = 'TRM-'.str_pad(self::$codigoCounter++, 4, '0', STR_PAD_LEFT);

        return [
            'codigo' => $codigo,
            'nombre' => $tramite['nombre'],
            'descripcion' => $this->generarDescripcion($tramite['nombre']),
            'institucion_id' => Institucion::factory(),
            'dias_habiles' => $tramite['dias'],
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }

    public function paraInstitucion(Institucion $institucion): static
    {
        return $this->state(['institucion_id' => $institucion->id]);
    }

    private function generarDescripcion(string $nombre): string
    {
        return "Trámite de {$nombre} requerido por los ciudadanos ante la institución competente. "
            .'Presentar documentación original y copia. '
            .'Costo sujeto a arancel vigente publicado en el Diario Oficial.';
    }
}

<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Tramite;
use Illuminate\Database\Seeder;

class TramiteSeeder extends Seeder
{
    public function run(): void
    {
        $minsal  = Institucion::where('nombre', 'Ministerio de Salud')->first();
        $mined   = Institucion::where('nombre', 'Ministerio de Educación')->first();
        $alcaldia = Institucion::where('nombre', 'Alcaldía Municipal de San Salvador')->first();
        $csj     = Institucion::where('nombre', 'Corte Suprema de Justicia')->first();
        $isss    = Institucion::where('nombre', 'Instituto Salvadoreño de Seguro Social')->first();

        $tramites = [
            [
                'codigo'        => 'TRM-0001',
                'nombre'        => 'Registro Sanitario de Alimentos',
                'descripcion'   => 'Autorización otorgada por el MINSAL para la comercialización de alimentos procesados. '
                    . 'Requiere análisis de laboratorio, ficha técnica del producto y plano de planta de producción.',
                'institucion_id' => $minsal->id,
                'dias_habiles'  => 45,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0002',
                'nombre'        => 'Autorización de Apertura de Farmacia',
                'descripcion'   => 'Permiso para establecer y operar una farmacia o botica. '
                    . 'Se requiere regente farmacéutico habilitado, planos del local y cumplimiento de normativa MINSAL.',
                'institucion_id' => $minsal->id,
                'dias_habiles'  => 30,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0003',
                'nombre'        => 'Certificación de Notas de Bachillerato',
                'descripcion'   => 'Documento oficial que certifica las calificaciones obtenidas durante el bachillerato. '
                    . 'Presentar DUI o partida de nacimiento, más constancia del centro educativo.',
                'institucion_id' => $mined->id,
                'dias_habiles'  => 5,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0004',
                'nombre'        => 'Reconocimiento de Títulos Extranjeros',
                'descripcion'   => 'Proceso de validación de títulos académicos obtenidos en el extranjero '
                    . 'para su reconocimiento oficial en El Salvador. Requiere apostilla y traducción oficial.',
                'institucion_id' => $mined->id,
                'dias_habiles'  => 60,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0005',
                'nombre'        => 'Solvencia Municipal',
                'descripcion'   => 'Constancia emitida por la Alcaldía que certifica que el ciudadano o empresa '
                    . 'está al día con sus obligaciones tributarias municipales.',
                'institucion_id' => $alcaldia->id,
                'dias_habiles'  => 1,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0006',
                'nombre'        => 'Licencia de Funcionamiento Comercial',
                'descripcion'   => 'Autorización municipal para el funcionamiento de establecimientos comerciales, '
                    . 'industriales o de servicios dentro del municipio de San Salvador.',
                'institucion_id' => $alcaldia->id,
                'dias_habiles'  => 15,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0007',
                'nombre'        => 'Permiso de Construcción',
                'descripcion'   => 'Autorización para iniciar obras de construcción, ampliación o remodelación '
                    . 'de inmuebles. Requiere planos firmados por ingeniero o arquitecto habilitado.',
                'institucion_id' => $alcaldia->id,
                'dias_habiles'  => 30,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0008',
                'nombre'        => 'Certificación de Antecedentes Penales',
                'descripcion'   => 'Documento emitido por la Corte Suprema de Justicia que acredita si una persona '
                    . 'posee o no antecedentes penales en El Salvador.',
                'institucion_id' => $csj->id,
                'dias_habiles'  => 2,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0009',
                'nombre'        => 'Inscripción Patronal en el ISSS',
                'descripcion'   => 'Registro de empresas y empleadores ante el Instituto Salvadoreño de Seguro Social '
                    . 'para la afiliación de trabajadores al sistema de seguridad social.',
                'institucion_id' => $isss->id,
                'dias_habiles'  => 5,
                'activo'        => true,
            ],
            [
                'codigo'        => 'TRM-0010',
                'nombre'        => 'Calificación de Invalidez',
                'descripcion'   => 'Proceso de evaluación médica realizado por el ISSS para determinar el grado '
                    . 'de invalidez de un asegurado y su derecho a prestaciones económicas.',
                'institucion_id' => $isss->id,
                'dias_habiles'  => 20,
                'activo'        => true,
            ],
        ];

        foreach ($tramites as $data) {
            Tramite::firstOrCreate(['codigo' => $data['codigo']], $data);
        }
    }
}

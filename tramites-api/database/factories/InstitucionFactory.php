<?php

namespace Database\Factories;

use App\Enums\TipoInstitucion;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    private static array $ministerios = [
        'Ministerio de Agricultura y Ganadería',
        'Ministerio de Cultura',
        'Ministerio de Economía',
        'Ministerio de Gobernación y Desarrollo Territorial',
        'Ministerio de Justicia y Seguridad Pública',
        'Ministerio de Medio Ambiente y Recursos Naturales',
        'Ministerio de Obras Públicas',
        'Ministerio de Relaciones Exteriores',
        'Ministerio de Turismo',
        'Ministerio de Vivienda',
    ];

    private static array $alcaldias = [
        'Alcaldía Municipal de Antiguo Cuscatlán',
        'Alcaldía Municipal de Apopa',
        'Alcaldía Municipal de Ciudad Delgado',
        'Alcaldía Municipal de Cuscatancingo',
        'Alcaldía Municipal de Ilopango',
        'Alcaldía Municipal de Mejicanos',
        'Alcaldía Municipal de San Marcos',
        'Alcaldía Municipal de Santa Tecla',
        'Alcaldía Municipal de Soyapango',
        'Alcaldía Municipal de Tonacatepeque',
    ];

    private static array $autonomas = [
        'Banco Central de Reserva de El Salvador',
        'Comisión Nacional de la Micro y Pequeña Empresa',
        'Defensoría del Consumidor',
        'Fondo de Inversión Social para el Desarrollo Local',
        'Fondo Nacional de Vivienda Popular',
        'Instituto Nacional de Pensiones de los Empleados Públicos',
        'Instituto Salvadoreño de Seguro Social',
        'Lotería Nacional de Beneficencia',
        'Superintendencia del Sistema Financiero',
        'Universidad de El Salvador',
    ];

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(TipoInstitucion::cases());

        $nombre = match ($tipo) {
            TipoInstitucion::MINISTERIO => $this->faker->unique()->randomElement(self::$ministerios),
            TipoInstitucion::ALCALDIA   => $this->faker->unique()->randomElement(self::$alcaldias),
            TipoInstitucion::AUTONOMA   => $this->faker->unique()->randomElement(self::$autonomas),
        };

        return [
            'nombre' => $nombre,
            'tipo'   => $tipo,
            'activo' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(['activo' => false]);
    }

    public function ministerio(): static
    {
        return $this->state([
            'tipo'   => TipoInstitucion::MINISTERIO,
            'nombre' => $this->faker->unique()->randomElement(self::$ministerios),
        ]);
    }

    public function alcaldia(): static
    {
        return $this->state([
            'tipo'   => TipoInstitucion::ALCALDIA,
            'nombre' => $this->faker->unique()->randomElement(self::$alcaldias),
        ]);
    }

    public function autonoma(): static
    {
        return $this->state([
            'tipo'   => TipoInstitucion::AUTONOMA,
            'nombre' => $this->faker->unique()->randomElement(self::$autonomas),
        ]);
    }
}

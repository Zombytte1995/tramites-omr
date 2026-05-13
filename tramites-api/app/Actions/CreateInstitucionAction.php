<?php

namespace App\Actions;

use App\Models\Institucion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;

class CreateInstitucionAction
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Crea una nueva institución con los datos proporcionados.
     *
     * @param  array{nombre: string, tipo: string, activo?: bool}  $data
     */
    public function execute(array $data): Institucion
    {
        return $this->instituciones->create($data);
    }
}

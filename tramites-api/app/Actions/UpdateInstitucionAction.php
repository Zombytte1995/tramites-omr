<?php

namespace App\Actions;

use App\Models\Institucion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;

class UpdateInstitucionAction
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Actualiza los campos permitidos de una institución.
     *
     * @param  array{nombre?: string, tipo?: string}  $data
     */
    public function execute(int $id, array $data): Institucion
    {
        return $this->instituciones->update($id, $data);
    }
}

<?php

namespace App\Actions;

use App\Models\Institucion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListInstitucionesAction
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Retorna todas las instituciones activas ordenadas por nombre.
     *
     * @return Collection<int, Institucion>
     */
    public function execute(): Collection
    {
        return $this->instituciones->allActive();
    }
}

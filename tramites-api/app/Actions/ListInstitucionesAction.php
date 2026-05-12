<?php

namespace App\Actions;

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
     * @return Collection<int, \App\Models\Institucion>
     */
    public function execute(): Collection
    {
        return $this->instituciones->allActive();
    }
}

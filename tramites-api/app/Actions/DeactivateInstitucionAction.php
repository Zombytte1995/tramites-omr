<?php

namespace App\Actions;

use App\Repositories\Contracts\InstitucionRepositoryInterface;
use DomainException;

class DeactivateInstitucionAction
{
    public function __construct(
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Desactiva una institución (soft delete lógico: activo = false).
     *
     * @throws DomainException Si la institución tiene trámites activos asociados.
     */
    public function execute(int $id): void
    {
        $institucion = $this->instituciones->find($id);

        if ($institucion->tramitesActivos()->exists()) {
            throw new DomainException(
                "No se puede desactivar \"{$institucion->nombre}\" porque tiene trámites activos asociados. Desactiva los trámites primero."
            );
        }

        $this->instituciones->deactivate($id);
    }
}

<?php

namespace App\Actions;

use App\Repositories\Contracts\TramiteRepositoryInterface;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateTramiteAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
    ) {}

    /**
     * Desactiva un trámite (soft delete lógico: activo = false).
     *
     * Regla de negocio: desactivar un trámite ya inactivo es una operación
     * sin sentido que indica un error en el flujo del llamador (por ejemplo,
     * doble clic en el botón de desactivar en el frontend).
     *
     *
     * @throws ModelNotFoundException Si el trámite no existe.
     * @throws DomainException Si el trámite ya está inactivo.
     */
    public function execute(int $id): void
    {
        $tramite = $this->tramites->find($id);

        if (! $tramite->activo) {
            throw new DomainException(
                "El trámite \"{$tramite->nombre}\" ya se encuentra inactivo."
            );
        }

        $this->tramites->deactivate($id);
    }
}

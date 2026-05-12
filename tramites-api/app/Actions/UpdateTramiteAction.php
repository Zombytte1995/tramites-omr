<?php

namespace App\Actions;

use App\Models\Tramite;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use DomainException;

class UpdateTramiteAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Actualiza los campos de un trámite existente.
     *
     * Regla de negocio: si se cambia la institución, la nueva debe estar activa.
     * No se permite reasignar un trámite a una institución inactiva porque
     * implicaría que el trámite deja de ser gestionable.
     *
     * @param  int  $id
     * @param  array{
     *     codigo?: string,
     *     nombre?: string,
     *     descripcion?: string,
     *     institucion_id?: int,
     *     dias_habiles?: int,
     * }  $data  Datos ya validados por el Form Request.
     * @return Tramite  La instancia actualizada con la relación `institucion` refrescada.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  Si el trámite o la institución no existen.
     * @throws DomainException  Si la nueva institución está inactiva.
     */
    public function execute(int $id, array $data): Tramite
    {
        if (isset($data['institucion_id'])) {
            $institucion = $this->instituciones->find($data['institucion_id']);

            if (! $institucion->activo) {
                throw new DomainException(
                    "No se puede reasignar el trámite a \"{$institucion->nombre}\" porque está inactiva."
                );
            }
        }

        return $this->tramites->update($id, $data);
    }
}

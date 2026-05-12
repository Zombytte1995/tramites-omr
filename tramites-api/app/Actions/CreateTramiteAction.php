<?php

namespace App\Actions;

use App\Models\Tramite;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use DomainException;

class CreateTramiteAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
        private readonly InstitucionRepositoryInterface $instituciones,
    ) {}

    /**
     * Crea un nuevo trámite verificando que la institución destino esté activa.
     *
     * Regla de negocio: no se pueden registrar trámites bajo instituciones
     * inactivas, ya que dejarían de ser accesibles para los ciudadanos.
     *
     * @param  array{
     *     codigo: string,
     *     nombre: string,
     *     descripcion: string,
     *     institucion_id: int,
     *     dias_habiles: int,
     * }  $data  Datos ya validados por el Form Request.
     * @return Tramite  La instancia creada con la relación `institucion` cargada.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  Si la institución no existe.
     * @throws DomainException  Si la institución está inactiva.
     */
    public function execute(array $data): Tramite
    {
        $institucion = $this->instituciones->find($data['institucion_id']);

        if (! $institucion->activo) {
            throw new DomainException(
                "No se pueden crear trámites para la institución \"{$institucion->nombre}\" porque está inactiva."
            );
        }

        return $this->tramites->create($data);
    }
}

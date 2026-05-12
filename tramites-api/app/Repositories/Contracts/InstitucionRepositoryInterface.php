<?php

namespace App\Repositories\Contracts;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Collection;

interface InstitucionRepositoryInterface
{
    /**
     * Retorna todas las instituciones con activo = true, ordenadas por nombre.
     *
     * @return Collection<int, Institucion>
     */
    public function allActive(): Collection;

    /**
     * Busca una institución por su ID.
     *
     * @param  int  $id
     * @return Institucion
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int $id): Institucion;

    /**
     * Crea una nueva institución con los datos proporcionados.
     *
     * @param  array{nombre: string, tipo: string, activo?: bool}  $data
     * @return Institucion  La instancia recién persistida.
     */
    public function create(array $data): Institucion;
}

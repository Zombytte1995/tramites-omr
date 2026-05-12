<?php

namespace App\Repositories\Contracts;

use App\Models\Tramite;
use Illuminate\Pagination\LengthAwarePaginator;

interface TramiteRepositoryInterface
{
    /**
     * Retorna un listado paginado de trámites activos.
     *
     * Filtra opcionalmente por institución y por búsqueda de texto en nombre.
     * Los resultados incluyen la relación `institucion` para evitar N+1.
     *
     * @param  int       $perPage      Número de registros por página.
     * @param  int|null  $institucionId  Filtra por institución si se proporciona.
     * @param  string|null  $search    Filtra por nombre (LIKE %search%) si se proporciona.
     * @return LengthAwarePaginator<Tramite>
     */
    public function paginate(int $perPage, ?int $institucionId, ?string $search): LengthAwarePaginator;

    /**
     * Busca un trámite por su ID, cargando la relación `institucion`.
     *
     * @param  int  $id
     * @return Tramite
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int $id): Tramite;

    /**
     * Crea un nuevo trámite con los datos proporcionados.
     *
     * @param  array{
     *     codigo: string,
     *     nombre: string,
     *     descripcion: string,
     *     institucion_id: int,
     *     dias_habiles: int,
     *     activo?: bool
     * }  $data
     * @return Tramite  La instancia recién persistida, con la relación `institucion` cargada.
     */
    public function create(array $data): Tramite;

    /**
     * Actualiza los campos permitidos de un trámite existente.
     *
     * @param  int    $id
     * @param  array{
     *     codigo?: string,
     *     nombre?: string,
     *     descripcion?: string,
     *     institucion_id?: int,
     *     dias_habiles?: int
     * }  $data
     * @return Tramite  La instancia actualizada, con la relación `institucion` refrescada.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Tramite;

    /**
     * Desactiva un trámite estableciendo activo = false (soft delete lógico).
     *
     * No elimina el registro de la base de datos.
     *
     * @param  int  $id
     * @return bool  True si la operación fue exitosa.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deactivate(int $id): bool;
}

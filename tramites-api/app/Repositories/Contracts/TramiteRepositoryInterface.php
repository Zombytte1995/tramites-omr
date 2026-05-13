<?php

namespace App\Repositories\Contracts;

use App\Models\Tramite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TramiteRepositoryInterface
{
    /**
     * Retorna un listado paginado de trámites activos.
     *
     * Filtra opcionalmente por institución y por búsqueda de texto en nombre.
     * Los resultados incluyen la relación `institucion` para evitar N+1.
     *
     * @param  int  $perPage  Número de registros por página.
     * @param  int|null  $institucionId  Filtra por institución si se proporciona.
     * @param  string|null  $search  Filtra por nombre (LIKE %search%) si se proporciona.
     * @return LengthAwarePaginator<Tramite>
     */
    public function paginate(int $perPage, ?int $institucionId, ?string $search): LengthAwarePaginator;

    /**
     * Busca un trámite por su ID, cargando la relación `institucion`.
     *
     *
     * @throws ModelNotFoundException
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
     * @return Tramite La instancia recién persistida, con la relación `institucion` cargada.
     */
    public function create(array $data): Tramite;

    /**
     * Actualiza los campos permitidos de un trámite existente.
     *
     * @param  array{
     *     codigo?: string,
     *     nombre?: string,
     *     descripcion?: string,
     *     institucion_id?: int,
     *     dias_habiles?: int
     * }  $data
     * @return Tramite La instancia actualizada, con la relación `institucion` refrescada.
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Tramite;

    /**
     * Desactiva un trámite estableciendo activo = false (soft delete lógico).
     *
     * No elimina el registro de la base de datos.
     *
     * @return bool True si la operación fue exitosa.
     *
     * @throws ModelNotFoundException
     */
    public function deactivate(int $id): bool;

    /**
     * Retorna todos los trámites activos (sin paginar) aplicando los mismos
     * filtros que paginate(). Se usa exclusivamente para la exportación a Excel.
     *
     * @param  int|null  $institucionId  Restringe por institución.
     * @param  string|null  $search  Filtra por nombre (LIKE %search%).
     * @return Collection<int, Tramite>
     */
    public function getForExport(?int $institucionId, ?string $search): Collection;
}

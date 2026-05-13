<?php

namespace App\Actions;

use App\Models\Tramite;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListTramitesAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
    ) {}

    /**
     * Retorna el listado paginado de trámites activos con filtros opcionales.
     *
     * @param  int  $perPage  Registros por página (default: 10).
     * @param  int|null  $institucionId  Restringe los resultados a una institución.
     * @param  string|null  $search  Filtro por nombre (búsqueda parcial).
     * @return LengthAwarePaginator<Tramite>
     */
    public function execute(
        int $perPage = 10,
        ?int $institucionId = null,
        ?string $search = null,
    ): LengthAwarePaginator {
        return $this->tramites->paginate($perPage, $institucionId, $search);
    }
}

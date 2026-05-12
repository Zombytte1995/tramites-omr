<?php

namespace App\Repositories\Eloquent;

use App\Models\Tramite;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TramiteRepository implements TramiteRepositoryInterface
{
    public function __construct(private readonly Tramite $model) {}

    public function paginate(int $perPage, ?int $institucionId, ?string $search): LengthAwarePaginator
    {
        return $this->model
            ->with('institucion')
            ->activos()
            ->when($institucionId, fn ($q) => $q->deInstitucion($institucionId))
            ->when(
                $search !== null && $search !== '',
                fn ($q) => $q->where('nombre', 'like', "%{$search}%")
            )
            ->orderBy('nombre')
            ->paginate($perPage);
    }

    public function find(int $id): Tramite
    {
        return $this->model->with('institucion')->findOrFail($id);
    }

    public function create(array $data): Tramite
    {
        $tramite = $this->model->create($data);

        return $tramite->load('institucion');
    }

    public function update(int $id, array $data): Tramite
    {
        $tramite = $this->model->findOrFail($id);
        $tramite->update($data);

        return $tramite->load('institucion');
    }

    public function deactivate(int $id): bool
    {
        $tramite = $this->model->findOrFail($id);

        return $tramite->update(['activo' => false]);
    }
}

<?php

namespace App\Repositories\Eloquent;

use App\Models\Institucion;
use App\Repositories\Contracts\InstitucionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InstitucionRepository implements InstitucionRepositoryInterface
{
    public function __construct(private readonly Institucion $model) {}

    public function allActive(): Collection
    {
        return $this->model
            ->activas()
            ->orderBy('nombre')
            ->get();
    }

    public function find(int $id): Institucion
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Institucion
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Institucion
    {
        $institucion = $this->model->findOrFail($id);
        $institucion->update($data);

        return $institucion->fresh();
    }

    public function deactivate(int $id): bool
    {
        $institucion = $this->model->findOrFail($id);

        return $institucion->update(['activo' => false]);
    }
}

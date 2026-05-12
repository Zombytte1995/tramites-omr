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
}

<?php

namespace App\Actions;

use App\Models\Tramite;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetTramiteAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
    ) {}

    /**
     * Recupera un trámite por su ID con la institución precargada.
     *
     *
     * @throws ModelNotFoundException Si el ID no existe.
     */
    public function execute(int $id): Tramite
    {
        return $this->tramites->find($id);
    }
}

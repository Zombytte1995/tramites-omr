<?php

namespace App\Http\Controllers;

use App\Actions\CreateInstitucionAction;
use App\Actions\DeactivateInstitucionAction;
use App\Actions\ListInstitucionesAction;
use App\Actions\UpdateInstitucionAction;
use App\Http\Requests\CreateInstitucionRequest;
use App\Http\Requests\UpdateInstitucionRequest;
use App\Http\Resources\InstitucionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstitucionController extends Controller
{
    public function __construct(
        private readonly ListInstitucionesAction $list,
        private readonly CreateInstitucionAction $create,
        private readonly UpdateInstitucionAction $update,
        private readonly DeactivateInstitucionAction $deactivate,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return InstitucionResource::collection($this->list->execute());
    }

    public function store(CreateInstitucionRequest $request): JsonResponse
    {
        $institucion = $this->create->execute($request->validated());

        return (new InstitucionResource($institucion))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateInstitucionRequest $request, int $id): InstitucionResource
    {
        return new InstitucionResource($this->update->execute($id, $request->validated()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deactivate->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Institución desactivada correctamente.',
            'errors' => (object) [],
        ]);
    }
}

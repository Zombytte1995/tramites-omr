<?php

namespace App\Http\Controllers;

use App\Actions\CreateInstitucionAction;
use App\Actions\ListInstitucionesAction;
use App\Http\Requests\CreateInstitucionRequest;
use App\Http\Resources\InstitucionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstitucionController extends Controller
{
    public function __construct(
        private readonly ListInstitucionesAction $list,
        private readonly CreateInstitucionAction $create,
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
}

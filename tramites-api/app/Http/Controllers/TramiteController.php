<?php

namespace App\Http\Controllers;

use App\Actions\CreateTramiteAction;
use App\Actions\DeactivateTramiteAction;
use App\Actions\ExportTramitesAction;
use App\Actions\GenerarResumenIaAction;
use App\Actions\GetTramiteAction;
use App\Actions\ListTramitesAction;
use App\Actions\UpdateTramiteAction;
use App\Http\Requests\CreateTramiteRequest;
use App\Http\Requests\UpdateTramiteRequest;
use App\Http\Resources\TramiteCollection;
use App\Http\Resources\TramiteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TramiteController extends Controller
{
    public function __construct(
        private readonly ListTramitesAction $list,
        private readonly GetTramiteAction $get,
        private readonly CreateTramiteAction $create,
        private readonly UpdateTramiteAction $update,
        private readonly DeactivateTramiteAction $deactivate,
        private readonly GenerarResumenIaAction $resumenIa,
        private readonly ExportTramitesAction $export,
    ) {}

    public function index(Request $request): TramiteCollection
    {
        $paginator = $this->list->execute(
            perPage: 10,
            institucionId: $request->integer('institucion_id') ?: null,
            search: $request->string('search')->toString() ?: null,
        );

        return new TramiteCollection($paginator);
    }

    public function show(int $id): TramiteResource
    {
        return new TramiteResource($this->get->execute($id));
    }

    public function store(CreateTramiteRequest $request): JsonResponse
    {
        $tramite = $this->create->execute($request->validated());

        return (new TramiteResource($tramite))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTramiteRequest $request, int $id): TramiteResource
    {
        return new TramiteResource($this->update->execute($id, $request->validated()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deactivate->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Trámite desactivado correctamente.',
            'errors'  => (object) [],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        return $this->export->execute(
            institucionId: $request->integer('institucion_id') ?: null,
            search: $request->string('search')->toString() ?: null,
        );
    }

    public function resumen(int $id): JsonResponse
    {
        $tramite = $this->get->execute($id);
        $resumen = $this->resumenIa->execute($tramite);

        return response()->json([
            'data' => [
                'tramite_id' => $tramite->id,
                'resumen'    => $resumen,
            ],
        ]);
    }
}

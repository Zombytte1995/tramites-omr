<?php

namespace App\Http\Controllers;

use App\Actions\DashboardAction;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAction $action,
    ) {}

    /**
     * GET /api/dashboard/stats
     *
     * Devuelve las métricas agregadas del sistema: totales, trámites por
     * institución y los 5 trámites más recientes.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->action->execute(),
        ]);
    }
}

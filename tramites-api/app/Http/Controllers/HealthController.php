<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * GET /api/health
     *
     * Endpoint para liveness y readiness probes de Kubernetes.
     * No requiere autenticación ni acceso a la base de datos.
     * Retorna 200 inmediatamente para confirmar que el proceso PHP responde.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

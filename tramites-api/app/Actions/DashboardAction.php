<?php

namespace App\Actions;

use App\Models\Institucion;
use App\Models\Tramite;

class DashboardAction
{
    /**
     * Agrega las métricas del dashboard en una sola llamada.
     *
     * Queries simples — no se usan joins complejos ni sub-aggregations
     * que rompan la compatibilidad SQLite/PostgreSQL. withCount genera
     * subqueries correlacionadas que funcionan en ambos motores.
     *
     * @return array{
     *     total_tramites: int,
     *     tramites_activos: int,
     *     tramites_inactivos: int,
     *     total_instituciones: int,
     *     tramites_por_institucion: array<int, array{institucion: string, tipo: string, total: int, activos: int}>,
     *     tramites_recientes: array<int, array{id: int, codigo: string, nombre: string, dias_habiles: int, activo: bool, created_at: string, institucion_nombre: string|null}>
     * }
     */
    public function execute(): array
    {
        return [
            'total_tramites'       => Tramite::count(),
            'tramites_activos'     => Tramite::where('activo', true)->count(),
            'tramites_inactivos'   => Tramite::where('activo', false)->count(),
            'total_instituciones'  => Institucion::where('activo', true)->count(),

            'tramites_por_institucion' => $this->tramitesPorInstitucion(),
            'tramites_recientes'       => $this->tramitesRecientes(),
        ];
    }

    /** @return array<int, array{institucion: string, tipo: string, total: int, activos: int}> */
    private function tramitesPorInstitucion(): array
    {
        return Institucion::where('activo', true)
            ->withCount([
                'tramites',
                'tramites as tramites_activos_count' => fn ($q) => $q->where('activo', true),
            ])
            ->orderByDesc('tramites_count')
            ->get()
            ->map(fn ($i) => [
                'institucion' => $i->nombre,
                'tipo'        => $i->tipo->value,
                'total'       => (int) $i->tramites_count,
                'activos'     => (int) $i->tramites_activos_count,
            ])
            ->all();
    }

    /** @return array<int, array{id: int, codigo: string, nombre: string, dias_habiles: int, activo: bool, created_at: string, institucion_nombre: string|null}> */
    private function tramitesRecientes(): array
    {
        return Tramite::with(['institucion:id,nombre'])
            ->select(['id', 'codigo', 'nombre', 'dias_habiles', 'activo', 'created_at', 'institucion_id'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id'                => $t->id,
                'codigo'            => $t->codigo,
                'nombre'            => $t->nombre,
                'dias_habiles'      => $t->dias_habiles,
                'activo'            => (bool) $t->activo,
                'created_at'        => $t->created_at->toDateTimeString(),
                'institucion_nombre' => $t->institucion?->nombre,
            ])
            ->all();
    }
}

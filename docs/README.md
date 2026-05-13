# Documentación Técnica — Trámites OMR

## Diagramas

| Documento | Descripción |
| --- | --- |
| [er-diagram.md](er-diagram.md) | Modelo entidad-relación (Mermaid) con notas del diseño |
| [architecture.md](architecture.md) | Arquitectura de alto nivel, flujo de request, capas del backend, infra GKE |

## Architecture Decision Records (ADRs)

Los ADRs documentan las decisiones técnicas significativas, su contexto y las
consecuencias aceptadas. Son ligeros y directos al punto.

| ADR | Decisión | Estado |
| --- | --- | --- |
| [ADR-001](decisions/ADR-001-laravel-10.md) | Usar Laravel 10 (no 11/12) | Aceptado |
| [ADR-002](decisions/ADR-002-actions-pattern.md) | Action Classes sobre Service Classes | Aceptado |
| [ADR-003](decisions/ADR-003-repository-pattern.md) | Repository Pattern con interfaces | Aceptado |
| [ADR-004](decisions/ADR-004-soft-delete-boolean.md) | Soft delete con `activo` boolean | Aceptado |
| [ADR-005](decisions/ADR-005-vue3-typescript.md) | Vue 3 + TypeScript strict + erasableSyntaxOnly | Aceptado |
| [ADR-006](decisions/ADR-006-pinia-over-vuex.md) | Pinia sobre Vuex | Aceptado |
| [ADR-007](decisions/ADR-007-sqlite-local-postgres-prod.md) | SQLite local, PostgreSQL en producción | Aceptado |
| [ADR-008](decisions/ADR-008-jwt-auth.md) | JWT stateless sobre Sanctum con sesiones | Aceptado |

## ¿Qué es un ADR?

Un Architecture Decision Record registra una decisión de diseño importante junto
con su contexto y consecuencias. El formato es intencionalmente ligero:

```
# ADR-NNN: Título de la decisión

Estado: Aceptado | Propuesto | Rechazado | Obsoleto

## Contexto
¿Qué situación o problema motivó esta decisión?

## Decisión
¿Qué se decidió hacer?

## Consecuencias
¿Qué se gana y qué se pierde con esta decisión?
```

Los ADRs son documentación viva: cuando una decisión cambia, el ADR anterior
se marca como `Obsoleto` y se crea uno nuevo con la nueva decisión.

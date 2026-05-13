# ADR-002: Action Classes en lugar de Service Classes

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

Laravel no prescribe cómo organizar la lógica de negocio. Las opciones comunes son:

1. **Fat Controllers** — lógica directamente en el controlador.
2. **Service Classes** — un `TramiteService` con múltiples métodos (`create`, `update`, `deactivate`, etc.).
3. **Action Classes** — una clase por operación con un único método `execute()`.

El proyecto requiere operaciones CRUD sobre Trámites e Instituciones, más operaciones
especiales (resumen IA, exportación Excel, estadísticas de dashboard).

## Decisión

Usar **Action Classes**: una clase PHP por operación de negocio, con un único
método público `execute()`. Ejemplo: `CreateTramiteAction`, `DeactivateTramiteAction`,
`GenerarResumenIaAction`.

## Razonamiento

**Fat Controllers** violan SRP y dificultan el testing unitario.

**Service Classes** tienen un problema de escalado: `TramiteService` empieza con
3 métodos y acaba con 15. Esto crea un "God Object" que sabe demasiado, es difícil
de leer y genera merges conflictivos en equipos. Además, testear un método requiere
instanciar toda la clase con sus dependencias.

**Action Classes** siguen el **Single Responsibility Principle** al máximo:
- Una clase = una responsabilidad = un test.
- La inyección de dependencias funciona por constructor, compatible con el IoC de Laravel.
- El nombre de la clase documenta la intención mejor que `$service->create()`.
- Agregar una nueva operación = nuevo archivo, sin tocar clases existentes (Open/Closed).
- Se pueden componer acciones: `CreateTramiteAction` puede llamar a `GetInstitucionAction`.

## Consecuencias

**Positivas:**
- Cada acción es testeable de forma aislada sin setup de toda la clase.
- `grep CreateTramiteAction` localiza todo el código relevante en segundos.
- El controlador es delgado: solo orquesta `Request → Action → Resource`.
- Facilita feature flags: intercambiar una acción por otra sin tocar el controlador.

**Negativas:**
- Más archivos (11 Actions para este proyecto). En proyectos pequeños puede
  parecer overhead.
- Sin convención en el ecosistema Laravel (a diferencia de Services que son estándar
  en muchos proyectos). Un nuevo desarrollador puede confundirse.

**Alternativa considerada descartada:**
`TramiteService` con todos los métodos CRUD. Descartado por God Object y porque
`GenerarResumenIaAction` y `ExportTramitesAction` no pertenecen semánticamente
al mismo "servicio" que el CRUD de trámites.

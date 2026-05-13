# ADR-004: Soft delete con campo `activo` en lugar de `SoftDeletes` de Laravel

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

Laravel provee el trait `SoftDeletes` que agrega una columna `deleted_at timestamp`
y modifica todas las queries del modelo para excluir registros donde `deleted_at IS NOT NULL`.
Para recuperar registros "eliminados" se usa `withTrashed()` o `onlyTrashed()`.

El requerimiento de la prueba especifica que la desactivación de un trámite debe
ser un "soft delete" — el registro no se elimina de la base de datos.

## Decisión

Implementar soft delete con un campo **`activo boolean DEFAULT true`** en lugar
del trait `SoftDeletes` de Laravel.

## Razonamiento

### Semántica de dominio

En el contexto regulatorio salvadoreño, un trámite "inactivo" es un **estado de
negocio** con significado explícito: "este trámite ya no está disponible para los
ciudadanos, pero debe mantenerse en el historial administrativo". Esto es diferente
de "este registro fue eliminado por error y puede restaurarse".

`deleted_at` comunica "eliminación accidental reversible". `activo = false` comunica
"decisión administrativa de desactivar". El lenguaje importa.

### Visibilidad en queries

Con `SoftDeletes`, cualquier query sin `withTrashed()` excluye los registros
eliminados **silenciosamente**. Un desarrollador nuevo que lea el código podría
no saber por qué ciertos registros no aparecen.

Con `activo`, el filtro es explícito: `->where('activo', true)` o `->activos()`
(scope). La intención es visible en el código.

### Scopes vs. macros

El scope `scopeActivos()` funciona igual que `SoftDeletes` pero sin magia:

```php
// Con SoftDeletes (implícito, invisible)
Tramite::all(); // excluye deleted_at IS NOT NULL sin que el código lo diga

// Con activo (explícito, visible)
Tramite::activos()->get(); // la intención es obvia
```

### Compatibilidad

`SoftDeletes` tiene comportamiento inesperado con `cascade` en algunas relaciones.
`activo` es simplemente un campo boolean, sin lógica oculta.

## Consecuencias

**Positivas:**
- El estado del trámite es un ciudadano de primera clase en el modelo de dominio.
- Las queries son explícitas y fáciles de auditar.
- Sin magia de traits que modifique queries globalmente.
- Más fácil de entender para desarrolladores nuevos en el proyecto.

**Negativas:**
- Hay que recordar agregar `.activos()` (o el scope equivalente) en cada query
  donde se quieran excluir inactivos. `SoftDeletes` lo hace automáticamente.
- No hay `restore()` method built-in (pero sí `update(['activo' => true])`).
- Algunos paquetes de terceros (ej. auditoría) asumen `SoftDeletes` para detectar
  eliminaciones.

**Cuándo usar `SoftDeletes` en su lugar:**
Cuando el caso de uso es genuinamente "el usuario eliminó este registro por error
y quiere recuperarlo". Papeleras de reciclaje, historial de versiones, sistemas
de auditoría con timestamps de eliminación.

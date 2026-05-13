# ADR-003: Repository Pattern con interfaces

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

Eloquent implementa internamente el patrón Active Record, donde el Model es
responsable tanto de representar los datos como de persistirlos. Esto hace que
el Model sea a la vez "dominio" e "infraestructura de persistencia".

La crítica más común al Repository Pattern en Laravel es: *"Eloquent ya es un
Repository; añadir otra capa es YAGNI"*.

## Decisión

Implementar el **Repository Pattern con interfaces** para las entidades principales
(`Institucion`, `Tramite`):

- `Contracts/TramiteRepositoryInterface` — contrato de acceso a datos.
- `Eloquent/TramiteRepository` — implementación concreta.
- `RepositoryServiceProvider` — binding en el IoC container.

## Razonamiento

El argumento "Eloquent ya es Repository" es parcialmente correcto pero confunde
niveles de abstracción:

- **Active Record** (Eloquent): el objeto sabe cómo persistirse a sí mismo.
  `Tramite::where('activo', true)->paginate()` está acoplado al ORM.
- **Repository**: abstrae el acceso a datos detrás de una interfaz. Las Actions
  no saben si los datos vienen de Eloquent, Redis, una API o un array en memoria.

Para un proyecto de prueba técnica esto tiene valor adicional:

1. **Demostrar conocimiento de patrones** — el evaluador puede ver que se entiende
   la diferencia entre Eloquent como conveniencia vs. arquitectura limpia.
2. **Testabilidad real** — se puede hacer mock del repositorio en tests unitarios
   sin base de datos. (En este proyecto usamos `RefreshDatabase` con SQLite in-memory,
   pero la interfaz lo habilita.)
3. **Reemplazabilidad** — si mañana el cliente exige migrar a Doctrine o un
   microservicio de datos, solo cambia la implementación, no las Actions.

## Consecuencias

**Positivas:**
- Las Actions dependen de abstracciones, no de Eloquent directamente (DIP).
- El binding en el IoC container permite cambiar implementaciones sin tocar código
  de aplicación.
- Los métodos del repositorio tienen documentación PHPDoc explícita con tipos
  de entrada/salida.

**Negativas:**
- Duplicación aparente: `Tramite::paginate()` en Eloquent vs. `TramiteRepository::paginate()`.
- Dos archivos por entidad (interface + implementation) vs. uno.
- Para un CRUD simple, el beneficio puede ser invisible en el día a día.

**Cuándo NO usar este patrón:**
En aplicaciones Laravel donde Eloquent es el único ORM previsto por toda la vida
del proyecto y los tests usan bases de datos reales (factory + seeder), el Repository
Pattern agrega complejidad innecesaria. En ese caso, llamar a Eloquent directamente
desde las Actions es perfectamente válido.

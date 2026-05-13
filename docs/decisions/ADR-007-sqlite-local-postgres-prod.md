# ADR-007: SQLite en desarrollo local, PostgreSQL en producción

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

El proyecto necesita persistencia de datos tanto en desarrollo local como en
producción. Las opciones son:

1. **MySQL/PostgreSQL siempre** — mismo motor en todos los entornos.
2. **SQLite siempre** — simple pero no escalable para producción.
3. **SQLite local + PostgreSQL en producción** — cada entorno usa lo que más
   le conviene.

## Decisión

Usar **SQLite** para desarrollo local y tests, y **PostgreSQL** en producción
(Render) y en la infraestructura de referencia (GKE + Cloud SQL).

## Razonamiento

### SQLite en desarrollo

SQLite no requiere servidor, instalación, ni configuración de red. El flujo de
onboarding es:

```bash
touch database/database.sqlite && php artisan migrate --seed
```

Esto permite que cualquier desarrollador (o evaluador de la prueba técnica) tenga
el sistema funcionando en menos de 2 minutos, sin Docker, sin bases de datos
externas, sin variables de entorno complejas.

Para tests, SQLite `:memory:` hace que cada suite sea completamente aislada,
reproducible y extremadamente rápida (~1.3 s para 19 tests).

### PostgreSQL en producción

SQLite tiene limitaciones que importan en producción:
- **Concurrencia**: un solo escritor a la vez (lock a nivel de archivo). En una
  API REST con múltiples requests simultáneos, esto es un cuello de botella.
- **Escalado horizontal**: no es posible tener múltiples instancias de la API
  apuntando al mismo archivo SQLite.
- **Tipos de datos**: PostgreSQL ofrece `JSONB`, `UUID`, `ARRAY`, índices
  parciales y GIN/GiST para búsqueda full-text.
- **Cloud**: los proveedores cloud ofrecen PostgreSQL gestionado (Cloud SQL,
  RDS, Render DB) con backups automáticos, failover y réplicas de lectura.

### ¿No crea problemas la diferencia de motor?

La abstracción PDO de Laravel garantiza que las queries generadas por Eloquent
son compatibles con ambos motores, con excepciones menores:

- `whereJsonContains()` — no disponible en SQLite. No se usa en este proyecto.
- `fullText()` — búsqueda full-text difiere. Se usa `LIKE '%x%'` que funciona
  en ambos.
- Collation y case-sensitivity en `LIKE` — SQLite es case-insensitive por defecto;
  PostgreSQL es case-sensitive. En este proyecto el comportamiento es consistente
  porque las búsquedas son sobre `nombre` en minúsculas normalizadas.

El `phpunit.xml` especifica `DB_CONNECTION=sqlite, DB_DATABASE=:memory:` para
tests, independientemente del `.env` de desarrollo.

## Consecuencias

**Positivas:**
- Zero-config onboarding para desarrollo y evaluación.
- Tests ultra-rápidos con SQLite in-memory.
- Producción en PostgreSQL con concurrencia, tipos avanzados y managed hosting.
- La abstracción de Laravel hace el switch transparente.

**Negativas:**
- Inconsistencia teórica entre entornos. Un bug que solo aparece en PostgreSQL
  (ej. diferencia de case en LIKE) podría no detectarse en tests locales.
- Requiere mantener dos configuraciones de base de datos.

**Mitigación:**
El CI de GitHub Actions también corre con SQLite in-memory (consistente con tests
locales). Para detectar bugs específicos de PostgreSQL se necesitaría un entorno
de staging con el mismo motor que producción. Para este proyecto de prueba técnica,
el trade-off es aceptable.

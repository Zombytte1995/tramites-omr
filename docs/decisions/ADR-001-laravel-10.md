# ADR-001: Usar Laravel 10 en lugar de Laravel 11/12

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

La oferta de empleo del Organismo de Mejora Regulatoria especifica explícitamente
Laravel 10 como requisito del stack. Además, al momento del desarrollo existen
opciones más recientes (Laravel 11, 12) con cambios estructurales significativos
(eliminación de `app/Http/Kernel.php`, nuevo bootstrapping con `bootstrap/app.php`
en L11, directivas de middleware modificadas).

## Decisión

Usar **Laravel 10** tal como especifica la oferta, sin actualizar a versiones más
recientes.

## Consecuencias

**Positivas:**
- Cumple el requisito explícito de la oferta de empleo.
- Laravel 10 es LTS con soporte hasta agosto 2025 (bug fixes) y febrero 2026 (security).
- La amplia mayoría de proyectos gubernamentales latinoamericanos corren versiones
  estables de n-1, haciendo L10 representativo del entorno real de trabajo.
- Documentación, paquetes y tutoriales abundantes y maduros.
- `php-open-source-saver/jwt-auth ^2.8` tiene soporte estable en L10.

**Negativas:**
- L11/12 eliminan boilerplate (`Kernel.php`, múltiples providers) que en L10
  aún existe.
- Las Invokable Single Actions como default de controlador son más idiomáticas
  en L11+.
- `maatwebsite/excel` 3.1 funciona en L10; en L11+ existe v4.

**Neutras:**
- El patrón Repository, Actions y JsonResources funciona igual en cualquier
  versión de Laravel.

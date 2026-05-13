# ADR-005: Vue 3 con TypeScript strict y `erasableSyntaxOnly`

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

El frontend necesita ser una SPA que consuma la API Laravel. Las opciones principales
son React + TypeScript, Vue 3 + TypeScript, o Vue 3 con JavaScript.

La oferta de empleo menciona Vue.js en el stack, sin especificar versión.

## Decisión

Usar **Vue 3.5** con **TypeScript 6.0** en modo `strict`, incluyendo la directiva
`erasableSyntaxOnly: true` en el `tsconfig`.

## Razonamiento

### Vue 3 vs. Vue 2

Vue 2 entró en End of Life en diciembre 2023. Vue 3 es la versión activa con
Composition API, mejor rendimiento (Proxy-based reactivity), y mejor soporte
de TypeScript gracias a `<script setup>`.

### TypeScript sobre JavaScript

TypeScript permite:
- Detectar errores en tiempo de compilación (no en runtime con usuarios reales).
- Tipar exactamente los JsonResources de Laravel: `PaginatedResponse<Tramite>`,
  `ApiResponse<Institucion>`, etc.
- Autocompletar en el IDE para los campos de las entidades del backend.
- Refactorizar con confianza: renombrar un campo en el tipo se propaga a todos
  los usos.

Para un API REST, los tipos son la documentación viva del contrato entre frontend y backend.

### `erasableSyntaxOnly: true`

TypeScript 6.0 introdujo la directiva `erasableSyntaxOnly` que prohíbe syntax
que requiere transformación (no solo "borrar los tipos"):

- **Prohibe**: `enum` de TypeScript (requiere generación de código), parameter
  properties en constructors, `namespace`.
- **Permite**: todo lo que se puede implementar borrando las anotaciones de tipo.

**¿Por qué importa?**

Vite procesa cada archivo independientemente (modo `--isolatedModules`). Las
sintaxis que requieren información de tipo inter-archivo (como los `const enum`)
no pueden procesarse en modo aislado. `erasableSyntaxOnly` garantiza que el
código sea compatible con este modo de procesamiento, resultando en builds más
rápidos y compatibilidad futura con el modo strip-only de Node.js.

**Consecuencia práctica**: en lugar de `enum TipoInstitucion { MINISTERIO }`,
se usa:

```typescript
export const TipoInstitucion = {
  MINISTERIO: 'MINISTERIO',
  ALCALDIA: 'ALCALDIA',
  AUTONOMA: 'AUTONOMA',
} as const

export type TipoInstitucion = (typeof TipoInstitucion)[keyof typeof TipoInstitucion]
```

Esto es más verboso pero produce el mismo resultado en runtime.

## Consecuencias

**Positivas:**
- Tipado completo end-to-end: los tipos reflejan los JsonResources de Laravel.
- Errores de tipo detectados en CI antes de llegar a producción.
- `erasableSyntaxOnly` garantiza compatibilidad con tooling moderno y futuro.
- `strict: true` + `noUnusedLocals` + `noUnusedParameters` mantiene el código limpio.

**Negativas:**
- Mayor verbosidad inicial (definir tipos, const assertions para enums).
- `erasableSyntaxOnly` puede sorprender a desarrolladores que usan `enum` de TS
  como primera línea de defensa.
- TypeScript 6.0 es reciente; algunos plugins de ESLint/tooling pueden tener
  compatibilidad parcial.

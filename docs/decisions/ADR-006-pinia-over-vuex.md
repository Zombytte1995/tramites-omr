# ADR-006: Pinia en lugar de Vuex

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

Vue 3 tiene dos opciones principales de gestión de estado global:
- **Vuex 4** — el store oficial anterior, con `state`, `mutations`, `actions`, `getters`.
- **Pinia** — el store oficial actual, recomendado por el equipo de Vue desde 2022.

## Decisión

Usar **Pinia 3** con Composition API syntax.

## Razonamiento

### Vuex está en modo mantenimiento

El equipo de Vue anunció que Vuex no recibirá nuevas características y que Pinia
es el reemplazo oficial. Para proyectos nuevos, elegir Vuex es elegir deuda técnica
desde el día 1.

### Sin `mutations`

Vuex requiere `mutations` para mutaciones síncronas y `actions` para asíncronas.
Esta separación añade boilerplate sin beneficio real en aplicaciones que ya usan
TypeScript (donde el compilador garantiza la corrección sin necesidad de la capa extra).

Pinia elimina las `mutations`: todo se hace en `actions`. Menos código, misma seguridad.

### Composition API nativa

Pinia soporta Composition API de primera clase:

```typescript
// Pinia con Composition API (lo que usamos)
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => user.value !== null)

  async function login(credentials: LoginCredentials) { ... }

  return { user, isAuthenticated, login }
})
```

vs. Vuex Options API:

```typescript
export default createStore({
  state: { user: null },
  getters: { isAuthenticated: (state) => state.user !== null },
  mutations: { SET_USER(state, user) { state.user = user } },
  actions: { async login({ commit }, credentials) { ... } }
})
```

La sintaxis de Pinia es idéntica a `<script setup>`, reduciendo el cambio de
contexto mental al 100%.

### DevTools y SSR

Pinia tiene integración completa con Vue DevTools, soporte SSR nativo (state
hydration), y hot module replacement sin perder el estado.

## Consecuencias

**Positivas:**
- 30-40% menos boilerplate vs. Vuex para el mismo store.
- TypeScript inference funciona sin plugins adicionales.
- La sintaxis de Composition API en el store es idéntica a los composables y
  los `<script setup>` de los componentes.
- Stores modulares por defecto (no hay "namespaced modules" que configurar).

**Negativas:**
- Pinia 3 (para Vue 3.5+) aún no tiene tantos recursos de terceros como Vuex.
- Developers familiarizados con Redux/Vuex pueden necesitar adaptarse a la ausencia
  de mutations.

**Patrón elegido:**
Stores con Composition API en lugar de Options API, porque la consistencia
con `<script setup>` reduce la curva de aprendizaje para el equipo.

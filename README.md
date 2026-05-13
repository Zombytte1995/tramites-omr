# Sistema de Trámites OMR

[![Backend CI](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml)
[![Frontend CI](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.5-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-6.0-3178C6?logo=typescript&logoColor=white)](https://typescriptlang.org)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> Prueba técnica para la posición de **Fullstack Developer** en el Organismo de Mejora Regulatoria de El Salvador (OMR).

---

## 🌐 Demo en vivo

| Servicio  | URL                                                                  |
|-----------|----------------------------------------------------------------------|
| Frontend  | <https://tramites-omr.vercel.app>                                    |
| API       | <https://tramites-omr-api.onrender.com/api>                          |
| Health    | <https://tramites-omr-api.onrender.com/api/health>                   |

**Credenciales de demo:**

```text
Email:    admin@omr.gob.sv
Password: password
```

> El backend en Render puede tardar ~30 s en despertar si estuvo inactivo (plan gratuito).

---

## 📋 Acerca del Proyecto

El **Sistema de Trámites OMR** es una plataforma web para gestionar el catálogo de trámites administrativos que las instituciones gubernamentales de El Salvador ofrecen a los ciudadanos, en el marco de la Ley de Mejora Regulatoria.

El sistema permite registrar instituciones (ministerios, alcaldías, entidades autónomas), asociarles trámites con su código oficial, descripción, días hábiles de resolución y estado activo/inactivo. Un panel de administración ofrece filtros, búsqueda en tiempo real, exportación a Excel y un resumen ejecutivo generado por inteligencia artificial mediante la API de Google Gemini.

El proyecto fue construido como prueba técnica con énfasis en arquitectura limpia, separación de capas, tipado estricto en el frontend y cobertura de calidad mediante tests automatizados, CI y manifests de infraestructura.

---

## 🏗️ Arquitectura

```text
┌─────────────────────────────────────────────────────────┐
│                    Navegador / Cliente                   │
└────────────────────────┬────────────────────────────────┘
                         │ HTTPS
          ┌──────────────┴──────────────┐
          │                             │
          ▼                             ▼
  ┌───────────────┐           ┌─────────────────┐
  │  Vue 3 SPA    │           │  Laravel 10 API  │
  │  (nginx)      │──────────▶│  (PHP-FPM)       │
  │               │  /api/*   │                  │
  │  Pinia stores │           │  JWT Auth        │
  │  Axios client │           │  Repository      │
  │  Tailwind v4  │           │  Pattern         │
  └───────────────┘           └────────┬─────────┘
                                       │
                    ┌──────────────────┼──────────────────┐
                    │                  │                   │
                    ▼                  ▼                   ▼
             ┌──────────┐    ┌─────────────────┐  ┌──────────────┐
             │ SQLite   │    │ Google Gemini   │  │ PhpSpreadsheet│
             │ (local)  │    │ AI API          │  │ (Excel export)│
             │ PostgreSQL│   │ (caché 24 h)    │  │               │
             │ (prod)   │    └─────────────────┘  └──────────────┘
             └──────────┘

  Infra (referencia GKE):
  ┌─────────────────────────────────────────────────────┐
  │  GKE Ingress (HTTP LB)                              │
  │  /api/* ──▶ tramites-api-svc (ClusterIP :80→8000)  │
  │  /*     ──▶ tramites-app-svc (NodePort :80)         │
  │                                                     │
  │  Google Managed Certificate (TLS automático)        │
  │  Cloud SQL PostgreSQL (via Cloud SQL Auth Proxy)    │
  └─────────────────────────────────────────────────────┘
```

---

## 🛠️ Stack Tecnológico

### Backend

| Tecnología          | Versión | Razón de elección                                          |
| ------------------- | ------- | ---------------------------------------------------------- |
| PHP                 | 8.2     | Enums nativos, intersection types, `readonly` properties   |
| Laravel             | 10      | Requerido en la oferta · LTS estable · ecosistema maduro   |
| JWT Auth            | 2.8     | Stateless: apto para APIs consumidas desde SPAs            |
| SQLite / PostgreSQL | —       | SQLite en local (zero-config) · PostgreSQL en producción   |
| maatwebsite/excel   | 3.1     | Export .xlsx con formato (headers negrita, autowidth)      |
| Pest                | 2       | DSL más expresivo que PHPUnit · fixtures con factories     |

### Frontend

| Tecnología   | Versión | Razón de elección                                          |
| ------------ | ------- | ---------------------------------------------------------- |
| Vue 3        | 3.5     | Composition API · `<script setup>` · SFCs                  |
| TypeScript   | 6.0     | `erasableSyntaxOnly`, strict mode, tipos estrictos         |
| Vite         | 8       | HMR instantáneo · tree-shaking · build < 2 s               |
| Pinia        | 3       | Sucesor oficial de Vuex · Composition API nativa           |
| Tailwind CSS | v4      | Sin config extra · CSS-first · Vite plugin integrado       |
| Headless UI  | 1.7     | Dialog/Menu accesibles sin estilos opinionados             |
| Axios        | 1.x     | Interceptores para JWT y manejo de errores centralizado    |

### Infraestructura

| Herramienta             | Uso                                                            |
| ----------------------- | -------------------------------------------------------------- |
| Docker + docker-compose | Entorno reproducible en un comando |
| GitHub Actions | CI paralelo: Pest + Pint (backend) · type-check + build (frontend) |
| nginx:alpine | Servir SPA con gzip, cache inmutable de assets hasheados |
| GKE (referencia) | Manifests K8s con probes, limits, Managed Certificate y Ingress |
| Render | Deploy del backend (gratis, auto-deploy desde main) |
| Vercel | Deploy del frontend (gratis, preview por PR) |

---

## 🚀 Inicio Rápido

### Opción 1: Localmente (sin Docker)

**Backend:**

```bash
cd tramites-api
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
touch database/database.sqlite
php artisan migrate --seed
php artisan serve                    # http://localhost:8000
```

**Frontend** (terminal separada):

```bash
cd tramites-app
npm install
# Crear tramites-app/.env.local:
echo "VITE_API_URL=http://localhost:8000/api" > .env.local
npm run dev                          # http://localhost:5173
```

### Opción 2: Con Docker Compose

```bash
# 1. Copiar y completar variables de entorno
cp .env.example .env
# Editar .env con APP_KEY y JWT_SECRET generados

# 2. Levantar todo
docker compose up --build

# Frontend → http://localhost
# API      → http://localhost:8000/api
```

Al arrancar, el contenedor API ejecuta `php artisan migrate:fresh --seed --force` automáticamente.

---

## 📂 Estructura del Monorepo

```text
tramites-omr/
│
├── tramites-api/                   # Laravel 10 — API REST
│   ├── app/
│   │   ├── Actions/                # Single-action classes (CreateTramiteAction, etc.)
│   │   ├── Enums/                  # TipoInstitucion (PHP 8.1 backed enum)
│   │   ├── Exports/                # TramitesExport (maatwebsite/excel)
│   │   ├── Http/
│   │   │   ├── Controllers/        # Delgados: solo Request → Action → Resource
│   │   │   ├── Requests/           # Form Requests con mensajes en español
│   │   │   └── Resources/          # JsonResources y TramiteCollection
│   │   ├── Models/                 # Institucion, Tramite (activo como soft-delete)
│   │   └── Repositories/
│   │       ├── Contracts/          # Interfaces de repositorio
│   │       └── Eloquent/           # Implementaciones Eloquent
│   ├── database/
│   │   ├── factories/              # InstitucionFactory, TramiteFactory (datos SV)
│   │   ├── migrations/
│   │   └── seeders/                # 5 instituciones reales + 10 trámites
│   ├── tests/Feature/              # AuthTest, InstitucionTest, TramiteTest (Pest)
│   ├── Dockerfile                  # Multi-stage: composer → php:8.2-fpm-alpine
│   └── .env.example
│
├── tramites-app/                   # Vue 3 + TypeScript — SPA
│   ├── src/
│   │   ├── api/                    # client.ts, auth.ts, tramites.ts, etc.
│   │   ├── components/
│   │   │   ├── layout/             # MainLayout (sidebar), AuthLayout
│   │   │   └── ui/                 # BaseButton, BaseTable, BaseModal, etc.
│   │   ├── composables/            # useAsync, useDebounce, useToast
│   │   ├── stores/                 # Pinia: auth, tramites, instituciones
│   │   ├── types/                  # Tipos TS espejo de JsonResources
│   │   └── views/                  # Login, Dashboard, Lista, Form, Detail, etc.
│   ├── nginx.conf                  # SPA routing + gzip + cache assets
│   └── Dockerfile                  # Multi-stage: node:20-alpine → nginx:alpine
│
├── k8s/                            # Manifests Kubernetes (referencia GKE)
│   ├── namespace.yaml
│   ├── configmap.yaml
│   ├── secret.yaml.example         # Template — nunca commitear valores reales
│   ├── api-deployment.yaml         # 2 réplicas, probes /api/health
│   ├── api-service.yaml
│   ├── app-deployment.yaml
│   ├── app-service.yaml
│   └── ingress.yaml                # GKE HTTP LB + Managed Certificate
│
├── .github/workflows/
│   └── ci.yml                      # Backend CI + Frontend CI (paralelos)
│
├── docker-compose.yml              # Stack completo local + PostgreSQL opcional
├── .env.example                    # Template de variables para docker-compose
└── README.md
```

---

## ✅ Funcionalidades Implementadas

### Requeridas por la prueba

- [x] Listado de trámites con paginación
- [x] Formulario de creación y edición de trámites
- [x] Vista de detalle del trámite
- [x] Formulario de creación de instituciones
- [x] Endpoints REST para todas las operaciones CRUD
- [x] Relación Tramite → Institución
- [x] Validación de datos en backend (Form Requests) y frontend
- [x] Manejo de errores con respuestas JSON consistentes

### Bonus pedidos

- [x] Filtro por nombre con debounce de 300 ms (sin disparar N requests)
- [x] Confirmación antes de desactivar un trámite (ConfirmDialog)
- [x] Exportación a **Excel .xlsx** con headers en negrita y ancho automático (no solo CSV)

### Extras añadidos

- [x] **Autenticación JWT** completa (login, logout, refresh, /me)
- [x] **Integración Google Gemini** — resumen ejecutivo IA por trámite (caché 24 h)
- [x] **Dashboard** con 4 KPIs, barras por institución y trámites recientes
- [x] **Tests automatizados** con Pest — 19 tests, 77 assertions
- [x] **Docker + docker-compose** — entorno reproducible en un comando
- [x] **GitHub Actions CI** — jobs paralelos backend/frontend, verde desde el primer commit
- [x] **Manifests Kubernetes** de referencia para GKE (Deployment, Service, Ingress, Probes)
- [x] **Deploy en vivo** — Render (API) + Vercel (frontend)
- [x] **Endpoint /api/health** para liveness/readiness probes
- [x] **Deep linking** — filtros reflejados en la URL, compartibles y recargables
- [x] **Dirty guard** — confirmación antes de salir de formulario con cambios sin guardar
- [x] **Retorno fluido** — "Nueva Institución" desde el formulario de trámite vuelve con el ID preseleccionado
- [x] **Exportación a Excel** respeta los filtros activos del listado

---

## 🧠 Decisiones Técnicas

### ¿Por qué Laravel 10 y no Laravel 11/12?

La oferta de empleo especificaba explícitamente Laravel 10. Más allá del requerimiento, Laravel 10 es la versión LTS activa más extendida en el ecosistema hispano y la que mayor adopción tiene en instituciones públicas de la región, que suelen ir un ciclo por detrás de bleeding edge por razones de estabilidad y soporte a largo plazo.

### ¿Por qué Action Classes en lugar de Services?

Los Services tienden a acumular lógica con el tiempo ("God Service"), violan SRP y son difíciles de testear en aislamiento. Las Action Classes siguen el principio de responsabilidad única: `CreateTramiteAction::execute()` hace exactamente una cosa. Son más fáciles de localizar, de testear unitariamente y de reemplazar sin afectar otras operaciones. La inyección de dependencias funciona igual.

### ¿Por qué Repository Pattern si Eloquent ya es Repository?

Eloquent implementa el patrón Data Mapper, no Repository puro. Sin la capa de repositorio, los controllers o Actions quedan acoplados a Eloquent directamente: si mañana necesitamos una fuente de datos distinta (un microservicio, una caché, un ORM diferente) hay que reescribir la lógica de negocio. El Repository Interface desacopla qué datos necesito de cómo los obtengo, facilitando también el mock en tests sin tocar la base de datos.

### ¿Por qué `activo` boolean en lugar del trait `SoftDeletes`?

`SoftDeletes` agrega `deleted_at` timestamp, lo cual tiene sentido para registros que se restauran frecuentemente o para auditoría temporal. En el contexto regulatorio salvadoreño, un trámite "inactivo" es un estado de negocio (ya no se tramita pero debe mantenerse en historial). Usar `activo` boolean hace el estado explícito en el dominio, visible en el listado y filtrable sin magia de traits. Además, `deleted_at` requiere `withTrashed()` explícito en cada query, fuente frecuente de bugs.

### ¿Por qué Vue 3 + TypeScript con `erasableSyntaxOnly`?

`erasableSyntaxOnly: true` es la directiva de TypeScript 6 que prohíbe syntax que no puede "borrarse" sin transformación (enums, parameter properties, namespaces). Esto garantiza que el código TS sea compatible con el modo `--isolatedModules` de Vite, que procesa cada archivo independientemente sin información de tipo inter-archivo. El resultado es un pipeline de build más rápido y futuro-compatible con el modo Erase-only que Node.js adoptará nativamente.

### ¿Por qué Pinia en lugar de Vuex?

Vuex está en modo mantenimiento y no tendrá soporte para Vue 3 Composition API nativo. Pinia es el reemplazo oficial del equipo de Vue: stores tipadas sin `mutations` (solo `state` y `actions`), DevTools integradas, SSR-ready, y un 30% menos de boilerplate. La sintaxis de Composition API en Pinia es idéntica a `<script setup>`, reduciendo la curva de aprendizaje.

### ¿Por qué SQLite local y PostgreSQL en producción?

SQLite elimina cualquier dependencia de infraestructura local: `touch database/database.sqlite && php artisan migrate` y listo. Esto permite que cualquier desarrollador tenga el entorno funcionando en 2 minutos sin Docker ni servicios externos. En producción, PostgreSQL ofrece concurrencia real (sin locks a nivel de archivo), tipos nativos (`JSONB`, `UUID`, `ARRAY`), índices parciales y escalabilidad horizontal con réplicas de lectura. La abstracción PDO de Laravel hace el cambio completamente transparente.

### ¿Por qué el interceptor de 401 no redirige en la ruta de login?

El 401 del endpoint `/api/auth/login` significa "credenciales incorrectas", no "sesión expirada". Si el interceptor redirigiera siempre a `/login` al recibir un 401, el usuario que ingresa la contraseña mal sería redirigido a la misma página sin ver el mensaje de error, en un loop infinito. La condición `isLoginRequest` en el interceptor Axios distingue estos dos casos.

### ¿Por qué `Rule::in()` en lugar de `Rule::enum()` para TipoInstitucion?

`Rule::enum()` tiene su propio sistema de mensajes de error que ignora completamente el método `messages()` del Form Request. Para mostrar un mensaje en español descriptivo ("El tipo debe ser uno de: MINISTERIO, ALCALDIA, AUTONOMA"), `Rule::in(array_column(TipoInstitucion::cases(), 'value'))` respeta el método `messages()` y permite personalización completa del texto de error.

---

## ⚖️ Trade-offs Conocidos

- **Sin roles/permisos granulares**: el sistema tiene un único rol de administrador. Implementar ACL (ej. `spatie/laravel-permission`) estaba fuera del scope de la prueba pero es la siguiente iteración natural.
- **Tests de happy path principalmente**: la suite cubre los flujos principales (CRUD, auth, validación). Los edge cases de concurrencia, condiciones de red y errores de terceros (Gemini down) no están cubiertos por tests.
- **Sin rate limiting**: las rutas de login y los endpoints de IA no tienen throttling. En producción se añadiría `throttle:api` y límites específicos para Gemini.
- **`php artisan serve` en producción**: el Dockerfile usa el servidor integrado de PHP, que no es apto para carga real. Un deploy serio usaría php-fpm + nginx o FrankenPHP. Se eligió por simplicidad para la prueba.
- **Sin auditoría de cambios**: no hay log de quién cambió qué y cuándo. `spatie/laravel-activitylog` lo cubriría en una iteración real.
- **VITE_API_URL hardcodeada en build**: la URL del backend se incrusta en el bundle en build-time. En un entorno multi-stage (dev/staging/prod) requeriría 3 builds separados o un runtime env injection vía nginx.

---

## 🔮 Mejoras Futuras

Con más tiempo, estas serían las siguientes prioridades:

1. **Auditoría completa** — `spatie/laravel-activitylog` para registrar creación/modificación/desactivación con usuario y timestamp.
2. **Roles y permisos** — Diferenciación entre admin y consultor de solo lectura con `spatie/laravel-permission`.
3. **Cache Redis** — Mover las respuestas del listado de trámites a Redis con invalidación automática al crear/editar.
4. **Búsqueda full-text** — Usar `ILIKE` en PostgreSQL o integrar Meilisearch para búsquedas más sofisticadas.
5. **Export PDF** — Ficha individual del trámite en PDF con `barryvdh/laravel-dompdf`.
6. **Papelera de reciclaje** — Vista para restaurar trámites desactivados.
7. **Notificaciones en tiempo real** — WebSockets con Laravel Reverb para alertas de cambios.
8. **Tests E2E** — Playwright o Cypress para flujos de usuario completos.
9. **OpenAPI/Swagger** — Documentación interactiva del API con `l5-swagger`.

---

## ⏱️ Tiempo Invertido

| Módulo                                | Estimado |
|---------------------------------------|----------|
| Backend (estructura, API, JWT, IA)    | ~10 h    |
| Frontend (Vue, vistas, stores, UI)    | ~12 h    |
| Tests automatizados (Pest)            | ~2 h     |
| Docker, CI/CD, K8s manifests          | ~3 h     |
| Deploy en Render y Vercel             | ~1 h     |
| Documentación                         | ~2 h     |
| **Total**                             | **~30 h** |

---

## 📚 Documentación Adicional

- [README del Backend](./tramites-api/README.md) — arquitectura, endpoints, tests
- [README del Frontend](./tramites-app/README.md) — estructura de componentes, stores, convenciones

---

## 👤 Autor

**David Chavarría**
[GitHub](https://github.com/Zombytte1995) · [LinkedIn](https://linkedin.com/in/david-chavarria)

---

## 📄 Licencia

MIT — libre para uso, modificación y distribución.

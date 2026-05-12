# Contexto del Proyecto: tramites-omr

## Propósito
Prueba técnica para plaza de Desarrollador Fullstack en el Organismo de 
Mejora Regulatoria (OMR) de El Salvador. Sistema simple de gestión de 
trámites administrativos y las instituciones que los administran.

## Stack obligatorio
- Backend: Laravel 10 con PHP 8.2
- Frontend: Vue 3 + TypeScript + Vite + Pinia + Tailwind CSS
- DB: SQLite local (memoria/archivo), PostgreSQL en producción
- Auth: JWT (php-open-source-saver/jwt-auth)
- Deploy: Render (backend) + Vercel (frontend)

## Estructura del monorepo
tramites-omr/
├── tramites-api/   ← Laravel
├── tramites-app/   ← Vue 3
├── .github/workflows/   ← CI/CD
├── k8s/                 ← manifests de referencia
└── README.md

## Patrones arquitectónicos definidos
- Backend: Controller → Action (Single Action Classes) → Repository → Model
- NO usar Services monolíticos
- Repository con Interface bindeado en ServiceProvider
- Form Requests para validación
- JsonResources para respuestas
- Custom Exception Handler para JSON consistente:
  { "success": bool, "message": string, "errors": object }
- Enums nativos PHP 8.1+ para tipos cerrados (TipoInstitucion)

- Frontend: Composition API con <script setup lang="ts">
- Pinia stores por dominio (tramites, instituciones, auth)
- Composable useAsync para estados {data, loading, error}
- Cliente Axios con interceptor JWT + manejo 401/422
- Tipos TypeScript espejando los JsonResources

## Entidades de dominio
Institucion { id, nombre, tipo[MINISTERIO/ALCALDIA/AUTONOMA], activo }
Tramite { id, codigo, nombre, descripcion, institucionId, diasHabiles, activo }

## Endpoints obligatorios (todos bajo /api)
GET    /instituciones        - listar activas
POST   /instituciones        - crear
GET    /tramites             - paginado 10/pág + filtro institución
GET    /tramites/{id}        - detalle
POST   /tramites             - crear
PUT    /tramites/{id}        - actualizar
DELETE /tramites/{id}        - soft delete (activo=false)

## Features extra (bonus estratégicos)
- Login JWT (POST /auth/login, POST /auth/logout, GET /auth/me)
- POST /tramites/{id}/resumen-ia - genera resumen con Gemini API
- Filtro búsqueda por nombre con debounce 300ms
- Modal de confirmación antes de desactivar
- Export a Excel (no solo CSV) con maatwebsite/excel
- Dashboard simple con conteos

## Convenciones de código
- Conventional Commits (feat:, fix:, refactor:, docs:, test:, chore:)
- Mensajes de validación en español (publicar lang/es.json)
- Variables y comentarios en español, código en inglés
- PHP: Laravel Pint para formato
- TS: Prettier + ESLint con config Vue 3 + TS

## Decisiones técnicas que debo poder defender
1. Por qué Laravel 10 y no 13: estabilidad, evaluadores familiarizados, 
   ecosistema legacy en OMR (mencionan Lumen en oferta).
2. Por qué Actions en lugar de Services: SRP, testabilidad, 
   experiencia previa con Services que se volvieron god-objects.
3. Por qué Repository con Interface: DIP de SOLID, mockeable, 
   abstracción de fuente de datos.
4. Por qué Vue 3 sobre Vue 2: Vue 2 EOL desde diciembre 2023, 
   Composition API, mejor TS support.
5. Por qué TypeScript: paridad de tipos con backend, refactoring 
   seguro, mejor DX.
6. Por qué Pinia sobre Vuex: API más simple, mejor TS support, 
   recomendación oficial Vue.

## Reglas estrictas
- NO uses paquetes que no sean estrictamente necesarios
- NO inventes features fuera de scope sin preguntar
- NO uses sintaxis Laravel 11+ (no bootstrap/app.php, sí Kernel.php)
- Mantén commits atómicos y descriptivos
- Cada cambio debe ser explicable
- Si tienes dudas de diseño, PREGUNTA antes de implementar

## Política de Git

### Commits
- Hacer commits automáticamente después de completar cada unidad lógica de trabajo
- Un commit por feature/refactor/fix coherente, NO un mega-commit al final
- Formato estricto: Conventional Commits en español
  - `feat(api): agregar Repository pattern para Tramite`
  - `feat(app): implementar composable useAsync`
  - `fix(api): corregir validación de codigo único en Tramite`
  - `refactor(api): extraer lógica de filtros a query scope`
  - `docs: actualizar README con instrucciones de Docker`
  - `test(api): agregar tests para TramiteController`
  - `chore: configurar GitHub Actions CI`
  - `style: formatear código con Pint`
- Scope obligatorio: `api`, `app`, `infra`, `docs`
- Mensajes en español, descriptivos pero concisos (máx 72 caracteres)
- NO incluir referencias a IA en los commits:
  - NO agregar "🤖 Generated with Claude Code"
  - NO agregar "Co-Authored-By: Claude <noreply@anthropic.com>"
  - NO mencionar Claude, IA, ni asistente en mensajes de commit
- Author de los commits: el usuario (yo), no Claude

### Push
- NO ejecutar `git push` automáticamente
- Después de cada commit, terminar con un mensaje:
  "Commit hecho. Revisa con `git log -1` y haz push cuando estés listo."
- El push es responsabilidad exclusiva del usuario

### Branches
- Trabajar en `main` directamente (es una prueba técnica, no producción)
- NO crear branches feature/, NO hacer rebase, NO hacer merge complicado
- Mantener el historial lineal y simple

### Antes de cada commit
- Verificar que el código compile/arranque
- Si hay tests, que pasen
- Asegurar que no se commitean: .env, vendor/, node_modules/, .DS_Store
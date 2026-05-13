# Trámites OMR

[![Backend CI](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml)
[![Frontend CI](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Zombytte1995/tramites-omr/actions/workflows/ci.yml)

Sistema de gestión de trámites administrativos para el Organismo de Mejora Regulatoria de El Salvador.

**Stack:** Laravel 10 · Vue 3 + TypeScript · Tailwind CSS v4 · JWT Auth · SQLite/PostgreSQL · Docker

---

## Inicio rápido con Docker Compose

### 1. Pre-requisitos

- Docker Desktop (o Docker Engine + Compose v2)
- Git

### 2. Clonar y configurar

```bash
git clone <url-del-repositorio>
cd tramites-omr

# Copiar variables de entorno
cp .env.example .env
```

Editar `.env` con los valores requeridos:

```bash
# Generar APP_KEY de Laravel
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"

# Generar JWT_SECRET
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"
```

Pegar los valores generados en `.env`.

### 3. Levantar los servicios

```bash
docker compose up --build
```

El primer arranque tarda ~2-3 minutos (build de imágenes). Los siguientes arranques son instantáneos.

Al iniciar, la API ejecuta automáticamente:

```bash
php artisan migrate:fresh --seed --force
```

Esto crea las tablas y carga los datos de demo (instituciones reales de El Salvador + trámites de ejemplo).

### 4. Acceder a la aplicación

| Servicio | URL                              |
|----------|----------------------------------|
| Frontend | <http://localhost>               |
| API      | <http://localhost:8000/api>      |

**Credenciales de demo:**

```text
Email:    admin@omr.gob.sv
Password: password
```

### 5. Detener

```bash
docker compose down          # detiene y elimina contenedores
docker compose down -v       # también elimina volúmenes (borra la DB)
```

---

## Comandos útiles

```bash
# Ver logs en tiempo real
docker compose logs -f

# Solo logs de la API
docker compose logs -f api

# Ejecutar comandos Artisan dentro del contenedor
docker compose exec api php artisan tinker
docker compose exec api php artisan db:seed

# Correr los tests (Pest)
docker compose exec api ./vendor/bin/pest

# Reconstruir sin caché (tras cambios en Dockerfile o deps)
docker compose build --no-cache
docker compose up
```

---

## Activar PostgreSQL

El `docker-compose.yml` incluye el servicio `postgres` comentado. Para activarlo:

1. Descomentar el bloque `postgres:` en `docker-compose.yml`
2. En el servicio `api`, comentar las variables `DB_CONNECTION: sqlite` / `DB_DATABASE` y descomentar el bloque `pgsql`
3. Descomentar `postgres_data:` en la sección `volumes:`
4. Agregar `POSTGRES_PASSWORD` en `.env`
5. Ejecutar `docker compose up --build`

---

## Desarrollo local (sin Docker)

### API (Laravel)

```bash
cd tramites-api
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

### Frontend (Vue)

```bash
cd tramites-app
npm install
# Crear tramites-app/.env.local con:
# VITE_API_URL=http://localhost:8000/api
npm run dev
```

---

## Tests

```bash
cd tramites-api
./vendor/bin/pest               # todos los tests
./vendor/bin/pest --coverage    # con cobertura
```

Suite: **17 tests · 75 assertions** — InstitucionTest, TramiteTest, AuthTest.

---

## Estructura del monorepo

```text
tramites-omr/
├── tramites-api/          # Backend Laravel 10
│   ├── app/
│   │   ├── Actions/       # Single-action classes
│   │   ├── Enums/         # TipoInstitucion
│   │   ├── Exports/       # Excel exports (maatwebsite/excel)
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   └── Repositories/  # Contracts + Eloquent implementations
│   ├── tests/Feature/     # Pest tests
│   └── Dockerfile
├── tramites-app/          # Frontend Vue 3 + TypeScript
│   ├── src/
│   │   ├── api/           # Axios client + domain services
│   │   ├── components/    # UI + Layout
│   │   ├── composables/   # useAsync, useToast, useDebounce
│   │   ├── stores/        # Pinia (auth, tramites, instituciones)
│   │   ├── types/         # TypeScript types
│   │   └── views/         # Pages
│   ├── nginx.conf
│   └── Dockerfile
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## Endpoints de la API

| Método | Ruta                                      | Auth | Descripción                       |
|--------|-------------------------------------------|------|-----------------------------------|
| POST   | `/api/auth/login`                         | —    | Obtener token JWT                 |
| POST   | `/api/auth/logout`                        | ✓    | Invalidar token                   |
| GET    | `/api/auth/me`                            | ✓    | Datos del usuario autenticado     |
| GET    | `/api/instituciones`                      | —    | Listar instituciones activas      |
| POST   | `/api/instituciones`                      | ✓    | Crear institución                 |
| GET    | `/api/tramites`                           | —    | Listar trámites (paginado)        |
| GET    | `/api/tramites?search=&institucion_id=`   | —    | Filtrar trámites                  |
| GET    | `/api/tramites/export`                    | —    | Exportar a Excel (.xlsx)          |
| POST   | `/api/tramites`                           | ✓    | Crear trámite                     |
| PUT    | `/api/tramites/{id}`                      | ✓    | Actualizar trámite                |
| DELETE | `/api/tramites/{id}`                      | ✓    | Desactivar trámite (soft delete)  |
| POST   | `/api/tramites/{id}/resumen-ia`           | ✓    | Resumen con Gemini AI (caché 24h) |
| GET    | `/api/dashboard/stats`                    | ✓    | Estadísticas del dashboard        |
| GET    | `/api/health`                             | —    | Liveness/readiness probe (K8s)    |

---

## Kubernetes — Manifests de referencia para GKE

> Manifests de Kubernetes de referencia para deployment en Google Kubernetes Engine,
> alineado con el stack mencionado en la oferta de empleo.
> Los archivos en `k8s/` son **documentación técnica** y no se ejecutan en la prueba.

```text
k8s/
├── namespace.yaml          Namespace tramites-omr
├── configmap.yaml          Variables públicas (APP_ENV, DB_*, PORT…)
├── secret.yaml.example     Template de Secrets — NUNCA commitear valores reales
├── api-deployment.yaml     2 réplicas, limits 256Mi/250m, liveness+readiness en /api/health
├── api-service.yaml        ClusterIP 80 → 8000 (backend interno)
├── app-deployment.yaml     2 réplicas nginx, limits 64Mi/100m
├── app-service.yaml        NodePort 80 (requerido por GKE Ingress)
└── ingress.yaml            GKE HTTP LB + Google Managed Certificate (TLS) + redirect HTTPS
```

### Flujo de despliegue en GKE

```bash
# 1. Autenticar con GKE
gcloud container clusters get-credentials CLUSTER_NAME --region REGION

# 2. Crear namespace y recursos base
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml

# 3. Crear el Secret con valores reales (no el .example)
kubectl create secret generic tramites-omr-secrets \
  --namespace tramites-omr \
  --from-literal=APP_KEY='base64:...' \
  --from-literal=JWT_SECRET='...' \
  --from-literal=DB_USERNAME='omr' \
  --from-literal=DB_PASSWORD='...'

# 4. Build y push de imágenes al Container Registry de GCP
docker build -t gcr.io/PROJECT_ID/tramites-api:$SHA ./tramites-api
docker build --build-arg VITE_API_URL=https://tramites.omr.gob.sv/api \
             -t gcr.io/PROJECT_ID/tramites-app:$SHA ./tramites-app
docker push gcr.io/PROJECT_ID/tramites-api:$SHA
docker push gcr.io/PROJECT_ID/tramites-app:$SHA

# 5. Actualizar imagen en el Deployment (rolling update sin downtime)
kubectl set image deployment/tramites-api tramites-api=gcr.io/PROJECT_ID/tramites-api:$SHA \
  -n tramites-omr
kubectl set image deployment/tramites-app tramites-app=gcr.io/PROJECT_ID/tramites-app:$SHA \
  -n tramites-omr

# 6. Aplicar servicios e ingress
kubectl apply -f k8s/api-service.yaml
kubectl apply -f k8s/app-service.yaml
kubectl apply -f k8s/ingress.yaml

# 7. Verificar estado
kubectl get pods -n tramites-omr
kubectl get ingress -n tramites-omr
```

# Arquitectura del Sistema

## Diagrama de alto nivel — Flujo de producción

```mermaid
graph TB
    subgraph Cliente
        U[👤 Usuario]
        B[Navegador]
    end

    subgraph Vercel["Vercel (CDN Global)"]
        N[nginx:alpine]
        SPA["Vue 3 SPA\n(bundle estático)"]
    end

    subgraph Render["Render (Free tier)"]
        PHP["php artisan serve\n(php:8.2-fpm-alpine)"]
        subgraph Laravel["Laravel 10"]
            R[Router]
            M[Middleware JWT]
            C[Controllers]
            A[Actions]
            RP[Repositories]
            EL[Eloquent Models]
        end
    end

    subgraph External["Servicios Externos"]
        PG[(PostgreSQL\nRender DB)]
        GEM[Google Gemini API\ngoogle.generativeai.com]
        GH[GitHub Actions CI]
    end

    U --> B
    B -->|"HTTPS :443"| N
    N -->|"SPA routing\ntry_files"| SPA
    SPA -->|"XHR /api/*\nBearer JWT"| PHP
    PHP --> R --> M --> C --> A --> RP --> EL --> PG
    A -->|"POST\ncaché 24h"| GEM
    GH -->|"push a main\nverde"| Render
    GH -->|"push a main\nverde"| Vercel
```

## Diagrama de flujo — Request típico autenticado

```mermaid
sequenceDiagram
    participant B as Browser (Vue 3)
    participant AX as Axios Client
    participant MW as JWT Middleware
    participant CT as Controller
    participant AC as Action
    participant RP as Repository
    participant DB as SQLite / PostgreSQL

    B->>AX: store.fetchList(filters)
    AX->>AX: Interceptor adjunta Bearer token
    AX->>MW: GET /api/tramites?search=x
    MW->>MW: JWTGuard::check()
    alt Token inválido
        MW-->>AX: 401 Unauthorized
        AX->>AX: Interceptor → window.location = /login
    end
    MW->>CT: TramiteController::index(Request)
    CT->>AC: ListTramitesAction::execute(perPage, search, ...)
    AC->>RP: TramiteRepository::paginate(10, null, "x")
    RP->>DB: SELECT ... WHERE nombre LIKE '%x%' ORDER BY nombre LIMIT 10
    DB-->>RP: Eloquent Collection
    RP-->>AC: LengthAwarePaginator
    AC-->>CT: LengthAwarePaginator
    CT-->>AX: TramiteCollection → JSON {data[], meta{}}
    AX-->>B: PaginatedResponse<Tramite>
    B->>B: tramitesStore.tramites = response
```

## Diagrama de capas — Backend Laravel

```mermaid
graph LR
    subgraph HTTP
        RQ[Request HTTP]
        RS[Response JSON]
    end

    subgraph "Capa de Presentación"
        FR[Form Request\nValidación + Auth]
        CT[Controller\nOrquestación delgada]
        RES[JsonResource\nTransformación de salida]
    end

    subgraph "Capa de Aplicación"
        AC["Action Class\nLógica de negocio\n(SRP: un execute())"]
    end

    subgraph "Capa de Infraestructura"
        RI[Repository Interface\nContrato desacoplado]
        RE["Eloquent Repository\nImplementación concreta\n(bind en ServiceProvider)"]
    end

    subgraph "Capa de Dominio"
        MD[Eloquent Model\nScopes, Casts, Relations]
        EN[PHP 8.1 Enum\nTipoInstitucion]
    end

    subgraph "Servicios Externos"
        GEM[Gemini API\nHttp facade + Cache 24h]
        XL[PhpSpreadsheet\nExport .xlsx]
    end

    RQ --> FR --> CT
    CT --> AC
    AC --> RI
    RI -.->|"IoC binding"| RE
    RE --> MD
    MD --> EN
    AC --> GEM
    AC --> XL
    CT --> RES --> RS
```

## Diagrama de despliegue — Infraestructura de referencia (GKE)

```mermaid
graph TB
    subgraph Internet
        DNS[DNS\ntramites.omr.gob.sv]
        LB["GKE HTTP(S) Load Balancer\nGoogle Managed Certificate TLS"]
    end

    subgraph GKE["Google Kubernetes Engine\nCluster"]
        subgraph NS["Namespace: tramites-omr"]
            ING["Ingress\n/api/* → api-svc\n/* → app-svc"]

            subgraph API["Deployment: tramites-api (2 réplicas)"]
                P1["Pod 1\nphp:8.2-fpm-alpine\n256Mi / 250m CPU"]
                P2["Pod 2\nphp:8.2-fpm-alpine\n256Mi / 250m CPU"]
            end

            subgraph APP["Deployment: tramites-app (2 réplicas)"]
                P3["Pod 3\nnginx:alpine\n64Mi / 100m CPU"]
                P4["Pod 4\nnginx:alpine\n64Mi / 100m CPU"]
            end

            ASVC["api-svc\nClusterIP :80→8000"]
            APPSVC["app-svc\nNodePort :80"]
            CM[ConfigMap\nvariables públicas]
            SEC[Secret\nAPP_KEY, JWT_SECRET\nDB_PASSWORD]
        end
    end

    subgraph GCP["Google Cloud Platform"]
        CSQL["Cloud SQL\nPostgreSQL 16"]
        PROXY["Cloud SQL Auth Proxy\nsidecar container"]
        SM["Secret Manager\nfuente de verdad de secrets"]
    end

    DNS --> LB --> ING
    ING -->|"/api/*"| ASVC --> P1 & P2
    ING -->|"/*"| APPSVC --> P3 & P4
    P1 & P2 --> PROXY --> CSQL
    CM -.-> P1 & P2
    SEC -.-> P1 & P2
    SM -.->|"Workload Identity"| SEC

    style API fill:#fff3e0
    style APP fill:#e8f5e9
    style GCP fill:#e3f2fd
```

## Decisiones de infraestructura

| Aspecto             | Demo (actual)          | Producción (GKE)                      |
| ------------------- | ---------------------- | ------------------------------------- |
| Servidor web        | php artisan serve      | php-fpm + nginx (o FrankenPHP)        |
| Base de datos       | SQLite (local) / Render PG | Cloud SQL PostgreSQL con réplicas |
| Secrets             | .env / Render env vars | GCP Secret Manager + Workload Identity |
| Imágenes Docker     | :latest                | SHA del commit (inmutable)            |
| Escalado            | 1 instancia            | HPA: 2-10 réplicas por CPU/RPS        |
| TLS                 | Render automático      | Google Managed Certificate            |
| Observabilidad      | Logs de Render         | Cloud Logging + Cloud Monitoring      |

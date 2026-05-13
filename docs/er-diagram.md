# Diagrama Entidad-Relación

Modelo de datos del Sistema de Trámites OMR.

## Diagrama ER

```mermaid
erDiagram
    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        timestamp email_verified_at
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    instituciones {
        bigint id PK
        varchar(150) nombre UK
        varchar(20) tipo
        boolean activo
        timestamp created_at
        timestamp updated_at
    }

    tramites {
        bigint id PK
        varchar(20) codigo UK
        varchar(200) nombre
        text descripcion
        bigint institucion_id FK
        smallint dias_habiles
        boolean activo
        timestamp created_at
        timestamp updated_at
    }

    password_reset_tokens {
        varchar email PK
        varchar token
        timestamp created_at
    }

    personal_access_tokens {
        bigint id PK
        varchar tokenable_type
        bigint tokenable_id
        varchar name
        varchar token UK
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    instituciones ||--o{ tramites : "tiene"
    users ||--o{ personal_access_tokens : "posee"
```

## Notas del modelo

### `instituciones`

| Campo  | Tipo          | Restricción | Descripción                                              |
| ------ | ------------- | ----------- | -------------------------------------------------------- |
| tipo   | varchar(20)   | NOT NULL    | Enum: `MINISTERIO`, `ALCALDIA`, `AUTONOMA`               |
| activo | boolean       | DEFAULT true | Soft delete lógico — no se usan `SoftDeletes` de Laravel |

### `tramites`

| Campo          | Tipo          | Restricción | Descripción                                                  |
| -------------- | ------------- | ----------- | ------------------------------------------------------------ |
| codigo         | varchar(20)   | UNIQUE      | Código oficial del trámite (ej. `TRM-0001`)                  |
| institucion_id | bigint FK     | RESTRICT    | `ON DELETE RESTRICT`: no se puede eliminar la institución si tiene trámites |
| dias_habiles   | smallint      | NOT NULL    | Plazo legal máximo de resolución                             |
| activo         | boolean       | DEFAULT true | Soft delete lógico — inactivar sin borrar historial          |

### ¿Por qué `activo` y no `deleted_at`?

`activo` boolean modela un **estado de negocio explícito** en el dominio regulatorio:
un trámite inactivo no es un registro eliminado — sigue existiendo en el historial
y puede ser consultado por auditores. `SoftDeletes` requiere `withTrashed()` en cada
query y su semántica es más "borrar con posibilidad de restaurar" que "cambiar estado".

### Restricción RESTRICT en `institucion_id`

`restrictOnDelete()` previene que se elimine una institución que tiene trámites
asociados, manteniendo la integridad referencial en ambos motores (SQLite y PostgreSQL).
La alternativa `CASCADE` borraría trámites silenciosamente, lo cual es peligroso
en un contexto regulatorio donde el historial debe preservarse.

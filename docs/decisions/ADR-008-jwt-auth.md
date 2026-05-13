# ADR-008: JWT Stateless en lugar de Sanctum con sesiones

**Estado:** Aceptado  
**Fecha:** 2026-05-12  
**Autor:** David Chavarría

---

## Contexto

Laravel ofrece dos mecanismos principales de autenticación para APIs:

1. **Laravel Sanctum** — tokens opacos almacenados en base de datos, o cookies
   de sesión para SPAs del mismo dominio.
2. **JWT (JSON Web Tokens)** — tokens firmados criptográficamente, stateless
   (sin almacenamiento en servidor).

## Decisión

Usar **`php-open-source-saver/jwt-auth ^2.8`** para autenticación JWT stateless.

## Razonamiento

### Sanctum para SPAs del mismo dominio

Sanctum con cookies es excelente cuando el frontend y el backend comparten el
mismo dominio (ej. `miapp.com` y `api.miapp.com`). En ese caso, usa cookies
HttpOnly + CSRF protection, lo cual es más seguro que tokens en localStorage.

En este proyecto:
- Frontend: `tramites-omr.vercel.app` (Vercel)
- Backend: `tramites-omr-api.onrender.com` (Render)

Son dominios completamente diferentes, lo que hace que la autenticación por cookie
de sesión de Sanctum sea compleja (CORS con credenciales, SameSite=None, HTTPS
obligatorio). JWT con tokens Bearer en Authorization header es más simple en
escenarios cross-origin.

### JWT en un escenario de microservicios

Si en el futuro el OMR añade más servicios (un servicio de notificaciones, un
servicio de documentos), un token JWT puede ser verificado por cualquiera de
ellos sin consultar la base de datos central. Los tokens de Sanctum requieren
un lookup a la tabla `personal_access_tokens` en cada request.

### Stateless = escalado horizontal trivial

Con JWT, cualquier instancia del servidor puede verificar cualquier token usando
solo la clave secreta (`JWT_SECRET`). Con sesiones, todas las instancias deben
compartir almacenamiento de sesión (Redis, base de datos) para que el token sea
válido en cualquier instancia. Esto es especialmente relevante para los 2 pods
en el deployment de GKE.

## Consecuencias

**Positivas:**
- Sin lookup a base de datos en cada request autenticado (performance).
- Funciona de forma natural con cors cross-domain.
- Escalado horizontal sin configuración de sesión compartida.
- `refresh` endpoint permite renovar el token sin re-autenticar.

**Negativas:**
- Los JWT no se pueden "invalidar" antes de su expiración sin mantener una
  lista negra (blacklist). `php-open-source-saver/jwt-auth` mantiene la blacklist
  en caché, pero añade estado de todas formas.
- Los tokens en localStorage son vulnerables a XSS. (Mitigación: sanitizar todos
  los inputs del usuario.)
- El `JWT_SECRET` debe rotarse periódicamente y esto invalida todos los tokens
  existentes.

**Alternativa considerada:**
Sanctum con tokens de API (no cookies) hubiera funcionado también para este caso.
La elección de JWT fue influenciada por la mención en la oferta de empleo de
"experiencia con JWT" y para demostrar comprensión del estándar.

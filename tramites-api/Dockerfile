# ── Stage 1: Dependencias Composer ────────────────────────────────────────────
FROM composer:2 AS composer-builder

WORKDIR /app

# Copiar solo los manifiestos primero para aprovechar la caché de capas:
# si composer.json/lock no cambian, Docker reutiliza la capa del vendor/.
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# ── Stage 2: Imagen de producción ─────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS production

# ── Dependencias del sistema (Alpine) ─────────────────────────────────────────
# Agrupadas en un único RUN para minimizar capas.
# --no-cache evita guardar el índice de apk en la imagen final.
RUN apk add --no-cache \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    sqlite-dev \
    oniguruma-dev

# ── Extensiones PHP ────────────────────────────────────────────────────────────
# gd requiere configure previo para activar soporte freetype/jpeg.
# -j"$(nproc)" paraleliza la compilación.
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pdo_sqlite \
        bcmath \
        mbstring \
        zip \
        gd \
        opcache

# ── OPcache para producción ────────────────────────────────────────────────────
# validate_timestamps=0: sin re-validación en disco (código inmutable en imagen).
# revalidate_freq=0:     irrelevante cuando timestamps=0, pero explícito.
RUN printf '[opcache]\n\
opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.revalidate_freq=0\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n' \
    > "$PHP_INI_DIR/conf.d/opcache-production.ini"

# ── Código de la aplicación ────────────────────────────────────────────────────
WORKDIR /var/www/html

COPY . .

# vendor/ viene del stage anterior, no de la copia anterior
COPY --from=composer-builder /app/vendor ./vendor

# ── Directorios de runtime y permisos ─────────────────────────────────────────
# Crear directorios que .dockerignore excluye con wildcard (*).
# chown en un solo RUN evita capas intermedias con archivos raíz.
RUN addgroup -g 1001 -S laravel \
    && adduser -u 1001 -S laravel -G laravel \
    && mkdir -p \
        storage/logs \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 755 storage bootstrap/cache

USER laravel

EXPOSE 8000

# Shell form para que ${PORT:-8000} se expanda en tiempo de ejecución.
# Permite que plataformas como Render o Railway inyecten $PORT dinámicamente.
CMD ["/bin/sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]

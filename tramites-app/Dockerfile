# ── Stage 1: Build ────────────────────────────────────────────────────────────
FROM node:20-alpine AS builder

WORKDIR /app

# Copiar manifiestos primero para aprovechar caché de capas:
# si package.json/lock no cambian, npm ci no se re-ejecuta.
COPY package.json package-lock.json ./

RUN npm ci

# VITE_API_URL es build-time: Vite la incrusta en los archivos JS
# durante el build. No disponible en runtime del contenedor.
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

# Copiar el resto del código y compilar
COPY . .

RUN npm run build

# ── Stage 2: Servidor de producción ───────────────────────────────────────────
FROM nginx:alpine AS production

# Eliminar la config default de nginx (la reemplazamos con la nuestra)
RUN rm /etc/nginx/conf.d/default.conf

# Configuración personalizada: SPA routing + gzip + cache de assets
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Artefactos del build (HTML, JS, CSS, assets hasheados)
COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

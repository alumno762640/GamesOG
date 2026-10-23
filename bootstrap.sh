#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

if [[ ! -f compose.yaml ]]; then
  echo "Error: falta compose.yaml en $PROJECT_DIR" >&2
  exit 1
fi
if [[ -e .env ]]; then
  echo "Error: .env ya existe; no lo sobrescribo." >&2
  exit 1
fi
if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
  echo "Error: Docker Engine y el plugin Docker Compose deben estar instalados." >&2
  exit 1
fi
DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  if sudo docker info >/dev/null 2>&1; then
    DOCKER=(sudo docker)
  else
    echo "Error: Docker no está disponible. Comprueba: sudo systemctl status docker" >&2
    exit 1
  fi
fi
docker_cmd() { "${DOCKER[@]}" "$@"; }
compose() { docker_cmd compose "$@"; }

# Do not reuse any pre-existing GamesOG Docker objects or data.
for volume in gamesog_db_data gamesog_wordpress_data; do
  if docker_cmd volume inspect "$volume" >/dev/null 2>&1; then
    echo "Error: ya existe el volumen $volume; no lo reutilizo ni modifico." >&2
    exit 1
  fi
done
if docker_cmd network inspect gamesog_default >/dev/null 2>&1; then
  echo "Error: ya existe la red gamesog_default; no la reutilizo ni modifico." >&2
  exit 1
fi
if docker_cmd ps -a --format '{{.Names}}' | grep -Eq '^gamesog-(db|wordpress|wpcli)(-|$)'; then
  echo "Error: ya existe un contenedor GamesOG; no lo modifico." >&2
  exit 1
fi

WP_PORT=8080
if ss -H -ltn "sport = :$WP_PORT" | grep -q .; then
  echo "Error: el puerto $WP_PORT está ocupado; no inicio el proyecto." >&2
  exit 1
fi

# Bind to loopback by default; opt in to LAN access by editing this value.
WP_BIND_ADDRESS="127.0.0.1"
WP_SITE_URL="http://${WP_BIND_ADDRESS}:${WP_PORT}"
WORDPRESS_DB_PASSWORD="$(openssl rand -hex 32)"
MARIADB_ROOT_PASSWORD="$(openssl rand -hex 32)"
WP_ADMIN_PASSWORD="$(openssl rand -hex 32)"

umask 077
cat > .env <<EOF
WP_PORT=$WP_PORT
WP_BIND_ADDRESS=$WP_BIND_ADDRESS
WP_SITE_URL=$WP_SITE_URL
WORDPRESS_DB_NAME=gamesog
WORDPRESS_DB_USER=gamesog
WORDPRESS_DB_PASSWORD=$WORDPRESS_DB_PASSWORD
MARIADB_ROOT_PASSWORD=$MARIADB_ROOT_PASSWORD
WP_ADMIN_USER=gamesog
WP_ADMIN_PASSWORD=$WP_ADMIN_PASSWORD
WP_ADMIN_EMAIL=admin@gamesog.local
EOF
chmod 600 .env
unset WORDPRESS_DB_PASSWORD MARIADB_ROOT_PASSWORD WP_ADMIN_PASSWORD

set -a
# shellcheck disable=SC1091
source .env
set +a

compose config --quiet
echo "Descargando imágenes y arrancando WordPress y MariaDB..."
compose up -d --wait --wait-timeout 180

ready=0
for _ in $(seq 1 90); do
  if compose exec -T wordpress test -f /var/www/html/wp-includes/version.php >/dev/null 2>&1; then
    ready=1
    break
  fi
  sleep 2
done
if [[ "$ready" != 1 ]]; then
  echo "Error: WordPress no terminó de inicializar. Consulta: docker compose logs" >&2
  exit 1
fi

if ! compose --profile tools run --rm -T wpcli core is-installed >/dev/null 2>&1; then
  compose --profile tools run --rm -T wpcli core install \
    --url="$WP_SITE_URL" \
    --title='GamesOG' \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --locale=es_ES \
    --skip-email
fi
unset WP_ADMIN_PASSWORD

compose --profile tools run --rm -T wpcli core is-installed
compose --profile tools run --rm -T wpcli option get blogname

echo "Reiniciando contenedores y verificando que el sitio y la base de datos persisten..."
compose restart
compose up -d --wait --wait-timeout 180
compose --profile tools run --rm -T wpcli core is-installed
compose --profile tools run --rm -T wpcli option get blogname

python3 - "$WP_SITE_URL/" <<'PY'
import sys
from urllib.request import urlopen

url = sys.argv[1]
with urlopen(url, timeout=20) as response:
    print(f"HTTP {response.status} — {url}")
    if response.status >= 400:
        raise SystemExit(1)
PY

echo "Instalación y comprobaciones completadas."
echo "URL: $WP_SITE_URL"
echo "Usuario administrador: $WP_ADMIN_USER"
echo "Contraseña: guárdala de forma segura y consúltala localmente en .env (no se muestra aquí)."

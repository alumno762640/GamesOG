#!/usr/bin/env bash
set -Eeuo pipefail
cd "$(dirname "$0")/.."
if [[ $# -ne 1 ]]; then echo "Uso: scripts/backup.sh DIRECTORIO_DE_DESTINO" >&2; exit 2; fi
if [[ ! -f .env ]]; then echo "Falta .env." >&2; exit 1; fi
docker_cmd=(docker)
if ! docker info >/dev/null 2>&1; then
  echo "Docker requiere permisos elevados para este usuario; solicita sudo solo para Docker." >&2
  sudo -v
  docker_cmd=(sudo docker)
fi
compose() { "${docker_cmd[@]}" compose "$@"; }
umask 077
dest="$1/gamesog-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$dest"
chmod 700 "$dest"
compose exec -T db sh -c 'mariadb-dump --single-transaction --routines --triggers -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > "$dest/database.sql"
compose exec -T wordpress tar -czf - -C /var/www/html . > "$dest/wordpress-files.tar.gz"
compose exec -T wordpress tar -czf - -C /var/private/gamesog-evidence . > "$dest/private-evidence.tar.gz"
tar --exclude=.git --exclude=Directrices.txt -czf "$dest/project-source.tar.gz" .
cp .env "$dest/env.private"
chmod 600 "$dest"/*
echo "Copia privada creada en $dest. Conserva el directorio bajo acceso restringido."

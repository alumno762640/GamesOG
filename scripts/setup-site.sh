#!/usr/bin/env bash
set -Eeuo pipefail
cd "$(dirname "$0")/.."
if [[ ! -f .env ]]; then echo "Falta .env. Copia .env.example y genera claves privadas antes de continuar." >&2; exit 1; fi
docker_cmd=(docker)
if ! docker info >/dev/null 2>&1; then
  echo "Docker requiere permisos elevados para este usuario; solicita sudo solo para Docker." >&2
  sudo -v
  docker_cmd=(sudo docker)
fi
compose() { "${docker_cmd[@]}" compose "$@"; }
compose config --quiet
compose up -d --wait --wait-timeout 180
if ! compose exec --user 33:33 -T wordpress php -r '$roots = ["/var/www/html/wp-content/themes/gamesog", "/var/www/html/wp-content/plugins/gamesog-core"]; $bad = []; foreach ($roots as $root) { try { if (!is_dir($root) || !is_readable($root)) { $bad[] = $root; continue; } $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)); foreach ($files as $file) { if (!$file->isReadable()) $bad[] = $file->getPathname(); } } catch (Throwable $error) { $bad[] = $root; } } if ($bad) { foreach (array_unique($bad) as $file) fwrite(STDERR, "No legible para Apache/WP-CLI (UID 33): $file\n"); exit(1); }'; then
  echo "El usuario Apache/WP-CLI no puede leer los archivos fuente montados." >&2
  echo "En Ubuntu ejecuta: sudo chmod -R a+rX wp-content/themes/gamesog wp-content/plugins/gamesog-core" >&2
  exit 1
fi
if ! compose --profile tools run --rm -T wpcli core is-installed >/dev/null; then
  set -a
  source .env
  set +a
  if [[ -z "$WP_ADMIN_USER" || -z "$WP_ADMIN_PASSWORD" || -z "$WP_ADMIN_EMAIL" ]]; then
    echo "Faltan credenciales de instalación en .env." >&2
    exit 1
  fi
  compose --profile tools run --rm -T wpcli core install \
    --url="$WP_SITE_URL" --title="GamesOG" --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" --admin_email="$WP_ADMIN_EMAIL" \
    --locale=es_ES --skip-email
  unset WP_ADMIN_PASSWORD
fi
if ! compose --profile tools run --rm -T wpcli plugin is-installed woocommerce >/dev/null 2>&1; then
  compose --profile tools run --rm -T wpcli plugin install woocommerce --version=11.2.1 --activate
else
  compose --profile tools run --rm -T wpcli plugin activate woocommerce
fi
# Existing installations may still have the default en_US locale even if a new install used --locale=es_ES.
compose --profile tools run --rm -T wpcli language core install es_ES
compose --profile tools run --rm -T wpcli site switch-language es_ES
compose --profile tools run --rm -T wpcli language plugin install woocommerce es_ES
compose --profile tools run --rm -T wpcli theme activate gamesog
compose --profile tools run --rm -T wpcli plugin activate gamesog-core
compose --profile tools run --rm -T wpcli eval 'gamesog_seed();'
compose --profile tools run --rm -T wpcli option update blog_public 0
compose --profile tools run --rm -T wpcli option update permalink_structure '/%postname%/'
compose --profile tools run --rm -T wpcli rewrite flush
compose --profile tools run --rm -T wpcli option update woocommerce_currency EUR
compose --profile tools run --rm -T wpcli option update woocommerce_coming_soon no
compose --profile tools run --rm -T wpcli option update woocommerce_enable_guest_checkout yes
echo "Activación terminada. Visita el valor WP_SITE_URL de .env."

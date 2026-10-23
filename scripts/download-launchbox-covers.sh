#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEST="$ROOT/wp-content/themes/gamesog/assets/images/covers"
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="$DEST/.antes-launchbox-$STAMP"
CREATED=()
CURRENT_TMP=""
mkdir -p "$DEST" "$BACKUP"

files=(red-dead-redemption-2 minecraft cyberpunk-2077 elden-ring stardew-valley hogwarts-legacy)
urls=(
  "https://images.launchbox-app.com/2e0ea254-dccc-47dd-b86c-18249cfde183.jpg"
  "https://images.launchbox-app.com/11ded2cc-d1b4-4f2d-b9e7-c9ff5a56d16d.jpg"
  "https://images.launchbox-app.com/r2_25827e2f-7c05-4603-8c3d-7b9967857e0f.jpg"
  "https://images.launchbox-app.com/f4cf7db9-c812-4a36-a5eb-1e9bcd6b6c37.jpg"
  "https://images.launchbox-app.com/1f51c08a-3c0c-48ea-82a5-810454b95c87.jpg"
  "https://images.launchbox-app.com/r2_35584094-f2cd-4688-a1ff-4254f1bc4ac0.jpg"
)

restore_previous() {
  echo "Falló la descarga; restaurando las imágenes anteriores desde $BACKUP" >&2
  [[ -z "$CURRENT_TMP" ]] || rm -f "$CURRENT_TMP"
  for name in "${files[@]}"; do
    if [[ -f "$BACKUP/$name.jpg" ]]; then
      cp -f "$BACKUP/$name.jpg" "$DEST/$name.jpg"
    elif [[ " ${CREATED[*]} " == *" $name "* ]]; then
      rm -f "$DEST/$name.jpg"
    fi
  done
}
for name in "${files[@]}"; do
  if [[ -f "$DEST/$name.jpg" ]]; then cp -p "$DEST/$name.jpg" "$BACKUP/$name.jpg"; fi
done
trap restore_previous ERR

for i in "${!files[@]}"; do
  name="${files[$i]}"
  CURRENT_TMP="$(mktemp "$DEST/.$name.XXXXXX")"
  curl --fail --location --silent --show-error --retry 3 --max-time 90 "${urls[$i]}" -o "$CURRENT_TMP"
  mime="$(file --brief --mime-type "$CURRENT_TMP")"
  if [[ "$mime" != "image/jpeg" ]]; then
    echo "Tipo inesperado para $name: $mime" >&2
    false
  fi
  mv -f "$CURRENT_TMP" "$DEST/$name.jpg"
  CURRENT_TMP=""
  if [[ ! -f "$BACKUP/$name.jpg" ]]; then CREATED+=("$name"); fi
  chmod 644 "$DEST/$name.jpg"
  echo "OK: $name.jpg ($mime)"
done
trap - ERR
echo "Carátulas locales actualizadas. Copia anterior: $BACKUP"
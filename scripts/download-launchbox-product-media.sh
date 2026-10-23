#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BASE="$ROOT/wp-content/themes/gamesog/assets/images"
STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="$BASE/.antes-launchbox-media-$STAMP"
CURRENT_TMP=""
CREATED=()
mkdir -p "$BACKUP"

paths=(
  logos/red-dead-redemption-2.png fanart/red-dead-redemption-2.jpg spines/red-dead-redemption-2.jpg
  logos/minecraft.png fanart/minecraft.jpg
  logos/cyberpunk-2077.png fanart/cyberpunk-2077.jpg spines/cyberpunk-2077.jpg
  logos/elden-ring.png fanart/elden-ring.jpg spines/elden-ring.jpg
  logos/stardew-valley.png fanart/stardew-valley.png
  logos/hogwarts-legacy.png fanart/hogwarts-legacy.png spines/hogwarts-legacy.jpg
)
urls=(
  https://images.launchbox-app.com/dccf6a4e-c1bc-4eee-9491-ff61a3769f12.png
  https://images.launchbox-app.com/c69dd747-0e40-4aad-ab4a-2940764de622.png
  https://images.launchbox-app.com/r2_a207b969-2ee2-4c2c-967b-5a64e5547eaa.jpg
  https://images.launchbox-app.com/8dde1705-711e-460c-ad1b-d5198b569fb9.png
  https://images.launchbox-app.com/4d8ee962-79ef-4fc0-bef3-4794b3497d36.jpg
  https://images.launchbox-app.com/r2_d29900cd-8dc6-4a89-a6c4-1ed0dc912810.png
  https://images.launchbox-app.com/r2_0ee5a7d9-35f5-4120-906f-9e95944b9b5d.jpg
  https://images.launchbox-app.com/r2_4caab0e9-aae4-44eb-b8ab-dcb854de7ecc.jpg
  https://images.launchbox-app.com/32387d34-bafd-463c-9feb-ea2bb4028f14.png
  https://images.launchbox-app.com/01a7a6ba-14ea-4232-a741-59b12ee05a29.jpg
  https://images.launchbox-app.com/3bc1aba7-5f5d-44be-9504-c05bc1e96b6b.jpg
  https://images.launchbox-app.com/31abc3c0-dd93-48b2-8a43-41538c4baf9f.png
  https://images.launchbox-app.com/ff60ab0c-64d5-491c-88de-932645577851.png
  https://images.launchbox-app.com/543a39a4-b4f0-4a5f-979c-1fd068c7e3cb.png
  https://images.launchbox-app.com/4590ff8f-fa3f-4861-9148-7afa363f2826.png
  https://images.launchbox-app.com/r2_23575fd3-3b1e-4845-b143-1a468cc6f499.jpg
)

rollback() {
  echo "Error de descarga; restaurando el arte anterior desde $BACKUP" >&2
  [[ -z "$CURRENT_TMP" ]] || rm -f "$CURRENT_TMP"
  for rel in "${paths[@]}"; do
    if [[ -f "$BACKUP/$rel" ]]; then cp -f "$BACKUP/$rel" "$BASE/$rel"
    elif [[ " ${CREATED[*]} " == *" $rel "* ]]; then rm -f "$BASE/$rel"; fi
  done
}
for rel in "${paths[@]}"; do
  mkdir -p "$BASE/$(dirname "$rel")" "$BACKUP/$(dirname "$rel")"
  [[ ! -f "$BASE/$rel" ]] || cp -p "$BASE/$rel" "$BACKUP/$rel"
done
trap rollback ERR
for i in "${!paths[@]}"; do
  rel="${paths[$i]}"; ext="${rel##*.}"
  CURRENT_TMP="$(mktemp "$BASE/$(dirname "$rel")/.download.XXXXXX")"
  curl --fail --location --silent --show-error --retry 3 --max-time 90 "${urls[$i]}" -o "$CURRENT_TMP"
  mime="$(file --brief --mime-type "$CURRENT_TMP")"
  if [[ "$ext" == png && "$mime" != image/png || "$ext" == jpg && "$mime" != image/jpeg ]]; then
    echo "Tipo inesperado para $rel: $mime" >&2; false
  fi
  mv -f "$CURRENT_TMP" "$BASE/$rel"; CURRENT_TMP=""
  [[ -f "$BACKUP/$rel" ]] || CREATED+=("$rel")
  chmod 644 "$BASE/$rel"
  echo "OK: $rel"
done
trap - ERR
echo "Arte local de LaunchBox actualizado. Copia anterior: $BACKUP"
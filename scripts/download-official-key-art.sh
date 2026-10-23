#!/usr/bin/env bash
# Descarga key art oficial de las tiendas de los titulares para la maqueta académica.
# No descarga ni distribuye software del juego.
set -u
cd "$(dirname "$0")/.."
output="wp-content/themes/gamesog/assets/images/covers"
mkdir -p "$output"
failed=0
fetch_art() {
  local slug="$1" url="$2" target="$output/$1.jpg" temp="$output/$1.jpg.part"
  if [ -s "$target" ]; then echo "EXISTE: $target (se conserva)"; return; fi
  if ! curl --fail --location --silent --show-error --retry 2 --max-time 45 --user-agent 'GamesOG academic demo' "$url" -o "$temp"; then
    rm -f "$temp"; echo "FALLO DE DESCARGA: $slug"; failed=1; return
  fi
  if [ "$(file --brief --mime-type "$temp")" != "image/jpeg" ]; then
    rm -f "$temp"; echo "FALLO: $slug no devolvió JPEG"; failed=1; return
  fi
  mv "$temp" "$target"; chmod 644 "$target"; echo "OK: $target"
}
fetch_art red-dead-redemption-2 https://cdn.akamai.steamstatic.com/steam/apps/1174180/library_600x900.jpg
fetch_art cyberpunk-2077 https://cdn.akamai.steamstatic.com/steam/apps/1091500/library_600x900.jpg
fetch_art elden-ring https://cdn.akamai.steamstatic.com/steam/apps/1245620/library_600x900.jpg
fetch_art stardew-valley https://cdn.akamai.steamstatic.com/steam/apps/413150/library_600x900.jpg
fetch_art hogwarts-legacy https://cdn.akamai.steamstatic.com/steam/apps/990080/library_600x900.jpg
fetch_art minecraft https://www.minecraft.net/content/dam/minecraftnet/games/minecraft/key-art/New_Key-Art_01.jpg
if [ "$failed" -ne 0 ]; then echo 'Algunas fuentes no respondieron. Los productos conservan el arte vectorial local.'; exit 1; fi
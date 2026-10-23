# GamesOG

Proyecto académico para mostrar ediciones físicas ficticias de videojuegos. No vende, copia ni distribuye juegos. Los pedidos son demostraciones sin cobro ni envío.

## Instalar

Requisitos: Ubuntu 24.04, Docker Compose v2 e Internet.

```bash
bash bootstrap.sh
bash scripts/setup-site.sh
```

Abre la dirección `WP_SITE_URL` de `.env`. No compartas ni subas `.env`.

## Administrar

```bash
sudo docker compose ps
sudo docker compose logs -f wordpress
sudo docker compose restart wordpress
```

## Git

```bash
git status
git add -A
git commit -m "Describe brevemente el cambio"
git push
```

No subas `.env`, datos personales ni copias de seguridad. Git no sustituye la copia de datos de WordPress.

## Copias

```bash
bash scripts/backup.sh /ruta/segura/de/copias
```

Las imágenes y marcas pertenecen a sus titulares y se usan solo como referencia académica.

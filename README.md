# GamesOG

Proyecto académico WordPress para explorar la presentación física y personalización visual de colecciones de videojuegos. El importe mostrado representa un servicio ficticio; no vende licencias ni copia, descarga, fabrica o distribuye juegos. Los pedidos son demostraciones sin cobro ni envío.

## Requisitos y arquitectura

Ubuntu 24.04, Docker Engine con Compose v2 y conexión a Internet para descargar imágenes e instalar WooCommerce. WordPress 7.1.2 con PHP 8.3 y Apache, MariaDB 11.4.13 y WooCommerce 11.2.1. El tema GamesOG 2.4.0 y el plugin GamesOG Core 1.2.6 son fuentes propias. WordPress y MariaDB están en una red privada de Compose; solo se publica el puerto web configurable. Los contenedores reinician salvo parada manual. WordPress, la base de datos y las evidencias privadas usan volúmenes persistentes.

Los archivos propios editables están en wp-content/themes/gamesog y wp-content/plugins/gamesog-core. Se montan en el contenedor en solo lectura. Los cambios de tema/plugin requieren actualizar los archivos del proyecto; no editar el núcleo ni plugins de terceros.

## Preparación e instalación

Revisa .env.example. En la primera instalación, bootstrap.sh crea .env con permisos privados y credenciales aleatorias. No compartas .env, no lo añadas a Git y no pegues claves en incidencias. Si .env ya existe, bootstrap.sh se detiene para protegerlo. Después ejecuta:

    bash scripts/setup-site.sh

El script arranca los servicios, instala WooCommerce 11.2.1, activa el tema y plugin y crea páginas y seis productos de forma idempotente. También instala y activa el idioma `es_ES` para WordPress y WooCommerce, pone la visibilidad local de WooCommerce en Live, activa checkout como invitado y deshabilita el modo «Próximamente». Los cambios de contenido en administración se conservan.

En la instalación probada, Compose publicó el sitio en http://172.19.208.7:8080/ y la administración en http://172.19.208.7:8080/wp-admin/. Usa siempre el valor WP_SITE_URL de `.env`: puede cambiarse configurando también WP_BIND_ADDRESS y WP_PORT. La dirección observada es una IP privada del entorno Ubuntu; no se configuró router ni firewall ni se publicó el sitio en Internet.

Consultar en el terminal privado del servidor los nombres de usuario/clave de administración o la URL:

    cd /home/gamesog/GamesOG
    grep -E '^WP_(SITE_URL|ADMIN_USER|ADMIN_PASSWORD)=' .env

Ese comando muestra el secreto solo en el terminal de Ubuntu; no compartas su salida.

## Uso diario

    sudo docker compose ps
    sudo docker compose logs -f wordpress
    sudo docker compose logs -f db
    sudo docker compose stop
    sudo docker compose up -d

Si tu usuario no tiene acceso al socket de Docker, usa `sudo docker compose ...`; el script de instalación solicita sudo cuando lo necesita. No hace falta cambiar los grupos del sistema. Nunca ejecutes down -v ni docker volume rm para “reiniciar”: esos comandos eliminan datos. docker compose down sin -v elimina contenedores/red, pero conserva los volúmenes.

Tras actualizar fuentes del tema o plugin, reinicia solo WordPress para que PHP vuelva a cargar el código: `sudo docker compose restart wordpress`. El reinicio conserva pedidos, archivos y base de datos.

## Contenido y pruebas

WooCommerce administra productos, precio base e inventario. Cada concepto permite CD, DVD o USB. El diseño básico ofrece una paleta de colores; solo Cyberpunk 2077, Elden Ring y Hogwarts Legacy ofrecen edición especial, con arte fijo y sin selector de color (editable por producto en su ficha de administración). El precio regular y el recargo se validan en servidor. Las maquetas representan una caja genérica sin marca de consola: los CD/DVD básicos enseñan carátula, estuche, manual y disco; las especiales usan un estuche metálico SteelBook con grabados temáticos; los USB básicos muestran un dispositivo con el color elegido y el nombre, y los especiales llevan una ilustración fija. Las carátulas frontales de LaunchBox se pueden descargar a `assets/images/covers/` con `bash scripts/download-launchbox-covers.sh` desde Ubuntu. El arte complementario —lateral Box Spine, Clear Logo y Fanart Background— se obtiene con `bash scripts/download-launchbox-product-media.sh` en `assets/images/spines/`, `logos/` y `fanart/`. Ambos scripts guardan los archivos anteriores y los restauran si falla la descarga. El Clear Logo se usa en el USB básico y el Fanart Background en la edición especial; si no hay lateral disponible, la caja muestra el título. Las imágenes y marcas pertenecen a sus titulares y se incluyen solo como referencia académica; GamesOG no está afiliada con ellos.

Los productos están configurados como virtuales para que WooCommerce no solicite dirección de envío ni calcule un envío. Un pedido solo se guarda como demostración. El gateway propio no cobra. Checkout como invitado solicita únicamente nombre y correo ficticios, confirmación explícita y JPEG/PNG de prueba hasta 2 MB. Las evidencias se guardan en el volumen gamesog_private_evidence fuera de la raíz web y solo administración WooCommerce puede verlas desde el pedido; esa respuesta lleva cabeceras privadas y no se almacena en caché. El estado inicial es “Pendiente de revisión de propiedad”. En la ficha del pedido hay una acción protegida para eliminar cada evidencia cuando ya no haga falta.

Compose activa `GAMESOG_LOCAL_DEMO=1` para WordPress y WP-CLI; en ese modo, GamesOG intercepta `wp_mail` y no envía correo. Al migrar a un alojamiento real, no configures esa marca y define el entorno/correo de acuerdo con el proveedor. Contacto se almacena como mensaje privado en Administración > Mensajes de contacto y no envía correo.

Para comprobar la ficha y dos configuraciones distintas del mismo producto en un carrito invitado temporal, ejecuta `python3 scripts/smoke-cart.py` con la web encendida. La prueba usa una cookie aislada, no crea pedidos ni sube evidencias y trata de vaciar ese carrito al terminar.

Para comprobar el pedido y la evidencia de extremo a extremo, ejecuta `sudo -v` y después `python3 scripts/smoke-order.py` en Ubuntu con la web encendida. La prueba usa una imagen PNG mínima generada localmente y un correo aleatorio `example.invalid`; no envía mensajes ni cobra. Comprueba el estado de revisión y la privacidad del archivo y, al terminar, elimina solo pedidos que coincidan con el correo único generado. Si falla el checkout, busca el correo de esta ejecución en Administración; el script elimina solo su PNG huérfano tras verificar el hash y comprobar que ningún pedido lo referencia. No ejecutes esta prueba en producción.

## Copias y riesgos

Crea una copia antes de cambios grandes:

    bash scripts/backup.sh /ruta/segura/de/copias

La copia incluye base de datos, volumen completo de WordPress, evidencias, código fuente y una copia privada de .env. Protege el directorio de destino. Para restaurar, consulta docs/migracion.md. No pruebes la restauración encima del sitio original.

## Resolución de problemas

Si falta Docker, comprueba `sudo systemctl status docker` y `docker compose version`. Si Docker exige permisos, ejecuta los comandos de administración con `sudo`; no compartas la contraseña de sudo ni la añadas al proyecto.

Si la web no responde, revisa `sudo docker compose ps`, `sudo docker compose logs wordpress` y `sudo docker compose logs db`. Si el puerto ya está ocupado, detén el servicio propietario con autorización o cambia WP_PORT y WP_SITE_URL en .env y vuelve a crear solo el contenedor WordPress con `sudo docker compose up -d wordpress`. No cambies ni borres volúmenes.

Si la portada devuelve HTTP 200 con 0 bytes o el registro indica `Permission denied` al cargar una plantilla, comprueba que Apache (UID 33, `www-data`) puede leer las fuentes montadas. `php -l` ejecutado como root no comprueba ese permiso. En Ubuntu, concede solo lectura y acceso de directorio al tema y plugin propios con `sudo chmod -R a+rX wp-content/themes/gamesog wp-content/plugins/gamesog-core`; el script `scripts/setup-site.sh` comprueba como UID 33 la lectura de todos sus archivos y muestra el comando de reparación si encuentra alguno bloqueado.

Para la demostración, usa datos y archivos ficticios. Nunca subas tickets reales, documentos de identidad, contraseñas ni datos personales.

## Historial de cambios con Git

El repositorio conserva el código, la documentación y los recursos visuales del proyecto. Excluye `.env`, copias `.bak`, dependencias descargadas, cachés y datos persistentes de WordPress. Antes de guardar una versión, revisa los cambios y confirma solo lo que quieras conservar:

    git status
    git diff
    git add -A
    git commit -m "Describe el cambio"
    git log --oneline -10

El historial local no sustituye las copias de seguridad de la base de datos y de los volúmenes descritas arriba.

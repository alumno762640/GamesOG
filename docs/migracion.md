# Copias, restauración y migración

## Copia de seguridad

Con los servicios activos, ejecuta bash scripts/backup.sh /ruta/segura. El script crea un directorio con permisos 0700 y archivos 0600: dump consistente de MariaDB, tar del volumen WordPress, tar separado de evidencias privadas, fuentes del proyecto y copia privada de .env. Mantén otra copia cifrada fuera del equipo. No publiques ni envíes estos archivos.

El usuario creó una copia en `/home/gamesog/backups/gamesog-20261016-164059`. Sus cinco componentes quedaron con modo 600 y `tar -tzf` leyó correctamente los tres archivos comprimidos. La copia y el inventario se generaron, pero todavía no se ha restaurado en un entorno aislado ni se ha comprobado su contenido mediante extracción.

## Restauración en entorno aislado

1. Prepara otra máquina o proyecto Docker con versiones compatibles y un nombre nuevo de volúmenes y puerto. No reutilices los volúmenes GamesOG.
2. Restaura el archivo de base de datos en una base vacía.
3. Extrae wordpress-files.tar.gz en un volumen WordPress vacío.
4. Extrae private-evidence.tar.gz fuera de la raíz pública. Ajusta propietario a www-data y permisos de directorio 0700 y archivos 0600.
5. Monta el tema y plugin propios en modo de solo lectura y activa el conjunto.
6. Asegura que la URL temporal coincide o utiliza WP-CLI search-replace con --all-tables --precise. Esta herramienta entiende datos serializados. No hagas sustitución de texto sobre SQL.
7. Comprueba la URL, HTTPS, enlaces permanentes, imágenes, carrito, personalización, pedido y evidencia con usuario no administrador. Verifica que el archivo solo se descarga en administración.
8. Solo tras revisión, planea la migración real. No se conoce aún el alojamiento destino ni se ejecuta migración.

## Adaptación a hosting WordPress convencional

Se necesitan WordPress y PHP compatibles con WooCommerce, MariaDB/MySQL, HTTPS y capacidad para plugins propios. El alojamiento no necesita Docker. Instala WooCommerce desde la fuente oficial y despliega los directorios fuente del tema y plugin. Migra archivos públicos y base de datos con herramientas compatibles con datos serializados.

Docker define DB_HOST como el servicio db, las URLs locales, `WP_ENVIRONMENT_TYPE=local` y `GAMESOG_LOCAL_DEMO=1`. En hosting, configura credenciales nuevas, URL HTTPS y ruta privada fuera de document root, propiedad/permisos de PHP adecuados y copia automática segura de esa carpeta. Si el proveedor no puede garantizar privacidad, desactiva carga y descarga de evidencias antes de aceptar pedidos. No traslades `GAMESOG_LOCAL_DEMO=1` al alojamiento real; decide y configura el correo allí por separado.

Después actualiza DNS y certificados bajo autorización del titular, fuerza HTTPS, revisa salud y seguridad y define copias externas. No se han solicitado accesos del alojamiento porque no ha sido elegido.

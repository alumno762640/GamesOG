# Arquitectura

## Contenedores

Compose crea tres servicios. db ejecuta MariaDB 11.4.13 sin puertos publicados. wordpress ejecuta WordPress 7.1.2, PHP 8.3 y Apache, expone solo el puerto indicado por WP_PORT y se enlaza por defecto a 127.0.0.1. wpcli es un perfil de utilidad ejecutado solo bajo demanda. Los servicios se comunican por una red bridge privada.

## Código y persistencia

- gamesog_wordpress_data guarda núcleo, uploads y plugins instalados.
- gamesog_db_data guarda MariaDB.
- gamesog_private_evidence guarda los archivos de prueba en /var/private/gamesog-evidence, fuera de /var/www/html.
- Los directorios del tema y plugin del repositorio se montan en solo lectura.
- El tema aporta `woocommerce.php` como envoltorio de las vistas de tienda/producto y delega el contenido en `woocommerce_content()`, manteniendo los hooks estándar de WooCommerce.
- Las fichas de producto usan la imagen destacada asignada en WooCommerce o, si falta, el placeholder SVG local correspondiente al slug. La portada enlaza la selección destacada directamente con su ficha.
- Los seis conceptos WooCommerce se marcan como virtuales y no descargables para evitar direcciones de envío y entregas físicas en el pedido ficticio.
- .env almacena secretos locales y se excluye de Git.

La carpeta privada se prepara en cada arranque con propietario www-data y permisos 0700; las evidencias usan nombres UUID aleatorios y permisos 0600. Solo usuarios con manage_woocommerce pueden acceder mediante admin-post con nonce. No se crean adjuntos de medios ni enlaces públicos. Al migrar, el directorio privado debe permanecer fuera de la raíz pública y con permisos equivalentes; si no es posible, desactiva la carga antes de aceptar pedidos.

Para eliminar una evidencia tras la demostración, abre el pedido en WooCommerce y usa “Eliminar evidencia privada”; confirma la acción. Se borra el archivo y se quita su referencia del pedido, manteniendo el estado de revisión. Para una limpieza global, verifica antes el proyecto y conserva solo los datos requeridos; el comando `docker compose exec -T wordpress find /var/private/gamesog-evidence -type f -delete` borra permanentemente todos los archivos. No se ejecuta automáticamente.

## Recorridos

El checkout clásico envía el formulario con `FormData` mediante `assets/js/checkout.js`, porque el envío serializado habitual de WooCommerce no incluye archivos. El servidor acepta solo una imagen ficticia JPEG/PNG, valida MIME real y tamaño máximo, genera un nombre aleatorio y la mueve al volumen privado. Se guarda el nombre aleatorio en el pedido. El administrador accede al archivo con control de capacidad y nonce; desde la misma ficha puede borrarlo individualmente. El estado del pedido queda pendiente de revisión. El gateway propio es una confirmación académica y no procesa pagos.

Una petición llega al puerto local y Apache sirve WordPress. El tema GamesOG presenta la navegación, el contenido editorial y los placeholders de producto. WooCommerce resuelve producto, carrito, checkout y pedido; GamesOG Core acepta solo CD/DVD/USB, los diseños y colores permitidos y recalcula el precio con el precio regular y el recargo guardados en el producto. La configuración se guarda como datos de línea del carrito y del pedido.

Los mensajes de contacto se almacenan como tipo privado no público, visible solo a usuarios con `manage_options`, sin salida de correo. Compose define `GAMESOG_LOCAL_DEMO=1` tanto para WordPress como para WP-CLI. GamesOG Core usa esa marca para interceptar `wp_mail` en local.

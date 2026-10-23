# Checklist de entrega

## Implementado y probado

- [x] Sintaxis JavaScript analizada con Node.js. Evidencia: site.js.
- [x] Ocho archivos SVG se analizaron como XML válido. Evidencia: assets/images.
- [x] Instalación en Ubuntu de WordPress, WooCommerce, tema y plugin; Compose válido, servicios saludables, seis productos publicados y respuestas HTTP de páginas principales. Evidencia: salida de setup-site.sh, wp-cli y curl aportada el 16-10-2026; docs/pruebas.md.
- [x] Sintaxis PHP de tema y plugin válida; versiones PHP/Apache/MariaDB consultadas; almacenamiento privado fuera del document root con permisos 700. Evidencia: salida de comandos ejecutados en Ubuntu el 16-10-2026.
- [x] Sintaxis Bash y ejecución idempotente de `scripts/setup-site.sh` comprobadas; WP-CLI confirmó GamesOG Core 1.2.1 en esa ejecución. Evidencia: salida aportada de Ubuntu el 16-10-2026.
- [x] `compose.yaml` validado después de retirar la definición duplicada de `WP_DEBUG`; se guardó `compose.yaml.pre-wpdebug-cleanup.bak` y se recreó WordPress sin tocar volúmenes. Después, la portada respondió HTTP 200 con 36.435 bytes y no hubo avisos PHP recientes.
- [x] Resuelto el HTML vacío de la portada: Apache/WP-CLI UID 33 no podía leer la plantilla montada. Tras corregir permisos, el usuario confirmó `is_readable()` y HTTP 200 con 36.435 bytes. README documenta la reparación. Evidencia: `docs/pruebas.md`.
- [x] La ficha pública vuelve a mostrar la plantilla WooCommerce: se confirmó «Próximamente» desactivado y `curl` devolvió HTTP 200 con `woocommerce-shell`, opciones GamesOG, nonce y botón de alta al carrito; después, `smoke-cart.py` comprobó alta y precios de dos opciones. Evidencia: `docs/pruebas.md`.
- [x] Copia de seguridad creada; cinco archivos protegidos con permisos 600 y los tres tar.gz leídos correctamente. Evidencia: salidas de `scripts/backup.sh` y `tar -tzf` el 16-10-2026; docs/pruebas.md. Restauración aún sin verificar.
- [x] Prueba integral de pedido con GamesOG Core 1.2.6: Compose válido, lint PHP, pedido #26 pendiente con opciones/precio correctos, evidencia fuera de web y modo 0600, gateway sin cobro, `wp_mail` bloqueado, ruta pública inaccesible, 403 anónimo y carrito vacío. El script limpió pedido y evidencia. Salida de Ubuntu aportada el 16-10-2026.

## Implementado, pendiente de prueba manual

- [x] Compose con WordPress Apache, MariaDB, red privada, volúmenes, puerto web y salud. Evidencia: compose.yaml y `docker compose config --quiet` ejecutado en Ubuntu.
- [x] Tema editable, responsive, entrada, navegación, colores, modo oscuro/claro. Evidencia: wp-content/themes/gamesog.
- [x] Seis placeholders SVG locales originales. Evidencia: assets/images/games.
- [x] Plugin para catálogo, opciones, precio servidor, revisión y contacto. Evidencia: wp-content/plugins/gamesog-core/gamesog-core.php.
- [x] Documentación de arquitectura, diseño, demo, backup/migración y estado de pruebas.
- [x] Scripts de activación y copia. Evidencia: scripts.
- [x] Entrada con diseño de dos paneles, cuadrícula, tema claro/oscuro y apertura al pulsar Empezar; se neutralizaron reglas heredadas de la portada en `site.css`. El usuario confirmó la portada y el HTML servido el 16-10-2026; queda pendiente revisar transición y responsive manualmente. `filemtime` versiona los recursos. Evidencia: `front-page.php`, `functions.php`, `assets/css/intro.css` y `assets/css/site.css`.
- [x] Ruta de imágenes de muestra del inicio corregida para apuntar a `assets/images/games/`; `php -l` pasó y el usuario confirmó HTTP 200 en los seis SVG desde Ubuntu. Evidencia: `front-page.php`, `assets/images/games` y salida de `curl` del 16-10-2026.
- [x] Portada ampliada con selección destacada y botón directo a Personalizar; fichas con placeholders SVG y elección CD/DVD/USB, preset y color con validación servidor; mensajes limitados a administración, productos virtuales sin envío y carga privada reforzada. Evidencia de código: `front-page.php`, `functions.php`, `gamesog-core.php`; comprobación pendiente.
- [x] Checkout clásico envía archivo y pedido con `FormData`; `smoke-order.py` confirmó el recorrido completo. La ficha de pedido incluye acceso y eliminación protegidos de evidencia; la acción de administración sigue pendiente de revisión manual. Evidencia: `assets/js/checkout.js`, `gamesog-core.php`.
- [x] Las imágenes destacadas de productos sustituyen los placeholders desde el panel WooCommerce; los SVG locales siguen como alternativa. Evidencia: `front-page.php`, `functions.php`, `gamesog-core.php`; cambio de una imagen desde Administración pendiente de probar manualmente.
- [x] `setup-site.sh` comprueba la lectura de todos los archivos del tema y plugin como UID 33, incluidos los templates WooCommerce, y muestra el comando de reparación si falla. Configura la visibilidad local de WooCommerce como Live. Evidencia: `scripts/setup-site.sh`; `bash -n` pasó después de la ampliación.
- [x] El código de personalización valida nonce por producto; el filtro de diseño incluye básico y especial; el formulario de contacto detecta fallo de guardado. `woocommerce.php` delega el renderizado en WooCommerce; `smoke-cart.py` verificó alta al carrito, opciones y precio calculado en servidor. `smoke-order.py` completó después el pedido con carga privada.
- [x] La prueba HTTP confirmó dos configuraciones distintas, presets, colores, precios calculados en servidor y vaciado del carrito de invitado. Evidencia: salida de `python3 scripts/smoke-cart.py` aportada por el usuario.
- [x] `smoke-cart.py` confirmó el checkout clásico, nonce, archivo de evidencia, confirmación explícita y pasarela única de demostración, sin crear pedido. Evidencia: salida aportada por el usuario.
- [x] GamesOG Core 1.2.4 corrigió la firma del hook que guarda las opciones en las líneas de pedido. La segunda ejecución de `smoke-order.py` creó el pedido #24 con estado, configuración, precio y evidencia privada correctos, vació el carrito y limpió el pedido.
- [x] GamesOG Core 1.2.5 devuelve 403 explícito al visitante anónimo que solicita evidencia; la ejecución de `smoke-order.py` confirmó esta denegación.
- [x] GamesOG Core 1.2.6 y `compose.yaml` activan `GAMESOG_LOCAL_DEMO=1` para WordPress y WP-CLI; la prueba confirmó el filtro local de `wp_mail`.
- [x] Gateway simulado sin cobro, rechazo de evidencia inválida y supresión local de correo verificados por `scripts/smoke-order.py`.
- [x] Se preparó activación idempotente de `es_ES` para WordPress/WooCommerce y fallback SVG para tarjetas de productos relacionados; el CTA relacionado lleva a Personalizar. Cambios en fuente, validación del servidor y revisión visual pendientes. Evidencia: `scripts/setup-site.sh`, `functions.php`, `assets/css/site.css`; estado detallado en `docs/pruebas.md`.
- [x] El enlace «Cómo funciona» conserva el ancla local desde la portada y evita que la entrada vuelva a cubrir el destino al entrar con `/#como-funciona`. Cambios en fuente; lint y prueba de clic pendientes. Evidencia: `header.php`, `footer.php`, `assets/js/site.js`.

## Pendiente de prueba manual

- [ ] Pruebas visuales de navegador/móvil, logs, backup/restauración y persistencia tras reinicio.
- [ ] Probar selección de formato, diseño y color, precio calculado en servidor y envío al carrito desde las fichas.
- [ ] Completar pruebas manuales de seis fichas, precio, carrito/pedido, privacidad de evidencias y permiso de mensajes. Falta verificar la descarga de evidencia por un administrador autenticado.
- [ ] Confirmar la portada tras recrear WordPress y revisar logs por avisos PHP.
- [ ] Ejecutar `setup-site.sh` y confirmar interfaz WooCommerce en español; revisar imágenes, proporciones y enlaces Personalizar en las recomendaciones de una ficha.
- [ ] Probar «Cómo funciona» desde Inicio y desde una página interna; revisar que se desplaza al bloque sin volver a mostrar la entrada.
- [ ] Revisar visualmente la nueva capa de acabado en escritorio y móvil, con tema claro/oscuro y movimiento reducido; confirmar que la aparición progresiva no deja tarjetas ocultas.
- [ ] Revisar que la galería del producto no tape las pestañas ni el texto durante el scroll.
- [ ] Pulsar cada juego destacado de la portada y confirmar su ficha; revisar hover, foco de teclado y botones de recomendaciones en móvil.

## Pendiente

- [ ] Definir los nombres de los seis integrantes cuando el equipo los facilite.
- [ ] Sustituir imágenes de muestra mediante la administración cuando haya recursos propios autorizados.
- [ ] Revisar diseño con navegador real y realizar presentación de clase.
- [ ] Elegir alojamiento antes de configurar una migración real.

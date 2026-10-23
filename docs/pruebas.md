# Pruebas y estado

Fecha de registro: 16-10-2026.

## Comprobado desde esta sesión

- Se reprodujo la portada vacía: WordPress respondía HTTP 200 con 0 bytes. La prueba de inclusión como UID 33 identificó `Permission denied` al abrir `front-page.php`; el hash del archivo del contenedor coincidía con el proyecto. Tras conceder lectura y búsqueda de directorio con `chmod -R a+rX` solo a tema y plugin, `is_readable()` como UID 33 devolvió `sí` y `/` respondió HTTP 200 con 36.435 bytes. El usuario confirmó la solución el 16-10-2026.
- Confirmada la identidad del montaje: la unidad Z: apunta al home remoto gamesog y Z:/GamesOG ya existía. No se leyeron .env, claves SSH ni archivos personales.
- Confirmado que Docker y WSL no están disponibles en Windows. El acceso SSH desde esta sesión fue denegado; no se consultaron servicios ajenos.
- El usuario reparó el acceso de lectura de `.env` y ejecutó `scripts/setup-site.sh` en Ubuntu.
- Salida aportada por el usuario: MariaDB 11.4.13 y WordPress 7.1.2 con PHP 8.3 Apache quedaron `healthy`; WordPress se instaló correctamente, WooCommerce 11.2.1 se instaló y activó, y se activaron el tema GamesOG y el plugin GamesOG Core. El script confirmó ajustes de no indexación, enlaces permanentes y moneda EUR.
- En la segunda ejecución del script no reaparecieron los avisos por `HTTP_HOST` ni por la caché WP-CLI. La ejecución fue idempotente: los plugins y tema ya activos permanecieron activos, y las opciones de WordPress quedaron iguales.
- WP-CLI confirmó WordPress 7.1.2, WooCommerce 11.2.1, GamesOG Core 1.0.0, tema GamesOG 1.0.0 y los seis productos publicados.
- El usuario comprobó con `php -l` los siete archivos PHP del tema y el plugin: sin errores de sintaxis. Versiones observadas: PHP 8.3.35, Apache 2.4.68 y cliente MariaDB 11.4.13.
- El usuario comprobó que `/var/private/gamesog-evidence` tiene modo 700, propietario `www-data:www-data` y está fuera de `/var/www/html`.
- El usuario ejecutó `scripts/backup.sh /home/gamesog/backups`. Se creó `/home/gamesog/backups/gamesog-20261016-164059` con `wordpress-files.tar.gz`, `private-evidence.tar.gz`, `database.sql`, `env.private` y `project-source.tar.gz`; los cinco archivos quedaron en modo 600. Se confirmó creación y permisos, pero no se ha probado la restauración ni la integridad mediante extracción.
- Después, `tar -tzf` comprobó sin extraer los tres archivos comprimidos (`wordpress-files.tar.gz`, `private-evidence.tar.gz` y `project-source.tar.gz`): los tres pasaron la lectura de archivo. La restauración sigue pendiente.
- Desde Ubuntu, `curl` recibió HTTP 200 para `/`, `/catalogo/`, `/sobre-nosotros/`, `/contacto/`; HTTP 200 para `/carrito/`; HTTP 302 para `/pedido/` sin carrito; y HTTP 301 para la ficha a su URL canónica.
- El puerto observado por el usuario fue `172.19.208.7:8080->80/tcp`; la base de datos no publica puerto en el host. La comprobación de Compose terminó sin errores.
- Node.js ejecutó `--check` sobre `site.js` y `checkout.js`: correcto, incluido después de los últimos cambios. Se analizaron ocho SVG con el parser XML de .NET: correcto.
- La prueba HTTP desde la terminal Windows no pudo abrir el socket por las restricciones de red de esta sesión. El usuario sí confirmó visualmente la entrada mediante capturas; la inspección automatizada del navegador no se pudo iniciar en esta sesión. No se han dado por comprobados todos los tamaños de pantalla ni el flujo de páginas completo.
- Después de recibir las referencias visuales, se añadieron estilos y markup a portada, catálogo y equipo; se amplió la entrada para que el cambio de tema también esté disponible antes de Empezar.
- Tras la captura se retiró el CSS duplicado de `front-page.php` y se aisló la entrada en `intro.css`. Una captura posterior todavía mostró reglas heredadas de versiones anteriores en `site.css` (marco crema y titular desproporcionado); se añadió una capa de compatibilidad al CSS común para corregir ambas estructuras de portada, quitar el marco, ordenar la navegación y conservar la retícula y la apertura central.
- `intro.css` y el CSS común tienen llaves equilibradas; `site.js` pasa `node --check`. El usuario reinició WordPress y confirmó que el contenedor encuentra `intro.css`; `php -l` pasó para `front-page.php` y `functions.php`. Esto no demuestra qué HTML y hojas CSS recibe el navegador: su captura seguía mostrando una estructura de texto anterior. Falta comparar el HTML servido por Apache con la plantilla actual y validar el resultado visual en escritorio y móvil. No se da por corregida visualmente la portada.
- El usuario confirmó con una captura posterior que la entrada ya se ve correctamente y aportó una petición HTTP 200 de la raíz con `id="gamesog-intro"` e `intro-stage`; las hojas `site.css` e `intro.css` llevan versiones por `filemtime`. Reportó después imágenes rotas en otras secciones: se corrigió en `front-page.php` la ruta base de placeholders de `/assets/images/` a `/assets/images/games/`. El usuario confirmó después `php -l` sin errores y HTTP 200 para los seis SVG de muestra desde Ubuntu.
- En la salida de Ubuntu aportada el 16-10-2026, `bash -n scripts/setup-site.sh` y una segunda ejecución de `scripts/setup-site.sh` terminaron correctamente. El preflight de lectura de plantillas no se bloqueó, Compose mostró los servicios saludables, y el script conservó el estado de WooCommerce, GamesOG Core y el tema; no se duplicaron productos. WP-CLI confirmó GamesOG Core 1.2.1.
- El usuario validó `docker compose config --quiet`, creó `compose.yaml.pre-wpdebug-cleanup.bak`, retiró de `compose.yaml` la definición duplicada de `WP_DEBUG` y recreó solo el servicio WordPress. No se eliminaron volúmenes ni se tocaron datos de MariaDB. Después, la portada respondió HTTP 200 con contenido y los logs no mostraron avisos PHP recientes.
- El usuario ejecutó `php -l` como UID 33 sobre `gamesog-core.php` después de los últimos cambios: sin errores de sintaxis.
- Verificación posterior de Ubuntu: WP-CLI informa GamesOG Core 1.2.2, `/` responde HTTP 200 con 36.435 bytes, y el catálogo devuelve seis tarjetas con filtros básico/especial. Inicialmente la ficha pública respondía HTTP 200 sin formulario, aunque el producto era comprable y el hook generaba el nonce.
- La investigación encontró la pantalla de WooCommerce «Great things are on the horizon», causada por la visibilidad «Próximamente». La opción `woocommerce_coming_soon` quedó confirmada como `no` mediante WP-CLI.
- Después, la ficha canónica respondió HTTP 200 con 44.199 bytes. El HTML incluye `woocommerce-shell`, `gamesog-options`, `gamesog_config_nonce` y `single_add_to_cart_button`. `woocommerce.php` pasó `php -l` como UID 33. Esto confirma el formulario renderizado; falta probar su envío interactivo y el cálculo final del carrito.

## Cambios de contenido y flujo

- La portada editorial presenta Red Dead Redemption 2 como concepto destacado, incluye un acceso directo a personalizarlo y explica el alcance académico y la propiedad previa del juego.
- Las seis fichas de WooCommerce sustituyen la galería vacía por el placeholder SVG local según el slug, con alternativa gráfica local si falta una imagen.
- El catálogo, la ficha y el juego destacado usan una imagen asignada desde el panel WooCommerce cuando existe; el placeholder SVG local queda como alternativa. Falta comprobar el reemplazo desde Administración.
- El formulario ahora ofrece edición especial solo en productos habilitados (Cyberpunk 2077, Elden Ring y Hogwarts Legacy, editable desde administración). La especial usa el arte fijo y no admite color; el servidor rechaza esa opción en los demás juegos y sigue calculando el recargo desde el producto.
- `smoke-cart.py` se ha actualizado para cubrir un diseño básico con color y una edición especial sin color. La prueba está pendiente de ejecutarse después de este cambio; la ejecución anterior corresponde al comportamiento previo.
- Las maquetas SVG usan carátulas locales, caja genérica sin marca de consola y USB con forma de memoria física. Son representaciones académicas; no se promete compatibilidad, DRM ni uso sin conexión. La revisión visual en navegador y la comprobación del manual y SteelBook quedan pendientes.
- Los mensajes de contacto ahora requieren capacidad `manage_options` para leerlos o editarlos.
- El guardado del formulario de contacto ahora trata también el resultado `0` de `wp_insert_post()` como fallo; falta probar envío y lectura administrativa.
- La carga privada no guarda archivos si el checkout ya tiene errores; vuelve a verificar MIME real, tamaño, directorio privado fuera de `ABSPATH` y nombre generado. Los pedidos de demostración ya no reducen inventario.
- Se añadió `assets/js/checkout.js` para enviar el checkout clásico de WooCommerce con `FormData`, incluidos imagen, nonce y campos del pedido. `smoke-cart.py` comprobó la estructura y `smoke-order.py` confirmó el envío real del pedido y del archivo.
- La respuesta administrativa de evidencias privadas añade Cache-Control private/no-store, Pragma no-cache, Expires 0, nosniff y no-referrer. `smoke-order.py` verificó que el visitante anónimo recibe 403; falta probar la descarga como administrador y la acción de eliminación desde la ficha.
- El script de instalación incluye una comprobación de lectura como UID 33 para los PHP de entrada del tema y plugin. `bash -n` y una segunda ejecución idempotente pasaron en Ubuntu el 16-10-2026.
- El instalador ahora configura `woocommerce_coming_soon=no` para mantener accesibles las fichas en la instalación local. El usuario confirmó después que `bash -n scripts/setup-site.sh` terminó sin errores.
- Se amplió el preflight de permisos de `setup-site.sh`: ahora recorre todos los archivos del tema y plugin como UID 33, incluidos `woocommerce.php`, antes de activar el sitio. El usuario ejecutó `bash -n scripts/setup-site.sh` después del cambio y no informó errores.
- `scripts/smoke-cart.py` obtiene una sesión invitada aislada, añade dos opciones diferentes de Red Dead Redemption 2, revisa configuración y precio visibles y vacía las líneas de prueba. Primera ejecución: pasó configuración, diseño, color y limpieza; la aserción de importes falló por una comparación de formato. Se ajustó la prueba para comparar los importes normalizados que muestra WooCommerce.
- Segunda ejecución comunicada por el usuario: pasaron las dos configuraciones separadas, los presets básico/especial, los colores, los precios calculados en servidor y el vaciado del carrito temporal.
- Tercera ejecución comunicada por el usuario: pasaron el formulario clásico de checkout, nonce, campo de evidencia, confirmación explícita, única pasarela sin cobro y limpieza del carrito.
- Última ejecución comunicada por el usuario: `bash -n scripts/setup-site.sh`, `bash scripts/setup-site.sh` y `python3 scripts/smoke-cart.py` terminaron correctamente. El instalador informó que la visibilidad «Próximamente» y el checkout de invitado ya tenían el valor esperado. La prueba confirmó dos configuraciones separadas, presets, colores, precios calculados en servidor, checkout clásico, nonces, evidencia, confirmación explícita, campos mínimos de nombre y correo, pasarela única sin cobro y vaciado del carrito temporal. No creó ningún pedido.
- Se añadió `scripts/smoke-order.py` para rechazar un archivo que finge ser PNG y probar una imagen válida, pedido, almacenamiento privado, permisos, denegación HTTP y supresión de correo. La limpieza está restringida a un pedido asociado al correo aleatorio `example.invalid` generado en esa ejecución.
- Diagnóstico comunicado por el usuario: el HTTP 500 era un `ArgumentCountError` en `woocommerce_checkout_create_order_line_item`; el callback de GamesOG aceptaba un argumento, mientras WooCommerce enviaba cuatro. No se encontraron pedidos parciales; sí había un PNG de 68 bytes en el volumen privado.
- Corregido en GamesOG Core 1.2.4: el callback acepta los cuatro argumentos del hook. `smoke-order.py` genera un PNG único por hash y, si el checkout falla, solo elimina ese artefacto cuando no existe ningún pedido que lo referencie y el nombre, MIME y permisos coinciden.
- Segunda ejecución aportada: el pedido #24 se creó con estado de revisión, configuración y precio correctos; la evidencia quedó privada y el carrito se vació. El bloqueo de correo no pasó porque el contenedor WP-CLI no heredaba el modo local; la prueba tampoco recibió 403 explícito en la ruta anónima.
- GamesOG Core 1.2.5 corrigió la denegación anónima. En la tercera ejecución aportada, el pedido #25 pasó todas las comprobaciones funcionales y se limpió, incluida la respuesta 403. Seguía fallando la detección del modo de correo.
- Corregido en GamesOG Core 1.2.6 y `compose.yaml`: ambos servicios reciben `WP_ENVIRONMENT_TYPE=local` y `GAMESOG_LOCAL_DEMO=1`; el plugin bloquea `wp_mail` cuando el entorno es local o está marcada la demo. La prueba aplica el filtro y comprueba la marca local.
- Última ejecución aportada por el usuario: `docker compose config --quiet` y `php -l` pasaron; WP-CLI mostró Core 1.2.6. Se recreó únicamente WordPress y DB siguió saludable. `smoke-order.py` pasó todas las comprobaciones: rechazo de archivo inválido, pedido #26 pendiente, configuración/precio, gateway simulado, evidencia privada con modo 0600, bloqueo de `wp_mail`, ruta pública inaccesible, 403 anónimo y carrito vacío. El script eliminó el pedido, la evidencia y el PNG huérfano; no quedó artefacto de prueba.
- El script de datos repetible marca los productos como virtuales y no descargables para que el checkout no solicite dirección ni simule una entrega.
- Esta sesión de edición no tiene acceso directo al Docker remoto ni al navegador del servidor. La prueba automatizada integral del pedido ya pasó según la salida de Ubuntu aportada; las comprobaciones visuales y el acceso autenticado de administración siguen pendientes.

## Pendiente de prueba manual o en Ubuntu

- Comprobar visualmente acceso a la administración, enlaces permanentes y persistencia tras reinicio sin eliminar volúmenes.
- Recorrer entrada Start, navegación, tema oscuro/claro, menú móvil, búsqueda y filtros.
- Revisar visualmente que las imágenes del inicio, catálogo y fichas cargan en el navegador.
- Revisar el interruptor de tema y la transición en móvil y escritorio en navegador.
- Probar los seis precios base y recargos especiales; comprobar configuración en carrito, pedido y administración, incluyendo configuraciones distintas del mismo producto.
- Revisar manualmente el checkout invitado en navegador móvil y escritorio; la prueba automatizada ya confirmó pedido pendiente, gateway sin cobro, carrito vacío y correo bloqueado.
- Probar adicionalmente extensión incorrecta, archivo mayor de 2 MB y evidencia ausente; `smoke-order.py` ya comprobó PNG válido, rechazo de contenido falso, almacenamiento privado, ruta pública y denegación anónima.
- Enviar contacto y verificar su acceso administrativo.
- Comprobar la portada y CTA de producto destacado; abrir una ficha de cada juego, cambiar CD/DVD/USB, color y diseño y revisar imagen local, precio y configuración conservada.
- Probar interactivamente las opciones, el cálculo del precio en servidor y el envío al carrito desde una ficha de producto.
- `smoke-cart.py` confirmó por HTTP dos variantes de un producto y vació la sesión temporal. `smoke-order.py` pasó integralmente y limpió el pedido #26; los pedidos #24 y #25 de las iteraciones anteriores también se limpiaron.
- Comprobar enlaces, imágenes SVG, consola, registros, teclado, móvil y ausencia de desbordamiento.
- Probar integridad y restauración de la copia en un entorno aislado.

La revisión visual requiere abrir la web en un navegador con acceso al servidor. El flujo automatizado del pedido y las protecciones de evidencia/correo pasaron en Ubuntu con GamesOG Core 1.2.6. La prueba no sustituye la revisión visual ni la comprobación de descarga por un administrador autenticado.

## Ajustes de idioma e imágenes relacionados (16-10-2026)

- Se añadió a `scripts/setup-site.sh` la instalación y activación del idioma `es_ES` para WordPress y del paquete español de WooCommerce. Está preparado para instalaciones existentes; todavía falta ejecutar el script en Ubuntu y confirmar `wp option get WPLANG` y que las cadenas estándar de tienda aparecen en español.
- Se añadió un fallback de imágenes locales para los productos GamesOG sin adjunto multimedia. La rejilla de productos relacionados usa el SVG local del juego y un recorte cuadrado uniforme; se mantiene la imagen destacada si se asigna desde WordPress. Pendiente confirmar con navegador en Ubuntu que las seis imágenes cargan correctamente en fichas relacionadas.
- En recomendaciones de WooCommerce se reemplazó la acción de añadir directamente al carrito por el enlace «Personalizar», para mantener las opciones obligatorias de cada producto. Pendiente comprobar enlaces y maquetación en escritorio y móvil.
- El título de recomendaciones se fija como «Juegos relacionados». Las demás cadenas estándar dependen del paquete `es_ES` de WooCommerce.
- No se da por probado el resultado de estos cambios hasta ejecutar `bash -n scripts/setup-site.sh`, el lint PHP del tema, `scripts/setup-site.sh`, y revisar la salida HTML y visual de una ficha y sus recomendaciones.
- Se corrigió el enlace «Cómo funciona»: desde la portada usa un ancla local y desde otras páginas apunta a la portada con el ancla. La entrada ya no vuelve a cubrir la sección al entrar mediante `/#como-funciona`; pendiente comprobar el clic desde portada y desde una página interna.
- La salida de Ubuntu confirmó `WPLANG=es_ES`, instalación del paquete de traducción de WooCommerce y cuatro imágenes locales, cuatro CTA «Personalizar» y el encabezado «Juegos relacionados» en la ficha. El listado `wp language plugin list` mostró solo `en_US`; revisar en el HTML del sitio si las cadenas estándar de WooCommerce aparecen traducidas antes de cerrar esta comprobación.

## Pulido visual (16-10-2026)

- Se añadió `assets/css/polish.css` como capa visual posterior a la entrada: jerarquía de cabecera, foco y hover de botones, profundidad de tarjetas, galerías de producto y componentes de carrito/checkout; usa los colores y variables existentes.
- Se añadió aparición progresiva breve a filas editoriales, tarjetas y recomendaciones al entrar en viewport. La función no oculta contenido si no existe `IntersectionObserver`, si falla la inicialización o si el navegador indica movimiento reducido.
- Validación pendiente en el contenedor Ubuntu y navegador real: sintaxis PHP, respuesta HTTP de `polish.css`, móvil/escritorio, interruptor de tema, navegación por teclado y `prefers-reduced-motion`. En esta sesión no fue posible iniciar el navegador visual.
- La captura del usuario mostró que la galería fija de la ficha se superponía a las pestañas y al contenido al bajar. Se eliminó `position: sticky` de la imagen y se mantuvo el layout en cuadrícula. Pendiente validar el scroll en la ficha desde navegador tras refrescar la hoja versionada.
- La portada ahora resuelve los enlaces destacados desde el SKU real del producto y hace clicables las imágenes y CTA de Minecraft, Cyberpunk 2077 y Stardew Valley. Los hover/foco resaltan arte y acción; la rejilla relacionada limita móvil a dos columnas y ajusta el botón Personalizar al ancho disponible. Lint PHP y revisión visual aún pendientes en Ubuntu.

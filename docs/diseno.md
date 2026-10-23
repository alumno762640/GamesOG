# Diseño

## Identidad y paleta

GamesOG usa un modo oscuro inicial de negro cálido (#171715) y crema (#f4f0e6). El modo claro invierte la base con crema y tinta oscura. El amarillo suave (#f3d477) marca acciones y pequeños titulares; el azul (#78b8d0) se reserva para foco accesible y el coral (#df806b) para detalles. Las variables están al principio de assets/css/site.css.

La tipografía usa la pila de sistema para evitar descargas y dependencias externas. Los títulos combinan tamaño fluido y tracking compacto. Espaciado, anchos, radios y transiciones usan variables compartidas.

## Componentes

- Cabecera con navegación, menú móvil, contador del carrito y cambio de tema.
- Entrada Press start a pantalla completa, sin marco, con cuadrícula tenue y dos columnas: texto a la izquierda y pieza de muestra a la derecha. Al pulsar Empezar, la capa se separa en dos paneles que se deslizan a los lados; el titular y el objeto acompañan la apertura con un rebote breve. Sus estilos viven en intro.css y usan las variables compartidas para que el interruptor de tema también afecte a esta pantalla.
- Hero editorial con juego destacado, llamada directa a personalizar y aclaración visible del alcance académico; bloques alternos con placeholders SVG locales.
- Tarjetas y filtros de catálogo.
- Formularios y componentes nativos de WooCommerce.
- Fichas con una maqueta de producto: estuche genérico sin marca de consola, carátula local, manual y disco para el diseño básico de CD/DVD; SteelBook grabado con arte fijo para las especiales; carcasa USB con etiqueta o ilustración impresa. El color solo se elige en el diseño básico.
- Pie con aviso académico y atribución de titularidad.
- Galería editorial de conceptos locales en portada y catálogo con retícula clara, bordes definidos y filtros en primer plano.
- Sección de equipo con seis retratos gráficos anónimos; los nombres siguen editables y no se atribuyen identidades.

## Temas, accesibilidad y movimiento

El tema se guarda en localStorage. El HTML tiene controles accesibles y navegación por teclado; el menú móvil usa aria-expanded. El foco visible usa azul. La entrada se revela con JavaScript; si JavaScript falla, permanece disponible la portada y el resto del contenido. La capa se muestra en cada nueva carga de la raíz, no al navegar a catálogo o producto.

La dirección visual toma de las referencias una entrada de tipografía grande sobre fondo oscuro, secciones editoriales alternas, pequeños detalles de pixel art y una retícula de catálogo. La portada presenta una selección editorial con accesos a ejemplos; el catálogo usa imágenes cuadradas y filtros destacados; sobre nosotros muestra seis retratos simbólicos sin atribuir identidades. Las imágenes de juegos son placeholders SVG locales originales, no se reutilizan portadas o marcas de las referencias. El interruptor cambia las variables de color de la entrada y del resto del sitio. La entrada ocupa el viewport completo sin borde exterior, bloquea el scroll del documento y se ajusta al alto dinámico del navegador. Al pulsar Empezar, el titular y el objeto se separan con un rebote corto. Con movimiento reducido, se usa un fundido.

Se anima opacidad y transformaciones breves. prefers-reduced-motion elimina transiciones. Los puntos de ruptura principales son 900 px y 580 px; la cuadrícula pasa de tres columnas a dos y luego una.

## Capa de acabado visual

`assets/css/polish.css` añade una capa acotada sobre los estilos existentes: profundidad sutil, estados hover con rebote corto, énfasis en botones y tarjetas, tratamiento de formularios de WooCommerce y adaptación del producto a dos columnas en escritorio y una en móvil. Mantiene los SVG locales y los colores de GamesOG; no añade fuentes ni servicios externos.

Las filas editoriales, tarjetas del catálogo, pasos, equipo y recomendaciones aparecen al entrar en pantalla mediante `IntersectionObserver`. Solo se aplica la clase que oculta inicialmente una pieza cuando el navegador admite el observador y la persona no ha pedido movimiento reducido. Si falta JavaScript, falla el observador o se activa `prefers-reduced-motion`, el contenido se muestra desde el principio. El retardo se limita a tres grupos cortos para evitar una cascada lenta.

En la portada, las imágenes y enlaces de cada juego destacado llevan directamente a la ficha WooCommerce resuelta por SKU; el bloque «Explora el archivo» también es clicable por toda su superficie. Hover y foco elevan la tarjeta, amplían ligeramente el arte y resaltan la acción. En móvil, las recomendaciones se mantienen en dos columnas y el botón «Personalizar» ocupa el ancho de la tarjeta. La animación de hover se desactiva con movimiento reducido.

<?php
get_header();
$art = get_template_directory_uri() . "/assets/images/games/";
$cover = static function ($slug) use ($art) {
  $relative = "/assets/images/optimized/covers/" . $slug . ".jpg";
  if (!is_readable(get_template_directory() . $relative)) $relative = "/assets/images/covers/" . $slug . ".jpg";
  return is_readable(get_template_directory() . $relative) ? get_template_directory_uri() . $relative : $art . $slug . ".svg";
};
$mockup = static function ($slug, $title) use ($cover) {
  return ' class="gamesog-mockup-loading" data-gamesog-mockup="1" data-gamesog-title="' . esc_attr($title) . '" data-gamesog-slug="' . esc_attr($slug) . '" data-gamesog-cover="' . esc_url($cover($slug)) . '" data-gamesog-format="DVD" data-gamesog-design="basico"';
};$featured = function_exists("wc_get_product_id_by_sku") ? wc_get_product(wc_get_product_id_by_sku("gamesog-red-dead-redemption-2")) : false;
$productUrl = static function ($slug) {
  $id = function_exists("wc_get_product_id_by_sku") ? wc_get_product_id_by_sku("gamesog-" . $slug) : 0;
  return $id ? get_permalink($id) : home_url("/" . $slug . "/");
};
$redDeadUrl = $productUrl("red-dead-redemption-2");
$minecraftUrl = $productUrl("minecraft");
$cyberpunkUrl = $productUrl("cyberpunk-2077");
$stardewUrl = $productUrl("stardew-valley");
$eldenUrl = $productUrl("elden-ring");
$hogwartsUrl = $productUrl("hogwarts-legacy");
?>
<main id="contenido" tabindex="-1">
  <section class="hero home-hero" aria-labelledby="home-title">
    <div class="hero-copy-block">
      <p class="eyebrow">GamesOG · colección · memoria · objeto</p>
      <h1 id="home-title">Conserva el recuerdo de lo que juegas.</h1>
      <p class="hero-copy">Explora una colección digital desde otra perspectiva: elige un formato de muestra y un acabado visual para imaginar cómo podría ocupar un lugar en tu estantería.</p>
      <p class="hero-disclaimer">Proyecto académico. Debes poseer previamente el juego. No vendemos licencias ni copiamos, descargamos, fabricamos o distribuimos videojuegos.</p>
      <div class="hero-buttons">
        <a class="button" href="<?php echo esc_url($redDeadUrl); ?>">Personalizar la edición destacada <span aria-hidden="true">↗</span></a>
        <a class="button secondary" href="<?php echo esc_url(home_url("/catalogo/")); ?>">Explorar catálogo</a>
      </div>
    </div>
    <a class="hero-art hero-featured-art" href="<?php echo esc_url($redDeadUrl); ?>" aria-label="Ver la ficha de Red Dead Redemption 2">
      <img<?php echo $mockup("red-dead-redemption-2", "Red Dead Redemption 2"); ?> src="<?php echo esc_url($cover("red-dead-redemption-2")); ?>" alt="Caja DVD con carátula y lomo de Red Dead Redemption 2" fetchpriority="high" decoding="async">
      <span class="hero-featured-tag"><span>Selección destacada</span><strong>Red Dead Redemption 2</strong><span>Caja DVD · portada y lomo · edición destacada<?php if ($featured && function_exists("wc_price")) : ?> · desde <?php echo wp_kses_post(wc_price((float) $featured->get_regular_price())); ?><?php endif; ?></span></span>
    </a>
  </section>

  <section class="section" aria-labelledby="archivo-title">
    <div class="section-heading">
      <div><p class="eyebrow">Archivo seleccionado</p><h2 id="archivo-title">Una colección se construye pieza a pieza.</h2></div>
      <a class="button secondary" href="<?php echo esc_url(home_url("/catalogo/")); ?>">Ver los seis juegos</a>
    </div>
    <div class="feature-row">
      <a class="feature-image feature-image-link" href="<?php echo esc_url($minecraftUrl); ?>" aria-label="Abrir ficha de Minecraft"><img<?php echo $mockup("minecraft", "Minecraft"); ?> src="<?php echo esc_url($cover("minecraft")); ?>" alt="Caja DVD con carátula y lomo de Minecraft"><span class="feature-image-cta">Ver Minecraft <span aria-hidden="true">↗</span></span></a>
      <div class="feature-copy"><p class="eyebrow">01 · Minecraft · edición de muestra</p><h3>Un objeto para recordar lo que jugaste.</h3><div class="accent-rule"></div><p>Las imágenes y los nombres ilustran este servicio académico. Son propuestas visuales sin relación comercial con los titulares de los videojuegos.</p><a class="feature-game-link" href="<?php echo esc_url($minecraftUrl); ?>">Explorar Minecraft <span aria-hidden="true">↗</span></a></div>
    </div>
    <div class="feature-row">
      <a class="feature-image feature-image-link" href="<?php echo esc_url($cyberpunkUrl); ?>" aria-label="Abrir ficha de Cyberpunk 2077"><img<?php echo $mockup("cyberpunk-2077", "Cyberpunk 2077"); ?> src="<?php echo esc_url($cover("cyberpunk-2077")); ?>" alt="Caja DVD con carátula y lomo de Cyberpunk 2077"><span class="feature-image-cta">Ver Cyberpunk 2077 <span aria-hidden="true">↗</span></span></a>
      <div class="feature-copy"><p class="eyebrow">02 · Cyberpunk 2077 · acabados</p><h3>Formato, paleta y diseño, sin un editor complicado.</h3><div class="accent-rule"></div><p>Compara presets preparados por GamesOG. Los discos se presentan en el concepto con caja y carátula; el USB se personaliza con color y etiqueta.</p><a class="feature-game-link" href="<?php echo esc_url($cyberpunkUrl); ?>">Explorar Cyberpunk 2077 <span aria-hidden="true">↗</span></a></div>
    </div>
    <div class="feature-row">
      <a class="feature-image feature-image-link" href="<?php echo esc_url($stardewUrl); ?>" aria-label="Abrir ficha de Stardew Valley"><img<?php echo $mockup("stardew-valley", "Stardew Valley"); ?> src="<?php echo esc_url($cover("stardew-valley")); ?>" alt="Caja DVD con carátula y lomo de Stardew Valley"><span class="feature-image-cta">Ver Stardew Valley <span aria-hidden="true">↗</span></span></a>
      <div class="feature-copy"><p class="eyebrow">03 · Stardew Valley · coleccionismo</p><h3>El juego ya es tuyo. Aquí solo simulamos su presentación.</h3><div class="accent-rule"></div><p>El importe corresponde a un servicio y soporte ficticios. La acreditación de propiedad también es una simulación: no demuestra derechos legales ni autoriza copias.</p><a class="feature-game-link" href="<?php echo esc_url($stardewUrl); ?>">Explorar Stardew Valley <span aria-hidden="true">↗</span></a></div>
    </div>
  </section>

  <section class="browse-section" aria-labelledby="browse-title">
    <div class="section-heading"><div><p class="eyebrow">Tu próxima pieza de colección</p><h2 id="browse-title">Explora el archivo</h2></div><a class="button secondary" href="<?php echo esc_url(home_url("/catalogo/")); ?>">Ver los seis conceptos</a></div>
    <div class="browse-grid">
      <a class="browse-card" href="<?php echo esc_url($redDeadUrl); ?>"><img<?php echo $mockup("red-dead-redemption-2", "Red Dead Redemption 2"); ?> loading="lazy" src="<?php echo esc_url($cover("red-dead-redemption-2")); ?>" alt="Caja DVD con carátula y lomo de Red Dead Redemption 2"><span>Red Dead Redemption 2 <b aria-hidden="true">↗</b></span></a>
      <a class="browse-card" href="<?php echo esc_url($eldenUrl); ?>"><img<?php echo $mockup("elden-ring", "Elden Ring"); ?> loading="lazy" src="<?php echo esc_url($cover("elden-ring")); ?>" alt="Caja DVD con carátula y lomo de Elden Ring"><span>Elden Ring <b aria-hidden="true">↗</b></span></a>
      <a class="browse-card" href="<?php echo esc_url($hogwartsUrl); ?>"><img<?php echo $mockup("hogwarts-legacy", "Hogwarts Legacy"); ?> loading="lazy" src="<?php echo esc_url($cover("hogwarts-legacy")); ?>" alt="Caja DVD con carátula y lomo de Hogwarts Legacy"><span>Hogwarts Legacy <b aria-hidden="true">↗</b></span></a>
    </div>
  </section>

  <section class="section" id="como-funciona" aria-labelledby="how-title">
    <p class="eyebrow">Un recorrido sencillo</p><h2 id="how-title">Cómo funciona</h2>
    <div class="how-grid">
      <article class="how-card"><h3>Elige tu juego</h3><p>Explora los ejemplos del catálogo y abre la ficha que quieras personalizar.</p></article>
      <article class="how-card"><h3>Escoge formato y diseño</h3><p>Combina un formato de muestra con un preset y un color disponible.</p></article>
      <article class="how-card"><h3>Aporta una evidencia ficticia</h3><p>La demostración queda privada para revisión manual; no se verifica de forma concluyente la propiedad.</p></article>
      <article class="how-card"><h3>Confirma el pedido simulado</h3><p>Se guarda en WooCommerce como pendiente. No hay cobro, fabricación ni envío.</p></article>
    </div>
    <p class="notice">Utiliza únicamente datos y archivos ficticios. No subas tickets reales, documentos de identidad, contraseñas ni datos personales.</p>
  </section>

  <section class="section home-about" aria-labelledby="about-title">
    <div class="section-heading"><div><p class="eyebrow">Quiénes somos</p><h2 id="about-title">Seis estudiantes, una idea de colección.</h2></div><a class="button secondary" href="<?php echo esc_url(home_url("/sobre-nosotros/")); ?>">Conocer el proyecto</a></div>
    <p class="hero-copy">GamesOG es una empresa ficticia creada por seis estudiantes de ASIR. Exploramos el valor afectivo de conservar el recuerdo de una colección digital mediante un objeto de presentación personalizable.</p>
    <p class="home-about-note">Los videojuegos y sus marcas pertenecen a sus respectivos titulares. Este proyecto no está afiliado a ellos.</p>
  </section>
</main>

<?php if (is_front_page()) : ?>
<section id="gamesog-intro" class="intro-screen" data-intro hidden role="dialog" aria-modal="true" aria-label="Entrada de GamesOG">
  <div class="intro-door intro-door-left" aria-hidden="true"></div>
  <div class="intro-door intro-door-right" aria-hidden="true"></div>
  <header class="intro-topline">
    <a class="brand" href="<?php echo esc_url(home_url("/")); ?>"><span class="brand-mark"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 32" fill="none"><path d="M14 4h20c5 0 8 3 9 8l3 12c1 4-4 6-7 3l-6-6H15l-6 6c-3 3-8 1-7-3l3-12c1-5 4-8 9-8Z" fill="#F3D477" stroke="#171715" stroke-width="2"/><path d="M13 10v10m-5-5h10" stroke="#171715" stroke-width="2.5"/><circle cx="33" cy="13" r="2" fill="#327DA1"/><circle cx="38" cy="18" r="2" fill="#D76C59"/></svg></span><span>GamesOG</span></a>
    <div class="intro-actions">
      <nav class="intro-nav" aria-label="Navegación de entrada"><a href="<?php echo esc_url(home_url("/catalogo/")); ?>">Catálogo</a><a href="<?php echo esc_url(home_url("/sobre-nosotros/")); ?>">Sobre nosotros</a><a href="<?php echo esc_url(home_url("/contacto/")); ?>">Contacto</a></nav>
      <button class="theme-toggle" type="button" aria-label="Cambiar a tema claro" aria-pressed="false">◐ <span class="theme-label">Tema</span></button>
    </div>
  </header>
  <div class="intro-stage">
    <div class="intro-copy">
      <p class="eyebrow">Colección · memoria · objeto</p>
      <h2 class="intro-title" aria-label="Press start to continue"><span class="intro-word intro-word-left">Press</span><span class="intro-word intro-word-left">start</span><span class="intro-word intro-word-small intro-word-right">to continue</span></h2>
      <p class="intro-copy-text">Tu colección digital merece un lugar en tu estantería. Elige un juego, escoge el formato y crea una edición de muestra con tu estilo.</p>
      <button class="button intro-start" type="button" data-start>Empezar <span aria-hidden="true">↗</span></button>
    </div>
    <div class="intro-art" aria-hidden="true"><img<?php echo $mockup("red-dead-redemption-2", "Red Dead Redemption 2"); ?> src="<?php echo esc_url($cover("red-dead-redemption-2")); ?>" alt=""></div>
  </div>
</section>
<?php endif; ?>
<?php get_footer(); ?>

<?php
if (!defined("ABSPATH")) exit;
get_header();
?>
<main id="contenido" class="page-shell woocommerce-shell" tabindex="-1">
    <?php woocommerce_content(); ?>
</main>
<?php get_footer(); ?>

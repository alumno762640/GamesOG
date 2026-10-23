<?php
if (!defined("ABSPATH")) exit;
function gamesog_setup() {
 add_theme_support("title-tag"); add_theme_support("post-thumbnails"); add_theme_support("woocommerce");
 add_theme_support("html5", ["search-form","gallery","caption","style","script"]);
 register_nav_menus(["primary"=>__("Navegación principal","gamesog")]);
}
add_action("after_setup_theme","gamesog_setup");
function gamesog_theme_bootstrap() { echo "<script>try{var t=localStorage.getItem(\"gamesog-theme\");if(t===\"light\"||t===\"dark\"){document.documentElement.dataset.theme=t;}}catch(e){}</script>"; }
add_action("wp_head","gamesog_theme_bootstrap",1);
function gamesog_assets() {
 wp_enqueue_style("gamesog",get_stylesheet_uri(),[],filemtime(get_stylesheet_directory()."/style.css"));
 wp_enqueue_style("gamesog-components",get_template_directory_uri()."/assets/css/site.css",["gamesog"],filemtime(get_template_directory()."/assets/css/site.css"));
 wp_enqueue_style("gamesog-intro",get_template_directory_uri()."/assets/css/intro.css",["gamesog-components"],filemtime(get_template_directory()."/assets/css/intro.css"));
 wp_enqueue_style("gamesog-polish",get_template_directory_uri()."/assets/css/polish.css",["gamesog-intro"],filemtime(get_template_directory()."/assets/css/polish.css"));
 wp_enqueue_script("gamesog",get_template_directory_uri()."/assets/js/site.js",[],filemtime(get_template_directory()."/assets/js/site.js"),true);
 wp_enqueue_script("gamesog-product-mockups",get_template_directory_uri()."/assets/js/product-mockups.js",["gamesog"],filemtime(get_template_directory()."/assets/js/product-mockups.js"),true);
 if(function_exists("is_checkout")&&is_checkout()) {
  wp_enqueue_script("gamesog-checkout",get_template_directory_uri()."/assets/js/checkout.js",["jquery","wc-checkout"],filemtime(get_template_directory()."/assets/js/checkout.js"),true);
 }
}
add_action("wp_enqueue_scripts","gamesog_assets");

function gamesog_use_local_product_art() {
 if (!function_exists("is_product") || !is_product()) return;
 remove_action("woocommerce_before_single_product_summary","woocommerce_show_product_images",20);
 add_action("woocommerce_before_single_product_summary","gamesog_product_art",20);
}
add_action("wp","gamesog_use_local_product_art");
function gamesog_product_art() {
 global $product;
 if (!$product) return;
 if ($product->get_image_id()) {
  $image = wp_get_attachment_image($product->get_image_id(), "woocommerce_single", false, ["alt" => "Maqueta de presentación para " . $product->get_name(), "fetchpriority" => "high", "data-gamesog-mockup" => "1", "data-gamesog-title" => $product->get_name(), "data-gamesog-slug" => $product->get_slug(), "data-gamesog-cover" => get_template_directory_uri() . "/assets/images/optimized/covers/" . $product->get_slug() . ".jpg", "data-gamesog-format" => $product->get_meta("_gamesog_format") ?: "DVD", "data-gamesog-design" => $product->get_meta("_gamesog_design_filter") ?: "basico"]);
  echo '<div class="images gamesog-product-gallery"><figure>' . $image . '<figcaption>Maqueta de producto para fines académicos; no implica afiliación con los titulares del juego.</figcaption></figure></div>';
  return;
 }
 $slug = sanitize_title($product->get_slug());
 $relative = "/assets/images/games/" . $slug . ".svg";
 if (!is_readable(get_template_directory() . $relative)) $relative = "/assets/images/pad.svg";
 $url = get_template_directory_uri() . $relative;
 echo '<div class="images gamesog-product-gallery"><figure><img class="wp-post-image" data-gamesog-mockup="1" data-gamesog-title="' . esc_attr($product->get_name()) . '" data-gamesog-slug="' . esc_attr($slug) . '" data-gamesog-cover="' . esc_url(get_template_directory_uri() . "/assets/images/optimized/covers/" . $slug . ".jpg") . '" data-gamesog-format="' . esc_attr($product->get_meta("_gamesog_format") ?: "DVD") . '" data-gamesog-design="' . esc_attr($product->get_meta("_gamesog_design_filter") ?: "basico") . '" src="' . esc_url($url) . '" alt="Maqueta de presentación para ' . esc_attr($product->get_name()) . '" fetchpriority="high"><figcaption>Maqueta académica; las imágenes de tienda se cargan desde archivos locales y no implican afiliación con sus titulares.</figcaption></figure></div>';
}

/** Use the local concept art for WooCommerce loops when a demo product has no media attachment. */
add_filter("woocommerce_product_get_image", "gamesog_related_product_image", 10, 5);
function gamesog_related_product_image($image, $product, $size, $attr, $placeholder) {
 if (!($product instanceof WC_Product) || $product->get_image_id() || !$product->get_meta("_gamesog_format")) return $image;
 $slug = sanitize_title($product->get_slug());
 $relative = "/assets/images/games/" . $slug . ".svg";
 if (!is_readable(get_template_directory() . $relative)) $relative = "/assets/images/pad.svg";
 $attributes = is_array($attr) ? $attr : [];
 $attributes["src"] = get_template_directory_uri() . $relative;
 $attributes["alt"] = "Maqueta de presentación para " . $product->get_name(); $attributes["data-gamesog-mockup"] = "1"; $attributes["data-gamesog-title"] = $product->get_name(); $attributes["data-gamesog-slug"] = $product->get_slug(); $attributes["data-gamesog-cover"] = get_template_directory_uri() . "/assets/images/optimized/covers/" . $product->get_slug() . ".jpg"; $attributes["data-gamesog-format"] = $product->get_meta("_gamesog_format") ?: "DVD"; $attributes["data-gamesog-design"] = $product->get_meta("_gamesog_design_filter") ?: "basico"; $attributes["data-gamesog-color"] = "";
 $size_class = is_string($size) ? sanitize_html_class($size) : "woocommerce_thumbnail";
 $attributes["class"] = trim(($attributes["class"] ?? "") . " attachment-" . $size_class . " size-" . $size_class . " wp-post-image gamesog-local-product-art");
 $attributes["loading"] = $attributes["loading"] ?? "lazy";
 $attributes["decoding"] = $attributes["decoding"] ?? "async";
 $allowed = ["src", "alt", "class", "title", "width", "height", "loading", "decoding", "fetchpriority", "sizes", "role", "aria-hidden", "data-gamesog-mockup", "data-gamesog-title", "data-gamesog-slug", "data-gamesog-cover", "data-gamesog-format", "data-gamesog-design", "data-gamesog-color"];
 $html = "<img";
 foreach ($attributes as $name => $value) {
  if (!in_array($name, $allowed, true) || !is_scalar($value)) continue;
  $html .= " " . esc_attr($name) . "=\"" . esc_attr((string) $value) . "\"";
 }
 return $html . " />";
}

/** Keep the related-products cards aligned with the mandatory customization flow. */
add_action("wp", "gamesog_customize_related_product_loop");
function gamesog_customize_related_product_loop() {
 if (!function_exists("is_product") || !is_product()) return;
 remove_action("woocommerce_after_shop_loop_item", "woocommerce_template_loop_add_to_cart", 10);
 add_action("woocommerce_after_shop_loop_item", "gamesog_related_customize_link", 10);
}
function gamesog_related_customize_link() {
 global $product;
 if (!($product instanceof WC_Product)) return;
 echo '<a class="button gamesog-related-customize" href="' . esc_url(get_permalink($product->get_id())) . '">Personalizar</a>';
}
add_filter("woocommerce_product_related_products_heading", function () { return "Juegos relacionados"; });

function gamesog_cart_count() { return function_exists("WC") && WC()->cart ? WC()->cart->get_cart_contents_count() : 0; }

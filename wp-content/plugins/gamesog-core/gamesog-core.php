<?php
/**
 * Plugin Name: GamesOG Core
 * Description: Catálogo, personalización y flujo de demostración de GamesOG.
 * Version: 1.3.0
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Text Domain: gamesog
 */
if (!defined("ABSPATH")) exit;
define("GAMESOG_VERSION","1.3.0");
if(!defined("GAMESOG_PRIVATE_DIR"))define("GAMESOG_PRIVATE_DIR","/var/private/gamesog-evidence");

function gamesog_games() {
 return [
 "red-dead-redemption-2"=>["Red Dead Redemption 2",14.90,"USB","Básico","#282a29"],
 "minecraft"=>["Minecraft",9.90,"DVD","Preset verde","#355b42"],
 "cyberpunk-2077"=>["Cyberpunk 2077",16.90,"DVD","Especial","#e6bd39"],
 "elden-ring"=>["Elden Ring",17.90,"DVD","Especial","#24211a"],
 "stardew-valley"=>["Stardew Valley",8.90,"CD","Preset crema","#e9dfc3"],
 "hogwarts-legacy"=>["Hogwarts Legacy",13.90,"USB","Básico","#512b3d"]
 ];
}
function gamesog_available_formats() { return ["CD"=>"CD","DVD"=>"DVD","USB"=>"USB"]; }
function gamesog_available_colors() { return ["negro"=>"Negro","crema"=>"Crema","azul"=>"Azul","coral"=>"Coral","verde"=>"Verde","burdeos"=>"Burdeos","amarillo"=>"Amarillo","dorado"=>"Dorado"]; }
function gamesog_default_color($slug) { return ["red-dead-redemption-2"=>"negro","minecraft"=>"verde","cyberpunk-2077"=>"amarillo","elden-ring"=>"dorado","stardew-valley"=>"crema","hogwarts-legacy"=>"burdeos"][$slug] ?? "negro"; }
function gamesog_default_special_products() { return ["cyberpunk-2077","elden-ring","hogwarts-legacy"]; }
function gamesog_has_special_design($product) {
 $saved=$product->get_meta("_gamesog_special_available",true);
 return $saved!=="" ? $saved==="yes" : in_array($product->get_slug(),gamesog_default_special_products(),true);
}
register_activation_hook(__FILE__,"gamesog_activate");
function gamesog_activate(){gamesog_register_messages();flush_rewrite_rules();}

add_action("init","gamesog_register_messages");
function gamesog_register_messages(){
 register_post_type("gamesog_message",["labels"=>["name"=>"Mensajes de contacto","singular_name"=>"Mensaje"],"public"=>false,"show_ui"=>true,"show_in_menu"=>true,"supports"=>["title","editor"],"capabilities"=>["edit_post"=>"manage_options","read_post"=>"manage_options","delete_post"=>"manage_options","edit_posts"=>"manage_options","edit_others_posts"=>"manage_options","publish_posts"=>"manage_options","read_private_posts"=>"manage_options","delete_posts"=>"manage_options","delete_private_posts"=>"manage_options","delete_published_posts"=>"manage_options","delete_others_posts"=>"manage_options","edit_private_posts"=>"manage_options","edit_published_posts"=>"manage_options"],"map_meta_cap"=>false]);
}
add_action("admin_init","gamesog_seed");
function gamesog_seed(){
 if(!class_exists("WooCommerce")||(!defined("WP_CLI")&&!current_user_can("manage_options")))return;
 foreach(gamesog_games() as $slug=>$g){
  $id=wc_get_product_id_by_sku("gamesog-".$slug);
  if(!$id){$post=get_page_by_path($slug,OBJECT,"product");$id=$post?$post->ID:0;}
  if(!$id){$product=new WC_Product_Simple();$product->set_name($g[0]);$product->set_slug($slug);$product->set_sku("gamesog-".$slug);$product->set_status("publish");$product->set_catalog_visibility("visible");$product->set_regular_price(number_format($g[1],2,".",""));$product->set_description("Concepto de presentación física de muestra. El precio corresponde a un servicio académico simulado, no a una licencia ni al videojuego. No incluye copia, descarga, fabricación ni distribución de software.");$product->update_meta_data("_gamesog_format",$g[2]);$product->update_meta_data("_gamesog_fee","3.00");$id=$product->save();}
  if($id&&!get_post_meta($id,"_gamesog_format",true))update_post_meta($id,"_gamesog_format",$g[2]);
  if($id&&!get_post_meta($id,"_gamesog_fee",true))update_post_meta($id,"_gamesog_fee","3.00");
  if($id&&!get_post_meta($id,"_gamesog_design_filter",true))update_post_meta($id,"_gamesog_design_filter",in_array($slug,["cyberpunk-2077","elden-ring"],true)?"especial":"basico");
  if($id&&get_post_meta($id,"_gamesog_special_available",true)==="")update_post_meta($id,"_gamesog_special_available",in_array($slug,gamesog_default_special_products(),true)?"yes":"no");
  $product=wc_get_product($id);if($product&&!$product->is_virtual()){$product->set_virtual(true);$product->save();}
 }
 $pages=[
 "catalogo"=>["Catálogo","[gamesog_catalog]"],
 "sobre-nosotros"=>["Sobre nosotros","<p>GamesOG es una empresa ficticia creada por seis estudiantes de ASIR. Exploramos el valor afectivo de representar una colección digital mediante objetos de presentación.</p><p>El proyecto es una demostración académica: no fabrica, copia, descarga ni distribuye videojuegos. No existe afiliación con sus titulares. Los nombres y títulos se muestran solo como ejemplos.</p><h2>El equipo</h2><p>Seis estudiantes construyen esta propuesta. Los nombres se añadirán cuando el equipo decida publicarlos.</p>"],
 "contacto"=>["Contacto","[gamesog_contact]"],
 "carrito"=>["Carrito","[woocommerce_cart]"],
 "pedido"=>["Pedido","[woocommerce_checkout]"]
 ];
 foreach($pages as $slug=>$page){$existing=get_page_by_path($slug);if(!$existing)$existing=get_post(wp_insert_post(["post_type"=>"page","post_status"=>"publish","post_name"=>$slug,"post_title"=>$page[0],"post_content"=>$page[1]]));if($existing&&$slug==="carrito")update_option("woocommerce_cart_page_id",$existing->ID);if($existing&&$slug==="pedido")update_option("woocommerce_checkout_page_id",$existing->ID);}
 if(!get_option("gamesog_seeded")){update_option("show_on_front","page");$front=get_page_by_path("inicio");if(!$front){$id=wp_insert_post(["post_type"=>"page","post_status"=>"publish","post_name"=>"inicio","post_title"=>"Inicio"]);$front=get_post($id);}if($front)update_option("page_on_front",$front->ID);update_option("gamesog_seeded",GAMESOG_VERSION);}
}

add_action("woocommerce_product_options_pricing","gamesog_product_fee_field");
function gamesog_product_fee_field(){woocommerce_wp_text_input(["id"=>"_gamesog_fee","label"=>"Recargo edición especial (€)","type"=>"number","custom_attributes"=>["step"=>"0.01","min"=>"0"]]);}
add_action("woocommerce_product_options_general_product_data",function(){global $post;$product=$post?wc_get_product($post->ID):false;woocommerce_wp_checkbox(["id"=>"_gamesog_special_available","label"=>"Ofrecer edición especial","description"=>"La edición especial usa arte fijo y no permite elegir color.","value"=>$product&&gamesog_has_special_design($product)?"yes":"no"]);});
add_action("woocommerce_admin_process_product_object",function($product){if(isset($_POST["_gamesog_fee"]))$product->update_meta_data("_gamesog_fee",wc_format_decimal(wp_unslash($_POST["_gamesog_fee"])));$product->update_meta_data("_gamesog_special_available",!empty($_POST["_gamesog_special_available"])?"yes":"no");});

add_action("woocommerce_before_add_to_cart_button","gamesog_product_options");
add_action("woocommerce_single_product_summary",function(){echo '<p class="gamesog-product-context">Servicio académico de presentación para personas que ya poseen el juego. Esta ficha no ofrece el videojuego ni una licencia; tampoco promete compatibilidad, uso sin conexión o eliminación de DRM.</p>';},21);
function gamesog_product_options(){
 global $product;
 if(!$product||!$product->is_purchasable())return;
 wp_nonce_field("gamesog_add_to_cart_".$product->get_id(),"gamesog_config_nonce",false);
 $format=$product->get_meta("_gamesog_format")?:"DVD";
 $formats=gamesog_available_formats();
 $colors=gamesog_available_colors();
 $slug=sanitize_title($product->get_slug());
 $specialAvailable=gamesog_has_special_design($product);
 $defaultDesign=$specialAvailable?($product->get_meta("_gamesog_design_filter")?:"basico"):"basico";
 $defaultColor=gamesog_default_color($slug);
 $formatNote=$format==="USB"
  ?"USB básico: cuerpo en el color elegido y nombre del juego grabado. USB especial: ilustración temática fija."
  :"CD y DVD básicos: caja con carátula y manual; los especiales usan una caja metálica SteelBook con arte fijo.";

 echo '<div class="gamesog-options" data-special-available="'.($specialAvailable?"1":"0").'"><label>Formato de muestra<select name="gamesog_format" required>';
 foreach($formats as $key=>$label){
  echo '<option value="'.esc_attr($key).'" '.selected($format,$key,false).'>'.esc_html($label).'</option>';
 }
 echo '</select></label><label>Diseño<select name="gamesog_design" required>';
 echo '<option value="basico" '.selected($defaultDesign,"basico",false).'>Diseño básico</option>';
 if($specialAvailable){
  echo '<option value="especial" '.selected($defaultDesign,"especial",false).'>Edición especial (+'.wp_kses_post(wc_price((float)$product->get_meta("_gamesog_fee"))).')</option>';
 }
 echo '</select></label>';

 $colorHidden=$defaultDesign==="especial";
 echo '<label class="gamesog-color-choice"'.($colorHidden?' hidden':'').'>Color del diseño básico<select name="gamesog_color" '.($colorHidden?'disabled':'required').'>';
 foreach($colors as $key=>$label){
  echo '<option value="'.esc_attr($key).'" '.selected($defaultColor,$key,false).'>'.esc_html($label).'</option>';
 }
 echo '</select></label>';
 echo '<input type="hidden" name="gamesog_color" value="fijo" '.($colorHidden?'':'disabled').'>';
 echo '<p class="gamesog-format-note" data-format-note>'.esc_html($formatNote).'</p>';

 $base=(float)$product->get_regular_price();
 $fee=(float)$product->get_meta("_gamesog_fee");
 echo '<p class="gamesog-price-note" data-price-summary data-base-price="'.esc_attr((string)$base).'" data-special-price="'.esc_attr((string)$fee).'">Precio estimado: <strong data-price-total>'.wp_kses_post(wc_price($base)).'</strong>. Importe de servicio simulado; el precio final se valida y calcula en el servidor.</p></div>';
}
add_filter("woocommerce_add_to_cart_validation",function($ok,$product_id){
 $p=wc_get_product($product_id);$formats=gamesog_available_formats();$colors=gamesog_available_colors();
 $format=isset($_POST["gamesog_format"])?sanitize_text_field(wp_unslash($_POST["gamesog_format"])):"";$design=isset($_POST["gamesog_design"])?sanitize_key(wp_unslash($_POST["gamesog_design"])):"";$color=isset($_POST["gamesog_color"])?sanitize_key(wp_unslash($_POST["gamesog_color"])):"";$nonce=isset($_POST["gamesog_config_nonce"])?sanitize_text_field(wp_unslash($_POST["gamesog_config_nonce"])):"";
 $specialAvailable=$p&&gamesog_has_special_design($p);
 if(!$p||!$nonce||!wp_verify_nonce($nonce,"gamesog_add_to_cart_".$product_id)||!isset($formats[$format])||!in_array($design,$specialAvailable?["basico","especial"]:["basico"],true)||($design==="basico"&&!isset($colors[$color]))){wc_add_notice("Revisa las opciones de personalización.","error");return false;}
 return $ok;
},10,2);
add_filter("woocommerce_add_cart_item_data",function($data,$product_id){
 $design=sanitize_key(wp_unslash($_POST["gamesog_design"]));$color=$design==="especial"?"fijo":sanitize_key(wp_unslash($_POST["gamesog_color"]));$data["gamesog_config"]=["formato"=>sanitize_text_field(wp_unslash($_POST["gamesog_format"])),"diseno"=>$design,"color"=>$color];$data["gamesog_key"]=wp_hash($product_id.wp_json_encode($data["gamesog_config"]));return $data;
},10,2);
add_filter("woocommerce_get_cart_item_from_session",function($item,$values){if(isset($values["gamesog_config"]))$item["gamesog_config"]=$values["gamesog_config"];if(isset($values["gamesog_key"]))$item["gamesog_key"]=$values["gamesog_key"];return $item;},10,2);
add_action("woocommerce_before_calculate_totals",function($cart){if(is_admin()&&!defined("DOING_AJAX"))return;foreach($cart->get_cart() as $item){if(empty($item["gamesog_config"]))continue;$base=(float)$item["data"]->get_regular_price();$fee=(float)$item["data"]->get_meta("_gamesog_fee");$item["data"]->set_price(wc_format_decimal($base+($item["gamesog_config"]["diseno"]==="especial"?$fee:0)));}});
add_filter("woocommerce_get_item_data",function($meta,$item){if(!empty($item["gamesog_config"])){$c=$item["gamesog_config"];$meta[]=["key"=>"Formato","value"=>esc_html($c["formato"])];$meta[]=["key"=>"Diseño","value"=>esc_html($c["diseno"]==="especial"?"Edición especial · arte fijo":"Diseño básico")];if($c["diseno"]==="basico")$meta[]=["key"=>"Color","value"=>esc_html(ucfirst($c["color"]))];}return $meta;},10,2);
add_action("woocommerce_checkout_create_order_line_item",function($line,$key,$values,$order){if(!empty($values["gamesog_config"]))foreach($values["gamesog_config"] as $k=>$v){if($k==="color"&&$values["gamesog_config"]["diseno"]==="especial")continue;$line->add_meta_data(["formato"=>"Formato","diseno"=>"Diseño","color"=>"Color"][$k]??$k,$k==="diseno"?($v==="especial"?"Edición especial · arte fijo":"Diseño básico"):ucfirst($v),true);}},10,4);
add_action("plugins_loaded","gamesog_register_demo_gateway",20);
function gamesog_register_demo_gateway(){if(!class_exists("WC_Payment_Gateway"))return;
 class GamesOG_Demo_Gateway extends WC_Payment_Gateway{
  public function __construct(){$this->id="gamesog_demo";$this->method_title="Pedido de demostración";$this->method_description="No se cobra ni se envía nada. Guarda un pedido académico para revisión.";$this->has_fields=false;$this->title="Pedido de demostración — sin cobro";$this->enabled="yes";}
   public function process_payment($order_id){$order=wc_get_order($order_id);$order->update_status("pending-property","Pedido de demostración; pendiente de revisión de propiedad.");if(WC()->cart)WC()->cart->empty_cart();return ["result"=>"success","redirect"=>$this->get_return_url($order)];}
 }
}
add_filter("woocommerce_payment_gateways",function($gateways){return ["GamesOG_Demo_Gateway"];},99);
add_filter("woocommerce_checkout_fields",function($fields){
 foreach(["billing_company","billing_last_name","billing_address_1","billing_address_2","billing_city","billing_state","billing_postcode","billing_country","billing_phone"] as $key)unset($fields["billing"][$key]);
 $fields["billing"]["billing_first_name"]["label"]="Nombre ficticio";$fields["billing"]["billing_first_name"]["required"]=true;$fields["billing"]["billing_email"]["label"]="Correo de prueba";$fields["billing"]["billing_email"]["required"]=true;
 return $fields;
});
add_filter("woocommerce_checkout_form_tag",function($tag){if(stripos($tag,"enctype=")!==false)return $tag;return preg_replace("/<form\\b/i","<form enctype=\"multipart/form-data\"",$tag,1);});
add_action("woocommerce_after_order_notes",function(){wp_nonce_field("gamesog_checkout","gamesog_checkout_nonce");echo '<div class="form-row form-row-wide"><label for="gamesog_evidence">Evidencia ficticia: justificante de prueba o fotografía de demostración (JPEG o PNG, máximo 2 MB) <abbr class="required" title="obligatorio">*</abbr></label><input type="file" id="gamesog_evidence" name="gamesog_evidence" accept="image/jpeg,image/png" required><p>Utiliza únicamente archivos de prueba. No subas tickets reales, documentos de identidad, contraseñas ni datos personales. La revisión es simulada y no acredita derechos legales ni autoriza la copia o distribución del juego.</p></div><p><label><input type="checkbox" name="gamesog_demo_confirm" value="1" required> Confirmo que es un pedido académico de demostración sin cobro ni envío.</label></p><p>Proyecto académico. Pedido de demostración: no se realizará ningún cobro ni envío.</p>';});
add_action("woocommerce_checkout_process",function(){
 if(empty($_POST["gamesog_checkout_nonce"])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["gamesog_checkout_nonce"])),"gamesog_checkout"))wc_add_notice("No se pudo validar el formulario. Recarga la página e inténtalo de nuevo.","error");
 if(empty($_POST["gamesog_demo_confirm"]))wc_add_notice("Confirma que se trata de un pedido de demostración.","error");
 if(!is_dir(GAMESOG_PRIVATE_DIR)||!is_writable(GAMESOG_PRIVATE_DIR))wc_add_notice("El almacenamiento privado de evidencias no está disponible; no se puede confirmar el pedido.","error");
 if(empty($_FILES["gamesog_evidence"]["tmp_name"])||$_FILES["gamesog_evidence"]["error"]!==UPLOAD_ERR_OK)wc_add_notice("Añade una imagen ficticia JPEG o PNG, de hasta 2 MB.","error");
 else {$f=$_FILES["gamesog_evidence"];if($f["size"]>2*1024*1024)wc_add_notice("La imagen supera el límite de 2 MB.","error");$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f["tmp_name"]);$image=@getimagesize($f["tmp_name"]);if(!in_array($mime,["image/jpeg","image/png"],true)||!$image||!in_array($image[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG],true))wc_add_notice("El archivo debe ser una imagen JPEG o PNG válida.","error");}
});
function gamesog_store_evidence(){
 if(!function_exists("WC")||!WC()->session||wc_notice_count("error")>0)return;
 if(empty($_FILES["gamesog_evidence"]["tmp_name"])||!is_uploaded_file($_FILES["gamesog_evidence"]["tmp_name"]))return;
 $file=$_FILES["gamesog_evidence"];if($file["error"]!==UPLOAD_ERR_OK||$file["size"]>2097152)return;
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);$image=@getimagesize($file["tmp_name"]);$ext=["image/jpeg"=>"jpg","image/png"=>"png"][$mime]??"";
 if(!$ext||!$image||!in_array($image[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG],true))return;
 $dir=GAMESOG_PRIVATE_DIR;if(!str_starts_with($dir,"/")||!is_dir($dir)&&!wp_mkdir_p($dir)){wc_add_notice("El almacenamiento privado de evidencias no está disponible; no se puede confirmar el pedido.","error");return;}
 $realDir=realpath($dir);$webRoot=realpath(ABSPATH);
 if(!$realDir||!$webRoot||$realDir===$webRoot||str_starts_with($realDir,rtrim($webRoot,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)){wc_add_notice("El almacenamiento de evidencias no está fuera de la carpeta pública; no se puede confirmar el pedido.","error");return;}
 @chmod($realDir,0700);$name=wp_generate_uuid4().".".$ext;$dest=trailingslashit($realDir).$name;
 if(!move_uploaded_file($file["tmp_name"],$dest)){wc_add_notice("No se pudo guardar la evidencia en el almacenamiento privado.","error");return;}
 @chmod($dest,0600);
 $previous=WC()->session->get("gamesog_evidence_token");
 if(is_string($previous)&&preg_match("/^[a-f0-9-]{36}\.(jpg|png)$/",$previous)){$old=trailingslashit($realDir).$previous;if(is_file($old))@unlink($old);}
 WC()->session->set("gamesog_evidence_token",$name);
}
add_action("woocommerce_checkout_process","gamesog_store_evidence",99);
add_action("woocommerce_checkout_create_order",function($order){if(!function_exists("WC")||!WC()->session)return;$name=WC()->session->get("gamesog_evidence_token");if($name&&preg_match("/^[a-f0-9-]{36}\\.(jpg|png)$/",$name)){$order->update_meta_data("_gamesog_evidence_file",$name);$order->update_meta_data("_gamesog_property_review","pending");WC()->session->__unset("gamesog_evidence_token");}});
add_action("woocommerce_checkout_order_processed",function($id){$o=wc_get_order($id);if($o&&$o->get_status()==="pending")$o->update_status("pending-property","Pedido creado; pendiente de revisión de propiedad.");},20);
add_action("init",function(){register_post_status("wc-pending-property",["label"=>"Pendiente de revisión de propiedad","public"=>false,"exclude_from_search"=>false,"show_in_admin_all_list"=>true,"show_in_admin_status_list"=>true,"label_count"=>_n_noop("Pendiente de revisión <span class=\"count\">(%s)</span>","Pendiente de revisión <span class=\"count\">(%s)</span>")]);});
add_filter("wc_order_statuses",function($s){$s["wc-pending-property"]="Pendiente de revisión de propiedad";return $s;});

add_action("add_meta_boxes",function(){$screen=function_exists("wc_get_page_screen_id")?wc_get_page_screen_id("shop-order"):"shop_order";add_meta_box("gamesog-review","Revisión de propiedad (demostración)","gamesog_review_box",$screen,"side","high");});
function gamesog_review_box($post){
 $id=is_object($post)&&isset($post->ID)?absint($post->ID):(is_object($post)&&method_exists($post,"get_id")?absint($post->get_id()):0);$o=$id?wc_get_order($id):false;if(!$o)return;
 $state=$o->get_meta("_gamesog_property_review")?: "pending";wp_nonce_field("gamesog_review_".$o->get_id(),"gamesog_review_nonce");
 echo "<p>Estado: ".esc_html(["pending"=>"Pendiente","approved"=>"Aprobada para la demostración","rejected"=>"Rechazada"][$state]??$state)."</p><select name=\"gamesog_review\">";
 foreach(["pending"=>"Pendiente","approved"=>"Aprobada para la demostración","rejected"=>"Rechazada"] as $key=>$label)echo "<option value=\"".esc_attr($key)."\" ".selected($state,$key,false).">".esc_html($label)."</option>";
 echo "</select><p><button class=\"button\" name=\"gamesog_save_review\" value=\"1\">Guardar revisión</button></p>";
 $name=$o->get_meta("_gamesog_evidence_file");if($name){$url=wp_nonce_url(admin_url("admin-post.php?action=gamesog_evidence&order_id=".$o->get_id()),"gamesog_evidence_".$o->get_id());$delete=wp_nonce_url(admin_url("admin-post.php?action=gamesog_delete_evidence&order_id=".$o->get_id()),"gamesog_delete_evidence_".$o->get_id());echo "<p><a href=\"".esc_url($url)."\">Ver evidencia privada</a></p><p><a href=\"".esc_url($delete)."\" onclick=\"return confirm('¿Eliminar permanentemente esta evidencia privada?');\">Eliminar evidencia privada</a></p>";}
}
function gamesog_save_review($id){if(empty($_POST["gamesog_save_review"])||empty($_POST["gamesog_review_nonce"])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["gamesog_review_nonce"])),"gamesog_review_".$id)||!current_user_can("manage_woocommerce"))return;$state=sanitize_key(wp_unslash($_POST["gamesog_review"]));if(!in_array($state,["pending","approved","rejected"],true))return;$o=wc_get_order($id);if($o){$o->update_meta_data("_gamesog_property_review",$state);$o->save();}}
add_action("save_post_shop_order","gamesog_save_review");
add_action("woocommerce_process_shop_order_meta","gamesog_save_review");
add_action("admin_post_gamesog_evidence", function () {
 if (!current_user_can("manage_woocommerce")) wp_die("Acceso denegado.", 403);
 $id = absint($_GET["order_id"] ?? 0);
 check_admin_referer("gamesog_evidence_" . $id);
 $order = wc_get_order($id);
 $name = $order ? $order->get_meta("_gamesog_evidence_file") : "";
 if (!$name || !preg_match("/^[a-f0-9-]{36}\\.(jpg|png)$/", $name)) wp_die("Archivo no disponible.", 404);
 $file = trailingslashit(GAMESOG_PRIVATE_DIR) . $name;
 if (!is_file($file)) wp_die("Archivo no disponible.", 404);
 $extension = pathinfo($name, PATHINFO_EXTENSION);
 header("Content-Type: " . ($extension === "png" ? "image/png" : "image/jpeg"));
 header("Content-Length: " . filesize($file));
 header("Content-Disposition: inline; filename=\"evidencia-" . $id . "." . $extension . "\"");
 header("Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0");
 header("Pragma: no-cache");
 header("Expires: 0");
 header("X-Content-Type-Options: nosniff");
 header("Referrer-Policy: no-referrer");
 readfile($file);
 exit;
});

add_action("admin_post_nopriv_gamesog_evidence",function(){wp_die("Acceso denegado.",403);});
add_action("admin_post_gamesog_delete_evidence",function(){if(!current_user_can("manage_woocommerce"))wp_die("Acceso denegado.",403);$id=absint($_GET["order_id"]??0);check_admin_referer("gamesog_delete_evidence_".$id);$o=wc_get_order($id);$name=$o?$o->get_meta("_gamesog_evidence_file"):"";if(!$o||!$name||!preg_match("/^[a-f0-9-]{36}\\.(jpg|png)$/",$name))wp_die("Evidencia no disponible.",404);$file=trailingslashit(GAMESOG_PRIVATE_DIR).$name;if(is_file($file)&&!@unlink($file))wp_die("No se pudo eliminar el archivo privado.",500);$o->delete_meta_data("_gamesog_evidence_file");$o->save();wp_safe_redirect(wp_get_referer()?:admin_url("edit.php?post_type=shop_order"));exit;});
if(wp_get_environment_type()==="local"||getenv("GAMESOG_LOCAL_DEMO")==="1")add_filter("pre_wp_mail",function(){return true;});
add_shortcode("gamesog_catalog",function(){
 $html='<div class="catalog-controls"><label>Buscar por nombre<input type="search" data-catalog-search placeholder="Ej. Minecraft"></label><label>Formato<select data-format-filter><option value="">Todos los formatos</option><option>CD</option><option>DVD</option><option>USB</option></select></label><label>Diseño<select data-design-filter><option value="">Todos los diseños</option><option value="basico">Básico</option><option value="especial">Especial</option></select></label></div><div class="product-grid">';
 $q=new WP_Query(["post_type"=>"product","posts_per_page"=>-1,"post_status"=>"publish","meta_key"=>"_gamesog_format","orderby"=>"menu_order title","order"=>"ASC"]);
 foreach($q->posts as $post){$p=wc_get_product($post->ID);if(!$p)continue;$slug=$post->post_name;$format=$p->get_meta("_gamesog_format");$relative="/assets/images/games/".$slug.".svg";if(!is_readable(get_template_directory().$relative))$relative="/assets/images/pad.svg";$src=get_template_directory_uri().$relative;$alt="Imagen conceptual de muestra para ".$p->get_name();$image=$p->get_image_id()?wp_get_attachment_image($p->get_image_id(),"woocommerce_thumbnail",false,["loading"=>"lazy","alt"=>$alt]):'<img loading="lazy" src="'.esc_url($src).'" alt="'.esc_attr($alt).'">';$base=(float)$p->get_regular_price();$designs=gamesog_has_special_design($p)?"basico especial":"basico";$default=$p->get_meta("_gamesog_design_filter")?:"basico";$html.='<article class="product-card" data-product-card data-name="'.esc_attr(mb_strtolower($p->get_name())).'" data-format="'.esc_attr($format).'" data-design="'.esc_attr($designs).'" data-default-design="'.esc_attr($default).'" data-cover-url="'.esc_url(get_template_directory_uri().'/assets/images/optimized/covers/'.$slug.'.jpg').'"><a href="'.esc_url(get_permalink($p->get_id())).'"><div class="product-card-image">'.$image.'</div><div class="product-card-copy"><p class="eyebrow">'.esc_html($format).' · concepto de muestra</p><h3>'.esc_html($p->get_name()).'</h3><p>Desde '.wp_kses_post(wc_price($base)).'</p><span class="button">Personalizar</span></div></a></article>';}
 wp_reset_postdata();$html.='</div><p data-empty-results hidden role="status">No hay productos que coincidan con estos filtros.</p><p class="notice">Los precios son ficticios y corresponden al servicio simulado. No incluyen licencia de videojuego.</p>';return $html;
});
add_shortcode("gamesog_contact",function(){
 $msg=isset($_GET["contacto"])?sanitize_key(wp_unslash($_GET["contacto"])):"";
 $html=$msg==="ok"?'<p class="notice" role="status">Mensaje guardado en esta instalación local.</p>':'';
 $html.='<form method="post" action="'.esc_url(admin_url("admin-post.php")).'" class="gamesog-contact"><input type="hidden" name="action" value="gamesog_contact"><label>Nombre<input name="name" maxlength="80" required></label><label>Correo ficticio<input type="email" name="email" maxlength="120" required></label><label>Mensaje<textarea name="message" rows="5" maxlength="2000" required></textarea></label>'; $html.=wp_nonce_field("gamesog_contact","gamesog_contact_nonce",true,false).'<button class="button" type="submit">Guardar mensaje</button><p>Usa únicamente datos ficticios. El mensaje queda visible solo en la administración local.</p></form>';return $html;
});
add_action("admin_post_nopriv_gamesog_contact","gamesog_contact_submit");add_action("admin_post_gamesog_contact","gamesog_contact_submit");
function gamesog_contact_submit(){if(empty($_POST["gamesog_contact_nonce"])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["gamesog_contact_nonce"])),"gamesog_contact"))wp_die("Formulario no válido.",403);$name=sanitize_text_field(wp_unslash($_POST["name"]??""));$email=sanitize_email(wp_unslash($_POST["email"]??""));$message=sanitize_textarea_field(wp_unslash($_POST["message"]??""));if(!$name||!is_email($email)||!$message||strlen($message)>2000)wp_die("Revisa los campos del formulario.",400);$id=wp_insert_post(["post_type"=>"gamesog_message","post_status"=>"private","post_title"=>$name." · ".$email,"post_content"=>$message],true);if(is_wp_error($id)||!$id)wp_die("No se pudo guardar el mensaje.",500);wp_safe_redirect(add_query_arg("contacto","ok",home_url("/contacto/")));exit;}

add_filter("woocommerce_get_checkout_url",function($url){return $url;});

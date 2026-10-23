#!/usr/bin/env python3
"""Prueba local de pedido/evidencia; limpia solo los artefactos de esta ejecución."""

from __future__ import annotations

import hashlib
import ipaddress
import json
import re
import secrets
import struct
import subprocess
import zlib
from http.cookiejar import CookieJar
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urljoin, urlsplit
from urllib.request import HTTPCookieProcessor, Request, build_opener


ROOT = Path(__file__).resolve().parents[1]
PRODUCT_PATH = "/product/red-dead-redemption-2/"
CART_PATH = "/carrito/"
CHECKOUT_PATH = "/pedido/"
MARKER = "GAMESOG_SMOKE_JSON:"


class CheckoutParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.fields: dict[str, str] = {}
        self.product_id = ""
        self.has_checkout_form = False
        self.remove_links: list[str] = []
        self._select_name = ""
        self._select_first = ""
        self._select_value = ""

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = {key: value or "" for key, value in attrs}
        if tag == "form" and "checkout" in values.get("class", "").split():
            self.has_checkout_form = True
        if tag == "input":
            name = values.get("name", "")
            kind = values.get("type", "text").lower()
            if name == "add-to-cart":
                self.product_id = values.get("value", "")
            if not name or kind in {"file", "submit", "button", "image", "reset"}:
                return
            if kind in {"checkbox", "radio"} and "checked" not in values:
                return
            self.fields[name] = values.get("value", "")
        elif tag == "select":
            self._select_name = values.get("name", "")
            self._select_first = ""
            self._select_value = ""
        elif tag == "option" and self._select_name:
            value = values.get("value", "")
            if not self._select_first:
                self._select_first = value
            if "selected" in values:
                self._select_value = value
        elif tag == "button" and values.get("name") == "add-to-cart":
            self.product_id = values.get("value", "")
        elif tag == "a" and "remove_item=" in values.get("href", ""):
            self.remove_links.append(values["href"])

    def handle_endtag(self, tag: str) -> None:
        if tag == "select" and self._select_name:
            self.fields[self._select_name] = self._select_value or self._select_first
            self._select_name = ""


def local_site_url() -> str:
    try:
        lines = (ROOT / ".env").read_text(encoding="utf-8").splitlines()
    except OSError as error:
        raise RuntimeError("No se pudo leer la URL local de .env.") from error
    value = next((line.split("=", 1)[1].strip() for line in lines if line.startswith("WP_SITE_URL=")), "")
    value = value.strip("\"'").rstrip("/")
    parsed = urlsplit(value)
    if parsed.scheme != "http" or not parsed.hostname or parsed.username or parsed.password:
        raise RuntimeError("WP_SITE_URL debe ser una URL HTTP local sin credenciales.")
    host = parsed.hostname.lower()
    if host != "localhost" and not host.endswith(".localhost"):
        try:
            if not ipaddress.ip_address(host).is_private:
                raise RuntimeError("La prueba solo acepta localhost o una IP privada.")
        except ValueError as error:
            raise RuntimeError("La prueba solo acepta localhost o una IP privada.") from error
    return value


def request_response(opener, url: str, data: bytes | None = None, headers: dict[str, str] | None = None):
    request_headers = {"User-Agent": "GamesOG-local-order-smoke/1.0"}
    if headers:
        request_headers.update(headers)
    try:
        with opener.open(Request(url, data=data, headers=request_headers), timeout=25) as response:
            return response.status, response.headers, response.read()
    except HTTPError as error:
        return error.code, error.headers, error.read()
    except (URLError, TimeoutError) as error:
        raise RuntimeError(f"No se pudo consultar {urlsplit(url).path}: {error}") from error


def fetch_html(opener, url: str) -> str:
    status, _headers, body = request_response(opener, url)
    if status != 200:
        raise RuntimeError(f"HTTP {status} al consultar {urlsplit(url).path}.")
    return body.decode("utf-8", errors="replace")


def png_1x1() -> bytes:
    def chunk(kind: bytes, data: bytes) -> bytes:
        return struct.pack(">I", len(data)) + kind + data + struct.pack(">I", zlib.crc32(kind + data) & 0xFFFFFFFF)

    pixel = secrets.token_bytes(4)
    return (
        b"\x89PNG\r\n\x1a\n"
        + chunk(b"IHDR", struct.pack(">IIBBBBB", 1, 1, 8, 6, 0, 0, 0))
        + chunk(b"IDAT", zlib.compress(b"\x00" + pixel))
        + chunk(b"IEND", b"")
    )


def multipart(fields: dict[str, str], filename: str, image: bytes, boundary: str) -> bytes:
    parts: list[bytes] = []
    for name, value in fields.items():
        safe_name = name.replace('"', "")
        parts.append(
            f"--{boundary}\r\nContent-Disposition: form-data; name=\"{safe_name}\"\r\n\r\n".encode()
            + str(value).encode("utf-8")
            + b"\r\n"
        )
    parts.append(
        f"--{boundary}\r\nContent-Disposition: form-data; name=\"gamesog_evidence\"; filename=\"{filename}\"\r\nContent-Type: image/png\r\n\r\n".encode()
        + image
        + b"\r\n"
    )
    parts.append(f"--{boundary}--\r\n".encode())
    return b"".join(parts)


def send_checkout(opener, base: str, fields: dict[str, str], filename: str, image: bytes):
    boundary = "----GamesOG" + secrets.token_hex(18)
    body = multipart(fields, filename, image, boundary)
    return request_response(
        opener,
        base + "/?wc-ajax=checkout",
        body,
        {
            "Content-Type": f"multipart/form-data; boundary={boundary}",
            "X-Requested-With": "XMLHttpRequest",
            "Referer": base + CHECKOUT_PATH,
        },
    )


def wpcli_eval(code: str) -> str:
    command = [
        "sudo", "-n", "docker", "compose", "--profile", "tools", "run", "--rm", "-T",
        "--volume", "gamesog_private_evidence:/var/private/gamesog-evidence", "wpcli", "eval", code
    ]
    result = subprocess.run(command, cwd=ROOT, capture_output=True, text=True, check=False)
    if result.returncode:
        raise RuntimeError("WP-CLI no pudo consultar WordPress. Ejecuta primero `sudo -v` y vuelve a intentarlo.")
    return result.stdout


def marked_json(output: str) -> dict:
    line = next((item for item in reversed(output.splitlines()) if item.startswith(MARKER)), None)
    if line is None:
        raise RuntimeError("WP-CLI no devolvió el resultado de verificación esperado.")
    try:
        return json.loads(line[len(MARKER):])
    except json.JSONDecodeError as error:
        raise RuntimeError("WP-CLI devolvió datos de verificación no válidos.") from error


def remove_test_cart(opener, base: str) -> bool:
    for _ in range(4):
        parser = CheckoutParser()
        parser.feed(fetch_html(opener, base + CART_PATH))
        if not parser.remove_links:
            return True
        request_response(opener, urljoin(base + CART_PATH, parser.remove_links[0]))
    parser = CheckoutParser()
    parser.feed(fetch_html(opener, base + CART_PATH))
    return not parser.remove_links


def cleanup_order(order_id: int, email: str) -> bool:
    php_email = "'" + email.replace("'", "\\'") + "'"
    code = (
        f"$id={order_id};$email={php_email};$o=wc_get_order($id);"
        "if(!$o||$o->get_billing_email()!==$email){echo 'GAMESOG_CLEANUP:refused';exit;}"
        "$name=$o->get_meta('_gamesog_evidence_file');"
        "if($name&&preg_match('/^[a-f0-9-]{36}\\.(jpg|png)$/',$name)){$f=trailingslashit(GAMESOG_PRIVATE_DIR).$name;if(is_file($f)&&!unlink($f)){echo 'GAMESOG_CLEANUP:file-failed';exit;}}"
        "$o->delete(true);echo 'GAMESOG_CLEANUP:removed';"
    )
    return "GAMESOG_CLEANUP:removed" in wpcli_eval(code)


def find_test_orders(email: str) -> list[int]:
    php_email = "'" + email.replace("'", "\\'") + "'"
    code = (
        f"$email={php_email};$orders=wc_get_orders(['billing_email'=>$email,'limit'=>5,'orderby'=>'date','order'=>'DESC']);"
        "$ids=[];foreach($orders as $o){if($o->get_billing_email()===$email)$ids[]=$o->get_id();}"
        f"echo '{MARKER}'.wp_json_encode(['ids'=>$ids]);"
    )
    result = marked_json(wpcli_eval(code))
    return [int(item) for item in result.get("ids", []) if str(item).isdigit()]


def cleanup_orphan_evidence(image_hash: str) -> tuple[int, int]:
    """Remove only this run's exact PNG when no order references it."""
    if not re.fullmatch(r"[a-f0-9]{64}", image_hash):
        return 0, 0
    php_hash = "'" + image_hash + "'"
    code = (
        f"$expected={php_hash};$dir=realpath(GAMESOG_PRIVATE_DIR);$removed=0;$protected=0;"
        "if($dir){foreach(glob(trailingslashit($dir).'*.png')?:[] as $file){"
        "$name=basename($file);if(!preg_match('/^[a-f0-9-]{36}\\.png$/',$name)||!is_file($file)||hash_file('sha256',$file)!==$expected)continue;"
        "$orders=wc_get_orders(['limit'=>-1,'meta_key'=>'_gamesog_evidence_file','meta_value'=>$name]);"
        "if($orders){$protected++;continue;}"
        "$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);$mode=sprintf('%04o',fileperms($file)&0777);"
        "if($mime==='image/png'&&$mode==='0600'&&unlink($file))$removed++;"
        "}}"
        f"echo '{MARKER}'.wp_json_encode(['removed'=>$removed,'protected'=>$protected]);"
    )
    result = marked_json(wpcli_eval(code))
    return int(result.get("removed", 0)), int(result.get("protected", 0))


def main() -> None:
    base = local_site_url()
    opener = build_opener(HTTPCookieProcessor(CookieJar()))
    unique = secrets.token_hex(8)
    email = f"gamesog-smoke-{unique}@example.invalid"
    print(f"Correo ficticio único de esta ejecución: {email}")
    order_id = 0
    added_to_cart = False
    valid_checkout_sent = False
    valid_image_hash = ""
    try:
        product_html = fetch_html(opener, base + PRODUCT_PATH)
        product = CheckoutParser()
        product.feed(product_html)
        nonce_match = re.search(r'name=["\']gamesog_config_nonce["\'][^>]*value=["\']([^"\']+)', product_html)
        nonce = nonce_match.group(1) if nonce_match else ""
        if not nonce or not product.product_id:
            raise RuntimeError("La ficha no contiene el nonce y el botón de personalización.")
        add_fields = {
            "add-to-cart": product.product_id,
            "quantity": "1",
            "gamesog_config_nonce": nonce,
            "gamesog_format": "USB",
            "gamesog_design": "basico",
            "gamesog_color": "negro",
        }
        status, _headers, _body = request_response(
            opener,
            base + PRODUCT_PATH,
            urlencode(add_fields).encode("utf-8"),
            {"Content-Type": "application/x-www-form-urlencoded", "Referer": base + PRODUCT_PATH},
        )
        if status not in {200, 301, 302, 303}:
            raise RuntimeError(f"No se pudo añadir el producto de prueba (HTTP {status}).")
        added_to_cart = True

        checkout_html = fetch_html(opener, base + CHECKOUT_PATH)
        checkout = CheckoutParser()
        checkout.feed(checkout_html)
        required = {"woocommerce-process-checkout-nonce", "gamesog_checkout_nonce"}
        if not checkout.has_checkout_form or not required.issubset(checkout.fields):
            raise RuntimeError("El checkout no tiene el formulario y los nonces esperados.")
        if "gamesog_demo" not in checkout_html:
            raise RuntimeError("El checkout no ofrece la pasarela ficticia.")

        common = dict(checkout.fields)
        common.update({
            "billing_first_name": "GamesOG Prueba",
            "billing_email": email,
            "payment_method": "gamesog_demo",
            "gamesog_demo_confirm": "1",
        })
        invalid_status, _invalid_headers, invalid_body = send_checkout(
            opener, base, common, "gamesog-prueba.png", b"MZ-no-es-una-imagen"
        )
        invalid_text = invalid_body.decode("utf-8", errors="replace")
        try:
            invalid_json = json.loads(invalid_text)
        except json.JSONDecodeError:
            invalid_json = {}
        if invalid_json.get("result") == "success":
            invalid_redirect = str(invalid_json.get("redirect", ""))
            accepted_match = re.search(r"order-received/(\d+)", invalid_redirect)
            if accepted_match:
                order_id = int(accepted_match.group(1))
        invalid_rejected = (
            invalid_status == 200
            and invalid_json.get("result") != "success"
            and "JPEG o PNG" in invalid_text
        )
        print(f"{'OK' if invalid_rejected else 'FALLO'}: el servidor rechaza contenido que no es una imagen válida")
        if not invalid_rejected:
            raise RuntimeError("No se confirmó el rechazo del archivo de prueba inválido; no se enviará el pedido válido.")

        checkout_html = fetch_html(opener, base + CHECKOUT_PATH)
        checkout = CheckoutParser()
        checkout.feed(checkout_html)
        common = dict(checkout.fields)
        common.update({
            "billing_first_name": "GamesOG Prueba",
            "billing_email": email,
            "payment_method": "gamesog_demo",
            "gamesog_demo_confirm": "1",
        })
        valid_checkout_sent = True
        valid_image = png_1x1()
        valid_image_hash = hashlib.sha256(valid_image).hexdigest()
        status, response_headers, response_body = send_checkout(opener, base, common, "gamesog-prueba.png", valid_image)
        if status != 200:
            content_type = response_headers.get("Content-Type", "desconocido")
            raise RuntimeError(f"El checkout devolvió HTTP {status} (Content-Type: {content_type}; {len(response_body)} bytes). Se buscará y limpiará cualquier pedido con el correo ficticio único.")
        try:
            response = json.loads(response_body.decode("utf-8", errors="replace"))
        except json.JSONDecodeError as error:
            raise RuntimeError("El checkout no devolvió una respuesta JSON.") from error
        redirect = str(response.get("redirect", ""))
        match = re.search(r"order-received/(\d+)", redirect)
        if response.get("result") != "success" or not match:
            raise RuntimeError("WooCommerce no confirmó el pedido ficticio; revisa el mensaje del checkout.")
        order_id = int(match.group(1))
        print(f"OK: pedido de demostración #{order_id} creado; se comprobará y limpiará al terminar")

        php = (
            f"$id={order_id};$email='{email}';$o=wc_get_order($id);"
            "if(!$o){echo 'GAMESOG_SMOKE_JSON:'.wp_json_encode(['found'=>false]);exit;}"
            "$name=$o->get_meta('_gamesog_evidence_file');$base=realpath(GAMESOG_PRIVATE_DIR);$web=realpath(ABSPATH);"
            "$file=($base&&$name)?trailingslashit($base).$name:'';$mime=$file&&is_file($file)?(new finfo(FILEINFO_MIME_TYPE))->file($file):'';"
            "$line_ok=false;$price_ok=false;foreach($o->get_items() as $item){$p=wc_get_product($item->get_product_id());"
            "$line_ok=$item->get_meta('Formato')==='USB'&&$item->get_meta('Diseño')==='Preset básico'&&$item->get_meta('Color')==='Negro';"
            "$price_ok=$p&&abs((float)$item->get_total()-(float)$p->get_regular_price())<0.01;break;}"
            "$outside=(bool)($base&&$web&&$base!==$web&&!str_starts_with($base,rtrim($web,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR));"
            "$mode=$file&&is_file($file)?sprintf('%04o',fileperms($file)&0777):'';"
            "$status_ok=$o->get_status()==='pending-property';$email_ok=$o->get_billing_email()===$email;"
            "$mail_environment=wp_get_environment_type();$mail_local=$mail_environment==='local'||getenv('GAMESOG_LOCAL_DEMO')==='1';$mail_filter=apply_filters('pre_wp_mail',null,[])===true;$mail_ok=$mail_local&&$mail_filter;"
            "$payment_ok=$o->get_payment_method()==='gamesog_demo';"
            "echo 'GAMESOG_SMOKE_JSON:'.wp_json_encode(['found'=>true,'email_ok'=>$email_ok,'status_ok'=>$status_ok,'status'=>$o->get_status(),'review'=>$o->get_meta('_gamesog_property_review'),'filename_ok'=>(bool)preg_match('/^[a-f0-9-]{36}\\.png$/',(string)$name),'file_exists'=>(bool)($file&&is_file($file)),'mime_ok'=>$mime==='image/png','private_path'=>$outside,'mode_ok'=>$mode==='0600','line_ok'=>$line_ok,'price_ok'=>$price_ok,'payment_ok'=>$payment_ok,'mail_suppressed'=>$mail_ok,'mail_local'=>$mail_local,'mail_environment'=>$mail_environment,'mail_filter'=>$mail_filter,'filename'=>$name]);"
        )
        result = marked_json(wpcli_eval(php))
        evidence_name = str(result.get("filename", ""))
        checks = {
            "pedido vinculado al correo ficticio único": result.get("found") is True and result.get("email_ok") is True,
            "estado Pendiente de revisión de propiedad": result.get("status_ok") is True and result.get("review") == "pending",
            "configuración conservada en la línea del pedido": result.get("line_ok") is True,
            "precio del pedido coincide con el precio de producto del servidor": result.get("price_ok") is True,
            "pedido usa solo la pasarela ficticia": result.get("payment_ok") is True,
            "evidencia PNG guardada fuera de la web con modo 0600": result.get("filename_ok") is True and result.get("file_exists") is True and result.get("mime_ok") is True and result.get("private_path") is True and result.get("mode_ok") is True,
            "modo local de correo activo": result.get("mail_local") is True,
            "filtro local bloquea wp_mail": result.get("mail_filter") is True,
        }
        for label, passed in checks.items():
            print(f"{'OK' if passed else 'FALLO'}: {label}")

        if evidence_name:
            private_url = base + "/var/private/gamesog-evidence/" + evidence_name
            public_status, public_headers, _public_body = request_response(opener, private_url)
            public_type = public_headers.get("Content-Type", "").lower()
            public_denied = public_status != 200 and not public_type.startswith("image/")
            print(f"{'OK' if public_denied else 'FALLO'}: la ruta de evidencia no es pública por HTTP")
            checks["la ruta de evidencia no es pública por HTTP"] = public_denied

            admin_url = base + f"/wp-admin/admin-post.php?action=gamesog_evidence&order_id={order_id}"
            auth_status, auth_headers, _auth_body = request_response(opener, admin_url)
            auth_type = auth_headers.get("Content-Type", "").lower()
            denied = auth_status == 403 and not auth_type.startswith("image/")
            print(f"{'OK' if denied else 'FALLO'}: la descarga administrativa deniega al visitante sin sesión")
            checks["la descarga administrativa deniega al visitante sin sesión"] = denied

        cart_html = fetch_html(opener, base + CART_PATH)
        cart_parser = CheckoutParser()
        cart_parser.feed(cart_html)
        cart_empty = not cart_parser.remove_links
        print(f"{'OK' if cart_empty else 'FALLO'}: carrito vacío después de confirmar el pedido")
        checks["carrito vacío después del pedido"] = cart_empty

        if not all(checks.values()):
            raise RuntimeError("El pedido se creó, pero falló al menos una comprobación de integración.")
    finally:
        orders_to_clean = [order_id] if order_id else []
        if not order_id and valid_checkout_sent:
            try:
                orders_to_clean = find_test_orders(email)
                if not orders_to_clean:
                    print("OK: no quedó un pedido parcial con el correo ficticio de esta prueba.")
            except RuntimeError:
                print(f"AVISO: no se pudo buscar un posible pedido parcial; comprueba en Administración el correo {email}.")
        if orders_to_clean:
            for test_order_id in orders_to_clean:
                try:
                    if cleanup_order(test_order_id, email):
                        print(f"OK: pedido de prueba {test_order_id} y evidencia temporal eliminados.")
                    else:
                        print(f"AVISO: no se eliminó el pedido de prueba {test_order_id}; comprueba en Administración el correo {email}.")
                except RuntimeError:
                    print(f"AVISO: no se pudo limpiar el pedido de prueba {test_order_id}; comprueba en Administración el correo {email}.")
        if valid_checkout_sent and valid_image_hash:
            try:
                removed, protected = cleanup_orphan_evidence(valid_image_hash)
                if removed:
                    print("OK: evidencia huérfana de esta ejecución eliminada tras verificar su hash y que ningún pedido la referencia.")
                elif protected:
                    print("AVISO: evidencia conservada porque un pedido la referencia; revisa el pedido de prueba.")
                else:
                    print("OK: no queda evidencia PNG huérfana de esta ejecución.")
            except RuntimeError:
                print("AVISO: no se pudo comprobar/eliminar la evidencia huérfana de esta ejecución; revisa el directorio privado.")
        if added_to_cart:
            try:
                cleaned = remove_test_cart(opener, base)
                print(f"{'OK' if cleaned else 'AVISO'}: carrito temporal {'vaciado' if cleaned else 'pendiente de vaciar'}.")
            except RuntimeError:
                print("AVISO: no se pudo vaciar el carrito de la sesión de prueba.")


if __name__ == "__main__":
    try:
        main()
    except (RuntimeError, SystemExit) as error:
        raise SystemExit(str(error))

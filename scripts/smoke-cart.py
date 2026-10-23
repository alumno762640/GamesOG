#!/usr/bin/env python3
"""Non-destructive guest-cart smoke test for the local GamesOG installation."""

from __future__ import annotations

import ipaddress
import re
from decimal import Decimal, InvalidOperation
from html.parser import HTMLParser
from http.cookiejar import CookieJar
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urljoin, urlsplit
from urllib.request import HTTPCookieProcessor, Request, build_opener


ROOT = Path(__file__).resolve().parents[1]
PRODUCT_PATH = "/product/hogwarts-legacy/"
CART_PATH = "/carrito/"


class PageParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.inputs: dict[str, str] = {}
        self.input_names: set[str] = set()
        self.input_types: dict[str, str] = {}
        self.product_id = ""
        self.payment_methods: list[str] = []
        self.remove_links: list[str] = []
        self.text: list[str] = []
        self.prices: list[list[str]] = []
        self.price_depth: int | None = None
        self.base_price = ""
        self.special_fee = ""

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = {key: value or "" for key, value in attrs}
        name = values.get("name", "")
        if tag == "input" and name:
            self.inputs[name] = values.get("value", "")
            self.input_names.add(name)
            self.input_types[name] = values.get("type", "")
            if name == "payment_method":
                self.payment_methods.append(values.get("value", ""))
        if tag == "button" and name == "add-to-cart":
            self.product_id = values.get("value", "")
        if tag == "a" and "remove_item=" in values.get("href", ""):
            self.remove_links.append(values["href"])
        if tag == "span" and "woocommerce-Price-amount" in values.get("class", "").split():
            self.prices.append([])
            self.price_depth = 0
        elif self.price_depth is not None:
            self.price_depth += 1
        if "data-price-summary" in values:
            self.base_price = values.get("data-base-price", "")
            self.special_fee = values.get("data-special-price", "")

    def handle_data(self, data: str) -> None:
        self.text.append(data)
        if self.price_depth is not None and self.prices:
            self.prices[-1].append(data)

    def handle_endtag(self, tag: str) -> None:
        if self.price_depth is None:
            return
        if tag == "span" and self.price_depth == 0:
            self.price_depth = None
        else:
            self.price_depth = max(0, self.price_depth - 1)


def decimal_amount(value: str) -> Decimal | None:
    match = re.search(r"\d[\d.,]*", value.replace("\xa0", " "))
    if not match:
        return None
    token = match.group(0)
    if "," in token and "." in token:
        decimal_separator = "," if token.rfind(",") > token.rfind(".") else "."
        thousands_separator = "." if decimal_separator == "," else ","
        token = token.replace(thousands_separator, "").replace(decimal_separator, ".")
    elif "," in token:
        token = token.replace(",", ".")
    try:
        return Decimal(token).quantize(Decimal("0.01"))
    except InvalidOperation:
        return None


def local_site_url() -> str:
    env_file = ROOT / ".env"
    try:
        lines = env_file.read_text(encoding="utf-8").splitlines()
    except OSError as error:
        raise SystemExit("No se pudo leer .env; ejecuta esta prueba dentro del proyecto.") from error

    value = next((line.split("=", 1)[1].strip() for line in lines if line.startswith("WP_SITE_URL=")), "")
    value = value.strip("\"'").rstrip("/")
    parsed = urlsplit(value)
    if parsed.scheme != "http" or not parsed.hostname or parsed.username or parsed.password:
        raise SystemExit("WP_SITE_URL debe ser una URL HTTP local sin credenciales.")
    host = parsed.hostname.lower()
    if host != "localhost" and not host.endswith(".localhost"):
        try:
            if not ipaddress.ip_address(host).is_private:
                raise SystemExit("Por seguridad, la prueba solo acepta localhost o una IP privada.")
        except ValueError as error:
            raise SystemExit("Por seguridad, WP_SITE_URL debe apuntar a localhost o a una IP privada.") from error
    return value


def request(opener, url: str, data: bytes | None = None, referer: str | None = None) -> str:
    headers = {"User-Agent": "GamesOG-local-smoke-test/1.0"}
    if data is not None:
        headers["Content-Type"] = "application/x-www-form-urlencoded"
    if referer:
        headers["Referer"] = referer
    try:
        with opener.open(Request(url, data=data, headers=headers), timeout=15) as response:
            if response.status != 200:
                raise RuntimeError(f"HTTP {response.status} al consultar {urlsplit(url).path}")
            return response.read().decode("utf-8", errors="replace")
    except (HTTPError, URLError, TimeoutError) as error:
        raise RuntimeError(f"No se pudo consultar {urlsplit(url).path}: {error}") from error


def remove_test_cart(opener, cart_url: str) -> bool:
    for _ in range(4):
        parser = PageParser()
        parser.feed(request(opener, cart_url))
        if not parser.remove_links:
            return True
        request(opener, urljoin(cart_url, parser.remove_links[0]))
    parser = PageParser()
    parser.feed(request(opener, cart_url))
    return not parser.remove_links


def main() -> None:
    base = local_site_url()
    product_url = base + PRODUCT_PATH
    cart_url = base + CART_PATH
    opener = build_opener(HTTPCookieProcessor(CookieJar()))
    created_lines = False

    try:
        product_html = request(opener, product_url)
        product = PageParser()
        product.feed(product_html)
        nonce = product.inputs.get("gamesog_config_nonce", "")
        product_id = product.product_id
        expected_base = decimal_amount(product.base_price)
        expected_fee = decimal_amount(product.special_fee)
        if not nonce or not product_id or "gamesog-options" not in product_html or expected_base is None or expected_fee is None:
            raise RuntimeError("La ficha no contiene el formulario completo de personalización.")

        configurations = [
            {"gamesog_format": "USB", "gamesog_design": "basico", "gamesog_color": "negro"},
            {"gamesog_format": "USB", "gamesog_design": "especial"},
        ]
        for config in configurations:
            fields = {
                "add-to-cart": product_id,
                "quantity": "1",
                "gamesog_config_nonce": nonce,
                **config,
            }
            created_lines = True
            request(opener, product_url, urlencode(fields).encode("utf-8"), product_url)

        cart_html = request(opener, cart_url)
        cart = PageParser()
        cart.feed(cart_html)
        cart_text = re.sub(r"\s+", " ", " ".join(cart.text))
        displayed_prices = ["".join(parts).replace("\xa0", " ").strip() for parts in cart.prices]
        observed_amounts = {decimal_amount(price) for price in displayed_prices}
        observed_amounts.discard(None)
        expected_amounts = {expected_base, expected_base + expected_fee}
        checks = {
            "dos configuraciones separadas": len(cart.remove_links) == 2,
            "preset básico": "Preset básico" in cart_text,
            "edición especial": "Edición especial" in cart_text,
            "color solo en diseño básico": "Negro" in cart_text and "Fijo" not in cart_text,
            "precios calculados en servidor según el precio editable": expected_amounts.issubset(observed_amounts),
        }
        failed = [label for label, passed in checks.items() if not passed]
        for label, passed in checks.items():
            print(f"{'OK' if passed else 'FALLO'}: {label}")
        if not checks["precios calculados en servidor según el precio editable"]:
            print("Importes esperados: " + ", ".join(f"{amount:.2f}" for amount in sorted(expected_amounts)))
            print("Importes visibles: " + (", ".join(displayed_prices) if displayed_prices else "ninguno detectado"))
        if failed:
            raise RuntimeError("La comprobación del carrito no superó todos los criterios.")

        checkout_html = request(opener, base + "/pedido/")
        checkout = PageParser()
        checkout.feed(checkout_html)
        checkout_checks = {
            "checkout clásico visible": bool(re.search(r"<form\b[^>]*class=[\"'][^\"']*\bcheckout\b", checkout_html, re.I)),
            "nonce del checkout presente": bool(checkout.inputs.get("gamesog_checkout_nonce")),
            "subida de evidencia y confirmación explícita": checkout.input_types.get("gamesog_evidence") == "file" and checkout.inputs.get("gamesog_demo_confirm") == "1",
            "datos de facturación mínimos": {"billing_first_name", "billing_email"}.issubset(checkout.input_names) and not ({"billing_last_name", "billing_address_1", "billing_phone"} & checkout.input_names),
            "única pasarela disponible: demostración sin cobro": set(checkout.payment_methods) == {"gamesog_demo"},
        }
        for label, passed in checkout_checks.items():
            print(f"{'OK' if passed else 'FALLO'}: {label}")
        if not all(checkout_checks.values()):
            print("Métodos de pago encontrados: " + (", ".join(checkout.payment_methods) if checkout.payment_methods else "ninguno"))
            print("Campos: nonce=" + str(bool(checkout.inputs.get("gamesog_checkout_nonce")))
                  + ", evidencia=" + str(checkout.input_types.get("gamesog_evidence") == "file")
                  + ", confirmación=" + str(checkout.inputs.get("gamesog_demo_confirm") == "1"))
            raise RuntimeError("El checkout no contiene todos los controles esperados.")
    finally:
        if created_lines:
            try:
                cleaned = remove_test_cart(opener, cart_url)
            except RuntimeError:
                cleaned = False
            if cleaned:
                print("OK: carrito temporal de prueba vaciado.")
            else:
                print("AVISO: no se pudo vaciar el carrito temporal; ábrelo con la sesión de prueba para retirarlo.")


if __name__ == "__main__":
    try:
        main()
    except (RuntimeError, SystemExit) as error:
        raise SystemExit(str(error))

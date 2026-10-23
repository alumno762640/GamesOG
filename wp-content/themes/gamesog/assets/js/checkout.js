(() => {
  if (!window.jQuery) return;

  window.jQuery(($) => {
    const form = document.querySelector("form.checkout");
    if (!form) return;

    let sending = false;

    const showMessages = (markup) => {
      form.querySelectorAll(".woocommerce-NoticeGroup-checkout").forEach((node) => node.remove());
      const group = document.createElement("div");
      group.className = "woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout";
      group.innerHTML = markup;
      form.prepend(group);
      group.scrollIntoView({ behavior: "smooth", block: "center" });
    };

    $(form).on("checkout_place_order.gamesog", function () {
      const evidence = form.querySelector("#gamesog_evidence");
      if (!evidence?.files?.length) return true;
      if (sending) return false;

      const params = window.wc_checkout_params || {};
      const endpoint = params.checkout_url || (params.wc_ajax_url && params.wc_ajax_url.toString().replace("%%endpoint%%", "checkout"));
      if (!endpoint) {
        showMessages('<ul class="woocommerce-error" role="alert"><li>No se pudo iniciar el pedido de demostración. Recarga e inténtalo de nuevo.</li></ul>');
        return false;
      }

      sending = true;
      const button = form.querySelector("#place_order");
      if (button) button.disabled = true;

      $.ajax({
        type: "POST",
        url: endpoint,
        data: new FormData(form),
        processData: false,
        contentType: false,
        dataType: "json"
      }).done((result) => {
        if (result?.result === "success" && result.redirect) {
          window.location.assign(result.redirect);
          return;
        }

        if (result?.messages) {
          showMessages(result.messages);
        } else {
          showMessages('<ul class="woocommerce-error" role="alert"><li>No se pudo guardar el pedido de demostración. Revisa los datos e inténtalo de nuevo.</li></ul>');
        }
      }).fail((xhr) => {
        const messages = xhr.responseJSON?.messages;
        showMessages(messages || '<ul class="woocommerce-error" role="alert"><li>No se pudo guardar la evidencia privada. Revisa la imagen e inténtalo de nuevo.</li></ul>');
      }).always(() => {
        sending = false;
        if (button) button.disabled = false;
      });

      return false;
    });
  });
})();

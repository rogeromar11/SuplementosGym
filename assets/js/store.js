(function () {
  'use strict';

  var cfg = window.STORE_CONFIG || {};
  var base = cfg.base || '/';

  function url(path) {
    return base.replace(/\/$/, '') + '/' + String(path).replace(/^\//, '');
  }

  function csrfForm() {
    var fd = new FormData();
    if (cfg.csrfName) {
      fd.append(cfg.csrfName, cfg.csrfHash);
    }
    return fd;
  }

  function post(path, data) {
    var fd = csrfForm();
    Object.keys(data || {}).forEach(function (key) {
      fd.append(key, data[key]);
    });
    return fetch(url(path), {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json().catch(function () {
        return { success: false, message: 'Respuesta no valida del servidor.' };
      });
    });
  }

  function toast(message, isError) {
    var el = document.querySelector('.store-toast');
    if (!el) {
      el = document.createElement('div');
      el.className = 'store-toast';
      el.setAttribute('role', 'status');
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.classList.toggle('error', !!isError);
    el.classList.add('show');
    window.clearTimeout(el._timer);
    el._timer = window.setTimeout(function () {
      el.classList.remove('show');
    }, 3200);
  }

  function setBadge(count) {
    document.querySelectorAll('[data-cart-count]').forEach(function (node) {
      node.textContent = count;
      node.classList.toggle('d-none', count < 1);
    });
  }

  function bindAddToCart() {
    document.querySelectorAll('[data-add-to-cart]').forEach(function (button) {
      if (button._bound) { return; }
      button._bound = true;
      button.addEventListener('click', function (event) {
        event.preventDefault();
        if (button.disabled) { return; }
        var id = button.getAttribute('data-product-id');
        var qtyInput = document.querySelector('[data-qty-input][data-product-id="' + id + '"]');
        var quantity = qtyInput ? qtyInput.value : (button.getAttribute('data-quantity') || 1);

        button.disabled = true;
        post('carrito/agregar', { product_id: id, quantity: quantity }).then(function (res) {
          button.disabled = false;
          if (res.success) {
            setBadge(res.count);
            toast(res.message || 'Producto agregado.');
            if (button.hasAttribute('data-buy-now')) {
              window.location.href = url('checkout');
            }
          } else {
            toast(res.message || 'No fue posible agregar el producto.', true);
          }
        }).catch(function () {
          button.disabled = false;
          toast('Error de conexion. Intenta nuevamente.', true);
        });
      });
    });
  }

  function bindCartPage() {
    function applySummary(res) {
      if (!res || !res.success) { return; }
      setBadge(res.count);
      var subtotal = document.querySelector('[data-summary-subtotal]');
      var shipping = document.querySelector('[data-summary-shipping]');
      var total = document.querySelector('[data-summary-total]');
      if (subtotal) { subtotal.textContent = res.subtotal; }
      if (shipping) { shipping.textContent = res.shipping; }
      if (total) { total.textContent = res.total; }
      Object.keys(res.lines || {}).forEach(function (id) {
        var cell = document.querySelector('[data-line-total="' + id + '"]');
        if (cell) { cell.textContent = res.lines[id].line_total; }
        var input = document.querySelector('[data-cart-qty="' + id + '"] input');
        if (input) { input.value = res.lines[id].quantity; }
      });
    }

    document.querySelectorAll('[data-cart-remove]').forEach(function (button) {
      button.addEventListener('click', function () {
        var id = button.getAttribute('data-cart-remove');
        post('carrito/eliminar', { product_id: id }).then(function (res) {
          if (res.success) {
            var row = document.querySelector('[data-cart-row="' + id + '"]');
            if (row) { row.remove(); }
            applySummary(res);
            toast('Producto eliminado.');
            if (res.count === 0) { window.location.reload(); }
          }
        });
      });
    });

    document.querySelectorAll('[data-cart-qty] button').forEach(function (button) {
      button.addEventListener('click', function () {
        var control = button.closest('[data-cart-qty]');
        var input = control.querySelector('input');
        var id = control.getAttribute('data-cart-qty');
        var next = parseInt(input.value, 10) + parseInt(button.getAttribute('data-delta'), 10);
        if (next < 1) { next = 1; }
        post('carrito/actualizar', { product_id: id, quantity: next }).then(function (res) {
          if (res.success) {
            input.value = next;
            applySummary(res);
          } else {
            if (res.quantity) { input.value = res.quantity; }
            toast(res.message || 'Cantidad no disponible.', true);
          }
        });
      });
    });

    var clearButton = document.querySelector('[data-cart-clear]');
    if (clearButton) {
      clearButton.addEventListener('click', function () {
        post('carrito/vaciar', {}).then(function (res) {
          if (res.success) { window.location.reload(); }
        });
      });
    }
  }

  function bindCountrySelector() {
    var select = document.querySelector('[data-country-select]');
    if (!select) { return; }

    var modal = document.querySelector('[data-country-modal]');

    function change(confirmCart) {
      post('pais', { country_id: select.value, confirm_cart: confirmCart ? 1 : '' }).then(function (res) {
        if (res.require_confirmation) {
          if (modal) { modal.classList.add('open'); }
          return;
        }
        if (res.success) {
          window.location.href = res.redirect || url('productos');
        } else {
          toast(res.message || 'No fue posible cambiar de pais.', true);
        }
      });
    }

    select.addEventListener('change', function () {
      change(false);
    });

    if (modal) {
      var confirm = modal.querySelector('[data-country-confirm]');
      var cancel = modal.querySelector('[data-country-cancel]');
      if (confirm) {
        confirm.addEventListener('click', function () {
          modal.classList.remove('open');
          change(true);
        });
      }
      if (cancel) {
        cancel.addEventListener('click', function () {
          modal.classList.remove('open');
          window.location.reload();
        });
      }
    }
  }

  function bindLiveSearch() {
    var input = document.querySelector('[data-live-search]');
    if (!input) { return; }
    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-search-item]'));
    var empty = document.querySelector('[data-search-empty]');

    input.addEventListener('input', function () {
      var term = input.value.trim().toLowerCase();
      var visible = 0;
      cards.forEach(function (card) {
        var match = card.getAttribute('data-search-item').toLowerCase().indexOf(term) !== -1;
        card.classList.toggle('d-none', !match);
        if (match) { visible++; }
      });
      if (empty) {
        empty.classList.toggle('d-none', visible !== 0);
      }
    });
  }

  function bindMobileNav() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var links = document.querySelector('[data-nav-links]');
    if (!toggle || !links) { return; }
    toggle.addEventListener('click', function () {
      links.classList.toggle('open');
    });
  }

  function bindReveal() {
    var items = document.querySelectorAll('.reveal');
    if (!items.length || !('IntersectionObserver' in window)) {
      items.forEach(function (item) { item.classList.add('visible'); });
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    items.forEach(function (item) { observer.observe(item); });
  }

  function bindQtyInputs() {
    document.querySelectorAll('[data-qty-control]').forEach(function (control) {
      control.querySelectorAll('button').forEach(function (button) {
        button.addEventListener('click', function () {
          var input = control.querySelector('input');
          var next = parseInt(input.value, 10) + parseInt(button.getAttribute('data-delta'), 10);
          if (next < 1) { next = 1; }
          input.value = next;
        });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindAddToCart();
    bindCartPage();
    bindCountrySelector();
    bindLiveSearch();
    bindMobileNav();
    bindReveal();
    bindQtyInputs();
  });

  window.storeToast = toast;
})();

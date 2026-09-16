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

  function addProduct(id, quantity, button) {
    if (button) { button.disabled = true; }
    return post('carrito/agregar', { product_id: id, quantity: quantity || 1 }).then(function (res) {
      if (button) { button.disabled = false; }
      if (res.success) {
        setBadge(res.count);
        toast(res.message || 'Producto agregado.');
        if (button && button.hasAttribute('data-buy-now')) {
          window.location.href = url('checkout');
        }
      } else {
        toast(res.message || 'No fue posible agregar el producto.', true);
      }
      return res;
    }).catch(function () {
      if (button) { button.disabled = false; }
      toast('Error de conexion. Intenta nuevamente.', true);
    });
  }

  function openFlavorModal(button) {
    var modal = document.querySelector('[data-flavor-modal]');
    if (!modal) { return; }
    var flavors;
    try { flavors = JSON.parse(button.getAttribute('data-flavor-picker')); } catch (e) { return; }
    if (!flavors || !flavors.length) { return; }

    var optionsWrap = modal.querySelector('[data-flavor-options]');
    var productEl = modal.querySelector('[data-flavor-product]');
    var confirmBtn = modal.querySelector('[data-flavor-confirm]');
    var selected = null;

    if (productEl) { productEl.textContent = button.getAttribute('data-product-name') || ''; }
    optionsWrap.innerHTML = '';
    confirmBtn.disabled = true;

    flavors.forEach(function (f) {
      var option = document.createElement('button');
      option.type = 'button';
      option.className = 'flavor-option' + (f.available ? '' : ' is-out');
      option.textContent = f.flavor + (f.available ? ' · ' + f.price : ' (agotado)');
      if (!f.available) { option.disabled = true; }
      option.addEventListener('click', function () {
        optionsWrap.querySelectorAll('.flavor-option').forEach(function (el) { el.classList.remove('active'); });
        option.classList.add('active');
        selected = f;
        confirmBtn.disabled = false;
      });
      optionsWrap.appendChild(option);
      if (!selected && f.available) {
        selected = f;
        option.classList.add('active');
        confirmBtn.disabled = false;
      }
    });

    function close() { modal.classList.remove('open'); }
    confirmBtn.onclick = function () {
      if (!selected) { return; }
      close();
      addProduct(selected.id, 1, null);
    };
    var cancel = modal.querySelector('[data-flavor-cancel]');
    if (cancel) { cancel.onclick = close; }
    modal.onclick = function (event) { if (event.target === modal) { close(); } };

    modal.classList.add('open');
  }

  function bindAddToCart() {
    document.querySelectorAll('[data-add-to-cart]').forEach(function (button) {
      if (button._bound) { return; }
      button._bound = true;
      button.addEventListener('click', function (event) {
        event.preventDefault();
        if (button.disabled) { return; }
        if (button.hasAttribute('data-flavor-picker')) {
          openFlavorModal(button);
          return;
        }
        var id = button.getAttribute('data-product-id');
        var qtyInput = document.querySelector('[data-qty-input][data-product-id="' + id + '"]');
        var quantity = qtyInput ? qtyInput.value : (button.getAttribute('data-quantity') || 1);
        addProduct(id, quantity, button);
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
    var buttons = document.querySelectorAll('[data-country-choice]');
    if (!buttons.length) { return; }

    var modal = document.querySelector('[data-country-modal]');
    var pendingId = null;

    function doChange(countryId, confirmCart) {
      post('pais', { country_id: countryId, confirm_cart: confirmCart ? 1 : '' }).then(function (res) {
        if (res.require_confirmation) {
          if (modal) { modal.classList.add('open'); }
          return;
        }
        if (res.success) {
          window.location.href = res.redirect || window.location.href;
        } else {
          toast(res.message || 'No fue posible cambiar de pais.', true);
        }
      });
    }

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        if (button.classList.contains('active')) { return; }
        pendingId = button.getAttribute('data-country-id');
        doChange(pendingId, false);
      });
    });

    if (modal) {
      var confirm = modal.querySelector('[data-country-confirm]');
      var cancel = modal.querySelector('[data-country-cancel]');
      if (confirm) {
        confirm.addEventListener('click', function () {
          modal.classList.remove('open');
          if (pendingId) { doChange(pendingId, true); }
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
    var items = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
    if (!items.length) { return; }

    items.forEach(function (item) {
      var index = 0;
      var node = item.previousElementSibling;
      while (node) {
        if (node.classList && node.classList.contains('reveal')) { index++; }
        node = node.previousElementSibling;
      }
      item.style.setProperty('--d', Math.min(index, 8) * 80 + 'ms');
    });

    if (!('IntersectionObserver' in window)) {
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
    }, { threshold: 0.14, rootMargin: '0px 0px -40px 0px' });
    items.forEach(function (item) { observer.observe(item); });
  }

  function bindNavbarScroll() {
    var nav = document.querySelector('.site-navbar');
    if (!nav) { return; }
    var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 12); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function bindCounters() {
    var counters = Array.prototype.slice.call(document.querySelectorAll('[data-count-to]'));
    if (!counters.length) { return; }
    var animate = function (el) {
      var target = parseInt(el.getAttribute('data-count-to'), 10) || 0;
      var suffix = el.getAttribute('data-count-suffix') || '';
      var duration = 1400;
      var start = null;
      function step(ts) {
        if (!start) { start = ts; }
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(target * eased) + suffix;
        if (progress < 1) { requestAnimationFrame(step); }
      }
      requestAnimationFrame(step);
    };
    if (!('IntersectionObserver' in window)) {
      counters.forEach(animate);
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { animate(entry.target); observer.unobserve(entry.target); }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { observer.observe(el); });
  }

  function bindHeroParallax() {
    var card = document.querySelector('[data-hero-visual]');
    var hero = document.querySelector('.hero');
    if (!card || !hero || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
    hero.addEventListener('mousemove', function (event) {
      var rect = hero.getBoundingClientRect();
      var x = (event.clientX - rect.left) / rect.width - 0.5;
      var y = (event.clientY - rect.top) / rect.height - 0.5;
      card.style.transform = 'translate3d(' + (x * 14).toFixed(1) + 'px,' + (y * 14).toFixed(1) + 'px,0)';
    });
    hero.addEventListener('mouseleave', function () { card.style.transform = ''; });
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

  function bindAccordion() {
    document.querySelectorAll('[data-accordion]').forEach(function (item) {
      var btn = item.querySelector('[data-accordion-btn]');
      var panel = item.querySelector('[data-accordion-panel]');
      if (!btn || !panel) { return; }
      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') === 'true';
        document.querySelectorAll('[data-accordion] [data-accordion-btn]').forEach(function (other) {
          other.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('[data-accordion] [data-accordion-panel]').forEach(function (otherPanel) {
          otherPanel.style.maxHeight = null;
        });
        if (!open) {
          btn.setAttribute('aria-expanded', 'true');
          panel.style.maxHeight = panel.scrollHeight + 'px';
        }
      });
    });
  }

  function bindCheckoutWhatsApp() {
    var button = document.querySelector('[data-wa-checkout]');
    var dataEl = document.getElementById('waCheckoutData');
    if (!button || !dataEl) { return; }
    var data;
    try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

    button.addEventListener('click', function () {
      var field = function (name) {
        var el = document.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
      };
      var msg = 'Hola, quiero finalizar esta compra (' + data.country + '):\n\n';
      (data.lines || []).forEach(function (l) {
        msg += '- ' + l.qty + 'x ' + l.name + (l.lab ? ' (' + l.lab + ')' : '') + (l.flavor ? ' - Sabor: ' + l.flavor : '') + ' - ' + l.total + '\n';
      });
      msg += '\nSubtotal: ' + data.subtotal;
      msg += '\nEnvio: ' + data.shipping;
      msg += '\nTotal: ' + data.total;
      var nombre = field('customer_name');
      var tel = field('customer_phone');
      var zona = field('delivery_zone');
      var dir = field('delivery_address');
      if (nombre || tel || zona || dir) { msg += '\n\n'; }
      if (nombre) { msg += 'Nombre: ' + nombre + '\n'; }
      if (tel) { msg += 'Telefono: ' + tel + '\n'; }
      if (zona) { msg += 'Zona: ' + zona + '\n'; }
      if (dir) { msg += 'Direccion: ' + dir + '\n'; }
      window.open('https://wa.me/' + data.number + '?text=' + encodeURIComponent(msg), '_blank');
    });
  }

  function bindDropdowns() {
    var drops = Array.prototype.slice.call(document.querySelectorAll('[data-dropdown]'));
    if (!drops.length) { return; }

    function closeAll(except) {
      drops.forEach(function (d) {
        if (d !== except) {
          d.classList.remove('open');
          var b = d.querySelector('[data-dropdown-toggle]');
          if (b) { b.setAttribute('aria-expanded', 'false'); }
        }
      });
    }

    drops.forEach(function (d) {
      var btn = d.querySelector('[data-dropdown-toggle]');
      if (!btn) { return; }
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var willOpen = !d.classList.contains('open');
        closeAll(d);
        d.classList.toggle('open', willOpen);
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });
    });

    document.addEventListener('click', function () { closeAll(null); });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { closeAll(null); }
    });
    document.querySelectorAll('[data-dropdown-menu] a').forEach(function (link) {
      link.addEventListener('click', function () { closeAll(null); });
    });
  }

  function bindProductGallery() {
    var main = document.getElementById('pdMainImage');
    var thumbs = Array.prototype.slice.call(document.querySelectorAll('.pd-thumb'));
    if (!main || !thumbs.length) { return; }
    thumbs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var src = btn.getAttribute('data-src');
        if (!src) { return; }
        main.src = src;
        thumbs.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
      });
    });
  }

  function bindProductZoom() {
    var media = document.querySelector('.pd-media');
    if (!media) { return; }
    var img = media.querySelector('img');
    if (!img) { return; }

    media.classList.add('is-zoomable');
    media.setAttribute('role', 'button');
    media.setAttribute('tabindex', '0');
    media.setAttribute('aria-label', 'Ampliar imagen del producto');

    var hint = document.createElement('span');
    hint.className = 'pd-zoom-hint';
    hint.textContent = 'Clic para ampliar';
    media.appendChild(hint);

    var overlay = document.createElement('div');
    overlay.className = 'store-zoom-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML = '<button type="button" class="store-zoom-close" aria-label="Cerrar">&times;</button><img alt="">';
    document.body.appendChild(overlay);
    var big = overlay.querySelector('img');

    function open() {
      big.src = img.getAttribute('src');
      big.alt = img.getAttribute('alt') || '';
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.classList.add('zoom-open');
    }

    function close() {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('zoom-open');
    }

    media.addEventListener('click', open);
    media.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        open();
      }
    });
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay || event.target.classList.contains('store-zoom-close')) {
        close();
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
        close();
      }
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
    bindAccordion();
    bindNavbarScroll();
    bindCounters();
    bindHeroParallax();
    bindCheckoutWhatsApp();
    bindDropdowns();
    bindProductGallery();
    bindProductZoom();
  });

  window.storeToast = toast;
})();

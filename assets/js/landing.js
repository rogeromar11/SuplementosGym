(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function fmt(value, dec) {
    dec = dec || 0;
    try {
      return new Intl.NumberFormat('es-CR', { minimumFractionDigits: dec, maximumFractionDigits: dec }).format(value);
    } catch (e) {
      return Number(value).toFixed(dec);
    }
  }

  function initCounters() {
    var els = $$('.count[data-target]');
    if (!els.length) { return; }
    var run = function (el) {
      var target = parseFloat(el.getAttribute('data-target')) || 0;
      var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
      var suf = el.getAttribute('data-sufijo') || '';
      var dur = REDUCED ? 0 : 1500;
      var t0 = null;
      function step(t) {
        if (t0 === null) { t0 = t; }
        var p = Math.min(1, (t - t0) / dur) || 1;
        p = 1 - Math.pow(1 - p, 3);
        el.textContent = fmt(target * p, dec) + suf;
        if (p < 1) { requestAnimationFrame(step); }
      }
      if (dur === 0) { el.textContent = fmt(target, dec) + suf; } else { requestAnimationFrame(step); }
    };
    if (!('IntersectionObserver' in window)) { els.forEach(run); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { run(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.5 });
    els.forEach(function (el) { io.observe(el); });
  }

  function initUI() {
    var header = $('.site-header');
    var bar = $('#progressBar');
    var onScroll = function () {
      var y = window.scrollY || 0;
      if (header) { header.classList.toggle('scrolled', y > 24); }
      if (bar) {
        var h = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var btn = $('.menu-btn');
    if (btn) {
      btn.addEventListener('click', function () {
        var open = document.body.classList.toggle('menu-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
    $$('.menu-panel a').forEach(function (a) {
      a.addEventListener('click', function () {
        document.body.classList.remove('menu-open');
        if (btn) { btn.setAttribute('aria-expanded', 'false'); }
      });
    });

    var video = $('.hero-video');
    var vbtn = $('.video-toggle');
    if (video && vbtn) {
      vbtn.addEventListener('click', function () {
        if (video.paused) {
          video.play();
          vbtn.classList.remove('is-paused');
          vbtn.setAttribute('aria-label', 'Pausar video de fondo');
        } else {
          video.pause();
          vbtn.classList.add('is-paused');
          vbtn.setAttribute('aria-label', 'Reproducir video de fondo');
        }
      });
    }

    document.addEventListener('mousemove', function (e) {
      var el = e.target.closest ? e.target.closest('.spot') : null;
      if (!el) { return; }
      var r = el.getBoundingClientRect();
      el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      el.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });

    var anio = $('#anio');
    if (anio) { anio.textContent = new Date().getFullYear(); }
  }

  function initAnimations() {
    if (!window.gsap || !window.ScrollTrigger || REDUCED) { return; }
    gsap.registerPlugin(ScrollTrigger);

    if ($('.hero-title')) {
      var tl = gsap.timeline({ defaults: { ease: 'power4.out' } });
      tl.from('.hero-title .line > span', { yPercent: 118, duration: 1.15, stagger: 0.12 })
        .from('.hero-kicker, .hero-sub, .hero-ctas', { y: 26, autoAlpha: 0, duration: 0.8, stagger: 0.1 }, '-=0.75')
        .from('.hero-stats .stat', { y: 20, autoAlpha: 0, duration: 0.6, stagger: 0.07 }, '-=0.5');
      if ($('.hero-video')) { tl.from('.hero-video', { scale: 1.1, duration: 2.4, ease: 'power2.out' }, 0); }
    }

    if ($('.hero-media')) {
      gsap.to('.hero-media', {
        yPercent: 12,
        ease: 'none',
        scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
      });
    }

    ScrollTrigger.batch('[data-reveal]', {
      start: 'top 88%',
      once: true,
      batchMax: 4,
      onEnter: function (batch) {
        gsap.from(batch, { y: 34, autoAlpha: 0, duration: 0.8, stagger: 0.08, ease: 'power3.out', clearProps: 'transform,opacity,visibility' });
      }
    });
    ScrollTrigger.refresh();
  }

  function init() {
    initUI();
    initCounters();
    initAnimations();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

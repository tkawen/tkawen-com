/*!
 * TKAWEN Intel · track.js v1.0
 *
 * Drop-in tracker. Add to any TKAWEN page:
 *   <script async src="https://tkawen.online/intel/track.js"
 *           data-source="page-slug"></script>
 *
 * What it does automatically:
 *   - pageview event on load
 *   - time-on-page on unload
 *   - scroll-depth milestones (25/50/75/100%)
 *   - outbound clicks
 *   - form submissions on <form data-tkawen-track>
 *   - SPA route changes via History API
 *
 * Public API (window.tkawen):
 *   tkawen.track(kind, fields?)        — fire arbitrary event
 *   tkawen.identify(email, fields?)    — promote anonymous → known lead
 *   tkawen.set(key, value)             — set sticky context (e.g. source)
 *
 * Privacy: respects DNT header, no cookies set by this script
 * (cookie management is server-side via capture.php).
 */
(function () {
  'use strict';

  // ─── Honor Do-Not-Track ─────────────────────────────────────
  if (navigator.doNotTrack === '1' || window.doNotTrack === '1' || navigator.msDoNotTrack === '1') {
    window.tkawen = { track: function(){}, identify: function(){}, set: function(){} };
    return;
  }

  // ─── Config ─────────────────────────────────────────────────
  var SCRIPT = document.currentScript;
  var API = (SCRIPT && SCRIPT.dataset.api) || 'https://tkawen.online/intel/capture.php';
  var SOURCE = (SCRIPT && SCRIPT.dataset.source) || location.hostname.replace(/^www\./, '');
  var DEBUG = !!(SCRIPT && SCRIPT.dataset.debug);

  var context = {
    source: SOURCE,
    page_load_at: Date.now(),
    scroll_max: 0,
  };

  // ─── Util ───────────────────────────────────────────────────
  function log() { if (DEBUG && console && console.log) console.log.apply(console, ['[tkawen]'].concat([].slice.call(arguments))); }

  function parseUtm() {
    var p = new URLSearchParams(location.search);
    var o = {};
    ['source', 'medium', 'campaign', 'content', 'term'].forEach(function (k) {
      var v = p.get('utm_' + k);
      if (v) o['utm_' + k] = v.slice(0, 80);
    });
    var ref = p.get('ref'); if (ref) o.ref = ref.slice(0, 32);
    return o;
  }

  function post(payload, isEvent) {
    payload.source = payload.source || context.source;
    payload.page = payload.page || location.pathname + location.search;
    payload.referrer = payload.referrer || document.referrer;
    var utm = parseUtm();
    for (var k in utm) if (!payload[k]) payload[k] = utm[k];

    var url = API + (isEvent ? '?event=1' : '');
    log('POST', url, payload);

    // Use sendBeacon for unload events (most reliable)
    if (isEvent && payload.kind === 'time_on_page' && navigator.sendBeacon) {
      var blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
      try { navigator.sendBeacon(url, blob); return; } catch (e) {}
    }

    // Otherwise fetch
    if (window.fetch) {
      return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        keepalive: isEvent,
        body: JSON.stringify(payload),
      }).catch(function (e) { log('fetch error', e); });
    }
    // Fallback for ancient browsers
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.withCredentials = true;
    xhr.send(JSON.stringify(payload));
  }

  // ─── Public API ─────────────────────────────────────────────
  window.tkawen = {
    track: function (kind, fields) {
      return post({ kind: kind || 'event', fields: fields || {} }, true);
    },
    identify: function (email, fields) {
      if (!email || typeof email !== 'string') return;
      return post({
        kind: 'identify',
        email: email.trim().toLowerCase(),
        name: fields && fields.name,
        phone: fields && fields.phone,
        wilaya: fields && fields.wilaya,
        fields: fields || {},
      }, false);
    },
    set: function (key, value) {
      context[key] = value;
    },
    _v: '1.0',
  };

  // ─── Auto: pageview on load ─────────────────────────────────
  post({ kind: 'pageview' }, true);

  // ─── Auto: scroll depth milestones ──────────────────────────
  var milestones = [25, 50, 75, 100];
  var hit = {};
  var rafToken = null;
  function onScroll() {
    if (rafToken) return;
    rafToken = requestAnimationFrame(function () {
      rafToken = null;
      var doc = document.documentElement;
      var pct = Math.min(100, Math.round(((window.scrollY + window.innerHeight) / doc.scrollHeight) * 100));
      if (pct > context.scroll_max) context.scroll_max = pct;
      milestones.forEach(function (m) {
        if (pct >= m && !hit[m]) {
          hit[m] = true;
          post({ kind: 'scroll_depth', fields: { depth: m } }, true);
        }
      });
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });

  // ─── Auto: outbound click tracking ──────────────────────────
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href || href[0] === '#' || href.indexOf('javascript:') === 0) return;
    var host = '';
    try { host = new URL(href, location.href).hostname; } catch (er) { return; }
    if (!host) return;
    var isOutbound = host !== location.hostname;
    var isCta = a.dataset && a.dataset.tkawenCta;
    if (isOutbound || isCta) {
      post({
        kind: isCta ? 'cta_click' : 'outbound_click',
        fields: { href: href.slice(0, 256), text: (a.innerText || '').trim().slice(0, 80), cta: isCta || null },
      }, true);
    }
  });

  // ─── Auto: form submission catching ─────────────────────────
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f || !f.hasAttribute || !f.hasAttribute('data-tkawen-track')) return;
    var fd = new FormData(f);
    var email = fd.get('email');
    if (!email) return;

    var obj = {};
    fd.forEach(function (v, k) {
      if (k === 'password' || k === 'pass' || k === 'pin' || k === 'cvv') return;  // never send secrets
      if (typeof v === 'string' && v.length < 256) obj[k] = v;
    });

    post({
      kind: 'form_submit',
      email: String(email).trim().toLowerCase(),
      name: fd.get('name') || '',
      phone: fd.get('phone') || '',
      wilaya: fd.get('wilaya') || '',
      source: f.dataset.tkawenSource || context.source,
      fields: obj,
    }, false);
  });

  // ─── Auto: time on page (unload) ────────────────────────────
  function reportTimeOnPage() {
    var t = Math.round((Date.now() - context.page_load_at) / 1000);
    if (t < 2) return;  // ignore bounces
    post({
      kind: 'time_on_page',
      fields: { seconds: t, scroll_max: context.scroll_max },
    }, true);
  }
  window.addEventListener('pagehide', reportTimeOnPage);
  window.addEventListener('beforeunload', reportTimeOnPage);

  // ─── Auto: SPA route changes (Vue/React/Nuxt) ───────────────
  var lastPath = location.pathname + location.search;
  function onRouteChange() {
    var current = location.pathname + location.search;
    if (current === lastPath) return;
    lastPath = current;
    context.page_load_at = Date.now();
    context.scroll_max = 0;
    hit = {};
    post({ kind: 'pageview', fields: { spa: true } }, true);
  }
  ['pushState', 'replaceState'].forEach(function (method) {
    var orig = history[method];
    history[method] = function () {
      var ret = orig.apply(this, arguments);
      setTimeout(onRouteChange, 0);
      return ret;
    };
  });
  window.addEventListener('popstate', onRouteChange);

  log('tkawen tracker initialized', { source: SOURCE, api: API });
})();

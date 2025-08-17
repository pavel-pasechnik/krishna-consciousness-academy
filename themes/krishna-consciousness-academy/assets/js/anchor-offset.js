(function () {
  const $html = document.documentElement;

  function getHeaderOffset() {
    const header = document.querySelector('.header-wrapper');
    const adminBar = document.getElementById('wpadminbar');
    const h = header ? header.offsetHeight : 0;
    const a = adminBar ? adminBar.offsetHeight : 0;
    return h + a;
  }

  let rafId = 0;
  const recalc = () => {
    cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(() => {
      const offset = getHeaderOffset();
      $html.style.setProperty('--header-offset', offset + 'px');
    });
  };

  // Recalculation on load, resize, change of .scrolled class, and change of fonts/content
  window.addEventListener('load', recalc, { once: true });
  window.addEventListener('resize', recalc);
  window.addEventListener('orientationchange', recalc);

  // If you add/remove the .scrolled class on scroll, let's move the recalculation to the observer:
  const header = document.querySelector('.header-wrapper');
  if (header) {
    const mo = new MutationObserver(recalc);
    mo.observe(header, { attributes: true, attributeFilter: ['class', 'style'] });
  }

  // If the page is already open with a hash, after the first recalculation, scroll again taking into account the indentation.
  window.addEventListener('load', () => {
    if (location.hash.length > 1) {
      // wait one tick for scroll-padding to take effect
      setTimeout(() => {
        try {
          const raw = location.hash.slice(1);
          let id = raw;
          try {
            id = decodeURIComponent(raw);
          } catch (e) {
            // Malformed URI sequence — fall back to raw hash
            id = raw;
          }
          const target = document.getElementById(id);
          if (target) target.scrollIntoView({ block: 'start' });
        } catch (e) {
          // As a last resort, do nothing but touch the error to satisfy linters
          void e;
        }
      }, 0);
    }
  });
  // --- Preserve current hash when switching site language (Polylang) ---
  (function preserveHashOnLangSwitch() {
    const getCurrentHash = () => (location.hash && location.hash.length > 1 ? location.hash : '');

    // Click on language links (block, widget, menu, custom markup)
    document.addEventListener(
      'click',
      ev => {
        const a = ev.target && ev.target.closest && ev.target.closest('a');
        if (!a) return;
        // Heuristics: common wrappers/classes Polylang uses
        const wrapper = a.closest(
          '.wp-block-polylang-language-switcher, .language-switcher, .lang, .polylang-switcher, .widget_polylang, .menu-item-language'
        );
        if (!wrapper) return;

        const hash = getCurrentHash();
        if (!hash) return;

        try {
          const u = new URL(a.getAttribute('href'), location.origin);
          // Only append if target URL does not already have a hash
          if (!u.hash) {
            u.hash = hash;
            a.setAttribute('href', u.toString());
          }
        } catch (e) {
          // Non-standard/relative href; best-effort append
          if (!a.getAttribute('href').includes('#')) {
            a.setAttribute('href', a.getAttribute('href') + hash);
          }
        }
      },
      true
    );

    // Change on language <select> (dropdown mode)
    document.addEventListener(
      'change',
      ev => {
        const select =
          ev.target &&
          ev.target.closest &&
          ev.target.closest(
            '.wp-block-polylang-language-switcher select, .language-switcher select, .polylang-switcher select'
          );
        if (!select) return;

        const hash = getCurrentHash();
        if (!hash) return;

        const form = select.closest('form');
        if (form && form.action) {
          try {
            const u = new URL(form.action, location.origin);
            if (!u.hash) u.hash = hash;
            form.action = u.toString();
          } catch (_) {
            if (!form.action.includes('#')) form.action += hash;
          }
        } else if (select.value) {
          try {
            const u = new URL(select.value, location.origin);
            if (!u.hash) {
              u.hash = hash;
              select.value = u.toString();
            }
          } catch (_) {
            if (!select.value.includes('#')) select.value += hash;
          }
        }
      },
      true
    );
  })();
})();

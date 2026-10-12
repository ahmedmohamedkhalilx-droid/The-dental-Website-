(function () {
  var toggle = document.getElementById('menu-toggle');
  var nav = document.getElementById('site-nav');
  if (!toggle || !nav) { return; }
  function setOpen(open) {
    nav.setAttribute('data-open', open ? 'true' : 'false');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  }
  toggle.addEventListener('click', function () { setOpen(nav.getAttribute('data-open') !== 'true'); });

  // A button beside each menu item that has a submenu, used on small screens.
  nav.querySelectorAll('.menu-item-has-children').forEach(function (li) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'sub-toggle';
    b.setAttribute('aria-expanded', 'false');
    b.setAttribute('aria-label', 'Show submenu');
    b.textContent = '+';
    b.addEventListener('click', function () {
      var open = li.classList.toggle('is-open');
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
      b.textContent = open ? '\u2212' : '+';
    });
    var link = li.querySelector(':scope > a');
    if (link) { link.after(b); }
  });

  nav.addEventListener('click', function (e) { if (e.target.closest('a')) { setOpen(false); } });

  // Touch screens: the first tap on a treatment card shows its details, a second tap opens the page.
  var allCards = Array.prototype.slice.call(document.querySelectorAll('.svc'));
  if (allCards.length && window.matchMedia && window.matchMedia('(hover: none)').matches) {
    allCards.forEach(function (c) {
      c.addEventListener('click', function (e) {
        if (!c.classList.contains('is-open')) {
          e.preventDefault();
          allCards.forEach(function (o) { o.classList.remove('is-open'); });
          c.classList.add('is-open');
        } else if (!e.target.closest('a')) {
          c.classList.remove('is-open');
        }
      });
    });
  }

  // Treatment cards slide in as they scroll into view (only cards still below the fold; never hides anything for good).
  var cards = Array.prototype.slice.call(document.querySelectorAll('.svc'));
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (cards.length && 'IntersectionObserver' in window && !calm) {
    var below = cards.filter(function (c) { return c.getBoundingClientRect().top > window.innerHeight * 0.95; });
    var show = function (c) {
      if (c.classList.contains('is-in')) { return; }
      c.classList.add('is-in');
      // Once it has arrived, hand the card back to its normal styles so hover and tap motion work.
      window.setTimeout(function () { c.classList.remove('will-reveal', 'is-in'); c.style.removeProperty('--reveal-delay'); }, 1500);
    };
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { show(e.target); io.unobserve(e.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    below.forEach(function (c, i) {
      c.style.setProperty('--reveal-delay', ((i % 5) * 70) + 'ms');
      c.classList.add('will-reveal');
      io.observe(c);
    });
    // Safety net: whatever has not appeared after a few seconds is shown anyway.
    window.setTimeout(function () { below.forEach(show); }, 4000);
  }
})();

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
})();

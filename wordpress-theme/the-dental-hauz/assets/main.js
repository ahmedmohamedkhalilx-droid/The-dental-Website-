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
  nav.addEventListener('click', function (e) { if (e.target.closest('a')) { setOpen(false); } });
})();

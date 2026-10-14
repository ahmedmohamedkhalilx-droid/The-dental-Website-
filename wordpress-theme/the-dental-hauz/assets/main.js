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

  // Google reviews: a scrolling row of review cards. Arrows, swiping, dragging with the mouse and the keyboard all work,
  // and it moves along by itself every few seconds (paused while you hover, touch or focus it, and for reduced motion).
  document.querySelectorAll('[data-rv]').forEach(function (rv) {
    var track = rv.querySelector('.rv__track');
    var prev = rv.querySelector('.rv__nav--prev');
    var next = rv.querySelector('.rv__nav--next');
    if (!track || !prev || !next) { return; }
    var paused = false;
    var resumeTimer = null;
    var calmMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function maxScroll() { return track.scrollWidth - track.clientWidth; }
    function update() {
      var max = maxScroll();
      var scrolls = max > 4;
      prev.hidden = !scrolls || track.scrollLeft <= 4;
      next.hidden = !scrolls || track.scrollLeft >= max - 4;
    }
    function step(dir) {
      var card = track.querySelector('.rv__card');
      var by = card ? card.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
      track.scrollBy({ left: dir * by, behavior: calmMotion ? 'auto' : 'smooth' });
    }
    function pause() { paused = true; window.clearTimeout(resumeTimer); }
    function resume(delay) { window.clearTimeout(resumeTimer); resumeTimer = window.setTimeout(function () { paused = false; }, delay || 0); }
    prev.addEventListener('click', function () { step(-1); pause(); resume(8000); });
    next.addEventListener('click', function () { step(1); pause(); resume(8000); });
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    rv.addEventListener('mouseenter', pause);
    rv.addEventListener('mouseleave', function () { resume(1500); });
    rv.addEventListener('focusin', pause);
    rv.addEventListener('focusout', function () { resume(1500); });
    track.addEventListener('touchstart', pause, { passive: true });
    track.addEventListener('touchend', function () { resume(8000); }, { passive: true });

    // Drag with the mouse.
    var down = false, startX = 0, startLeft = 0, moved = false;
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) { return; }
      down = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
    });
    window.addEventListener('pointermove', function (e) {
      if (!down) { return; }
      var dx = e.clientX - startX;
      if (!moved && Math.abs(dx) > 5) { moved = true; track.classList.add('is-dragging'); }
      if (moved) { track.scrollLeft = startLeft - dx; }
    });
    window.addEventListener('pointerup', function () {
      if (!down) { return; }
      down = false;
      track.classList.remove('is-dragging');
    });

    // Move along by itself, and wrap back to the start at the end.
    if (!calmMotion) {
      window.setInterval(function () {
        if (paused || document.hidden || maxScroll() <= 4) { return; }
        var r = rv.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) { return; }
        if (track.scrollLeft >= maxScroll() - 4) { track.scrollTo({ left: 0, behavior: 'smooth' }); } else { step(1); }
      }, 5000);
    }
    update();
  });

  // Touch screens: the first tap on a treatment card shows its details, a second tap opens the page.
  // On phones the cards are compact tiles without details, so a tap opens the page straight away.
  var allCards = Array.prototype.slice.call(document.querySelectorAll('.svc'));
  var phone = window.matchMedia ? window.matchMedia('(max-width: 600px)') : { matches: false };
  if (allCards.length && window.matchMedia && window.matchMedia('(hover: none)').matches) {
    allCards.forEach(function (c) {
      c.addEventListener('click', function (e) {
        if (phone.matches) { return; }
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

  // Forms are sent in the background so the visitor stays on the page and sees a thank-you pop-up.
  // Without JavaScript (or if the background send fails) the form posts normally and still works.
  var modal = document.getElementById('dh-thanks');
  var lastFocus = null;
  function openThanks() {
    if (!modal) { return; }
    lastFocus = document.activeElement;
    modal.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    var done = modal.querySelector('.dh-modal__done');
    if (done) { done.focus(); }
  }
  function closeThanks() {
    if (!modal || modal.hidden) { return; }
    modal.hidden = true;
    document.documentElement.style.overflow = '';
    if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
  }
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.closest('[data-close]')) { closeThanks(); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeThanks(); } });
  }
  // Arrived back from a normal (non-background) submission: show the same pop-up and tidy the address bar.
  if (/[?&]dh_status=sent\b/.test(window.location.search)) {
    openThanks();
    if (window.history && window.history.replaceState) {
      var clean = window.location.pathname + window.location.search.replace(/([?&])dh_(form|status)=[^&]*/g, '$1').replace(/[?&]+$/, '').replace(/\?&+/, '?');
      window.history.replaceState(null, '', clean);
    }
  }
  Array.prototype.slice.call(document.querySelectorAll('form.form[action*="admin-post.php"]')).forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (e.defaultPrevented || form.dataset.native === '1' || !window.fetch || !window.FormData) { return; }
      e.preventDefault();
      var data = new FormData(form);
      data.append('dh_ajax', '1');
      var btn = form.querySelector('button[type=submit]');
      var label = btn ? btn.textContent : '';
      form.classList.add('is-sending');
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }
      var note = form.querySelector('.form-note');
      fetch(form.getAttribute('action'), { method: 'POST', body: data, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          form.classList.remove('is-sending');
          if (btn) { btn.disabled = false; btn.textContent = label; }
          if (res && res.ok) {
            form.reset();
            if (note) { note.remove(); }
            openThanks();
          } else {
            if (!note) {
              note = document.createElement('p');
              note.setAttribute('role', 'status');
              form.insertBefore(note, form.querySelector('.fields'));
            }
            note.className = 'form-note is-error';
            note.textContent = (res && res.message) || 'Something went wrong. Please try again or call the clinic.';
            note.scrollIntoView({ block: 'center', behavior: 'smooth' });
          }
        })
        .catch(function () {
          // Fall back to a normal submission.
          form.dataset.native = '1';
          form.classList.remove('is-sending');
          if (btn) { btn.disabled = false; btn.textContent = label; }
          HTMLFormElement.prototype.submit.call(form);
        });
    });
  });

  // Phones: long text sections show their first paragraph, the rest folds behind "Read more".
  if (window.matchMedia && window.matchMedia('(max-width: 600px)').matches) {
    Array.prototype.slice.call(document.querySelectorAll('.split__copy, .intro__copy')).forEach(function (box) {
      var paras = Array.prototype.slice.call(box.children).filter(function (el) {
        return el.tagName === 'P' && !el.classList.contains('eyebrow') && !el.classList.contains('lead');
      });
      var hasLead = !!box.querySelector(':scope > p.lead');
      var keep = hasLead ? 0 : 1;
      var extra = paras.slice(keep);
      if (!extra.length) { return; }
      extra.forEach(function (p) { p.classList.add('fold-extra'); });
      box.classList.add('is-folded');
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'read-more';
      btn.textContent = 'Read more';
      btn.setAttribute('aria-expanded', 'false');
      extra[extra.length - 1].insertAdjacentElement('afterend', btn);
      btn.addEventListener('click', function () {
        var open = box.classList.toggle('is-folded') === false;
        btn.textContent = open ? 'Read less' : 'Read more';
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });
  }
})();

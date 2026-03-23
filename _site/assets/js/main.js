// Phase 1 minimal JS
// - Mobile nav toggle

(function () {
  const header = document.querySelector(".site-header");
  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector("#site-nav");
  const backdrop = document.querySelector("[data-nav-backdrop]");
  const closeBtn = nav ? nav.querySelector("[data-nav-close]") : null;

  if (!header || !toggle || !nav) return;

  const mq = window.matchMedia("(min-width: 720px)");

  function setOpen(isOpen) {
    header.setAttribute("data-nav-open", String(isOpen));
    toggle.setAttribute("aria-expanded", String(isOpen));
    document.body.toggleAttribute("data-scroll-lock", Boolean(isOpen));

    // Prevent screen reader focus when closed on mobile
    if (mq.matches) {
      nav.removeAttribute("aria-hidden");
    } else {
      nav.setAttribute("aria-hidden", String(!isOpen));
    }
  }

  function open() {
    setOpen(true);
    // Focus the first interactive element inside the drawer
    const first = nav.querySelector("a, button");
    if (first && first.focus) first.focus();
  }

  function close() {
    setOpen(false);
  }

  toggle.addEventListener("click", () => {
    const isOpen = header.getAttribute("data-nav-open") === "true";
    if (isOpen) close();
    else open();
  });

  if (backdrop) backdrop.addEventListener("click", close);
  if (closeBtn) closeBtn.addEventListener("click", close);

  // Close drawer when a link is clicked (mobile)
  nav.addEventListener("click", (e) => {
    if (mq.matches) return;
    const a = e.target && e.target.closest ? e.target.closest("a") : null;
    if (a) close();
  });

  // Close on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });

  // If we enter desktop, ensure drawer isn't open
  mq.addEventListener("change", () => {
    if (mq.matches) close();
  });

  // Default closed
  close();
})();

// Dynamic nav — fetch nav items from API and replace static links
(function () {
  const primaryContainer = document.querySelector('[data-nav-primary]');
  const metaContainer = document.querySelector('[data-nav-meta]');
  if (!primaryContainer && !metaContainer) return;

  const currentPath = window.location.pathname.replace(/\/$/, '');

  function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function buildPrimaryLink(item) {
    const itemPath = item.url.replace(/\/$/, '');
    const active = currentPath === itemPath ? ' is-active' : '';
    return '<a class="nav-link nav-link--primary' + active + '" href="' + escapeHtml(item.url) + '">'
      + escapeHtml(item.label) + ' <span class="nav-caret" aria-hidden="true"></span></a>';
  }

  function buildMetaLink(item) {
    return '<a class="nav-link nav-link--muted" href="' + escapeHtml(item.url) + '">' + escapeHtml(item.label) + '</a>';
  }

  fetch('/nav-items.json', { cache: 'no-store' })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (primaryContainer && Array.isArray(data.primary)) {
        var mobileLinks = '<a class="nav-link nav-link--mobileonly" href="/search/#focus">SEARCH</a>'
          + '<a class="nav-link nav-link--mobileonly" href="/cart/">'
          + 'CART <span class="cart-badge" data-cart-count aria-label="Cart items">0</span></a>';
        primaryContainer.innerHTML = data.primary.map(buildPrimaryLink).join('') + mobileLinks;
      }
      if (metaContainer && Array.isArray(data.meta)) {
        metaContainer.innerHTML = data.meta.map(buildMetaLink).join('');
      }
    })
    .catch(function () {
      // Keep static fallback links on fetch failure
    });
})();

// Phase 3 enhancement
// - Collection page client-side sorting (optional)
(function () {
  const grids = Array.from(document.querySelectorAll('[data-product-grid]'));
  const selects = Array.from(document.querySelectorAll('[data-sort-select]'));

  if (!grids.length || !selects.length) return;

  // Capture original order for "Featured"
  grids.forEach((grid) => {
    Array.from(grid.querySelectorAll('.product-card')).forEach((card, idx) => {
      if (!card.dataset.originalIndex) card.dataset.originalIndex = String(idx);
    });
  });

  function readNumber(value) {
    const n = parseFloat(String(value || '').replace(/[^0-9.]/g, ''));
    return Number.isFinite(n) ? n : 0;
  }

  function sortCards(cards, mode) {
    const dir = mode.endsWith('-desc') ? -1 : 1;

    if (mode === 'featured') {
      return cards.sort((a, b) => (readNumber(a.dataset.originalIndex) - readNumber(b.dataset.originalIndex)));
    }

    if (mode.startsWith('price')) {
      return cards.sort((a, b) => dir * (readNumber(a.dataset.price) - readNumber(b.dataset.price)));
    }

    if (mode.startsWith('title')) {
      return cards.sort((a, b) => {
        const at = (a.dataset.title || '').toLowerCase();
        const bt = (b.dataset.title || '').toLowerCase();
        return dir * at.localeCompare(bt);
      });
    }

    return cards;
  }

  function applySort(mode) {
    grids.forEach((grid) => {
      const cards = Array.from(grid.querySelectorAll('.product-card'));
      const sorted = sortCards(cards, mode);
      sorted.forEach((card) => grid.appendChild(card));
    });
  }

  function setSelectValues(mode, source) {
    selects.forEach((sel) => {
      if (sel !== source) sel.value = mode;
    });
  }

  // Bind selects and keep them in sync
  selects.forEach((sel) => {
    sel.addEventListener('change', (e) => {
      const mode = e.target.value || 'featured';
      setSelectValues(mode, sel);
      applySort(mode);
    });
  });
})();


// Phase 4 enhancement
// - PDP image gallery (simple placeholders + thumbnails)
(function () {
  const galleries = Array.from(document.querySelectorAll('[data-gallery]'));
  if (!galleries.length) return;

  galleries.forEach((gallery) => {
    const mainImg = gallery.querySelector('[data-gallery-img]');
    const placeholder = gallery.querySelector('[data-gallery-placeholder]');
    const placeholderLabel = gallery.querySelector('[data-placeholder-label]');
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
    if (!thumbs.length) return;

    function setActive(btn) {
      thumbs.forEach((t) => t.classList.remove('is-active'));
      btn.classList.add('is-active');
    }

    thumbs.forEach((btn) => {
      btn.addEventListener('click', () => {
        const src = btn.getAttribute('data-src');
        const ph = btn.getAttribute('data-placeholder');

        if (src && mainImg) {
          mainImg.setAttribute('src', src);
        } else if (ph && placeholder && placeholderLabel) {
          placeholderLabel.textContent = ph;
        }

        setActive(btn);
      });
    });
  });
})();

// Phase 5 enhancement
// - Collection filters + live product feed rendering (no rebuild)
(function () {
  const plp = document.querySelector('[data-collection-page]');
  if (!plp) return;

  const grid = plp.querySelector('[data-product-grid]');
  const countEl = plp.querySelector('[data-plp-count]');
  const refreshButtons = Array.from(plp.querySelectorAll('[data-products-refresh]'));
  const chips = Array.from(plp.querySelectorAll('[data-filter-chip]'));
  const clearBtn = plp.querySelector('[data-filters-clear]');
  const collectionKey = (plp.getAttribute('data-collection-key') || '').trim();

  const toggleBtn = plp.querySelector('[data-filters-toggle]');
  const closeBtn = plp.querySelector('[data-filters-close]');
  const backdrop = document.querySelector('[data-filters-backdrop]');

  let all = [];

  function readNumber(value) {
    const n = parseFloat(String(value || '').replace(/[^0-9.]/g, ''));
    return Number.isFinite(n) ? n : 0;
  }

  function toArr(v) {
    if (Array.isArray(v)) return v.map(String);
    if (typeof v === 'string') return v.split(',').map(s => s.trim()).filter(Boolean);
    return [];
  }

  function valuesFor(p) {
    const tags = toArr(p.tags);
    const cols = toArr(p.collections || p.collection || []);
    const badges = toArr(p.badges);
    if (p.badge) badges.push(String(p.badge));
    return {
      tags: tags.map(s => s.toLowerCase()),
      collections: cols.map(s => s.toLowerCase()),
      badges: badges.map(s => s.toLowerCase()),
    };
  }

  function inCollection(p) {
    if (!collectionKey) return true;
    const needle = collectionKey.toLowerCase();
    const v = valuesFor(p);
    return v.collections.includes(needle) || v.tags.includes(needle) || v.badges.includes(needle);
  }

  function matchesFilter(p, filter) {
    if (!filter) return true;
    const needle = String(filter).toLowerCase();
    const v = valuesFor(p);

    const anyIncludes = (arr) => arr.some(x => x.includes(needle));
    return anyIncludes(v.tags) || anyIncludes(v.collections) || anyIncludes(v.badges);
  }

  function matchesPrice(p, priceKey) {
    if (!priceKey) return true;
    const price = readNumber(p.price);
    if (priceKey === '0-25') return price >= 0 && price <= 25;
    if (priceKey === '25-75') return price >= 25 && price <= 75;
    if (priceKey === '75+') return price >= 75;
    return true;
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function productUrl(p) {
    const slug = (p.slug || '').replace(/^\//, '').replace(/\/$/, '');
    return p.url || `/products/${slug}/`;
  }

  function cardHtml(p, idx) {
    const img = p.image || '/assets/img/placeholder.jpg';
    const title = p.title || 'Untitled';
    const subtitle = p.subtitle || '';
    const price = p.price || '0.00';
    const compare = p.compareAt ? String(p.compareAt) : '';

    const v = valuesFor(p);

    const badgeText = (p.badge && String(p.badge).trim()) || (Array.isArray(p.badges) && p.badges.length ? p.badges[0] : '');
    const badge = badgeText ? `<span class="badge">${escapeHtml(badgeText)}</span>` : '';

    const pid = p.id || '';
    const slug = p.slug || '';

    return `
      <article class="product-card"
        data-title="${escapeHtml(title)}"
        data-price="${escapeHtml(price)}"
        data-original-index="${idx}"
        data-tags="${escapeHtml(v.tags.join(','))}"
        data-collections="${escapeHtml(v.collections.join(','))}"
        data-badges="${escapeHtml(v.badges.join(','))}">
        <a class="product-card__media" href="${escapeHtml(productUrl(p))}">
          <img class="product-card__img" src="${escapeHtml(img)}" alt="${escapeHtml(title)}" loading="lazy" />
          <div class="product-card__badge">${badge}</div>
        </a>
        <div class="product-card__body">
          <h3 class="product-card__title"><a href="${escapeHtml(productUrl(p))}">${escapeHtml(title)}</a></h3>
          <p class="product-card__subtitle muted">${escapeHtml(subtitle)}</p>
          <div class="product-card__bottom">
            <div class="price">
              <span class="price__current">$${escapeHtml(price)}</span>
              ${compare ? `<span class="price__compare">$${escapeHtml(compare)}</span>` : ''}
            </div>
            <button class="btn btn-primary btn-sm"
              type="button"
              data-cart-add
              data-id="${escapeHtml(pid)}"
              data-slug="${escapeHtml(slug)}"
              data-title="${escapeHtml(title)}"
              data-price="${escapeHtml(price)}"
              data-image="${escapeHtml(img)}">
              Add
            </button>
          </div>
        </div>
      </article>
    `;
  }

  function updateChipState(filter, priceKey) {
    chips.forEach((chip) => {
      const type = chip.getAttribute('data-filter-type');
      const value = chip.getAttribute('data-filter-value');
      const pressed = (type === 'filter' && value === (filter || '')) || (type === 'price' && value === (priceKey || ''));
      chip.setAttribute('aria-pressed', pressed ? 'true' : 'false');
    });
  }

  function setCount(n) {
    if (!countEl) return;
    if (!n) countEl.textContent = 'No products found';
    else countEl.textContent = `Showing ${n} item${n === 1 ? '' : 's'}`;
  }

  function currentSortMode() {
    const sel = plp.querySelector('[data-sort-select]');
    return sel ? (sel.value || 'featured') : 'featured';
  }

  function sortProducts(items, mode) {
    const dir = mode.endsWith('-desc') ? -1 : 1;
    if (mode === 'price-asc') return items.slice().sort((a, b) => readNumber(a.price) - readNumber(b.price));
    if (mode === 'price-desc') return items.slice().sort((a, b) => readNumber(b.price) - readNumber(a.price));
    if (mode === 'title-asc') return items.slice().sort((a, b) => String(a.title || '').localeCompare(String(b.title || ''), undefined, { sensitivity: 'base' }));
    if (mode === 'title-desc') return items.slice().sort((a, b) => String(b.title || '').localeCompare(String(a.title || ''), undefined, { sensitivity: 'base' }));
    return items;
  }

  function applyFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const filter = params.get('filter') || '';
    const priceKey = params.get('price') || '';

    updateChipState(filter, priceKey);

    const scoped = all.filter(inCollection);
    const filtered = scoped.filter(p => matchesFilter(p, filter) && matchesPrice(p, priceKey));
    const sorted = sortProducts(filtered, currentSortMode());

    if (grid) {
      grid.innerHTML = sorted.map((p, idx) => cardHtml(p, idx)).join('');
    }
    setCount(sorted.length);
  }

  async function fetchProducts() {
    const res = await fetch('/products.json', { cache: 'no-store' });
    const data = await res.json();
    all = Array.isArray(data) ? data : [];
  }

  async function refresh() {
    try {
      await fetchProducts();
      applyFromUrl();
    } catch (e) {
      // If fetch fails, keep static server-rendered list
    }
  }

  // Drawer
  function setDrawer(open) {
    if (open) {
      document.body.setAttribute('data-filters-open', 'true');
      if (backdrop) backdrop.hidden = false;
    } else {
      document.body.removeAttribute('data-filters-open');
      if (backdrop) backdrop.hidden = true;
    }
  }

  if (toggleBtn) toggleBtn.addEventListener('click', () => setDrawer(true));
  if (closeBtn) closeBtn.addEventListener('click', () => setDrawer(false));
  if (backdrop) backdrop.addEventListener('click', () => setDrawer(false));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setDrawer(false);
  });

  if (chips.length) {
    chips.forEach((chip) => {
      chip.addEventListener('click', () => {
        const type = chip.getAttribute('data-filter-type');
        const value = chip.getAttribute('data-filter-value') || '';
        const params = new URLSearchParams(window.location.search);

        const current = params.get(type === 'price' ? 'price' : 'filter') || '';
        const isSame = current === value;

        if (type === 'price') {
          if (isSame) params.delete('price');
          else params.set('price', value);
        } else {
          if (isSame) params.delete('filter');
          else params.set('filter', value);
        }

        const next = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
        window.history.replaceState({}, '', next);
        applyFromUrl();
        setDrawer(false);
      });
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      const next = `${window.location.pathname}`;
      window.history.replaceState({}, '', next);
      applyFromUrl();
      setDrawer(false);
    });
  }

  // Keep sort in sync with the existing sort module
  const sortSelects = Array.from(plp.querySelectorAll('[data-sort-select]'));
  sortSelects.forEach((sel) => {
    sel.addEventListener('change', () => applyFromUrl());
  });

  refreshButtons.forEach((btn) => btn.addEventListener('click', refresh));

  // Initial render with live feed
  refresh();
})();

// Phase 6 enhancement
// - PDP variant selection (size/color) -> updates add-to-cart datasets
(function () {
  const pdp = document.querySelector('[data-product-page]');
  if (!pdp) return;

  const addBtn = pdp.querySelector('[data-cart-add]');
  const groups = Array.from(pdp.querySelectorAll('[data-variant-group]'));
  if (!addBtn || !groups.length) return;

  const selected = {};

  function updateAdd() {
    if (selected.size) addBtn.dataset.size = selected.size;
    if (selected.color) addBtn.dataset.color = selected.color;
  }

  groups.forEach((group) => {
    const name = group.getAttribute('data-variant-name');
    const opts = Array.from(group.querySelectorAll('[data-variant-option]'));
    if (!name || !opts.length) return;

    function pick(btn) {
      opts.forEach((b) => b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'));
      selected[name] = btn.getAttribute('data-variant-value') || btn.textContent.trim();
      updateAdd();
    }

    const initial = opts.find((b) => b.getAttribute('aria-pressed') === 'true') || opts[0];
    pick(initial);

    opts.forEach((btn) => {
      btn.addEventListener('click', () => pick(btn));
    });
  });

  updateAdd();
})();

// Phase 7 enhancement
// - Make placeholder forms/buttons do something real (no "dead" clicks)
(function () {
  // Contact form: open mail client with prefilled message
  const contactForm = document.querySelector('[data-contact-form]');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = contactForm.querySelector('input[name="name"]')?.value || '';
      const email = contactForm.querySelector('input[name="email"]')?.value || '';
      const msg = contactForm.querySelector('textarea[name="message"]')?.value || '';
      const to = contactForm.getAttribute('data-mailto') || 'support@example.com';
      const subject = encodeURIComponent('Website contact');
      const body = encodeURIComponent(`Name: ${name}\nEmail: ${email}\n\n${msg}`);
      window.location.href = `mailto:${to}?subject=${subject}&body=${body}`;

      const feedback = contactForm.querySelector('[data-contact-feedback]');
      if (feedback) {
        feedback.textContent = 'Opening your email client…';
        feedback.style.display = 'block';
      }
    });
  }

  // Account "sign in" placeholder
  const accountForm = document.querySelector('[data-account-form]');
  if (accountForm) {
    accountForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const note = accountForm.querySelector('[data-account-feedback]');
      if (note) {
        note.textContent = 'Customer accounts are not enabled yet.';
        note.style.display = 'block';
      }
    });
  }
})();

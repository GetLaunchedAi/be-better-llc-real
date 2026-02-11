/* =========================================
   Search (client-side) — Phase 6
   - Fetches /products.json (built by Eleventy)
   - Simple substring match across title, slug, tags, collections
   ========================================= */

(function () {
  const root = document.querySelector("[data-search-page]");
  if (!root) return;

  const input = root.querySelector("[data-search-input]");
  const resultsEl = root.querySelector("[data-search-results]");
  const metaEl = root.querySelector("[data-search-meta]");
  const emptyEl = root.querySelector("[data-search-empty]");
  const clearBtn = root.querySelector("[data-search-clear]");
  const loadingEl = root.querySelector("[data-search-loading]");

  if (!input || !resultsEl || !metaEl || !emptyEl) return;

  const PLACEHOLDER = `
    <div class="product-card__placeholder" aria-hidden="true">
      <svg viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Product image placeholder">
        <defs>
          <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="rgba(109,124,255,0.35)" />
            <stop offset="1" stop-color="rgba(34,211,238,0.25)" />
          </linearGradient>
        </defs>
        <rect width="640" height="480" rx="28" fill="url(#g)" />
        <rect x="36" y="340" width="568" height="18" rx="9" fill="rgba(255,255,255,0.18)" />
        <rect x="36" y="370" width="440" height="18" rx="9" fill="rgba(255,255,255,0.12)" />
      </svg>
    </div>
  `;

  function escapeHtml(str) {
    return String(str || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function normalizeText(value) {
    return String(value || "").trim().toLowerCase();
  }

  function getSearchParam() {
    try {
      const url = new URL(window.location.href);
      return url.searchParams.get("q") || "";
    } catch (e) {
      return "";
    }
  }

  function shouldFocus() {
    try {
      return (window.location.hash || "") === "#focus";
    } catch (e) {
      return false;
    }
  }

  function setSearchParam(q) {
    try {
      const url = new URL(window.location.href);
      if (q) url.searchParams.set("q", q);
      else url.searchParams.delete("q");
      window.history.replaceState({}, "", url.toString());
    } catch (e) {}
  }

  function productHaystack(p) {
    const parts = [];
    parts.push(p.title, p.slug, p.subtitle);
    if (Array.isArray(p.tags)) parts.push(p.tags.join(" "));
    if (Array.isArray(p.collections)) parts.push(p.collections.join(" "));
    if (p.category) parts.push(p.category);
    return normalizeText(parts.filter(Boolean).join(" "));
  }

  function matches(p, q) {
    if (!q) return true;
    const hay = productHaystack(p);
    return hay.includes(q);
  }

  function productHref(p) {
    if (p && p.slug) return "/products/" + p.slug + "/";
    if (p && p.url) return p.url;
    return "#";
  }

  function money(value) {
    const raw = String(value || "").trim();
    if (!raw) return "$0.00";
    // If already formatted, keep it simple
    const n = parseFloat(raw.replace(/[^0-9.]/g, ""));
    if (!Number.isFinite(n)) return "$0.00";
    return "$" + n.toFixed(2);
  }

  function renderCards(list) {
    if (!Array.isArray(list)) list = [];

    if (!list.length) {
      resultsEl.innerHTML = "";
      emptyEl.hidden = false;
      return;
    }

    emptyEl.hidden = true;

    resultsEl.innerHTML = list.map((p) => {
      const href = productHref(p);
      const title = escapeHtml(p.title || "Product");
      const img = p.image
        ? `<img class="product-card__img" src="${escapeHtml(p.image)}" alt="${title}" loading="lazy" />`
        : PLACEHOLDER;

      const badge = p.badge
        ? `<span class="pill product-card__badge">${escapeHtml(p.badge)}</span>`
        : "";

      const compareAt = p.compareAt ? `<span class="price-compare">${escapeHtml(money(p.compareAt))}</span>` : "";

      return `
        <article class="product-card">
          <a class="product-card__link" href="${escapeHtml(href)}">
            <div class="product-card__media" aria-label="${title}">
              ${badge}
              ${img}
            </div>
            <div class="product-card__body">
              <h3 class="product-card__title">${title}</h3>
              ${p.subtitle ? `<p class="product-card__meta muted">${escapeHtml(p.subtitle)}</p>` : ""}
              <div class="product-card__price" aria-label="Price">
                <span class="price-current">${escapeHtml(money(p.price))}</span>
                ${compareAt}
              </div>
            </div>
          </a>

          <div class="product-card__actions">
            <button
              class="btn btn-primary btn-sm"
              type="button"
              data-cart-add
              data-id="${escapeHtml(p.id || p.slug || "")}"
              data-slug="${escapeHtml(p.slug || "")}"
              data-title="${title}"
              data-price="${escapeHtml(p.price || "0")}"
              data-image="${escapeHtml(p.image || "")}"
              aria-label="Add ${title} to cart"
            >
              Add to cart
            </button>
            <a class="btn btn-ghost btn-sm" href="${escapeHtml(href)}">Details</a>
          </div>
        </article>
      `;
    }).join("");
  }

  let all = [];

  function setMeta(query, shown, total) {
    if (!query) {
      metaEl.textContent = `Showing ${shown} of ${total} products.`;
      return;
    }
    metaEl.textContent = `Found ${shown} match${shown === 1 ? "" : "es"} for “${query}”.`;
  }

  function applyFilter() {
    const qRaw = String(input.value || "");
    const q = normalizeText(qRaw);

    // Keep URL in sync (nice for sharing)
    setSearchParam(qRaw.trim());

    const filtered = all.filter((p) => matches(p, q));
    renderCards(filtered);
    setMeta(qRaw.trim(), filtered.length, all.length);
  }

  async function load() {
    loadingEl && (loadingEl.hidden = false);

    try {
      const res = await fetch("/products.json", { cache: "no-store" });
      const json = await res.json();
      all = Array.isArray(json) ? json : [];

      // Sort alphabetically for search results (stable)
      all = all.slice().sort((a, b) => normalizeText(a.title).localeCompare(normalizeText(b.title)));

      const initial = getSearchParam();
      if (initial) input.value = initial;

      if (shouldFocus()) {
        // Focus for quick keyboard search, then drop the hash so it doesn't persist
        window.setTimeout(() => {
          try { input.focus(); } catch (e) {}
          try {
            const url = new URL(window.location.href);
            url.hash = "";
            window.history.replaceState({}, "", url.toString());
          } catch (e) {}
        }, 0);
      }

      applyFilter();
    } catch (e) {
      all = [];
      resultsEl.innerHTML = "";
      emptyEl.hidden = false;
      metaEl.textContent = "Could not load products.";
    } finally {
      loadingEl && (loadingEl.hidden = true);
    }
  }

  // Events
  let t = null;
  input.addEventListener("input", () => {
    window.clearTimeout(t);
    t = window.setTimeout(applyFilter, 60);
  });

  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      input.value = "";
      input.focus();
      applyFilter();
    });
  }

  load();
})();

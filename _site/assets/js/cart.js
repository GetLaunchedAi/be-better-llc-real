/* =========================================
   Cart (localStorage) — Phase 5
   - No frameworks
   - Minimal, modular, resilient
   ========================================= */

(function () {
  const STORAGE_KEY = "cart:v1";

  function normalizeVariant(ds) {
    // Allow several ways to pass variant data from markup
    const explicit = (ds && (ds.variant || ds.option || ds.options)) || "";
    const size = (ds && (ds.size || ds.sizes)) || "";
    const color = (ds && ds.color) || "";
    const parts = [];
    if (explicit) return String(explicit).trim();
    if (size) parts.push(String(size).trim());
    if (color) parts.push(String(color).trim());
    return parts.filter(Boolean).join(" / ");
  }

  function itemKey(item) {
    const base = (item && (item.id || item.slug)) ? String(item.id || item.slug) : "";
    const variant = (item && item.variant) ? String(item.variant) : "";
    return variant ? `${base}::${variant}` : base;
  }

  function safeParse(json) {
    try { return JSON.parse(json); } catch (e) { return null; }
  }

  function readCart() {
    const raw = localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? safeParse(raw) : null;
    const cart = parsed && typeof parsed === "object" ? parsed : null;
    if (!cart || !Array.isArray(cart.items)) return { items: [] };

    // Back-compat: add `key` if missing
    cart.items = (cart.items || []).map((it) => {
      if (!it || typeof it !== "object") return it;
      if (!it.variant) it.variant = "";
      if (!it.key) it.key = itemKey(it);
      return it;
    });

    return cart;
  }

  function writeCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent("cart:updated", { detail: cart }));
  }

  function priceToCents(value) {
    const n = parseFloat(String(value || "").replace(/[^0-9.]/g, ""));
    if (!Number.isFinite(n)) return 0;
    return Math.round(n * 100);
  }

  function centsToMoney(cents) {
    const n = Number(cents || 0);
    return "$" + (n / 100).toFixed(2);
  }

  function getCount(cart) {
    return (cart.items || []).reduce((sum, item) => sum + (Number(item.qty) || 0), 0);
  }

  function setCountBadge(count) {
    const nodes = document.querySelectorAll("[data-cart-count]");
    nodes.forEach((el) => {
      el.textContent = String(count);
      // Optional: hide when empty, but keep layout stable
      el.toggleAttribute("data-empty", count === 0);
    });
  }

  function updateBadge() {
    setCountBadge(getCount(readCart()));
  }

  function normalizeFromDataset(ds) {
    // ds values are always strings
    const id = ds.id || ds.slug || "";
    const slug = ds.slug || "";
    const title = ds.title || "Item";
    const price = ds.price || "0";
    const image = ds.image || "";
    const variant = normalizeVariant(ds);

    return {
      id,
      slug,
      title,
      priceCents: priceToCents(price),
      image,
      variant
    };
  }

  function addItem(item, qty = 1) {
    const cart = readCart();
    const items = cart.items || [];

    const key = itemKey(item);
    if (!key) return;

    const existing = items.find((x) => (x && (x.key || itemKey(x))) === key);
    if (existing) {
      existing.qty = Math.min(99, (Number(existing.qty) || 0) + qty);
    } else {
      items.push({
        key,
        id: item.id || (item.slug || ""),
        slug: item.slug || "",
        title: item.title || "Item",
        image: item.image || "",
        priceCents: Number(item.priceCents) || 0,
        variant: item.variant || "",
        qty: Math.min(99, Math.max(1, qty))
      });
    }

    cart.items = items;
    writeCart(cart);
  }

  function removeItem(key) {
    const cart = readCart();
    cart.items = (cart.items || []).filter((x) => (x && (x.key || itemKey(x))) !== key);
    writeCart(cart);
  }

  function setQty(key, nextQty) {
    const cart = readCart();
    const items = cart.items || [];
    const item = items.find((x) => (x && (x.key || itemKey(x))) === key);
    if (!item) return;

    if (nextQty <= 0) {
      cart.items = items.filter((x) => (x && (x.key || itemKey(x))) !== key);
    } else {
      item.qty = Math.min(99, Math.max(1, nextQty));
      cart.items = items;
    }

    writeCart(cart);
  }

  function subtotalCents(cart) {
    return (cart.items || []).reduce((sum, item) => {
      const qty = Number(item.qty) || 0;
      const price = Number(item.priceCents) || 0;
      return sum + qty * price;
    }, 0);
  }

  // -----------------------------------------
  // Bind add-to-cart buttons
  // -----------------------------------------
  function bindAddButtons() {
    document.addEventListener("click", (e) => {
      const btn = e.target && e.target.closest ? e.target.closest("[data-cart-add]") : null;
      if (!btn) return;

      e.preventDefault();

      const item = normalizeFromDataset(btn.dataset);
      addItem(item, 1);

      // Small feedback (no toast framework)
      const original = btn.textContent;
      btn.textContent = "Added";
      btn.classList.add("is-added");
      window.setTimeout(() => {
        btn.textContent = original;
        btn.classList.remove("is-added");
      }, 900);
    });
  }

  // -----------------------------------------
  // Cart page renderer
  // -----------------------------------------
  function renderCartPage() {
    const page = document.querySelector("[data-cart-page]");
    if (!page) return;

    const emptyEl = page.querySelector("[data-cart-empty]");
    const wrapEl = page.querySelector("[data-cart-wrap]");
    const listEl = page.querySelector("[data-cart-items]");
    const subtotalEl = page.querySelector("[data-cart-subtotal]");

    if (!emptyEl || !wrapEl || !listEl || !subtotalEl) return;

    const cart = readCart();
    const items = cart.items || [];

    if (!items.length) {
      emptyEl.hidden = false;
      wrapEl.hidden = true;
      listEl.innerHTML = "";
      subtotalEl.textContent = centsToMoney(0);
      return;
    }

    emptyEl.hidden = true;
    wrapEl.hidden = false;

    // Build rows
    const rows = items.map((item) => {
      const key = item.key || itemKey(item);
      const href = item.slug ? ("/products/" + item.slug + "/") : "#";
      const img = item.image
        ? `<img class="cart-item__img" src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}" loading="lazy" />`
        : `<div class="cart-item__img cart-item__img--ph" aria-hidden="true"></div>`;

      const lineTotal = (Number(item.priceCents) || 0) * (Number(item.qty) || 0);

      return `
        <div class="cart-item" data-cart-key="${escapeAttr(key)}">
          <a class="cart-item__media" href="${escapeAttr(href)}" aria-label="${escapeAttr(item.title)}">
            ${img}
          </a>

          <div class="cart-item__body">
            <div class="cart-item__top">
              <div class="cart-item__titles">
                <a class="cart-item__title" href="${escapeAttr(href)}">${escapeHtml(item.title)}</a>
                ${item.variant ? `<p class="muted cart-item__variant">${escapeHtml(item.variant)}</p>` : ""}
                <p class="muted cart-item__price">${centsToMoney(item.priceCents)}</p>
              </div>

              <button class="btn btn-ghost btn-sm cart-item__remove" type="button" data-cart-remove aria-label="Remove ${escapeAttr(item.title)}">
                Remove
              </button>
            </div>

            <div class="cart-item__bottom">
              <div class="qty" aria-label="Quantity controls">
                <button class="qty__btn" type="button" data-cart-dec aria-label="Decrease quantity">−</button>
                <span class="qty__value" data-cart-qty>${Number(item.qty) || 1}</span>
                <button class="qty__btn" type="button" data-cart-inc aria-label="Increase quantity">+</button>
              </div>

              <p class="cart-item__total"><strong>${centsToMoney(lineTotal)}</strong></p>
            </div>
          </div>
        </div>
      `;
    }).join("");

    listEl.innerHTML = rows;
    subtotalEl.textContent = centsToMoney(subtotalCents(cart));
  }

  // -----------------------------------------
  // Cart page interactions
  // -----------------------------------------
  function bindCartPageControls() {
    document.addEventListener("click", (e) => {
      const page = document.querySelector("[data-cart-page]");
      if (!page) return;

      const row = e.target && e.target.closest ? e.target.closest("[data-cart-key]") : null;
      if (!row) return;

      const key = row.getAttribute("data-cart-key");
      if (!key) return;

      if (e.target.closest("[data-cart-remove]")) {
        removeItem(key);
        return;
      }

      if (e.target.closest("[data-cart-inc]")) {
        const cart = readCart();
        const item = (cart.items || []).find((x) => (x && (x.key || itemKey(x))) === key);
        setQty(key, (Number(item && item.qty) || 0) + 1);
        return;
      }

      if (e.target.closest("[data-cart-dec]")) {
        const cart = readCart();
        const item = (cart.items || []).find((x) => (x && (x.key || itemKey(x))) === key);
        setQty(key, (Number(item && item.qty) || 0) - 1);
        return;
      }
    });
  }

  // -----------------------------------------
  // Tiny escaping helpers (prevent HTML injection)
  // -----------------------------------------
  function escapeHtml(str) {
    return String(str || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function escapeAttr(str) {
    // same as HTML escape, good enough for attribute values
    return escapeHtml(str);
  }

  // -----------------------------------------
  // Init
  // -----------------------------------------
  function init() {
    updateBadge();
    bindAddButtons();
    bindCartPageControls();
    renderCartPage();

    // Re-render / re-badge on updates
    window.addEventListener("cart:updated", () => {
      updateBadge();
      renderCartPage();
    });

    // Sync across tabs
    window.addEventListener("storage", (e) => {
      if (e.key === STORAGE_KEY) {
        updateBadge();
        renderCartPage();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

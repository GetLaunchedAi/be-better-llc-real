/* =========================================
   Admin — Products editor
   - Uses PHP endpoints under /api/
   - Writes directly to /products.json in docroot
   - No frameworks
   ========================================= */

(function () {
  const root = document.querySelector('[data-admin-root]');
  if (!root) return;

  const loginView = root.querySelector('[data-admin-login]');
  const appView = root.querySelector('[data-admin-app]');
  const loginForm = root.querySelector('[data-login-form]');
  const loginErr = root.querySelector('[data-login-error]');

  const statusEl = root.querySelector('[data-admin-status]');
  const logoutBtn = root.querySelector('[data-admin-logout]');

  const listEl = root.querySelector('[data-admin-list]');
  const searchInput = root.querySelector('[data-admin-search]');
  const addBtn = root.querySelector('[data-admin-add]');
  const exportBtn = root.querySelector('[data-admin-export]');
  const importBtn = root.querySelector('[data-admin-import]');
  const importFile = root.querySelector('[data-admin-import-file]');
  const refreshBtn = root.querySelector('[data-admin-refresh]');

  const emptyHint = root.querySelector('[data-admin-empty]');
  const form = root.querySelector('[data-admin-form]');
  const formErr = root.querySelector('[data-admin-error]');
  const saveBtn = root.querySelector('[data-admin-save]');
  const delBtn = root.querySelector('[data-admin-delete]');

  const uploadBtn = root.querySelector('[data-admin-upload]');
  const uploadFile = root.querySelector('[data-admin-upload-file]');
  const uploadStatus = root.querySelector('[data-admin-upload-status]');

  let products = [];
  let activeId = null;

  function show(el) { if (el) el.hidden = false; }
  function hide(el) { if (el) el.hidden = true; }
  function setText(el, txt) { if (el) el.textContent = txt; }

  async function api(url, opts) {
    const res = await fetch(url, Object.assign({
      credentials: 'include',
      cache: 'no-store',
      headers: { 'Content-Type': 'application/json' }
    }, opts || {}));

    let data = null;
    try { data = await res.json(); } catch (e) {}
    if (!res.ok) {
      const msg = (data && (data.error || data.message)) || `Request failed (${res.status})`;
      const err = new Error(msg);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  }

  function normalizeCsv(v) {
    if (Array.isArray(v)) return v.filter(Boolean).map(String);
    if (!v) return [];
    return String(v).split(',').map(s => s.trim()).filter(Boolean);
  }

  function val(id) {
    const el = form.querySelector(`[data-f="${id}"]`);
    return el ? el.value : '';
  }

  function setVal(id, value) {
    const el = form.querySelector(`[data-f="${id}"]`);
    if (el) el.value = value == null ? '' : String(value);
  }

  function currentProduct() {
    return products.find(p => String(p.id) === String(activeId)) || null;
  }

  function renderList() {
    if (!listEl) return;
    const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    const rows = products
      .filter(p => {
        if (!q) return true;
        const hay = `${p.title || ''} ${p.slug || ''} ${(p.collections || []).join(' ')} ${(p.tags || []).join(' ')}`.toLowerCase();
        return hay.includes(q);
      })
      .map(p => {
        const isActive = String(p.id) === String(activeId);
        return `
          <div class="admin-item ${isActive ? 'is-active' : ''}" data-pick-id="${escapeAttr(p.id)}">
            <div class="admin-item__title">${escapeHtml(p.title || 'Untitled')}</div>
            <div class="admin-item__meta">/${escapeHtml(p.slug || '')} · $${escapeHtml(String(p.price || '0'))}</div>
          </div>
        `;
      }).join('');

    listEl.innerHTML = rows || `<p class="muted">No products found.</p>`;
  }

  function openEditor(id) {
    activeId = id;
    const p = currentProduct();
    if (!p) {
      hide(form);
      show(emptyHint);
      renderList();
      return;
    }

    hide(emptyHint);
    show(form);
    hide(formErr);

    setVal('id', p.id || '');
    setVal('slug', p.slug || '');
    setVal('title', p.title || '');
    setVal('subtitle', p.subtitle || '');
    setVal('price', p.price || '');
    setVal('compareAt', p.compareAt || '');
    setVal('badge', p.badge || '');
    setVal('badges', (p.badges || []).join(', '));
    setVal('collections', (p.collections || []).join(', '));
    setVal('tags', (p.tags || []).join(', '));
    setVal('image', p.image || '/assets/img/placeholder.jpg');

    renderList();
  }

  async function loadProducts() {
    setText(statusEl, 'Loading…');
    const res = await api('/api/products-get.php', { method: 'GET' });
    products = Array.isArray(res.products) ? res.products : [];
    setText(statusEl, `Loaded ${products.length}`);
    renderList();
    if (activeId) openEditor(activeId);
  }

  async function saveAll(nextProducts) {
    setText(statusEl, 'Saving…');
    const res = await api('/api/products-save.php', {
      method: 'POST',
      body: JSON.stringify({ products: nextProducts })
    });
    products = Array.isArray(res.products) ? res.products : nextProducts;
    setText(statusEl, 'Saved');
    window.setTimeout(() => setText(statusEl, `Loaded ${products.length}`), 800);
    renderList();
    if (activeId) openEditor(activeId);
  }

  function newProduct() {
    return {
      id: `p_${Date.now()}_${Math.random().toString(16).slice(2, 10)}`,
      slug: '',
      title: 'New product',
      subtitle: '',
      price: '0.00',
      compareAt: '',
      badge: '',
      badges: [],
      collections: [],
      tags: [],
      image: '/assets/img/placeholder.jpg'
    };
  }

  function downloadJson(filename, obj) {
    const blob = new Blob([JSON.stringify(obj, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  // --- auth ---
  async function checkAuth() {
    try {
      const res = await api('/api/me.php', { method: 'GET' });
      return !!(res && res.authed);
    } catch (e) {
      return false;
    }
  }

  async function showApp() {
    hide(loginView);
    show(appView);
    await loadProducts();
  }

  function showLogin() {
    show(loginView);
    hide(appView);
  }

  // --- events ---
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hide(loginErr);

      const pass = loginForm.querySelector('input[name="password"]');
      const password = pass ? pass.value : '';

      try {
        await api('/api/login.php', {
          method: 'POST',
          body: JSON.stringify({ password })
        });
        if (pass) pass.value = '';
        await showApp();
      } catch (err) {
        if (loginErr) {
          loginErr.style.display = 'block';
          loginErr.textContent = err.message || 'Login failed';
        }
      }
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      try { await api('/api/logout.php', { method: 'POST', body: '{}' }); } catch (e) {}
      products = [];
      activeId = null;
      showLogin();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => renderList());
  }

  if (listEl) {
    listEl.addEventListener('click', (e) => {
      const row = e.target && e.target.closest ? e.target.closest('[data-pick-id]') : null;
      if (!row) return;
      openEditor(row.getAttribute('data-pick-id'));
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', async () => {
      const p = newProduct();
      const next = [p, ...products];
      activeId = p.id;
      products = next;
      renderList();
      openEditor(p.id);
      setText(statusEl, 'New product added (not saved yet)');
    });
  }

  if (refreshBtn) {
    refreshBtn.addEventListener('click', async () => {
      try { await loadProducts(); } catch (e) { setText(statusEl, 'Failed to refresh'); }
    });
  }

  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      downloadJson('products.json', products);
    });
  }

  if (importBtn && importFile) {
    importBtn.addEventListener('click', () => importFile.click());
    importFile.addEventListener('change', async () => {
      const file = importFile.files && importFile.files[0];
      if (!file) return;
      try {
        const text = await file.text();
        const parsed = JSON.parse(text);
        const arr = Array.isArray(parsed) ? parsed : (Array.isArray(parsed.products) ? parsed.products : null);
        if (!arr) throw new Error('Invalid JSON format (expected an array)');
        await saveAll(arr);
        setText(statusEl, 'Imported');
      } catch (err) {
        setText(statusEl, err.message || 'Import failed');
      } finally {
        importFile.value = '';
      }
    });
  }

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      hide(formErr);

      const p = currentProduct();
      if (!p) return;

      // Update only the editable fields, preserve unknown keys
      p.id = val('id').trim() || p.id;
      p.slug = val('slug').trim();
      p.title = val('title').trim();
      p.subtitle = val('subtitle').trim();
      p.price = val('price').trim();
      p.compareAt = val('compareAt').trim();
      p.badge = val('badge').trim();
      p.badges = normalizeCsv(val('badges'));
      p.collections = normalizeCsv(val('collections'));
      p.tags = normalizeCsv(val('tags'));
      p.image = val('image').trim();

      try {
        await saveAll(products);
      } catch (err) {
        if (formErr) {
          formErr.style.display = 'block';
          const details = err.data && err.data.details ? `\n${err.data.details.join('\n')}` : '';
          formErr.textContent = (err.message || 'Save failed') + details;
        }
      }
    });
  }

  if (delBtn) {
    delBtn.addEventListener('click', async () => {
      const p = currentProduct();
      if (!p) return;
      const ok = window.confirm(`Delete "${p.title || 'this product'}"?`);
      if (!ok) return;

      const next = products.filter(x => String(x.id) !== String(activeId));
      activeId = null;
      try {
        await saveAll(next);
        hide(form);
        show(emptyHint);
      } catch (err) {
        if (formErr) {
          formErr.style.display = 'block';
          formErr.textContent = err.message || 'Delete failed';
        }
      }
    });
  }

  if (uploadBtn && uploadFile) {
    uploadBtn.addEventListener('click', () => uploadFile.click());
    uploadFile.addEventListener('change', async () => {
      const f = uploadFile.files && uploadFile.files[0];
      if (!f) return;
      setText(uploadStatus, 'Uploading…');

      try {
        const fd = new FormData();
        fd.append('file', f);
        const res = await fetch('/api/upload-image.php', {
          method: 'POST',
          credentials: 'include',
          body: fd
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data && data.error ? data.error : 'Upload failed');
        const url = data && data.url ? data.url : '';
        if (url) setVal('image', url);
        setText(uploadStatus, url ? 'Uploaded' : 'Uploaded (no URL)');
      } catch (err) {
        setText(uploadStatus, err.message || 'Upload failed');
      } finally {
        uploadFile.value = '';
        window.setTimeout(() => setText(uploadStatus, ''), 1400);
      }
    });
  }

  // --- init ---
  (async function init() {
    const authed = await checkAuth();
    if (authed) await showApp();
    else showLogin();
  })();

  // --- tiny escapers (avoid depending on other modules) ---
  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeAttr(str) {
    return escapeHtml(str).replace(/`/g, '&#096;');
  }
})();

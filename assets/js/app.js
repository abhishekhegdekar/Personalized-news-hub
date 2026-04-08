(() => {
  const qs = (sel, el = document) => el.querySelector(sel);
  const qsa = (sel, el = document) => Array.from(el.querySelectorAll(sel));

  // Dark mode
  const themeKey = 'newsflow_theme';
  const applyTheme = (t) => document.documentElement.setAttribute('data-theme', t);
  const savedTheme = localStorage.getItem(themeKey);
  applyTheme(savedTheme || 'light');

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');

    if (action === 'toggle-dark') {
      const cur = document.documentElement.getAttribute('data-theme') || 'dark';
      const next = cur === 'dark' ? 'light' : 'dark';
      localStorage.setItem(themeKey, next);
      applyTheme(next);
    }

    if (action === 'save-article') {
      e.preventDefault();
      const payload = {
        title: btn.getAttribute('data-title') || '',
        description: btn.getAttribute('data-description') || '',
        url: btn.getAttribute('data-url') || '',
        image_url: btn.getAttribute('data-image') || '',
        category: btn.getAttribute('data-category') || '',
      };
      toggleSave(btn, payload);
    }
  });

  async function toggleSave(btn, payload) {
    const isSaved = btn.getAttribute('data-saved') === '1';
    btn.disabled = true;
    try {
      const res = await fetch('/api/saved.php', {
        method: isSaved ? 'DELETE' : 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': (window.NEWSFLOW && window.NEWSFLOW.csrf) || '',
        },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Request failed');
      btn.setAttribute('data-saved', data.saved ? '1' : '0');
      btn.textContent = data.saved ? 'Saved' : 'Save';
    } catch (err) {
      alert(err.message || String(err));
    } finally {
      btn.disabled = false;
    }
  }

  // Page loaders
  const mounts = qsa('[data-news-mount]');
  mounts.forEach((mount) => {
    const mode = mount.getAttribute('data-mode');
    const category = mount.getAttribute('data-category') || '';
    const q = mount.getAttribute('data-q') || '';
    loadNews(mount, { mode, category, q });
  });

  async function loadNews(mount, { mode, category, q }) {
    if (mode === 'saved') return; // saved.php handles its own fetch
    mount.innerHTML = skeletonGrid();
    try {
      const url = new URL('/api/news.php', window.location.origin);
      url.searchParams.set('mode', mode);
      if (category) url.searchParams.set('category', category);
      if (q) url.searchParams.set('q', q);
      const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Failed to load');
      mount.innerHTML = renderCards(data.articles || []);
    } catch (err) {
      mount.innerHTML = `<div class="panel"><strong>Couldn’t load news.</strong><div style="margin-top:6px;color:var(--muted)">${escapeHtml(err.message || String(err))}</div></div>`;
    }
  }

  function skeletonGrid() {
    return `<div class="grid">
      ${Array.from({ length: 6 }).map(() => `<div class="skeleton card"></div>`).join('')}
    </div>`;
  }

  function renderCards(items) {
    if (!items.length) {
      return `<div class="panel">No articles found.</div>`;
    }
    const cards = items.map((a) => {
      const img = a.image_url ? `<img class="card__img" loading="lazy" src="${escapeAttr(a.image_url)}" alt="" />` : `<div class="card__img" aria-hidden="true"></div>`;
      const desc = (a.description || '').trim();
      const saved = a.is_saved ? '1' : '0';
      const saveText = saved === '1' ? 'Saved' : 'Save';
      return `
        <article class="card">
          ${img}
          <div class="card__body">
            <div class="card__title">${escapeHtml(a.title || '')}</div>
            <div class="card__desc">${escapeHtml(desc || 'No description available.')}</div>
            <div class="card__meta">
              <span>${escapeHtml(a.source || '')}</span>
              <span class="pill">${escapeHtml((a.category || '').toUpperCase())}</span>
            </div>
            <div class="card__actions">
              <a class="btn btn--primary btn--sm" href="${escapeAttr(a.url || '#')}" target="_blank" rel="noopener">Read More</a>
              <button class="btn btn--ghost btn--sm"
                data-action="save-article"
                data-saved="${saved}"
                data-title="${escapeAttr(a.title || '')}"
                data-description="${escapeAttr(a.description || '')}"
                data-url="${escapeAttr(a.url || '')}"
                data-image="${escapeAttr(a.image_url || '')}"
                data-category="${escapeAttr(a.category || '')}"
              >${saveText}</button>
            </div>
          </div>
        </article>
      `;
    }).join('');
    return `<div class="grid">${cards}</div>`;
  }

  // Saved page helper (reuses same card renderer)
  window.__renderSavedCards = (items) => renderCards(items || []);

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function escapeAttr(str) {
    return escapeHtml(str).replaceAll('`', '&#096;');
  }
})();


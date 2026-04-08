<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$uid = require_login();
layout_header('For You', 'for_you');
?>

<div class="section">
  <div class="wireframe-row">
    <div class="hero" id="heroCard">
      <div class="skeleton" style="min-height:260px;"></div>
    </div>
    <div class="widget">
      <div class="widget__title">📈 Trending Now</div>
      <div id="trendingList" class="tlist">
        <div class="skeleton" style="min-height: 220px;"></div>
      </div>
    </div>
  </div>
</div>

<div class="section">
  <div class="section__title">Recommended For You</div>
  <div data-news-mount data-mode="for_you"></div>
</div>

<div class="section">
  <div class="section__title">Trending in Technology</div>
  <div data-news-mount data-mode="category" data-category="technology"></div>
</div>

<script>
  (async () => {
    const hero = document.getElementById('heroCard');
    const list = document.getElementById('trendingList');
    try {
      const res = await fetch('/api/news.php?mode=trending', { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Failed');
      const items = (data.articles || []).slice(0, 10);
      const first = items[0];
      if (first) {
        const img = first.image_url ? `<img class="hero__img" loading="lazy" src="${escapeAttr(first.image_url)}" alt="" />` : `<div class="hero__img" aria-hidden="true"></div>`;
        hero.innerHTML = `
          ${img}
          <div class="hero__body">
            <div class="hero__source">${escapeHtml(first.source || 'Source')}</div>
            <div class="hero__title">${escapeHtml(first.title || '')}</div>
            <div class="hero__desc">${escapeHtml((first.description || '').slice(0, 180) || 'Click to read the full story.')}</div>
            <div class="hero__cta">
              <a class="btn btn--primary btn--sm" href="${escapeAttr(first.url || '#')}" target="_blank" rel="noopener">Read Full Story</a>
              <button class="btn btn--ghost btn--sm"
                data-action="save-article"
                data-saved="${first.is_saved ? '1' : '0'}"
                data-title="${escapeAttr(first.title || '')}"
                data-description="${escapeAttr(first.description || '')}"
                data-url="${escapeAttr(first.url || '')}"
                data-image="${escapeAttr(first.image_url || '')}"
                data-category="${escapeAttr(first.category || 'technology')}"
              >${first.is_saved ? 'Saved' : 'Save'}</button>
            </div>
          </div>
        `;
      }

      list.innerHTML = items.slice(1, 6).map((a, i) => `
        <div class="titem">
          <div class="tidx">${i + 1}</div>
          <div>
            <div class="ttext">${escapeHtml(a.title || '')}</div>
            <div class="tmeta">${escapeHtml(a.category || 'news')}</div>
          </div>
        </div>
      `).join('');
    } catch (e) {
      hero.innerHTML = `<div class="panel">Couldn’t load hero story.</div>`;
      list.innerHTML = `<div class="panel">Couldn’t load trending list.</div>`;
    }
  })();

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
</script>

<?php
layout_footer();


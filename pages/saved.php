<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_login();
layout_header('Saved Articles', 'saved');
?>

<div class="section">
  <div class="section__title">Saved Articles</div>
  <div style="color:var(--muted); margin-bottom: 10px;">Articles you bookmarked for later.</div>
  <div id="savedMount" class="panel">Loading…</div>
</div>

<script>
  (async () => {
    const mount = document.getElementById('savedMount');
    try {
      const res = await fetch('/api/saved.php', { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Failed');
      mount.outerHTML = `<div id="savedGrid"></div>`;
      const el = document.getElementById('savedGrid');
      el.innerHTML = (window.__renderSavedCards ? window.__renderSavedCards(data.articles || []) : '<div class="panel">Loaded.</div>');
    } catch (e) {
      mount.className = 'msg is-error';
      mount.textContent = e.message || String(e);
    }
  })();
</script>

<?php
layout_footer();


<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../config/db.php';

$uid = require_login();

$welcome = isset($_GET['welcome']);
$saved = isset($_GET['saved']);

// Load current preferences
$stmt = db()->prepare('SELECT category, language, region FROM user_preferences WHERE user_id = ?');
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();
$selected = [];
$language = 'en';
$region = 'us';
foreach ($rows as $r) {
  $selected[] = normalize_category((string)$r['category']);
  $language = (string)$r['language'];
  $region = (string)$r['region'];
}
$selected = array_values(array_unique($selected));

$categories = ['technology' => 'Technology', 'sports' => 'Sports', 'business' => 'Business', 'entertainment' => 'Entertainment', 'health' => 'Health'];

layout_header('Settings', 'settings');
?>

<?php if ($welcome): ?>
  <div class="msg is-ok">Welcome! Choose your interests to personalize your feed.</div>
<?php elseif ($saved): ?>
  <div class="msg is-ok">Preferences saved.</div>
<?php endif; ?>

<div class="panel">
  <div class="section__title">Manage Interests</div>
  <form method="post" action="/api/preferences.php">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

    <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 10px;">
      <?php foreach ($categories as $key => $label): ?>
        <label class="pill" style="justify-content:flex-start;">
          <input type="checkbox" name="categories[]" value="<?= h($key) ?>" <?= in_array($key, $selected, true) ? 'checked' : '' ?> />
          <?= h($label) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <div class="section__title" style="margin-top: 16px;">Language &amp; Region</div>
    <div class="grid" style="grid-template-columns: repeat(12, 1fr);">
      <div class="field" style="grid-column: span 6;">
        <label>Language</label>
        <select name="language">
          <?php foreach (['en' => 'English', 'hi' => 'Hindi', 'es' => 'Spanish', 'fr' => 'French'] as $k => $v): ?>
            <option value="<?= h($k) ?>" <?= $language === $k ? 'selected' : '' ?>><?= h($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="grid-column: span 6;">
        <label>Region</label>
        <select name="region">
          <?php foreach (['us' => 'United States', 'in' => 'India', 'gb' => 'United Kingdom', 'au' => 'Australia'] as $k => $v): ?>
            <option value="<?= h($k) ?>" <?= $region === $k ? 'selected' : '' ?>><?= h($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-top: 10px;">
      <button class="btn btn--primary" type="submit">Save Settings</button>
    </div>
  </form>
</div>

<div class="section">
  <div class="section__title">Analytics</div>
  <div class="panel" id="analyticsPanel">Loading…</div>
</div>

<script>
  (async () => {
    const el = document.getElementById('analyticsPanel');
    try {
      const res = await fetch('/api/search.php?mode=analytics', { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Failed');
      el.innerHTML = `<div class="pill">Most viewed category: <strong style="margin-left:6px">${(data.most_viewed_category || 'N/A').toUpperCase()}</strong></div>`;
    } catch(e) {
      el.textContent = e.message || String(e);
    }
  })();
</script>

<?php
layout_footer();


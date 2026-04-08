<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$uid = require_login();
$q = trim((string)($_GET['q'] ?? ''));

// record search history (optional table)
if ($q !== '') {
  // best-effort, non-blocking-ish
  @file_get_contents('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/api/search.php?q=' . urlencode($q));
}

layout_header('Search', 'for_you');
?>

<div class="section">
  <div class="section__title">Search results<?= $q ? ' for “' . h($q) . '”' : '' ?></div>
  <?php if (!$q): ?>
    <div class="panel">Type a query in the search box above.</div>
  <?php else: ?>
    <div data-news-mount data-mode="search" data-q="<?= h($q) ?>"></div>
  <?php endif; ?>
</div>

<?php
layout_footer();


<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_login();
layout_header('View Wireframe', 'wireframe');

$dir = __DIR__ . '/../assets/wireframe';
@mkdir($dir, 0775, true);

$files = glob($dir . '/*');
usort($files ?: [], fn($a, $b) => filemtime($b) <=> filemtime($a));
$file = $files[0] ?? null;

$rel = $file ? str_replace(realpath(__DIR__ . '/..'), '', realpath($file)) : null;
$ext = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : '';
?>

<div class="panel">
  <div class="section__title">Wireframe</div>
  <div style="color:var(--muted); margin-bottom: 10px;">
    Put your uploaded wireframe (PDF/image) into <code>assets/wireframe/</code>. The latest file will show here.
  </div>

  <?php if (!$file || !$rel): ?>
    <div class="msg">No wireframe found yet.</div>
  <?php else: ?>
    <div style="display:flex; gap:10px; align-items:center; margin-bottom: 10px;">
      <a class="btn btn--primary btn--sm" href="<?= h($rel) ?>" download>Download Wireframe</a>
      <span class="pill"><?= h(basename($file)) ?></span>
    </div>

    <?php if ($ext === 'pdf'): ?>
      <iframe title="Wireframe PDF" src="<?= h($rel) ?>" style="width:100%; height: 72vh; border:1px solid var(--border); border-radius: 12px; background: var(--panel);"></iframe>
    <?php else: ?>
      <img src="<?= h($rel) ?>" alt="Wireframe" style="max-width:100%; border-radius: 12px; border:1px solid var(--border);" />
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php
layout_footer();


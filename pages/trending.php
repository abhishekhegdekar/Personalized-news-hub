<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_login();
layout_header('Trending News · Top 10 Right Now', 'trending');
?>

<div class="section">
  <div class="section__title">Top 10 Right Now</div>
  <div data-news-mount data-mode="trending"></div>
</div>

<?php
layout_footer();


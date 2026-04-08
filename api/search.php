<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// - mode=analytics => return most viewed category (bonus)
// - otherwise used by pages/search.php as normal page; API-based search is handled by api/news.php?mode=search

require_method('GET');
start_session();
$uid = current_user_id();

$mode = (string)($_GET['mode'] ?? '');
if ($mode === 'analytics') {
  // global most viewed category in last 7 days (simple + meaningful)
  $stmt = db()->prepare('
    SELECT category, COUNT(*) AS cnt
    FROM category_views
    WHERE viewed_at >= (NOW() - INTERVAL 7 DAY)
    GROUP BY category
    ORDER BY cnt DESC
    LIMIT 1
  ');
  $stmt->execute();
  $row = $stmt->fetch();
  json_response(['ok' => true, 'most_viewed_category' => $row ? (string)$row['category'] : null]);
}

// store search history (optional feature)
if ($uid) {
  $q = trim((string)($_GET['q'] ?? ''));
  if ($q !== '') {
    $stmt = db()->prepare('INSERT INTO search_history (user_id, query) VALUES (?, ?)');
    $stmt->execute([$uid, $q]);
  }
}

json_response(['ok' => true]);


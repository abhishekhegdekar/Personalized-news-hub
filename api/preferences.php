<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

start_session();
$uid = current_user_id();
if (!$uid) {
  json_response(['ok' => false, 'error' => 'Login required'], 401);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
  $stmt = db()->prepare('SELECT category, language, region FROM user_preferences WHERE user_id = ?');
  $stmt->execute([$uid]);
  $rows = $stmt->fetchAll();
  $prefs = [
    'language' => 'en',
    'region' => 'us',
    'categories' => [],
  ];
  foreach ($rows as $r) {
    $prefs['categories'][] = normalize_category((string)$r['category']);
    $prefs['language'] = (string)$r['language'];
    $prefs['region'] = (string)$r['region'];
  }
  $prefs['categories'] = array_values(array_unique($prefs['categories']));
  json_response(['ok' => true, 'preferences' => $prefs]);
}

if ($method === 'POST') {
  csrf_verify();
  $categories = $_POST['categories'] ?? [];
  if (!is_array($categories)) $categories = [];
  $categories = array_values(array_unique(array_map('normalize_category', array_map('strval', $categories))));

  $language = trim((string)($_POST['language'] ?? 'en'));
  $region = trim((string)($_POST['region'] ?? 'us'));
  if ($language === '') $language = 'en';
  if ($region === '') $region = 'us';

  // Replace preferences atomically
  $pdo = db();
  $pdo->beginTransaction();
  try {
    $del = $pdo->prepare('DELETE FROM user_preferences WHERE user_id = ?');
    $del->execute([$uid]);

    $ins = $pdo->prepare('INSERT INTO user_preferences (user_id, category, language, region) VALUES (?, ?, ?, ?)');
    foreach ($categories as $cat) {
      $ins->execute([$uid, $cat, $language, $region]);
    }

    // If user unchecks all categories, keep language/region persisted with a single row.
    if (!$categories) {
      $ins->execute([$uid, 'technology', $language, $region]);
      $del2 = $pdo->prepare('DELETE FROM user_preferences WHERE user_id = ? AND category <> ?');
      $del2->execute([$uid, 'technology']);
    }

    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    json_response(['ok' => false, 'error' => 'Failed to save preferences'], 500);
  }

  redirect('/pages/settings.php?saved=1');
}

json_response(['ok' => false, 'error' => 'Method not allowed'], 405);


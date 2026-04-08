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
  $stmt = db()->prepare('SELECT id, title, description, url, image_url, category, saved_at FROM saved_articles WHERE user_id = ? ORDER BY saved_at DESC');
  $stmt->execute([$uid]);
  $rows = $stmt->fetchAll();
  $articles = [];
  foreach ($rows as $r) {
    $articles[] = [
      'title' => (string)$r['title'],
      'description' => (string)($r['description'] ?? ''),
      'url' => (string)$r['url'],
      'image_url' => (string)($r['image_url'] ?? ''),
      'category' => (string)$r['category'],
      'saved_at' => (string)$r['saved_at'],
      'is_saved' => true,
    ];
  }
  json_response(['ok' => true, 'articles' => $articles]);
}

// Writes require CSRF
csrf_verify();

$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);
if (!is_array($payload)) {
  json_response(['ok' => false, 'error' => 'Invalid JSON payload'], 400);
}

$title = trim((string)($payload['title'] ?? ''));
$description = trim((string)($payload['description'] ?? ''));
$url = trim((string)($payload['url'] ?? ''));
$image = trim((string)($payload['image_url'] ?? ''));
$category = normalize_category((string)($payload['category'] ?? 'technology'));

if ($url === '' || $title === '') {
  json_response(['ok' => false, 'error' => 'Missing url/title'], 400);
}

if ($method === 'POST') {
  // Deduplicate by URL per user (prefix index helps)
  $stmt = db()->prepare('SELECT id FROM saved_articles WHERE user_id = ? AND url = ? LIMIT 1');
  $stmt->execute([$uid, $url]);
  $existing = $stmt->fetchColumn();
  if ($existing) {
    json_response(['ok' => true, 'saved' => true]);
  }

  $stmt = db()->prepare('INSERT INTO saved_articles (user_id, title, description, url, image_url, category) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt->execute([$uid, $title, $description, $url, $image, $category]);
  json_response(['ok' => true, 'saved' => true]);
}

if ($method === 'DELETE') {
  $stmt = db()->prepare('DELETE FROM saved_articles WHERE user_id = ? AND url = ?');
  $stmt->execute([$uid, $url]);
  json_response(['ok' => true, 'saved' => false]);
}

json_response(['ok' => false, 'error' => 'Method not allowed'], 405);


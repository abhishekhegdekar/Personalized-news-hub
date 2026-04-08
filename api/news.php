<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/news_providers.php';
require_once __DIR__ . '/../config/db.php';

// GET /api/news.php?mode=for_you|trending|category|search&category=technology&q=...
require_method('GET');

$mode = (string)($_GET['mode'] ?? 'for_you');
$modeAllowed = ['for_you', 'trending', 'category', 'search'];
if (!in_array($mode, $modeAllowed, true)) {
  json_response(['ok' => false, 'error' => 'Invalid mode'], 400);
}

$uid = current_user_id();
$category = normalize_category((string)($_GET['category'] ?? 'technology'));
$q = trim((string)($_GET['q'] ?? ''));

// Preferences (language/region + interests)
$language = 'en';
$region = 'us';
$interests = [];
if ($uid) {
  $stmt = db()->prepare('SELECT category, language, region FROM user_preferences WHERE user_id = ?');
  $stmt->execute([$uid]);
  $rows = $stmt->fetchAll();
  foreach ($rows as $r) {
    $interests[] = normalize_category((string)$r['category']);
    $language = (string)$r['language'];
    $region = (string)$r['region'];
  }
  $interests = array_values(array_unique($interests));
}

// Personalization logic
// - If For You: use selected interests (or a default)
// - Also boost categories based on saved article keywords (basic)
if ($mode === 'for_you') {
  $targetCats = $interests ?: ['technology', 'business'];
  $category = $targetCats[array_rand($targetCats)];

  if ($uid) {
    $kw = get_saved_keyword_hints($uid);
    if ($kw['suggested_category']) {
      $category = $kw['suggested_category'];
    }
  }
}

if ($mode === 'search' && $q === '') {
  json_response(['ok' => false, 'error' => 'Missing search query'], 400);
}

$cacheKey = 'news_' . $mode . '_' . $category . '_' . $language . '_' . $region . '_' . md5($q);
if ($cached = cache_get($cacheKey)) {
  $cached = annotate_saved($uid, $cached);
  json_response(['ok' => true, 'cached' => true, 'articles' => $cached]);
}

$params = [
  'category' => $category,
  'language' => $language,
  'region' => $region,
  'q' => $q,
];

// Provider fallback: try provider1, then provider2.
$articles = [];
$providerError = null;
try {
  $articles = provider1_fetch(map_mode($mode), $params);
} catch (Throwable $e) {
  $providerError = $e->getMessage();
  try {
    $articles = provider2_fetch(map_mode($mode), $params);
  } catch (Throwable $e2) {
    json_response([
      'ok' => false,
      'error' => 'Both news providers failed',
      'details' => [
        'provider1' => $providerError,
        'provider2' => $e2->getMessage(),
      ],
    ], 502);
  }
}

// Analytics: track category views for category/for_you pages
if (in_array($mode, ['for_you', 'category'], true)) {
  track_category_view($uid, $category);
}

cache_set($cacheKey, $articles);
$articles = annotate_saved($uid, $articles);

json_response(['ok' => true, 'cached' => false, 'articles' => $articles]);

function map_mode(string $mode): string
{
  if ($mode === 'for_you') return 'for_you';
  if ($mode === 'trending') return 'trending';
  if ($mode === 'category') return 'category';
  return 'search';
}

function annotate_saved(?int $uid, array $articles): array
{
  if (!$uid || !$articles) return $articles;
  $urls = [];
  foreach ($articles as $a) {
    if (!empty($a['url'])) $urls[] = (string)$a['url'];
  }
  if (!$urls) return $articles;

  // Build IN list safely
  $placeholders = implode(',', array_fill(0, count($urls), '?'));
  $sql = "SELECT url FROM saved_articles WHERE user_id = ? AND url IN ($placeholders)";
  $stmt = db()->prepare($sql);
  $stmt->execute(array_merge([$uid], $urls));
  $saved = $stmt->fetchAll(PDO::FETCH_COLUMN);
  $savedSet = [];
  foreach ($saved as $u) $savedSet[(string)$u] = true;

  foreach ($articles as &$a) {
    $a['is_saved'] = isset($savedSet[(string)($a['url'] ?? '')]);
  }
  unset($a);
  return $articles;
}

function get_saved_keyword_hints(int $uid): array
{
  $stmt = db()->prepare('SELECT title, description, category FROM saved_articles WHERE user_id = ? ORDER BY saved_at DESC LIMIT 30');
  $stmt->execute([$uid]);
  $rows = $stmt->fetchAll();
  $text = '';
  $catCount = [];
  foreach ($rows as $r) {
    $text .= ' ' . (string)$r['title'] . ' ' . (string)$r['description'];
    $c = normalize_category((string)$r['category']);
    $catCount[$c] = ($catCount[$c] ?? 0) + 1;
  }
  arsort($catCount);
  $topCat = $catCount ? array_key_first($catCount) : null;

  // basic keyword map
  $t = strtolower($text);
  $kwMap = [
    'technology' => ['ai','software','startup','chip','google','apple','microsoft','cyber','cloud','programming'],
    'business' => ['market','stocks','earnings','finance','ipo','investment','economy','inflation','bank'],
    'sports' => ['match','tournament','league','goal','cricket','football','nba','fifa','olympics'],
    'entertainment' => ['movie','film','music','celebrity','series','netflix','box office','show'],
    'health' => ['health','medicine','covid','fitness','diet','wellness','hospital','vaccine'],
  ];

  $score = [];
  foreach ($kwMap as $cat => $words) {
    $score[$cat] = 0;
    foreach ($words as $w) {
      if (str_contains($t, $w)) $score[$cat] += 1;
    }
  }
  arsort($score);
  $kwCat = $score ? array_key_first($score) : null;

  return ['suggested_category' => $kwCat ?: $topCat];
}

function track_category_view(?int $uid, string $category): void
{
  $stmt = db()->prepare('INSERT INTO category_views (user_id, category) VALUES (?, ?)');
  $stmt->execute([$uid, $category]);
}


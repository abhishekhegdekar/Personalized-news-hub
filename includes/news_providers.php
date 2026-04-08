<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Provider 1 (placeholder): NewsAPI.org-style endpoints.
 * Replace endpoints/params as needed for your chosen provider.
 */
function provider1_fetch(string $mode, array $params): array
{
  // Example: https://newsapi.org/docs/endpoints/top-headlines
  $base = 'https://newsapi.org/v2/';
  $endpoint = ($mode === 'search') ? 'everything' : 'top-headlines';

  $query = [];
  if ($mode === 'trending') {
    $query['pageSize'] = 10;
    $query['country'] = $params['region'] ?? 'us';
    $query['language'] = $params['language'] ?? 'en';
  } elseif ($mode === 'category') {
    $query['pageSize'] = 10;
    $query['country'] = $params['region'] ?? 'us';
    $query['category'] = $params['category'] ?? 'technology';
  } elseif ($mode === 'search') {
    $query['pageSize'] = 10;
    $query['q'] = $params['q'] ?? '';
    $query['language'] = $params['language'] ?? 'en';
    $query['sortBy'] = 'publishedAt';
  } else { // for_you
    $query['pageSize'] = 15;
    $query['country'] = $params['region'] ?? 'us';
    $query['category'] = $params['category'] ?? 'technology';
  }

  $url = $base . $endpoint . '?' . http_build_query($query);
  $resp = http_get_json($url, [
    'X-Api-Key: ' . API_KEY_1,
  ]);

  if (!isset($resp['articles']) || !is_array($resp['articles'])) {
    throw new RuntimeException('Provider1 invalid response');
  }

  return normalize_articles($resp['articles'], $params['category'] ?? null);
}

/**
 * Provider 2: World News API (worldnewsapi.com)
 */
function provider2_fetch(string $mode, array $params): array
{
  $base = 'https://api.worldnewsapi.com/';
  $language = $params['language'] ?? 'en';
  $region = $params['region'] ?? 'us';
  $category = normalize_category((string)($params['category'] ?? 'technology'));

  // Trending: use Top News clusters (flatten first item of each cluster)
  if ($mode === 'trending') {
    $query = [
      'api-key' => API_KEY_2,
      'source-country' => $region,
      'language' => $language,
      // NOTE: worldnewsapi's Top News is date-based; omit for "today"
    ];
    $url = $base . 'top-news?' . http_build_query($query);
    $resp = http_get_json($url);
    if (!isset($resp['top_news']) || !is_array($resp['top_news'])) {
      throw new RuntimeException('Provider2 invalid response');
    }
    $mapped = [];
    foreach ($resp['top_news'] as $cluster) {
      $news = $cluster['news'][0] ?? null;
      if (!is_array($news)) continue;
      $mapped[] = [
        'title' => $news['title'] ?? '',
        'description' => $news['summary'] ?? ($news['text'] ?? ''),
        'url' => $news['url'] ?? '',
        'urlToImage' => $news['image'] ?? '',
        'publishedAt' => $news['publish_date'] ?? '',
        'source' => ['name' => parse_host_as_source((string)($news['url'] ?? ''))],
      ];
      if (count($mapped) >= 10) break;
    }
    return normalize_articles($mapped, null);
  }

  // Category / For You / Search: use Search News
  $text = '';
  if ($mode === 'search') {
    $text = (string)($params['q'] ?? '');
  } else {
    $text = $category;
  }

  $query = [
    'api-key' => API_KEY_2,
    'text' => $text,
    'language' => $language,
    // doc uses "source-countries"
    'source-countries' => $region,
    'number' => ($mode === 'for_you') ? 15 : 10,
    'sort' => 'publish-time',
  ];
  $url = $base . 'search-news?' . http_build_query($query);
  $resp = http_get_json($url);
  if (!isset($resp['news']) || !is_array($resp['news'])) {
    throw new RuntimeException('Provider2 invalid response');
  }

  $mapped = [];
  foreach ($resp['news'] as $n) {
    $mapped[] = [
      'title' => $n['title'] ?? '',
      'description' => $n['summary'] ?? ($n['text'] ?? ''),
      'url' => $n['url'] ?? '',
      'urlToImage' => $n['image'] ?? '',
      'publishedAt' => $n['publish_date'] ?? '',
      'source' => ['name' => parse_host_as_source((string)($n['url'] ?? ''))],
    ];
  }

  return normalize_articles($mapped, $category);
}

/**
 * HTTP helper returning decoded JSON array.
 */
function http_get_json(string $url, array $headers = []): array
{
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_HTTPHEADER => array_merge([
      'Accept: application/json',
      'User-Agent: NewsFlow/1.0',
    ], $headers),
  ]);
  $raw = curl_exec($ch);
  $err = curl_error($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  // PHP 8.5+ may deprecate curl_close() in some builds (no-op since 8.0).

  if ($raw === false) {
    throw new RuntimeException('HTTP error: ' . ($err ?: 'unknown'));
  }
  if ($code < 200 || $code >= 300) {
    throw new RuntimeException('HTTP status ' . $code);
  }

  $decoded = json_decode($raw, true);
  if (!is_array($decoded)) {
    throw new RuntimeException('Invalid JSON');
  }
  return $decoded;
}

/**
 * Normalize provider articles into NewsFlow card schema.
 */
function normalize_articles(array $articles, ?string $category = null): array
{
  $out = [];
  foreach ($articles as $a) {
    $title = (string)($a['title'] ?? '');
    $url = (string)($a['url'] ?? '');
    if ($title === '' || $url === '') {
      continue;
    }
    $out[] = [
      'title' => $title,
      'description' => (string)($a['description'] ?? ''),
      'url' => $url,
      'image_url' => (string)($a['urlToImage'] ?? ''),
      'source' => (string)($a['source']['name'] ?? ''),
      'published_at' => (string)($a['publishedAt'] ?? ''),
      'category' => $category ? normalize_category($category) : '',
    ];
  }
  return $out;
}

function parse_host_as_source(string $url): string
{
  $host = (string)parse_url($url, PHP_URL_HOST);
  $host = preg_replace('/^www\./', '', $host);
  return $host ?: 'Source';
}


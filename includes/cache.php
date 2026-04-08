<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function cache_get(string $key): ?array
{
  $file = cache_file_path($key);
  if (!is_file($file)) {
    return null;
  }

  $raw = file_get_contents($file);
  if ($raw === false) {
    return null;
  }

  $decoded = json_decode($raw, true);
  if (!is_array($decoded) || !isset($decoded['expires_at'], $decoded['data'])) {
    return null;
  }

  if (time() > (int)$decoded['expires_at']) {
    @unlink($file);
    return null;
  }

  return is_array($decoded['data']) ? $decoded['data'] : null;
}

function cache_set(string $key, array $data, int $ttlSeconds = CACHE_TTL_SECONDS): void
{
  if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0775, true);
  }

  $payload = [
    'expires_at' => time() + max(1, $ttlSeconds),
    'data' => $data,
  ];

  $file = cache_file_path($key);
  @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function cache_file_path(string $key): string
{
  $safe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
  $safe = $safe ?: 'cache';
  return rtrim(CACHE_DIR, '/') . '/' . $safe . '.json';
}


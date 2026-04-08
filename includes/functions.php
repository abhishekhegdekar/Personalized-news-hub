<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function start_session(): void
{
  if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_COOKIE_NAME);
    session_start();
  }
}

function h(?string $s): string
{
  return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_response(array $payload, int $status = 200): void
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
}

function require_method(string $method): void
{
  if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
  }
}

function redirect(string $path): void
{
  $url = APP_BASE_URL ? rtrim(APP_BASE_URL, '/') . '/' . ltrim($path, '/') : $path;
  header('Location: ' . $url);
  exit;
}

function normalize_category(string $cat): string
{
  $cat = strtolower(trim($cat));
  $allowed = ['technology', 'sports', 'business', 'entertainment', 'health'];
  return in_array($cat, $allowed, true) ? $cat : 'technology';
}


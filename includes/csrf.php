<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function csrf_token(): string
{
  start_session();
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION['csrf_token'];
}

function csrf_verify(): void
{
  start_session();
  $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
  $sent = is_string($sent) ? $sent : '';
  $ok = isset($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $sent);
  if (!$ok) {
    json_response(['ok' => false, 'error' => 'Invalid CSRF token'], 403);
  }
}


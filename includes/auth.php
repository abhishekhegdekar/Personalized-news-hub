<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

function current_user_id(): ?int
{
  start_session();
  $id = $_SESSION['user_id'] ?? null;
  return is_int($id) ? $id : null;
}

function require_login(): int
{
  $uid = current_user_id();
  if (!$uid) {
    redirect('/pages/login.php');
  }
  return $uid;
}

function login_user(int $userId): void
{
  start_session();
  $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
  start_session();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}


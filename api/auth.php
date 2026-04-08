<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

// Minimal JSON auth endpoint (optional helper for AJAX integrations).
// POST /api/auth.php?action=login|register|logout

$action = (string)($_GET['action'] ?? '');
$allowed = ['login', 'register', 'logout'];
if (!in_array($action, $allowed, true)) {
  json_response(['ok' => false, 'error' => 'Invalid action'], 400);
}

if ($action === 'logout') {
  logout_user();
  json_response(['ok' => true]);
}

require_method('POST');
csrf_verify();

if ($action === 'login') {
  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  $stmt = db()->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if (!$user || !password_verify($password, (string)$user['password'])) {
    json_response(['ok' => false, 'error' => 'Invalid email or password'], 401);
  }
  login_user((int)$user['id']);
  json_response(['ok' => true]);
}

// register
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
  json_response(['ok' => false, 'error' => 'All fields are required'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_response(['ok' => false, 'error' => 'Invalid email'], 400);
}
if (strlen($password) < 6) {
  json_response(['ok' => false, 'error' => 'Password too short'], 400);
}

$stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetchColumn()) {
  json_response(['ok' => false, 'error' => 'Email already registered'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$ins->execute([$name, $email, $hash]);
$uid = (int)db()->lastInsertId();

// Default preferences
$pref = db()->prepare('INSERT INTO user_preferences (user_id, category, language, region) VALUES (?, ?, ?, ?)');
$pref->execute([$uid, 'technology', 'en', 'us']);

login_user($uid);
json_response(['ok' => true]);


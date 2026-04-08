<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Returns a shared PDO instance (MySQL).
 */
function db(): PDO
{
  static $pdo = null;
  if ($pdo instanceof PDO) {
    return $pdo;
  }

  // Allow overrides without editing tracked files (useful for XAMPP/WAMP).
  $host = (string)(getenv('NEWSFLOW_DB_HOST') ?: DB_HOST);
  $port = (string)(getenv('NEWSFLOW_DB_PORT') ?: DB_PORT);
  $name = (string)(getenv('NEWSFLOW_DB_NAME') ?: DB_NAME);
  $user = (string)(getenv('NEWSFLOW_DB_USER') ?: DB_USER);
  $pass = (string)(getenv('NEWSFLOW_DB_PASS') ?: DB_PASS);

  $dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $name
  );

  try {
    $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  } catch (PDOException $e) {
    // Friendly setup hint instead of a fatal stack trace.
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<!doctype html><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'>";
    echo "<title>Database connection failed</title>";
    echo "<body style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; padding:24px; background:#f6f7fb; color:#111'>";
    echo "<div style='max-width:900px;margin:0 auto;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:18px;'>";
    echo "<h2 style='margin:0 0 10px'>Database connection failed</h2>";
    echo "<div style='color:rgba(0,0,0,.7);margin-bottom:10px'>Error: <code>{$msg}</code></div>";
    echo "<h3 style='margin:14px 0 8px'>Fix</h3>";
    echo "<ol style='line-height:1.6;color:rgba(0,0,0,.75)'>";
    echo "<li>Update MySQL credentials in <code>config/config.php</code> (DB_USER/DB_PASS), or</li>";
    echo "<li>Set environment variables: <code>NEWSFLOW_DB_HOST</code>, <code>NEWSFLOW_DB_PORT</code>, <code>NEWSFLOW_DB_NAME</code>, <code>NEWSFLOW_DB_USER</code>, <code>NEWSFLOW_DB_PASS</code>.</li>";
    echo "<li>Ensure the database/tables exist by importing <code>database/schema.sql</code>.</li>";
    echo "</ol>";
    echo "<div style='margin-top:12px;color:rgba(0,0,0,.6);font-size:13px'>Current target: <code>{$host}:{$port}/{$name}</code> as <code>{$user}</code>.</div>";
    echo "</div></body>";
    exit;
  }

  return $pdo;
}


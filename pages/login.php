<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

start_session();

if (current_user_id()) {
  redirect('/pages/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  $stmt = db()->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($password, (string)$user['password'])) {
    $error = 'Invalid email or password.';
  } else {
    login_user((int)$user['id']);
    redirect('/pages/dashboard.php');
  }
}

layout_header('Login', 'for_you');
?>

<div class="auth">
  <div class="auth__wrap">
    <div class="panel">
      <div class="section__title">Welcome back</div>
      <div style="color:var(--muted); margin-bottom: 10px;">
        Login to see your personalized feed, trending news, and saved articles.
      </div>

      <?php if ($error): ?>
        <div class="msg is-error"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <div class="field">
          <label>Email</label>
          <input name="email" type="email" required autocomplete="email" />
        </div>
        <div class="field">
          <label>Password</label>
          <input name="password" type="password" required autocomplete="current-password" />
        </div>
        <button class="btn btn--primary" type="submit">Login</button>
        <a class="btn btn--ghost" href="/pages/register.php">Create account</a>
      </form>
    </div>

    <div class="panel">
      <div class="section__title">What you get</div>
      <div class="pill">For You feed</div>
      <div class="pill" style="margin-left:8px;">Top 10 Trending</div>
      <div class="pill" style="margin-left:8px;">Bookmarks</div>
      <div style="margin-top: 12px; color:var(--muted); line-height: 1.5;">
        NewsFlow uses your interests and saved articles to improve recommendations.
        API requests are cached to reduce calls and keep things fast.
      </div>
    </div>
  </div>
</div>

<?php
layout_footer();


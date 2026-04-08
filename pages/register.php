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
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $name = trim((string)($_POST['name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  if ($name === '' || $email === '' || $password === '') {
    $error = 'All fields are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email.';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } else {
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
      $error = 'Email already registered.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
      $ins->execute([$name, $email, $hash]);
      $uid = (int)db()->lastInsertId();

      // Default preference row (can be edited in Settings)
      $pref = db()->prepare('INSERT INTO user_preferences (user_id, category, language, region) VALUES (?, ?, ?, ?)');
      $pref->execute([$uid, 'technology', 'en', 'us']);

      login_user($uid);
      redirect('/pages/settings.php?welcome=1');
    }
  }
}

layout_header('Register', 'for_you');
?>

<div class="auth">
  <div class="auth__wrap">
    <div class="panel">
      <div class="section__title">Create your account</div>
      <div style="color:var(--muted); margin-bottom: 10px;">
        Pick your interests after registration to personalize the feed.
      </div>

      <?php if ($error): ?>
        <div class="msg is-error"><?= h($error) ?></div>
      <?php elseif ($ok): ?>
        <div class="msg is-ok"><?= h($ok) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <div class="field">
          <label>Name</label>
          <input name="name" required autocomplete="name" />
        </div>
        <div class="field">
          <label>Email</label>
          <input name="email" type="email" required autocomplete="email" />
        </div>
        <div class="field">
          <label>Password</label>
          <input name="password" type="password" required autocomplete="new-password" />
        </div>
        <button class="btn btn--primary" type="submit">Register</button>
        <a class="btn btn--ghost" href="/pages/login.php">Login</a>
      </form>
    </div>

    <div class="panel">
      <div class="section__title">Privacy</div>
      <div style="color:var(--muted); line-height:1.5;">
        Your preferences are stored in MySQL. Saved articles help tune recommendations
        via simple keyword matching. You can change interests anytime in Settings.
      </div>
    </div>
  </div>
</div>

<?php
layout_footer();


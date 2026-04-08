<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Default landing: dashboard if logged in, else login.
if (current_user_id()) {
  redirect('pages/dashboard.php');
}
redirect('pages/login.php');


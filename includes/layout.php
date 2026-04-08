<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

function layout_header(string $title, string $active = 'for_you'): void
{
  $uid = current_user_id();
  $csrf = csrf_token();
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= h($title) ?> · <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css" />
    <script>
      window.NEWSFLOW = { csrf: <?= json_encode($csrf) ?>, userId: <?= json_encode($uid) ?> };
    </script>
    <script defer src="/assets/js/app.js"></script>
  </head>
  <body>
    <div class="app">
      <aside class="sidebar">
        <div class="brand">
          <div class="brand__row">
            <div class="brand__mark" aria-hidden="true">📰</div>
            <div>
              <div class="brand__logo">NewsFlow</div>
              <div class="brand__sub">Personalized News Hub</div>
            </div>
          </div>
        </div>

        <nav class="nav">
          <div class="nav__section">Menu</div>
          <a class="nav__item <?= $active === 'for_you' ? 'is-active' : '' ?>" href="/pages/dashboard.php">
            <span class="nav__icon" aria-hidden="true">🏠</span>
            <span>For You</span>
          </a>
          <a class="nav__item <?= $active === 'trending' ? 'is-active' : '' ?>" href="/pages/trending.php">
            <span class="nav__icon" aria-hidden="true">🔥</span>
            <span>Trending News</span>
          </a>
          <a class="nav__item <?= $active === 'saved' ? 'is-active' : '' ?>" href="/pages/saved.php">
            <span class="nav__icon" aria-hidden="true">🔖</span>
            <span>Saved Articles</span>
          </a>
          <div class="nav__section">Categories</div>
          <a class="nav__item <?= $active === 'technology' ? 'is-active' : '' ?>" href="/pages/category.php?c=technology">
            <span class="nav__icon" aria-hidden="true">💻</span>
            <span>Technology</span>
          </a>
          <a class="nav__item <?= $active === 'business' ? 'is-active' : '' ?>" href="/pages/category.php?c=business">
            <span class="nav__icon" aria-hidden="true">💼</span>
            <span>Business</span>
          </a>
          <a class="nav__item <?= $active === 'health' ? 'is-active' : '' ?>" href="/pages/category.php?c=health">
            <span class="nav__icon" aria-hidden="true">🩺</span>
            <span>Health</span>
          </a>
          <a class="nav__item <?= $active === 'sports' ? 'is-active' : '' ?>" href="/pages/category.php?c=sports">
            <span class="nav__icon" aria-hidden="true">🏟️</span>
            <span>Sports</span>
          </a>
          <a class="nav__item <?= $active === 'entertainment' ? 'is-active' : '' ?>" href="/pages/category.php?c=entertainment">
            <span class="nav__icon" aria-hidden="true">🎬</span>
            <span>Entertainment</span>
          </a>
          <div class="nav__section">More</div>
          <a class="nav__item <?= $active === 'settings' ? 'is-active' : '' ?>" href="/pages/settings.php">
            <span class="nav__icon" aria-hidden="true">⚙️</span>
            <span>Settings</span>
          </a>
          <a class="nav__item <?= $active === 'wireframe' ? 'is-active' : '' ?>" href="/pages/wireframe.php">
            <span class="nav__icon" aria-hidden="true">🧩</span>
            <span>View Wireframe</span>
          </a>
        </nav>

        <div class="sidebar__footer">
          <?php if ($uid): ?>
            <a class="btn btn--ghost btn--sm" href="/pages/logout.php">Log Out</a>
          <?php else: ?>
            <a class="btn btn--ghost btn--sm" href="/pages/login.php">Login</a>
          <?php endif; ?>
          <button class="btn btn--ghost btn--sm" data-action="toggle-dark">Dark mode</button>
        </div>
      </aside>

      <main class="main">
        <header class="topbar">
          <form class="search" action="/pages/search.php" method="get">
            <span class="search__icon" aria-hidden="true">🔎</span>
            <input class="search__input" name="q" placeholder="Search for topics, sources…" />
          </form>
          <div class="topbar__actions">
            <button class="iconbtn" type="button" aria-label="Notifications" title="Notifications">🔔</button>
            <div class="avatar" aria-label="Profile" title="Profile">
              <?= $uid ? '👤' : '🔒' ?>
            </div>
          </div>
        </header>
        <section class="content">
  <?php
}

function layout_footer(): void
{
  ?>
        </section>
      </main>
    </div>

    <footer class="footer">
      <div class="footer__inner">
        <div><strong>ENROLLMENT NO:</strong> 22SE02CS019</div>
        <div><strong>NAME:</strong> ABHISHEK HEGDEKAR</div>
      </div>
    </footer>
  </body>
  </html>
  <?php
}


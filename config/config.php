<?php
declare(strict_types=1);

// -----------------------------
// NewsFlow Configuration
// -----------------------------

// MySQL (MANDATORY)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'news_hub');
define('DB_USER', 'root');
define('DB_PASS', '12345678');

// App
define('APP_NAME', 'Personalized News Hub (NewsFlow)');
define('APP_BASE_URL', ''); // optional, e.g. http://localhost/newsflow

// News APIs (placeholders required by spec)
define('API_KEY_1', '79d2b8ab10bc4f279e8307e385700c94'); // Provider 1: NewsAPI
define('API_KEY_2', 'becac91abf34446ba2feda8b0710c484'); // Provider 2: World News API

// Cache
define('CACHE_DIR', __DIR__ . '/../storage/cache');
define('CACHE_TTL_SECONDS', 300); // 5 minutes

// Security
define('SESSION_COOKIE_NAME', 'newsflow_session');


<?php
declare(strict_types = 1)
;

// -----------------------------
// NewsFlow Configuration
// -----------------------------

// MySQL — defaults work inside Docker; override with NEWSFLOW_DB_* env vars for XAMPP/WAMP.
// Inside Docker Compose the MySQL service is named "db".
define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_NAME', 'news_hub');
define('DB_USER', 'newsflow');
define('DB_PASS', 'newsflow_pass');

// App
define('APP_NAME', 'Personalized News Hub (NewsFlow)');
define('APP_BASE_URL', ''); // optional, e.g. http://localhost/newsflow

// News APIs — read from environment first, fall back to hardcoded keys
define('API_KEY_1', (string)(getenv('NEWSFLOW_API_KEY_1') ?: '79d2b8ab10bc4f279e8307e385700c94')); // Provider 1: NewsAPI
define('API_KEY_2', (string)(getenv('NEWSFLOW_API_KEY_2') ?: 'becac91abf34446ba2feda8b0710c484')); // Provider 2: World News API

// Cache
define('CACHE_DIR', __DIR__ . '/../storage/cache');
define('CACHE_TTL_SECONDS', 300); // 5 minutes

// Security
define('SESSION_COOKIE_NAME', 'newsflow_session');

## Personalized News Hub (NewsFlow)

Full-stack PHP + MySQL (PDO) application.

### Requirements
- PHP 8.x (XAMPP/WAMP/MAMP ok)
- MySQL 8.x (MANDATORY)

### Project structure
```
/config
  config.php
  db.php

/assets
  /css
  /js
  /images
  /wireframe

/includes
  auth.php
  csrf.php
  functions.php
  layout.php
  news_providers.php
  cache.php

/pages
  dashboard.php
  trending.php
  category.php
  saved.php
  settings.php
  search.php
  wireframe.php
  login.php
  register.php
  logout.php

/api
  news.php
  saved.php
  preferences.php
  auth.php
  search.php

/database
  schema.sql

index.php
```

### Setup (MySQL)
1. Create DB and tables using `database/schema.sql`.
2. Update credentials in `config/config.php`.

### Run
- **XAMPP/WAMP**: place this folder under `htdocs` (or your web root), then browse to `index.php`.
- **PHP built-in server** (quick local):

```bash
php -S localhost:8000 -t .
```

Then open `http://localhost:8000`.

### Wireframe
- Put your uploaded wireframe file (PDF/image) into `assets/wireframe/`.
- The app will show the newest file on the **View Wireframe** page with a download button.


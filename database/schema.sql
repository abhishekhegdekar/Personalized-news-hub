-- Personalized News Hub (NewsFlow)
-- Database: news_hub (MANDATORY)

CREATE DATABASE IF NOT EXISTS news_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE news_hub;

-- 1) users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_users_email (email),
  KEY idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) user_preferences
CREATE TABLE IF NOT EXISTS user_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category VARCHAR(64) NOT NULL,
  language VARCHAR(16) NOT NULL DEFAULT 'en',
  region VARCHAR(16) NOT NULL DEFAULT 'us',
  UNIQUE KEY uk_user_pref (user_id, category),
  KEY idx_user_pref_user (user_id),
  KEY idx_user_pref_category (category),
  CONSTRAINT fk_user_pref_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) saved_articles
CREATE TABLE IF NOT EXISTS saved_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title TEXT NOT NULL,
  description TEXT NULL,
  url TEXT NOT NULL,
  image_url TEXT NULL,
  category VARCHAR(64) NOT NULL,
  saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_saved_user_time (user_id, saved_at),
  KEY idx_saved_user_category (user_id, category),
  -- url is TEXT; add a prefix index for dedupe checks
  KEY idx_saved_user_url_prefix (user_id, (url(180))),
  CONSTRAINT fk_saved_articles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) search_history (optional but implemented)
CREATE TABLE IF NOT EXISTS search_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  query VARCHAR(255) NOT NULL,
  searched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_search_user_time (user_id, searched_at),
  KEY idx_search_query (query),
  CONSTRAINT fk_search_history_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BONUS: analytics (most viewed category)
CREATE TABLE IF NOT EXISTS category_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  category VARCHAR(64) NOT NULL,
  viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_views_category_time (category, viewed_at),
  KEY idx_views_user_time (user_id, viewed_at),
  CONSTRAINT fk_category_views_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


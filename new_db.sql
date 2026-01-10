/*
=============================================================================
DOKUMENTACJA SYSTEMU BAZY DANYCH EGZAMINACYJNYCH
=============================================================================
Opis: System do zarządzania egzaminami, wersjonowania pytań i śledzenia postępów.
Silnik: InnoDB | Kodowanie: utf8mb4 (pełne wsparcie Unicode/Emoji)
=============================================================================
*/

-- ---------------------------------------------------------
-- SEKCOJA 1: UŻYTKOWNICY I BEZPIECZEŃSTWO
-- ---------------------------------------------------------

-- Tabela przechowująca dane użytkowników i metodę autoryzacji.
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  google_id VARCHAR(255) DEFAULT NULL,            -- ID z Google OAuth (unikalne)
  auth_provider ENUM('local', 'google') NOT NULL DEFAULT 'local',
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash CHAR(255) DEFAULT NULL,           -- Hash hasła (puste dla Google Auth)
  is_verified TINYINT(1) DEFAULT 0,               -- Czy e-mail został potwierdzony
  role ENUM('user', 'admin') DEFAULT 'user',      -- Role systemowe (RBAC)
  is_active TINYINT(1) NOT NULL DEFAULT 1,        -- Flaga aktywności konta
  deleted_at DATETIME DEFAULT NULL,               -- Soft Delete: data "usunięcia"
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email),
  UNIQUE KEY uq_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- Tokeny krótkotrwałe (weryfikacja, reset hasła, zmiana e-mail).
CREATE TABLE user_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(512) NOT NULL,
  type ENUM('email_verify','password_reset','email_change') NOT NULL,
  token_data VARCHAR(255) DEFAULT NULL,           -- Np. nowy e-mail przy zmianie
  expires_at DATETIME NOT NULL,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user (user_id),
  KEY idx_token (token),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- SEKCJA 2: STRUKTURA TREŚCI (EGZAMINY I TEMATY)
-- ---------------------------------------------------------

-- Główne kategorie egzaminacyjne (np. Prawo Jazdy, Matura).
CREATE TABLE exam_types (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL,                      -- Krótki kod (np. 'PJ-B')
  name VARCHAR(200) NOT NULL,                      -- Pełna nazwa
  description TEXT,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Podkategorie/Tematy wewnątrz danego egzaminu.
CREATE TABLE topics (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  exam_type_id INT UNSIGNED NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_topic (exam_type_id, name),    -- Unikalność nazwy w obrębie egzaminu
  FOREIGN KEY (exam_type_id) REFERENCES exam_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- SEKCJA 3: PYTANIA I WERSJONOWANIE
-- ---------------------------------------------------------

-- Tabela bazowa pytania (kontener logiczny).
-- Zastosowano wzorzec wersjonowania treści (oddzielenie ID od treści).
CREATE TABLE questions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  exam_type_id INT UNSIGNED NOT NULL,
  topic_id INT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (exam_type_id) REFERENCES exam_types(id) ON DELETE RESTRICT,
  FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Faktyczna treść pytania (pozwala na edycję bez niszczenia historii wyników).
CREATE TABLE question_versions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_id INT UNSIGNED NOT NULL,
  version_num INT NOT NULL DEFAULT 1,             -- Numer wersji (inkrementowany ręcznie)
  question_text TEXT NOT NULL,
  image_path VARCHAR(500) NULL,                   -- Opcjonalna ścieżka do grafiki
  explanation TEXT NULL,                          -- Wyjaśnienie poprawnej odpowiedzi
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_qv (question_id, version_num),    -- Tylko jedna wersja o danym numerze dla pytania
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Opcje odpowiedzi przypisane do konkretnej wersji pytania.
CREATE TABLE answers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_version_id INT UNSIGNED NOT NULL,
  answer_text TEXT NOT NULL,
  answer_order INT UNSIGNED NOT NULL DEFAULT 1,   -- Kolejność wyświetlania
  is_correct TINYINT(1) NOT NULL DEFAULT 0,       -- Czy to poprawna odpowiedź?
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_answer_order (question_version_id, answer_order),
  FOREIGN KEY (question_version_id) REFERENCES question_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- SEKCJA 4: ANALITYKA I WYNIKI UŻYTKOWNIKA
-- ---------------------------------------------------------

-- Nagłówek sesji testowej (próby podejścia do egzaminu).
CREATE TABLE attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  exam_type_id INT UNSIGNED NOT NULL,
  test_type ENUM('full_exam','personalized') NOT NULL, -- Rodzaj generowania testu
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME DEFAULT NULL,             -- Data zakończenia (NULL = w trakcie)
  correct_count INT NOT NULL DEFAULT 0,           -- Suma poprawnych odpowiedzi
  total_questions INT NOT NULL DEFAULT 0,         -- Liczba pytań w teście
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (exam_type_id) REFERENCES exam_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relacja N:M określająca, z jakich tematów składała się próba (dla testów personalizowanych).
CREATE TABLE attempt_topics (
  attempt_id INT UNSIGNED NOT NULL,
  topic_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (attempt_id, topic_id),
  FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Zapis konkretnych odpowiedzi udzielonych przez użytkownika.
CREATE TABLE attempt_answers (
  attempt_id INT UNSIGNED NOT NULL,
  question_version_id INT UNSIGNED NOT NULL,      -- Referencja do wersji (wiemy co widział user)
  answer_id INT UNSIGNED NOT NULL,                -- Wybrana odpowiedź
  answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (attempt_id, question_version_id),
  FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_version_id) REFERENCES question_versions(id) ON DELETE NO ACTION,
  FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela zbiorcza (Cache statystyk).
-- Służy do szybkiego wyciągania postępu (np. "ile razy to pytanie było błędne?").
CREATE TABLE user_progress (
  user_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  attempts_count INT NOT NULL DEFAULT 0,          -- Ile razy user widział to pytanie
  correct_count INT NOT NULL DEFAULT 0,           -- Ile razy odpowiedział poprawnie
  last_attempt_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, question_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
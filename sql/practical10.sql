
USE studenthub;

SET @practical10_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND COLUMN_NAME = 'role'
);
SET @practical10_sql = IF(
    @practical10_column_exists = 0,
    'ALTER TABLE students ADD COLUMN role ENUM(''student'', ''admin'') NOT NULL DEFAULT ''student''',
    'SELECT 1'
);
PREPARE practical10_stmt FROM @practical10_sql;
EXECUTE practical10_stmt;
DEALLOCATE PREPARE practical10_stmt;

SET @practical10_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND COLUMN_NAME = 'last_login'
);
SET @practical10_sql = IF(
    @practical10_column_exists = 0,
    'ALTER TABLE students ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE practical10_stmt FROM @practical10_sql;
EXECUTE practical10_stmt;
DEALLOCATE PREPARE practical10_stmt;

SET @practical10_index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND INDEX_NAME = 'idx_students_role'
);
SET @practical10_sql = IF(
    @practical10_index_exists = 0,
    'CREATE INDEX idx_students_role ON students (role)',
    'SELECT 1'
);
PREPARE practical10_stmt FROM @practical10_sql;
EXECUTE practical10_stmt;
DEALLOCATE PREPARE practical10_stmt;

CREATE TABLE IF NOT EXISTS remember_tokens (
    remember_token_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (remember_token_id),
    UNIQUE KEY uq_remember_tokens_hash (token_hash),
    KEY idx_remember_tokens_student (student_id),
    KEY idx_remember_tokens_expiry (expires_at),
    CONSTRAINT fk_remember_tokens_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- To grant admin access, replace the address with one chosen existing account:
-- UPDATE students SET role = 'admin' WHERE email = 'chosen-existing-email';

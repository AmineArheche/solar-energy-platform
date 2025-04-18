-- Création de la table messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT,
    status ENUM('pending', 'answered', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vérifier si la colonne is_visible existe, sinon l'ajouter
SET @dbname = DATABASE();
SET @tablename = "messages";
SET @columnname = "is_visible";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (COLUMN_NAME = @columnname)
      AND (TABLE_NAME = @tablename)
      AND (TABLE_SCHEMA = @dbname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE messages ADD COLUMN is_visible TINYINT(1) NOT NULL DEFAULT 1"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists; 
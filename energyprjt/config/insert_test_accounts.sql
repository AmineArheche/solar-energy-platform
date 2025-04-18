-- Vérifier si la table users existe, sinon la créer
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vérifier si la colonne is_admin existe, sinon l'ajouter
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "is_admin";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insérer un administrateur (si n'existe pas déjà)
INSERT INTO users (firstname, lastname, email, password, is_admin)
SELECT 'Admin', 'User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@example.com');

-- Insérer un utilisateur normal (si n'existe pas déjà)
INSERT INTO users (firstname, lastname, email, password, is_admin)
SELECT 'Normal', 'User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'user@example.com');

-- Afficher les comptes créés
SELECT id, firstname, lastname, email, is_admin, created_at FROM users WHERE email IN ('admin@example.com', 'user@example.com'); 
<?php
require_once __DIR__ . '/../config/database.php';

// Paramètres de l'administrateur
$admin_username = 'admin';
$admin_password = 'admin123';

// Création du hash du mot de passe
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// Afficher le hash pour vérification
echo "Nouveau hash généré : " . $password_hash . "<br><br>";

try {
    // Supprimer d'abord la table admin si elle existe
    $pdo->exec("DROP TABLE IF EXISTS admin");
    
    // Recréer la table admin
    $pdo->exec("CREATE TABLE admin (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Créer un nouvel admin avec le nouveau hash
    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute([$admin_username, $password_hash]);
    
    echo "La table admin a été réinitialisée avec succès.<br>";
    echo "Un nouvel administrateur a été créé avec succès.<br><br>";

    // Vérifier que le mot de passe fonctionne
    $verify_stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $verify_stmt->execute([$admin_username]);
    $admin = $verify_stmt->fetch();
    
    if ($admin && password_verify($admin_password, $admin['password'])) {
        echo "Vérification réussie : Le mot de passe est correctement hashé.<br><br>";
    } else {
        echo "ERREUR : Le hash du mot de passe ne correspond pas.<br><br>";
    }

    echo "Identifiants de connexion :<br>";
    echo "Username: " . $admin_username . "<br>";
    echo "Password: " . $admin_password . "<br>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 
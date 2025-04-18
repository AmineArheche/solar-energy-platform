<?php
require_once '../config/database.php';

// Créer un nouveau hash pour "admin123"
$password = "admin123";
$new_hash = password_hash($password, PASSWORD_DEFAULT);

echo "Test de vérification du mot de passe :<br><br>";
echo "1. Hash stocké dans la base de données :<br>";
echo '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi<br><br>';

echo "2. Nouveau hash généré pour 'admin123' :<br>";
echo $new_hash . "<br><br>";

echo "3. Test de vérification avec le hash stocké :<br>";
$stored_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "Résultat : " . (password_verify($password, $stored_hash) ? 'VALIDE' : 'INVALIDE') . "<br><br>";

// Vérifier si la table admin existe et contient les bonnes données
try {
    $stmt = $pdo->query("SELECT * FROM admin");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "4. Hash actuellement dans la base de données :<br>";
        echo $admin['password'] . "<br><br>";
        
        echo "5. Test avec le hash de la base de données :<br>";
        echo "Résultat : " . (password_verify($password, $admin['password']) ? 'VALIDE' : 'INVALIDE');
    } else {
        echo "4. ERREUR : Aucun administrateur trouvé dans la base de données";
    }
} catch (PDOException $e) {
    echo "4. ERREUR : " . $e->getMessage();
}
?> 
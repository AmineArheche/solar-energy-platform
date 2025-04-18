<?php
require_once 'config/database.php';

try {
    // Récupérer tous les produits avec leurs images
    $stmt = $pdo->query("SELECT id, name, image_url FROM products");
    $products = $stmt->fetchAll();

    echo "<h1>Vérification des images des produits</h1>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px;'>";

    foreach ($products as $product) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; border-radius: 8px;'>";
        echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
        
        if (!empty($product['image_url'])) {
            $imagePath = $product['image_url'];
            $fullPath = __DIR__ . '/' . $imagePath;
            
            echo "<p>Chemin de l'image : " . htmlspecialchars($imagePath) . "</p>";
            
            if (file_exists($fullPath)) {
                echo "<img src='" . htmlspecialchars($imagePath) . "' 
                          alt='" . htmlspecialchars($product['name']) . "' 
                          style='width: 100%; height: 200px; object-fit: cover;'>";
                echo "<p style='color: green;'>✓ Image existe et est accessible</p>";
            } else {
                echo "<p style='color: red;'>✗ Image non trouvée sur le serveur</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Aucune image définie pour ce produit</p>";
        }
        
        echo "</div>";
    }

    echo "</div>";

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 
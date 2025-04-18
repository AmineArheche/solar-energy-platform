<?php
require_once 'config/database.php';

// Configuration
$imageWidth = 800;
$imageHeight = 600;

// Tableau d'images par catégorie
$categoryImages = [
    // Catégorie: Panneaux solaires
    1 => [
        'uploads/solar_panel1.jpg',
        'uploads/solar_panel2.jpg',
        'uploads/solar_panel3.jpg',
        'uploads/solar_panel4.jpg',
        'uploads/solar_panel5.jpg'
    ],
    // Catégorie: Batteries
    2 => [
        'uploads/battery1.jpg',
        'uploads/battery2.jpg',
        'uploads/battery3.jpg',
        'uploads/battery4.jpg',
        'uploads/battery5.jpg'
    ],
    // Catégorie: Onduleurs
    3 => [
        'uploads/inverter1.jpg',
        'uploads/inverter2.jpg',
        'uploads/inverter3.jpg',
        'uploads/inverter4.jpg',
        'uploads/inverter5.jpg'
    ],
    // Catégorie: Accessoires
    4 => [
        'uploads/accessory1.jpg',
        'uploads/accessory2.jpg',
        'uploads/accessory3.jpg',
        'uploads/accessory4.jpg',
        'uploads/accessory5.jpg'
    ]
];

// Images par défaut pour toutes les catégories
$defaultImages = [
    'uploads/solar_default1.jpg',
    'uploads/solar_default2.jpg',
    'uploads/solar_default3.jpg',
    'uploads/solar_default4.jpg',
    'uploads/solar_default5.jpg'
];

// Fonction pour générer une image de placeholder
function generatePlaceholderImage($text, $width, $height) {
    $image = imagecreatetruecolor($width, $height);
    
    // Couleurs
    $bgColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    
    // Remplir le fond
    imagefill($image, 0, 0, $bgColor);
    
    // Ajouter du texte
    $fontSize = 20;
    $font = 5; // Police par défaut
    
    // Centrer le texte
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Sauvegarder l'image
    $filename = 'uploads/placeholder_' . time() . '.jpg';
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
    
    return $filename;
}

// Fonction pour copier une image
function copyImage($source, $destination) {
    if (file_exists($source)) {
        return copy($source, $destination);
    }
    return false;
}

try {
    // Récupérer tous les produits
    $stmt = $pdo->query("SELECT id, name, category_id FROM products");
    $products = $stmt->fetchAll();
    
    // Récupérer les catégories
    $categories = [];
    $catStmt = $pdo->query("SELECT id, name FROM categories");
    while ($row = $catStmt->fetch()) {
        $categories[$row['id']] = $row['name'];
    }
    
    echo "<h1>Mise à jour des images des produits</h1>";
    echo "<div style='margin: 20px;'>";
    
    foreach ($products as $product) {
        echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<h3>Produit: " . htmlspecialchars($product['name']) . "</h3>";
        
        // Déterminer quelle image utiliser
        $imageSource = null;
        
        if (isset($product['category_id']) && isset($categoryImages[$product['category_id']])) {
            // Utiliser une image de la catégorie
            $categoryImageList = $categoryImages[$product['category_id']];
            $imageSource = $categoryImageList[array_rand($categoryImageList)];
        } else {
            // Utiliser une image par défaut
            $imageSource = $defaultImages[array_rand($defaultImages)];
        }
        
        // Créer un nom de fichier unique
        $filename = 'uploads/product_' . $product['id'] . '_' . time() . '.jpg';
        
        // Copier l'image
        if (copyImage($imageSource, $filename)) {
            // Mettre à jour l'URL de l'image dans la base de données
            $updateStmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            $updateStmt->execute([$filename, $product['id']]);
            
            echo "<p style='color: green;'>✓ Image mise à jour: <a href='" . htmlspecialchars($filename) . "' target='_blank'>Voir l'image</a></p>";
        } else {
            // Générer une image de placeholder si la copie échoue
            $placeholderFile = generatePlaceholderImage($product['name'], $imageWidth, $imageHeight);
            
            $updateStmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            $updateStmt->execute([$placeholderFile, $product['id']]);
            
            echo "<p style='color: orange;'>⚠ Impossible de copier l'image. Image de placeholder générée.</p>";
        }
        
        echo "</div>";
    }
    
    echo "<p style='margin-top: 20px;'><a href='index.php'>Retour à la page d'accueil</a></p>";
    echo "</div>";

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 
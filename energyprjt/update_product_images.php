<?php
require_once 'config/database.php';

// Configuration
$unsplashAccessKey = 'YOUR_UNSPLASH_ACCESS_KEY'; // Remplacez par votre clé d'API Unsplash
$imageWidth = 800;
$imageHeight = 600;
$imageQuality = 80;

// Fonction pour télécharger une image depuis une URL
function downloadImage($url, $savePath) {
    $ch = curl_init($url);
    $fp = fopen($savePath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $success = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $success;
}

// Fonction pour obtenir une image aléatoire d'Unsplash
function getRandomUnsplashImage($query) {
    global $unsplashAccessKey, $imageWidth, $imageHeight, $imageQuality;
    
    $url = "https://api.unsplash.com/photos/random?query=" . urlencode($query) . 
           "&client_id=" . $unsplashAccessKey;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['urls']['regular'])) {
        // Construire l'URL avec les paramètres de taille et qualité
        $imageUrl = $data['urls']['regular'] . "?w=" . $imageWidth . "&h=" . $imageHeight . "&q=" . $imageQuality;
        return $imageUrl;
    }
    
    return false;
}

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
        
        // Déterminer la requête de recherche en fonction de la catégorie et du nom du produit
        $searchQuery = "solar energy";
        if (isset($categories[$product['category_id']])) {
            $searchQuery = $categories[$product['category_id']] . " " . $product['name'];
        } else {
            $searchQuery = $product['name'] . " solar";
        }
        
        // Essayer d'obtenir une image d'Unsplash
        $imageUrl = getRandomUnsplashImage($searchQuery);
        
        if ($imageUrl) {
            // Créer un nom de fichier unique
            $filename = 'uploads/product_' . $product['id'] . '_' . time() . '.jpg';
            
            // Télécharger l'image
            if (downloadImage($imageUrl, $filename)) {
                // Mettre à jour l'URL de l'image dans la base de données
                $updateStmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                $updateStmt->execute([$filename, $product['id']]);
                
                echo "<p style='color: green;'>✓ Image mise à jour: <a href='" . htmlspecialchars($filename) . "' target='_blank'>Voir l'image</a></p>";
            } else {
                // Générer une image de placeholder si le téléchargement échoue
                $placeholderFile = generatePlaceholderImage($product['name'], $imageWidth, $imageHeight);
                
                $updateStmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                $updateStmt->execute([$placeholderFile, $product['id']]);
                
                echo "<p style='color: orange;'>⚠ Impossible de télécharger l'image. Image de placeholder générée.</p>";
            }
        } else {
            // Générer une image de placeholder si aucune image n'est trouvée
            $placeholderFile = generatePlaceholderImage($product['name'], $imageWidth, $imageHeight);
            
            $updateStmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            $updateStmt->execute([$placeholderFile, $product['id']]);
            
            echo "<p style='color: orange;'>⚠ Aucune image trouvée. Image de placeholder générée.</p>";
        }
        
        echo "</div>";
    }
    
    echo "<p style='margin-top: 20px;'><a href='index.php'>Retour à la page d'accueil</a></p>";
    echo "</div>";

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 
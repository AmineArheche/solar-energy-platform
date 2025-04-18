<?php
// Script pour télécharger des images d'énergie solaire depuis des sources gratuites

// Configuration
$imageWidth = 800;
$imageHeight = 600;
$imageQuality = 80;

// Créer le dossier uploads s'il n'existe pas
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Sources d'images gratuites
$imageSources = [
    // Panneaux solaires
    'solar_panel' => [
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg'
    ],
    // Batteries
    'battery' => [
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg'
    ],
    // Onduleurs
    'inverter' => [
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg'
    ],
    // Accessoires
    'accessory' => [
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg'
    ],
    // Images par défaut
    'default' => [
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg',
        'https://images.pexels.com/photos/414837/pexels-photo-414837.jpeg'
    ]
];

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

echo "<h1>Téléchargement des images d'énergie solaire</h1>";
echo "<div style='margin: 20px;'>";

// Télécharger les images pour chaque catégorie
foreach ($imageSources as $category => $urls) {
    echo "<h2>Catégorie: " . ucfirst($category) . "</h2>";
    echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;'>";
    
    for ($i = 0; $i < count($urls); $i++) {
        $url = $urls[$i];
        $filename = "uploads/{$category}{$i+1}.jpg";
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; width: 200px;'>";
        
        if (downloadImage($url, $filename)) {
            echo "<img src='{$filename}' style='width: 100%; height: 150px; object-fit: cover;'>";
            echo "<p style='color: green;'>✓ Image téléchargée: {$filename}</p>";
        } else {
            // Générer une image de placeholder si le téléchargement échoue
            $placeholderFile = generatePlaceholderImage(ucfirst($category), $imageWidth, $imageHeight);
            rename($placeholderFile, $filename);
            
            echo "<img src='{$filename}' style='width: 100%; height: 150px; object-fit: cover;'>";
            echo "<p style='color: orange;'>⚠ Impossible de télécharger l'image. Image de placeholder générée.</p>";
        }
        
        echo "</div>";
    }
    
    echo "</div>";
}

echo "<p style='margin-top: 20px;'><a href='update_product_images_local.php'>Mettre à jour les images des produits</a></p>";
echo "<p><a href='index.php'>Retour à la page d'accueil</a></p>";
echo "</div>";
?> 
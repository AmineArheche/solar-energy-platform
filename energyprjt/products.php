<?php
if (function_exists('apply_security_headers')) apply_security_headers();

require_once 'config/database.php';

// Fonction de validation des entrées
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Récupération sécurisée de l'ID du produit
$product_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$product_id) {
    header("Location: index.php");
    exit();
}

try {
    // Récupération du produit avec sa catégorie
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: index.php");
        exit();
    }

    // Récupération des produits similaires
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? 
        ORDER BY RAND() 
        LIMIT 3
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $similar_products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du produit : " . $e->getMessage());
    header("Location: index.php");
    exit();
}

// Protection contre les attaques XSS
$product = array_map('htmlspecialchars', $product);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - SolarTech</title>
    <meta name="description" content="<?php echo substr($product['description'], 0, 160); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-sun text-2xl text-blue-600"></i>
                        <i class="fas fa-wind text-2xl text-blue-600"></i>
                        <span class="text-xl font-bold text-gray-800">Réparation<span class="text-blue-600">Énergie</span></span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Produits</a>
                    <a href="about.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">À propos</a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
            <!-- Image du produit -->
            <div class="mb-8 lg:mb-0">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="w-full h-auto rounded-lg shadow-lg">
            </div>

            <!-- Détails du produit -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-sm text-gray-500 mb-4">Catégorie: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <div class="text-2xl font-bold text-blue-600 mb-6"><?php echo number_format($product['price'], 2, ',', ' '); ?> DH</div>
                <div class="prose max-w-none mb-8">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                    <div class="text-green-600 mb-4">
                        <i class="fas fa-check-circle"></i> En stock (<?php echo $product['stock']; ?> disponibles)
                    </div>
                <?php else: ?>
                    <div class="text-red-600 mb-4">
                        <i class="fas fa-times-circle"></i> Rupture de stock
                    </div>
                <?php endif; ?>

                <button class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-shopping-cart mr-2"></i> Ajouter au panier
                </button>
            </div>
        </div>

        <!-- Produits similaires -->
        <?php if (!empty($similar_products)): ?>
        <section class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Produits similaires</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($similar_products as $similar): ?>
                <a href="product.php?id=<?php echo $similar['id']; ?>" class="block">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <img src="<?php echo htmlspecialchars($similar['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($similar['name']); ?>" 
                             class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($similar['name']); ?></h3>
                            <p class="text-blue-600 font-bold"><?php echo number_format($similar['price'], 2, ',', ' '); ?> DH</p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">À propos de nous</h3>
                    <p class="text-gray-400">Votre partenaire de confiance pour la réparation d'énergie solaire et éolienne.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <ul class="text-gray-400 space-y-2">
                        <li><i class="fas fa-phone mr-2"></i> +212 651 441 563</li>
                        <li><i class="fas fa-phone mr-2"></i> +212 662 611 909</li>
                        <li><i class="fas fa-envelope mr-2"></i> elyamanihamza@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Rés. AL ATLAS, Avenue Hassan II, Imouzzer-Kandar 31250</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Notre équipe</h3>
                    <ul class="text-gray-400 space-y-2">
                        <li><i class="fas fa-user mr-2"></i> Fondateur: Hassan El Yamani</li>
                        <li><i class="fas fa-user-tie mr-2"></i> Chef de projet: Hamza El Yamani</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Réparation d'énergie solaire et éolienne - Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html> 
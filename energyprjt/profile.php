<?php
if (function_exists('apply_security_headers')) apply_security_headers();

session_start();
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$user_firstname = $_SESSION['user_firstname'];
$user_lastname = $_SESSION['user_lastname'];
$user_email = $_SESSION['user_email'];

// Configuration de la pagination pour les produits
$products_per_page = 6;  // Moins de produits par page pour cette vue
$current_page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$offset = ($current_page - 1) * $products_per_page;

try {
    // Récupérer les produits recommandés (les plus récents pour cet exemple)
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, 
        (SELECT COUNT(*) FROM products) as total_count 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$products_per_page, $offset]);
    $products = $stmt->fetchAll();

    // Calcul du nombre total de pages
    $total_products = !empty($products) ? $products[0]['total_count'] : 0;
    $total_pages = ceil($total_products / $products_per_page);

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des produits: " . $e->getMessage();
    $products = [];
    $total_pages = 0;
}

// Construction des paramètres de l'URL pour la pagination
$url_params = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - RéparationÉnergie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-sun text-2xl text-blue-600"></i>
                    <i class="fas fa-wind text-2xl text-blue-600"></i>
                    <h1 class="text-xl font-bold text-gray-800">Réparation<span class="text-blue-600">Énergie</span></h1>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobileMenuButton" class="p-2 text-gray-800 hover:text-blue-600 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
                
                <!-- Desktop navigation -->
                <div class="hidden md:flex items-center">
                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-8 mr-8 border-r pr-6">
                        <a href="index.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            <i class="fas fa-home"></i>
                            <span>Accueil</span>
                        </a>
                       
                        <a href="about.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            <i class="fas fa-info-circle"></i>
                            <span>À propos</span>
                        </a>
                        <a href="contact.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3">
                        <div class="relative group">
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center space-x-2">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($user_firstname); ?></span>
                                <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                                <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-500 hover:text-white">
                                    <i class="fas fa-user-circle mr-2"></i>Mon profil
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-500 hover:text-white">
                                    <i class="fas fa-cog mr-2"></i>Paramètres
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-500 hover:text-white">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu (hidden by default) -->
            <div id="mobileMenu" class="hidden md:hidden bg-white pb-4">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                        <i class="fas fa-home mr-2"></i>Accueil
                    </a>
                    <a href="index.php#products" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                        <i class="fas fa-box mr-2"></i>Produits
                    </a>
                    <a href="about.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                        <i class="fas fa-info-circle mr-2"></i>À propos
                    </a>
                    <a href="contact.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                        <i class="fas fa-envelope mr-2"></i>Contact
                    </a>
                </div>
                <div class="px-2 pt-4 pb-3 border-t border-gray-200">
                    <div class="flex flex-col space-y-2">
                        <span class="px-3 py-2 text-base font-medium text-gray-800">
                            <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($user_firstname . ' ' . $user_lastname); ?>
                        </span>
                        <a href="profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                            <i class="fas fa-user-circle mr-2"></i>Mon profil
                        </a>
                        <a href="settings.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                            <i class="fas fa-cog mr-2"></i>Paramètres
                        </a>
                        <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:text-white hover:bg-red-500">
                            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="pt-24 pb-6 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="bg-white p-2 rounded-full mr-4">
                        <i class="fas fa-user-circle text-5xl text-blue-600"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($user_firstname . ' ' . $user_lastname); ?></h1>
                        <p class="text-blue-100"><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="settings.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-400 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar -->
                <div class="col-span-1">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-blue-600 px-4 py-3">
                            <h3 class="text-white font-medium">Menu</h3>
                        </div>
                        <nav class="px-4 py-2">
                            <a href="profile.php" class="flex items-center space-x-3 px-3 py-3 text-blue-600 bg-blue-50 rounded">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Tableau de bord</span>
                            </a>
                            <a href="mes-commandes.php" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Mes commandes</span>
                            </a>
                            <a href="profile/messages.php" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-envelope"></i>
                                <span>Mes messages</span>
                            </a>
                            <a href="/prjt/energyprjt/calculateur-energetique.php" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-solar-panel"></i>
                                <span>Calculateur Énergétique</span>
                            </a>
                            <a href="settings.php" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-user-edit"></i>
                                <span>Modifier mon profil</span>
                            </a>
                            <a href="settings.php#password" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-lock"></i>
                                <span>Modifier mon mot de passe</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-span-1 md:col-span-3">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-box-open text-blue-600 mr-3"></i>
                            Produits recommandés pour vous
                        </h2>
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <p class="text-gray-600">
                                Découvrez nos produits les plus récents qui pourraient vous intéresser.
                            </p>
                            <a href="/prjt/energyprjt/calculateur-energetique.php" class="mt-3 md:mt-0 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2">
                                <i class="fas fa-calculator"></i>
                                <span>Calculer mes besoins énergétiques</span>
                            </a>
                        </div>

                        <!-- Products Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($products as $product): ?>
                                <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden transform transition duration-300 hover:shadow-lg hover:-translate-y-1">
                                    <div class="relative h-56">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="h-full bg-gradient-to-br from-blue-50 to-gray-50 flex items-center justify-center">
                                                <i class="fas fa-solar-panel text-blue-200 text-5xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['stock'] < 5): ?>
                                            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold shadow-md">
                                                Stock limité
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg font-bold text-gray-900 mb-1 hover:text-blue-600 transition duration-300">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </h3>
                                            <div class="bg-blue-50 text-blue-600 px-2 py-1 rounded-full text-sm font-semibold">
                                                <?php echo number_format($product['price'], 2, ',', ' '); ?> DH
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'Sans catégorie'); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                            <?php 
                                            if (!empty($product['description'])) {
                                                echo htmlspecialchars(substr($product['description'], 0, 120)) . 
                                                    (strlen($product['description']) > 120 ? '...' : '');
                                            } else {
                                                echo 'Aucune description disponible';
                                            }
                                            ?>
                                        </p>
                                        
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center bg-gray-50 px-2 py-1 rounded-full">
                                                <div class="w-2 h-2 rounded-full mr-2 <?php 
                                                    echo $product['stock'] > 0 
                                                        ? ($product['stock'] < 5 ? 'bg-orange-500' : 'bg-green-500')
                                                        : 'bg-red-500'; 
                                                ?>"></div>
                                                <span class="text-xs font-medium <?php 
                                                    echo $product['stock'] > 0
                                                        ? ($product['stock'] < 5 ? 'text-orange-600' : 'text-green-600')
                                                        : 'text-red-600';
                                                ?>">
                                                    <?php 
                                                    if ($product['stock'] > 0) {
                                                        echo $product['stock'] < 5 
                                                            ? $product['stock'] . ' en stock'
                                                            : 'En stock';
                                                    } else {
                                                        echo 'Rupture de stock';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <a href="product.php?id=<?php echo $product['id']; ?>" 
                                            class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye mr-1"></i> Détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="mt-8">
                                <nav class="flex justify-center" aria-label="Pagination">
                                    <ul class="inline-flex items-center space-x-1">
                                        <?php
                                        // Previous page
                                        if ($current_page > 1):
                                            $prev_params = array_merge($url_params, ['page' => $current_page - 1]);
                                        ?>
                                            <li>
                                                <a href="?<?php echo http_build_query($prev_params); ?>"
                                                class="px-3 py-1 rounded-md text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">
                                                    <i class="fas fa-chevron-left text-xs"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Page numbers
                                        $start_page = max(1, min($current_page - 2, $total_pages - 4));
                                        $end_page = min($total_pages, max(5, $current_page + 2));

                                        for ($i = $start_page; $i <= $end_page; $i++):
                                            $page_params = array_merge($url_params, ['page' => $i]);
                                        ?>
                                            <li>
                                                <a href="?<?php echo http_build_query($page_params); ?>"
                                                class="<?php echo $i === $current_page 
                                                    ? 'z-10 bg-blue-600 text-white border-blue-600'
                                                    : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'; ?> relative inline-flex items-center px-3 py-1 border text-sm font-medium rounded-md">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php
                                        // Next page
                                        if ($current_page < $total_pages):
                                            $next_params = array_merge($url_params, ['page' => $current_page + 1]);
                                        ?>
                                            <li>
                                                <a href="?<?php echo http_build_query($next_params); ?>"
                                                class="px-3 py-1 rounded-md text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">
                                                    <i class="fas fa-chevron-right text-xs"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Liens rapides -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="profile/messages.php" class="bg-white shadow rounded-lg p-6 hover:bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-envelope text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Mes messages</h3>
                            <p class="text-sm text-gray-500">Consultez vos messages et contactez l'administration</p>
                        </div>
                        <div class="ml-auto">
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                    </div>
                </a>
                <!-- ... other quick links ... -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Contactez-nous
                    </h3>
                    <div class="space-y-3">
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-phone text-blue-600 mr-3"></i>
                            <a href="tel:+212651441563">+212 651 441 563</a>
                        </p>
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-phone text-blue-600 mr-3"></i>
                            <a href="tel:+212662611909">+212 662 611 909</a>
                        </p>
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-envelope text-blue-600 mr-3"></i>
                            <a href="mailto:elyamanihamza@gmail.com">elyamanihamza@gmail.com</a>
                        </p>
                    </div>
                </div>

                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-2"></i>
                        Notre équipe
                    </h3>
                    <div class="space-y-3">
                        <p class="flex items-center text-gray-600">
                            <i class="fas fa-user text-blue-600 mr-3"></i>
                            Fondateur: Hassan El Yamani
                        </p>
                        <p class="flex items-center text-gray-600">
                            <i class="fas fa-user-tie text-blue-600 mr-3"></i>
                            Chef de projet: Hamza El Yamani
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-link text-blue-600 mr-2"></i>
                        Liens rapides
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="about.php" class="text-gray-600 hover:text-blue-600 transition duration-300 flex items-center">
                                <i class="fas fa-chevron-right mr-2"></i>
                                À propos de nous
                            </a>
                        </li>
                        <li>
                            <a href="contact.php" class="text-gray-600 hover:text-blue-600 transition duration-300 flex items-center">
                                <i class="fas fa-chevron-right mr-2"></i>
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-500">
                    © <?php echo date('Y'); ?> Réparation d'énergie solaire et éolienne - Tous droits réservés
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html> 
<?php
require_once 'config/database.php';

// Activer l'affichage des erreurs en développement uniquement
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Fonction de nettoyage des entrées
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Configuration de la pagination
$products_per_page = 9;
$current_page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Initialisation des paramètres de recherche avec validation
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$category = isset($_GET['category']) ? filter_var($_GET['category'], FILTER_VALIDATE_INT) : '';
$min_price = isset($_GET['min_price']) ? filter_var($_GET['min_price'], FILTER_VALIDATE_FLOAT) : '';
$max_price = isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT) : '';

try {
    // Construction de la requête SQL de base avec des jointures optimisées
    $sql = "SELECT p.*, c.name as category_name, 
            (SELECT COUNT(*) FROM products) as total_count 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id";
    
    $where_conditions = [];
    $params = [];

    // Ajout des conditions de recherche sécurisées
    if (!empty($search)) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if (!empty($category) && $category !== false) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category;
    }

    if (!empty($min_price) && $min_price !== false) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }

    if (!empty($max_price) && $max_price !== false) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }

    // Ajout des conditions WHERE si nécessaire
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // Ajout de l'ordre et de la pagination
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $products_per_page;
    $params[] = $offset;

    // Préparation et exécution de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Calcul du nombre total de pages
    $total_products = !empty($products) ? $products[0]['total_count'] : 0;
    $total_pages = ceil($total_products / $products_per_page);

    // Récupération des catégories pour le filtre
    $categories_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur de base de données : " . $e->getMessage());
    $error_message = "Une erreur est survenue lors de la récupération des produits.";
    $products = [];
    $categories = [];
    $total_pages = 0;
}

// Construction des paramètres de l'URL pour la pagination
$url_params = [];
if (!empty($search)) $url_params['search'] = $search;
if (!empty($category)) $url_params['category'] = $category;
if (!empty($min_price)) $url_params['min_price'] = $min_price;
if (!empty($max_price)) $url_params['max_price'] = $max_price;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarTech - Solutions d'Énergie Solaire</title>
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
                        <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Se connecter</span>
                        </a>
                        <a href="register.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-user-plus"></i>
                            <span>S'inscrire</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu (hidden by default) -->
            <div id="mobileMenu" class="hidden md:hidden bg-white pb-4">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
                        <i class="fas fa-home mr-2"></i>Accueil
                    </a>
                    <a href="#products" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
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
                        <a href="register.php" class="bg-green-600 text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>S'inscrire
                        </a>
                     
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-16 pb-32 flex content-center items-center justify-center min-h-[75vh] bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="absolute top-0 w-full h-full bg-black opacity-50"></div>
        <div class="container relative mx-auto">
            <div class="items-center flex flex-wrap">
                <div class="w-full lg:w-6/12 px-4 ml-auto mr-auto text-center">
                    <div class="text-white">
                        <h1 class="text-5xl font-semibold leading-tight mb-4">
                            Solutions d'Énergie Solaire
                        </h1>
                        <p class="text-xl leading-relaxed mt-4 mb-8">
                            Découvrez notre gamme complète de produits solaires pour une énergie propre et durable. 
                            Investissez dans l'avenir avec nos solutions écologiques.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="#search" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition duration-300 inline-flex items-center space-x-2">
                                <i class="fas fa-search"></i>
                                <span>Découvrir nos produits</span>
                            </a>
                            <a href="register.php" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition duration-300 inline-flex items-center space-x-2">
                                <i class="fas fa-user-plus"></i>
                                <span>S'inscrire</span>
                            </a>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <section id="search" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-search text-blue-600 mr-3"></i>
                    Rechercher des produits
                </h2>
                <form action="index.php#products" method="GET" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Search Input -->
                        <div class="relative">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom ou description
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Rechercher...">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Catégorie
                            </label>
                            <div class="relative">
                                <select id="category" 
                                        name="category"
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label for="min_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Prix minimum (DH)
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="min_price" 
                                       name="min_price" 
                                       value="<?php echo htmlspecialchars($min_price); ?>"
                                       min="0"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Min">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400">DH</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="max_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Prix maximum (DH)
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="max_price" 
                                       name="max_price" 
                                       value="<?php echo htmlspecialchars($max_price); ?>"
                                       min="0"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Max">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400">DH</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="index.php" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-redo-alt mr-2"></i>
                            Réinitialiser
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if ($total_products > 0): ?>
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-box-open text-blue-600 mr-3"></i>
                        Nos Produits
                        <span class="ml-4 text-lg font-normal text-gray-600">
                            (<?php echo $total_products; ?> produits)
                        </span>
                    </h2>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                        <div class="relative h-72">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="h-full bg-gradient-to-br from-blue-50 to-gray-50 flex items-center justify-center">
                                    <i class="fas fa-solar-panel text-blue-200 text-6xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] < 5): ?>
                                <div class="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Stock limité
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-blue-600 transition duration-300">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h3>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-tag mr-2"></i>
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'Sans catégorie'); ?>
                                    </span>
                                </div>
                                <div class="text-2xl font-bold text-blue-600 flex items-center bg-blue-50 px-4 py-2 rounded-full">
                                    <?php echo number_format($product['price'], 2, ',', ' '); ?> DH
                                </div>
                            </div>
                            
                            <p class="text-gray-600 mb-6 line-clamp-3">
                                <?php 
                                if (!empty($product['description'])) {
                                    echo htmlspecialchars(substr($product['description'], 0, 150)) . 
                                        (strlen($product['description']) > 150 ? '...' : '');
                                } else {
                                    echo 'Aucune description disponible';
                                }
                                ?>
                            </p>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center bg-gray-50 px-3 py-1 rounded-full">
                                    <div class="w-3 h-3 rounded-full mr-2 <?php 
                                        echo $product['stock'] > 0 
                                            ? ($product['stock'] < 5 ? 'bg-orange-500' : 'bg-green-500')
                                            : 'bg-red-500'; 
                                    ?>"></div>
                                    <span class="text-sm font-medium <?php 
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
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                                    Voir détails
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($products)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center max-w-2xl mx-auto">
                    <div class="text-blue-200 mb-6">
                        <i class="fas fa-box-open text-8xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">
                        Aucun produit trouvé
                    </h3>
                    <p class="text-gray-600 text-lg mb-8">
                        Désolé, aucun produit ne correspond à vos critères de recherche.
                        Essayez de modifier vos filtres pour trouver ce que vous cherchez.
                    </p>
                    <a href="index.php" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-lg hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-redo-alt mr-2"></i>
                        Réinitialiser les filtres
                    </a>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-12">
                    <nav class="flex justify-center" aria-label="Pagination">
                        <ul class="inline-flex items-center space-x-2">
                            <?php
                            // Previous page
                            if ($current_page > 1):
                                $prev_params = array_merge($url_params, ['page' => $current_page - 1]);
                            ?>
                                <li>
                                    <a href="?<?php echo http_build_query($prev_params); ?>#products"
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:z-20 focus:outline-offset-0 focus:ring-2 focus:ring-blue-500">
                                        <i class="fas fa-chevron-left mr-2"></i>
                                        Précédent
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            // Page numbers
                            $start_page = max(1, min($current_page - 2, $total_pages - 4));
                            $end_page = min($total_pages, max(5, $current_page + 2));

                            // First page
                            if ($start_page > 1):
                                $first_params = array_merge($url_params, ['page' => 1]);
                            ?>
                                <li>
                                    <a href="?<?php echo http_build_query($first_params); ?>#products"
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        1
                                    </a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li>
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                                            ...
                                        </span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php
                            // Page numbers
                            for ($i = $start_page; $i <= $end_page; $i++):
                                $page_params = array_merge($url_params, ['page' => $i]);
                            ?>
                                <li>
                                    <a href="?<?php echo http_build_query($page_params); ?>#products"
                                       class="<?php echo $i === $current_page 
                                            ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                            : 'text-gray-700 bg-white hover:bg-gray-50'; ?> inline-flex items-center px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg focus:z-20 focus:outline-offset-0">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php
                            // Last page
                            if ($end_page < $total_pages):
                                $last_params = array_merge($url_params, ['page' => $total_pages]);
                            ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li>
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                                            ...
                                        </span>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a href="?<?php echo http_build_query($last_params); ?>#products"
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        <?php echo $total_pages; ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            // Next page
                            if ($current_page < $total_pages):
                                $next_params = array_merge($url_params, ['page' => $current_page + 1]);
                            ?>
                                <li>
                                    <a href="?<?php echo http_build_query($next_params); ?>#products"
                                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:z-20 focus:outline-offset-0 focus:ring-2 focus:ring-blue-500">
                                        Suivant
                                        <i class="fas fa-chevron-right ml-2"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold text-white mb-6">
                    Prêt à démarrer votre projet d'énergie solaire ?
                </h2>
                <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                    Rejoignez notre communauté d'utilisateurs et accédez à des offres exclusives pour vos projets d'énergie renouvelable.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="register.php" class="bg-green-600 text-white px-8 py-4 rounded-lg hover:bg-green-700 transition duration-300 inline-flex items-center space-x-2 text-lg font-medium">
                        <i class="fas fa-user-plus"></i>
                        <span>Créer un compte</span>
                    </a>
                    <a href="contact.php" class="bg-white text-blue-700 px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-300 inline-flex items-center space-x-2 text-lg font-medium">
                        <i class="fas fa-envelope"></i>
                        <span>Contactez-nous</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

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
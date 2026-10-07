<?php
require_once 'check_admin.php';
if (function_exists('check_admin_auth')) check_admin_auth();
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
$auth->requireAdmin();

// Traitement de la suppression de produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['delete_product'];
    try {
        // Récupérer l'image du produit avant la suppression
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        // Supprimer l'image si elle existe
        if ($product && $product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }

        // Rediriger pour éviter la resoumission du formulaire
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}

// Vérification et création des tables si elles n'existent pas
try {
    // Création de la table categories si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Création de la table products si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Vérifier si des catégories existent, sinon en créer
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($categoryCount == 0) {
        $pdo->exec("INSERT INTO categories (name, description) VALUES
            ('Panneaux Solaires', 'Panneaux photovoltaïques pour la production d''électricité'),
            ('Batteries', 'Systèmes de stockage d''énergie solaire'),
            ('Onduleurs', 'Convertisseurs pour systèmes solaires'),
            ('Accessoires', 'Câbles, supports et autres accessoires d''installation')
        ");
    }

    // Statistiques avec gestion des erreurs
    try {
        $stats = [
            'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'total_categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'total_value' => $pdo->query("SELECT COALESCE(SUM(price * stock), 0) FROM products")->fetchColumn(),
            'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5")->fetchColumn()
        ];
    } catch (PDOException $e) {
        $stats = [
            'total_products' => 0,
            'total_categories' => 0,
            'total_value' => 0,
            'low_stock' => 0
        ];
    }

    // Récupération des produits avec leurs catégories
    try {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            ORDER BY p.created_at DESC");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }

    // Récupération des produits par catégorie pour le graphique
    try {
        $products_by_category = $pdo->query("SELECT c.name, COUNT(p.id) as count 
                                           FROM categories c 
                                           LEFT JOIN products p ON c.id = p.category_id 
                                           GROUP BY c.id")->fetchAll();
    } catch (PDOException $e) {
        $products_by_category = [];
    }

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Gestion Produits Solaires</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">Tableau de bord</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Tableau de bord</a>
                        <a href="categories.php" class="text-blue-600 hover:text-blue-800">Gérer les catégories</a>
                        <a href="add_product.php" class="text-blue-600 hover:text-blue-800">Gérer les produits</a>

                        <a href="manage_admins.php" class="text-blue-600 hover:text-blue-800">Gérer les administrateurs</a>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Déconnexion</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Produits</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total_products']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Catégories</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total_categories']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Valeur du Stock</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['total_value'], 2, ',', ' ') . ' DH'; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Stock Faible</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['low_stock']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphique -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Répartition des produits par catégorie</h2>
                <canvas id="categoryChart" class="w-full" height="300"></canvas>
            </div>

            <!-- Liste des produits -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Liste des Produits</h2>
                    <a href="add_product.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Ajouter un produit
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valeur du Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Sans catégorie'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo number_format($product['price'], 2, ',', ' ') . ' DH'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $product['stock'] < 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo number_format($product['stock'] * $product['price'], 2, ',', ' ') . ' DH'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-4">Modifier</a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="delete_product" value="<?php echo $product['id']; ?>">
                                        <button type="submit" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"
                                                class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cartes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Carte Messages -->
                <a href="messages.php" class="bg-white overflow-hidden shadow rounded-lg hover:bg-gray-50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Messages</h3>
                                <p class="text-sm text-gray-500">Gérer les messages des utilisateurs</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Carte Produits -->
                <a href="products.php" class="bg-white overflow-hidden shadow rounded-lg hover:bg-gray-50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <i class="fas fa-box text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Produits</h3>
                                <p class="text-sm text-gray-500">Gérer les produits</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Carte Catégories -->
                <a href="categories.php" class="bg-white overflow-hidden shadow rounded-lg hover:bg-gray-50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <i class="fas fa-tags text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Catégories</h3>
                                <p class="text-sm text-gray-500">Gérer les catégories</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
    // Initialisation du graphique
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($products_by_category, 'name')); ?>,
            datasets: [{
                label: 'Nombre de produits',
                data: <?php echo json_encode(array_column($products_by_category, 'count')); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 
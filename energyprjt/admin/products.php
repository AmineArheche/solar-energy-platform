<?php
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
$auth->requireAdmin();

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Paramètres de filtrage et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';

// Validation du tri
$allowed_sort_fields = ['name', 'price', 'stock', 'category_id'];
if (!in_array($sort, $allowed_sort_fields)) {
    $sort = 'name';
}
if (!in_array($order, ['ASC', 'DESC'])) {
    $order = 'ASC';
}

try {
    // Construction de la requête de base
    $query = "FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE 1=1";
    $params = [];

    // Ajout des conditions de recherche
    if (!empty($search)) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if (!empty($category_id)) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    // Compte total des produits
    $count_stmt = $pdo->prepare("SELECT COUNT(*) " . $query);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Récupération des produits
    $query = "SELECT p.*, c.name as category_name " . $query . 
             " ORDER BY p.{$sort} {$order} 
               LIMIT {$items_per_page} OFFSET {$offset}";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Récupération des catégories pour le filtre
    $categories_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des produits : " . $e->getMessage();
}

// Gestion de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['delete_product'];
    try {
        // Récupérer l'image du produit
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        // Supprimer l'image si elle existe
        if ($product && $product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }

        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        header('Location: products.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des produits - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">Gestion des produits</h1>
                    </div>
                    <a href="add_product.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-plus mr-2"></i>Ajouter un produit
                    </a>
                </div>
            </div>
        </header>

        <!-- Messages -->
        <?php if (isset($_GET['deleted'])): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">Le produit a été supprimé avec succès.</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtres et recherche -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom ou description...">
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                        <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700">Trier par</label>
                        <select name="sort" id="sort"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nom</option>
                            <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Prix</option>
                            <option value="stock" <?php echo $sort === 'stock' ? 'selected' : ''; ?>>Stock</option>
                            <option value="category_id" <?php echo $sort === 'category_id' ? 'selected' : ''; ?>>Catégorie</option>
                        </select>
                    </div>

                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700">Ordre</label>
                        <select name="order" id="order"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-search mr-2"></i>Filtrer
                    </button>
                </div>
            </form>

            <!-- Liste des produits -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($product['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="h-16 w-16 object-cover rounded">
                                    <?php else: ?>
                                        <div class="h-16 w-16 bg-gray-100 flex items-center justify-center rounded">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900"><?php echo htmlspecialchars($product['category_name'] ?? 'Non catégorisé'); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900"><?php echo number_format($product['price'], 2, ',', ' '); ?> €</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm <?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>"
                                       class="text-blue-600 hover:text-blue-900 mr-4">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        <input type="hidden" name="delete_product" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-4 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $page === $i ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Confirmation de suppression
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html> 
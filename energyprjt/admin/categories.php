<?php
require_once 'check_admin.php';
if (function_exists('check_admin_auth')) check_admin_auth();
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
$auth->requireAdmin();

$message = '';
$success_message = '';
$error_message = '';

// Paramètres de filtrage et tri
$sort_by = $_GET['sort_by'] ?? 'name';
$sort_order = $_GET['sort_order'] ?? 'asc';
$search = $_GET['search'] ?? '';
$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
$is_featured = isset($_GET['is_featured']) ? $_GET['is_featured'] : null;
$featured_only = isset($_GET['featured_only']) ? true : false;

// Vérification et création des tables si elles n'existent pas
try {
    // Exécuter le script de mise à jour de la base de données
    $update_sql = file_get_contents('../config/update_categories.sql');
    if ($update_sql) {
        $pdo->exec($update_sql);
    }
    
    // Création de la table categories si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        parent_id INT NULL,
        image_url VARCHAR(255) NULL,
        icon VARCHAR(50) NULL,
        is_featured TINYINT(1) DEFAULT 0,
        view_count INT DEFAULT 0,
        click_count INT DEFAULT 0,
        sort_order INT DEFAULT 0,
        slug VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
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
        $pdo->exec("INSERT INTO categories (name, description, slug) VALUES
            ('Panneaux Solaires', 'Panneaux photovoltaïques pour la production d''électricité', 'panneaux-solaires'),
            ('Batteries', 'Systèmes de stockage d''énergie solaire', 'batteries'),
            ('Onduleurs', 'Convertisseurs pour systèmes solaires', 'onduleurs'),
            ('Accessoires', 'Câbles, supports et autres accessoires d''installation', 'accessoires')
        ");
    }
} catch (PDOException $e) {
    $error_message = "Erreur de base de données : " . $e->getMessage();
}

// Traitement de l'ajout/modification/suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $icon = $_POST['icon'] ?? '';
        
        // Générer un slug à partir du nom
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
        
        // Vérifier si le slug existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $slug_exists = $stmt->fetchColumn();
        
        if ($slug_exists) {
            $slug .= '-' . time();
        }
        
        try {
            // Traitement de l'image si elle a été téléchargée
            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/categories/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = $slug . '-' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = 'uploads/categories/' . $file_name;
                }
            }
            
            // Insérer la catégorie
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, parent_id, image_url, icon, is_featured, sort_order, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $parent_id, $image_url, $icon, $is_featured, $sort_order, $slug]);
            
            $category_id = $pdo->lastInsertId();
            
            // Traitement des tags
            if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                foreach ($_POST['tags'] as $tag_name) {
                    // Vérifier si le tag existe déjà
                    $stmt = $pdo->prepare("SELECT id FROM category_tags WHERE name = ?");
                    $stmt->execute([$tag_name]);
                    $tag = $stmt->fetch();
                    
                    if ($tag) {
                        $tag_id = $tag['id'];
                    } else {
                        // Créer un nouveau tag
                        $stmt = $pdo->prepare("INSERT INTO category_tags (name) VALUES (?)");
                        $stmt->execute([$tag_name]);
                        $tag_id = $pdo->lastInsertId();
                    }
                    
                    // Associer le tag à la catégorie
                    $stmt = $pdo->prepare("INSERT INTO category_tag_relations (category_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$category_id, $tag_id]);
                }
            }
            
            // Enregistrer l'historique
            $stmt = $pdo->prepare("INSERT INTO category_history (category_id, user_id, action, new_data) VALUES (?, ?, 'create', ?)");
            $stmt->execute([$category_id, $_SESSION['user_id'], json_encode([
                'name' => $name,
                'description' => $description,
                'parent_id' => $parent_id,
                'is_featured' => $is_featured,
                'sort_order' => $sort_order
            ])]);
            
            $success_message = "Catégorie ajoutée avec succès.";
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'ajout de la catégorie : " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = (int)$_POST['id'];
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $icon = $_POST['icon'] ?? '';
        
        // Générer un nouveau slug si le nom a changé
        $stmt = $pdo->prepare("SELECT name, slug FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $old_category = $stmt->fetch();
        
        $slug = $old_category['slug'];
        if ($name !== $old_category['name']) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            $slug = trim($slug, '-');
            
            // Vérifier si le nouveau slug existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            $slug_exists = $stmt->fetchColumn();
            
            if ($slug_exists) {
                $slug .= '-' . time();
            }
        }
        
        try {
            // Récupérer l'ancienne image
            $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $old_image = $stmt->fetch()['image_url'];
            
            // Traitement de la nouvelle image si elle a été téléchargée
            $image_url = $old_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/categories/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = $slug . '-' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Supprimer l'ancienne image si elle existe
                    if ($old_image && file_exists('../' . $old_image)) {
                        unlink('../' . $old_image);
                    }
                    $image_url = 'uploads/categories/' . $file_name;
                }
            }
            
            // Mettre à jour la catégorie
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, image_url = ?, icon = ?, is_featured = ?, sort_order = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $description, $parent_id, $image_url, $icon, $is_featured, $sort_order, $slug, $id]);
            
            // Mettre à jour les tags
            $stmt = $pdo->prepare("DELETE FROM category_tag_relations WHERE category_id = ?");
            $stmt->execute([$id]);
            
            if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                foreach ($_POST['tags'] as $tag_name) {
                    // Vérifier si le tag existe déjà
                    $stmt = $pdo->prepare("SELECT id FROM category_tags WHERE name = ?");
                    $stmt->execute([$tag_name]);
                    $tag = $stmt->fetch();
                    
                    if ($tag) {
                        $tag_id = $tag['id'];
                    } else {
                        // Créer un nouveau tag
                        $stmt = $pdo->prepare("INSERT INTO category_tags (name) VALUES (?)");
                        $stmt->execute([$tag_name]);
                        $tag_id = $pdo->lastInsertId();
                    }
                    
                    // Associer le tag à la catégorie
                    $stmt = $pdo->prepare("INSERT INTO category_tag_relations (category_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$id, $tag_id]);
                }
            }
            
            // Enregistrer l'historique
            $stmt = $pdo->prepare("INSERT INTO category_history (category_id, user_id, action, new_data) VALUES (?, ?, 'update', ?)");
            $stmt->execute([$id, $_SESSION['user_id'], json_encode([
                'name' => $name,
                'description' => $description,
                'parent_id' => $parent_id,
                'is_featured' => $is_featured,
                'sort_order' => $sort_order
            ])]);
            
            $success_message = "Catégorie mise à jour avec succès.";
        } catch (PDOException $e) {
            $error_message = "Erreur lors de la mise à jour de la catégorie : " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['id'];
        
        try {
            // Vérifier si la catégorie a des produits
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            $product_count = $stmt->fetchColumn();
            
            if ($product_count > 0) {
                $error_message = "Impossible de supprimer cette catégorie car elle contient des produits.";
            } else {
                // Récupérer l'image avant la suppression
                $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $image_url = $stmt->fetch()['image_url'];
                
                // Supprimer les relations avec les tags
                $stmt = $pdo->prepare("DELETE FROM category_tag_relations WHERE category_id = ?");
                $stmt->execute([$id]);
                
                // Supprimer la catégorie
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                
                // Supprimer l'image si elle existe
                if ($image_url && file_exists('../' . $image_url)) {
                    unlink('../' . $image_url);
                }
                
                $success_message = "Catégorie supprimée avec succès.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur lors de la suppression de la catégorie : " . $e->getMessage();
        }
    }
}

// Récupérer toutes les catégories avec leurs statistiques
$query = "SELECT c.*, 
          p.name as parent_name,
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
          (SELECT COUNT(*) FROM category_tag_relations WHERE category_id = c.id) as tag_count
          FROM categories c 
          LEFT JOIN categories p ON c.parent_id = p.id";

// Ajouter les conditions de filtrage
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($parent_id)) {
    $where_conditions[] = "c.parent_id = ?";
    $params[] = $parent_id;
}

if ($is_featured !== null && $is_featured !== '') {
    $where_conditions[] = "c.is_featured = ?";
    $params[] = $is_featured;
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Ajouter le tri
$sort_by = in_array($sort_by, ['name', 'product_count', 'tag_count', 'is_featured', 'sort_order']) ? $sort_by : 'name';
$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
$query .= " ORDER BY c." . $sort_by . " " . $sort_order;

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des catégories : " . $e->getMessage();
    $categories = [];
}

// Récupérer toutes les catégories pour le menu déroulant
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$all_categories = $stmt->fetchAll();

// Récupérer tous les tags
$stmt = $pdo->query("SELECT * FROM category_tags ORDER BY name");
$all_tags = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des catégories</h1>
                    <div class="flex space-x-4">
                        <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord
                        </a>
                        <button onclick="showAddCategoryModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-plus mr-2"></i>Nouvelle catégorie
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Messages de succès/erreur -->
        <?php if (isset($success_message) && !empty($success_message)): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message) && !empty($error_message)): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700">Catégorie parente</label>
                        <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Toutes</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $parent_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="is_featured" class="block text-sm font-medium text-gray-700">Mise en avant</label>
                        <select name="is_featured" id="is_featured" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Toutes</option>
                            <option value="1" <?php echo $is_featured === '1' ? 'selected' : ''; ?>>Oui</option>
                            <option value="0" <?php echo $is_featured === '0' ? 'selected' : ''; ?>>Non</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-search mr-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des catégories -->
        <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?sort_by=name&sort_order=<?php echo $sort_by === 'name' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="flex items-center">
                                    Nom
                                    <?php if ($sort_by === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?> ml-2"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?sort_by=product_count&sort_order=<?php echo $sort_by === 'product_count' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="flex items-center">
                                    Produits
                                    <?php if ($sort_by === 'product_count'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?> ml-2"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?sort_by=tag_count&sort_order=<?php echo $sort_by === 'tag_count' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="flex items-center">
                                    Tags
                                    <?php if ($sort_by === 'tag_count'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?> ml-2"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?sort_by=is_featured&sort_order=<?php echo $sort_by === 'is_featured' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="flex items-center">
                                    Mise en avant
                                    <?php if ($sort_by === 'is_featured'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?> ml-2"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?sort_by=sort_order&sort_order=<?php echo $sort_by === 'sort_order' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="flex items-center">
                                    Ordre
                                    <?php if ($sort_by === 'sort_order'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?> ml-2"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($category['image_url']): ?>
                                            <img class="h-10 w-10 rounded-full object-cover" src="../<?php echo htmlspecialchars($category['image_url']); ?>" alt="">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-folder text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </div>
                                            <?php if ($category['parent_name']): ?>
                                                <div class="text-sm text-gray-500">
                                                    Parent: <?php echo htmlspecialchars($category['parent_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $category['product_count']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $category['tag_count']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($category['is_featured']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Oui
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Non
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $category['sort_order']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="showEditCategoryModal(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDeleteCategory(<?php echo $category['id']; ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de catégorie -->
    <div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Nouvelle catégorie</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="parent_id" class="block text-sm font-medium text-gray-700">Catégorie parente</label>
                        <select name="parent_id" id="parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Aucune</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="mt-1 block w-full">
                    </div>
                    <div class="mb-4">
                        <label for="icon" class="block text-sm font-medium text-gray-700">Icône (classe FontAwesome)</label>
                        <input type="text" name="icon" id="icon" placeholder="ex: fas fa-folder"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                        <select name="tags[]" id="tags" multiple
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($all_tags as $tag): ?>
                                <option value="<?php echo htmlspecialchars($tag['name']); ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Mise en avant</span>
                        </label>
                    </div>
                    <div class="mb-4">
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordre de tri</label>
                        <input type="number" name="sort_order" id="sort_order" value="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAddCategoryModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Annuler
                        </button>
                        <button type="submit" name="add_category"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de modification de catégorie -->
    <div id="editCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Modifier la catégorie</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-4">
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" id="edit_name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="edit_description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="edit_parent_id" class="block text-sm font-medium text-gray-700">Catégorie parente</label>
                        <select name="parent_id" id="edit_parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Aucune</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_image" class="block text-sm font-medium text-gray-700">Image</label>
                        <input type="file" name="image" id="edit_image" accept="image/*"
                               class="mt-1 block w-full">
                        <div id="current_image" class="mt-2"></div>
                    </div>
                    <div class="mb-4">
                        <label for="edit_icon" class="block text-sm font-medium text-gray-700">Icône (classe FontAwesome)</label>
                        <input type="text" name="icon" id="edit_icon" placeholder="ex: fas fa-folder"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_tags" class="block text-sm font-medium text-gray-700">Tags</label>
                        <select name="tags[]" id="edit_tags" multiple
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($all_tags as $tag): ?>
                                <option value="<?php echo htmlspecialchars($tag['name']); ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" id="edit_is_featured"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Mise en avant</span>
                        </label>
                    </div>
                    <div class="mb-4">
                        <label for="edit_sort_order" class="block text-sm font-medium text-gray-700">Ordre de tri</label>
                        <input type="number" name="sort_order" id="edit_sort_order"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideEditCategoryModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Annuler
                        </button>
                        <button type="submit" name="edit_category"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            Modifier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulaire de suppression -->
    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="id" id="delete_id">
        <input type="hidden" name="delete_category" value="1">
    </form>

    <script>
        // Fonctions pour les modals
        function showAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }

        function hideAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
        }

        function showEditCategoryModal(category) {
            document.getElementById('edit_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description;
            document.getElementById('edit_parent_id').value = category.parent_id || '';
            document.getElementById('edit_icon').value = category.icon;
            document.getElementById('edit_is_featured').checked = category.is_featured == 1;
            document.getElementById('edit_sort_order').value = category.sort_order;

            // Afficher l'image actuelle
            const currentImage = document.getElementById('current_image');
            if (category.image_url) {
                currentImage.innerHTML = `<img src="../${category.image_url}" alt="Image actuelle" class="h-20 w-20 object-cover rounded">`;
            } else {
                currentImage.innerHTML = '';
            }

            // Sélectionner les tags existants
            const tagSelect = document.getElementById('edit_tags');
            const existingTags = category.tags ? category.tags.split(',') : [];
            Array.from(tagSelect.options).forEach(option => {
                option.selected = existingTags.includes(option.value);
            });

            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        function hideEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }

        function confirmDeleteCategory(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html> 
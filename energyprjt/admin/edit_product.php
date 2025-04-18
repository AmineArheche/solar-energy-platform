<?php
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
$auth->requireAdmin();

$success_message = '';
$error_message = '';

// Récupération de l'ID du produit
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Récupération des informations du produit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération du produit : " . $e->getMessage();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    try {
        // Traitement de l'image
        $image_url = $product['image_url']; // Garder l'ancienne image par défaut
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Format d\'image non autorisé. Formats acceptés : JPG, JPEG, PNG, GIF');
            }

            $file_name = uniqid('product_') . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Supprimer l'ancienne image si elle existe
                if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
                    unlink('../' . $product['image_url']);
                }
                $image_url = 'uploads/products/' . $file_name;
            } else {
                throw new Exception('Erreur lors du téléchargement de l\'image');
            }
        }

        // Validation des données
        if (empty($name)) {
            throw new Exception('Le nom du produit est requis');
        }
        if ($price <= 0) {
            throw new Exception('Le prix doit être supérieur à 0');
        }
        if ($stock < 0) {
            throw new Exception('Le stock ne peut pas être négatif');
        }

        // Mise à jour du produit
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $image_url, $product_id]);

        $success_message = "Le produit a été mis à jour avec succès.";
        
        // Redirection vers la liste des produits après 2 secondes
        header("refresh:2;url=products.php");
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Récupération des catégories pour le menu déroulant
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Modifier un produit</h1>
                    <a href="products.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </header>

        <!-- Messages de succès/erreur -->
        <?php if (!empty($success_message)): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom du produit</label>
                        <input type="text" name="name" id="name" required
                               value="<?php echo htmlspecialchars($product['name']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Prix</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">€</span>
                                </div>
                                <input type="number" name="price" id="price" required step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($product['price']); ?>"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" name="stock" id="stock" required min="0"
                                   value="<?php echo htmlspecialchars($product['stock']); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                            <select name="category_id" id="category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Image du produit</label>
                        <?php if ($product['image_url']): ?>
                            <div class="mt-2 mb-4">
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="Image actuelle" 
                                     class="h-32 w-32 object-cover rounded-lg">
                                <p class="mt-1 text-sm text-gray-500">Image actuelle</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Formats acceptés : JPG, JPEG, PNG, GIF</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="products.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Annuler</a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
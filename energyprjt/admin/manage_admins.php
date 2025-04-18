<?php
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!$auth->isLoggedIn() || !$auth->isAdminUser()) {
    header('Location: ../login.php');
    exit();
}

// Traitement de l'ajout d'un administrateur
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
        $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            $error_message = "Tous les champs sont obligatoires.";
        } else {
            try {
                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $error_message = "Cet email est déjà utilisé.";
                } else {
                    // Créer le nouvel administrateur
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password, is_admin) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$firstname, $lastname, $email, $hashed_password, 1]);
                    
                    $success_message = "Administrateur ajouté avec succès.";
                }
            } catch (PDOException $e) {
                $error_message = "Erreur lors de l'ajout de l'administrateur : " . $e->getMessage();
            }
        }
    }
    
    // Traitement de la suppression d'un administrateur
    if (isset($_POST['delete_admin'])) {
        $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);
        
        if ($admin_id) {
            try {
                // Empêcher la suppression de son propre compte
                if ($admin_id == $_SESSION['user_id']) {
                    $error_message = "Vous ne pouvez pas supprimer votre propre compte.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 1");
                    $stmt->execute([$admin_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $success_message = "Administrateur supprimé avec succès.";
                    } else {
                        $error_message = "Administrateur non trouvé ou vous n'avez pas les droits pour le supprimer.";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Erreur lors de la suppression de l'administrateur : " . $e->getMessage();
            }
        }
    }
}

// Récupérer tous les administrateurs
try {
    $stmt = $pdo->query("SELECT id, firstname, lastname, email, created_at FROM users WHERE is_admin = 1 ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des administrateurs : " . $e->getMessage();
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Administrateurs - SolarTech</title>
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
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-700 hover:text-blue-600">Tableau de bord</a>
                    <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-user-shield mr-3"></i>
                        Gestion des Administrateurs
                    </h2>
                    <p class="text-blue-100 mt-2">
                        Ajoutez ou supprimez des administrateurs pour votre site
                    </p>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Formulaire d'ajout d'administrateur -->
                <div class="p-8 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ajouter un administrateur</h3>
                    <form method="POST" action="manage_admins.php" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="firstname" 
                                       name="firstname" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="lastname" 
                                       name="lastname" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="pt-2">
                            <button type="submit" 
                                    name="add_admin"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300">
                                <i class="fas fa-user-plus mr-2"></i>Ajouter l'administrateur
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Liste des administrateurs -->
                <div class="p-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Liste des administrateurs</h3>
                    
                    <?php if (empty($admins)): ?>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-gray-500">Aucun administrateur trouvé.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($admin['firstname'] . ' ' . $admin['lastname']); ?>
                                        <?php if ($admin['id'] == $_SESSION['user_id']): ?>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Vous
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($admin['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="manage_admins.php" class="inline">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" 
                                                    name="delete_admin"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')"
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt mr-1"></i>Supprimer
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
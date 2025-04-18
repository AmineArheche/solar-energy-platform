<?php
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

// Gestion du thème
if (isset($_POST['update_theme'])) {
    $theme = $_POST['theme'] ?? 'light';
    $_SESSION['theme'] = $theme;
    
    // Rediriger pour éviter la résoumission du formulaire
    header("Location: settings.php?theme_updated=1");
    exit;
}

// Traitement du formulaire de mise à jour du profil
$success_message = "";
$error_message = "";

// Si un thème a été mis à jour, afficher un message de succès
if (isset($_GET['theme_updated']) && $_GET['theme_updated'] == 1) {
    $success_message = "Thème mis à jour avec succès";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Récupérer et nettoyer les données du formulaire
        $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING));
        $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        
        // Valider l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format d'email invalide";
        } else {
            try {
                // Vérifier si l'email existe déjà (uniquement si l'email a changé)
                if ($email !== $user_email) {
                    $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $check_stmt->execute([$email, $user_id]);
                    if ($check_stmt->rowCount() > 0) {
                        $error_message = "Cet email est déjà utilisé par un autre compte";
                    }
                }
                
                // Si pas d'erreur, mettre à jour les informations
                if (empty($error_message)) {
                    $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
                    $result = $stmt->execute([$firstname, $lastname, $email, $user_id]);
                    
                    if ($result) {
                        // Mettre à jour les données de session
                        $_SESSION['user_firstname'] = $firstname;
                        $_SESSION['user_lastname'] = $lastname;
                        $_SESSION['user_email'] = $email;
                        
                        // Mettre à jour les variables locales
                        $user_firstname = $firstname;
                        $user_lastname = $lastname;
                        $user_email = $email;
                        
                        $success_message = "Profil mis à jour avec succès";
                    } else {
                        $error_message = "Erreur lors de la mise à jour du profil";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Erreur de base de données: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Récupérer les données du formulaire
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Vérifier que les champs requis sont remplis
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Tous les champs de mot de passe sont requis";
        } 
        // Vérifier que le nouveau mot de passe et la confirmation correspondent
        elseif ($new_password !== $confirm_password) {
            $error_message = "Le nouveau mot de passe et sa confirmation ne correspondent pas";
        } 
        // Vérifier que le nouveau mot de passe est suffisamment fort
        elseif (strlen($new_password) < 8) {
            $error_message = "Le nouveau mot de passe doit contenir au moins 8 caractères";
        } else {
            try {
                // Vérifier le mot de passe actuel
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch();
                
                if ($user_data && password_verify($current_password, $user_data['password'])) {
                    // Hasher le nouveau mot de passe
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Mettre à jour le mot de passe
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $result = $update_stmt->execute([$hashed_password, $user_id]);
                    
                    if ($result) {
                        $success_message = "Mot de passe mis à jour avec succès";
                    } else {
                        $error_message = "Erreur lors de la mise à jour du mot de passe";
                    }
                } else {
                    $error_message = "Mot de passe actuel incorrect";
                }
            } catch (PDOException $e) {
                $error_message = "Erreur de base de données: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="<?php echo isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - RéparationÉnergie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // Couleurs personnalisées si nécessaire
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dark {
            color-scheme: dark;
        }
        .dark body {
            background-color: #1a202c;
            color: #e2e8f0;
        }
        .dark .bg-white {
            background-color: #2d3748;
        }
        .dark .text-gray-700, .dark .text-gray-800, .dark .text-gray-900 {
            color: #e2e8f0;
        }
        .dark .text-gray-600 {
            color: #cbd5e0;
        }
        .dark .text-gray-500 {
            color: #a0aec0;
        }
        .dark .border-gray-200, .dark .border-gray-300 {
            border-color: #4a5568;
        }
        .dark .bg-gray-50 {
            background-color: #2d3748;
        }
        .dark .bg-blue-50 {
            background-color: #2c5282;
        }
        .dark .text-blue-600 {
            color: #63b3ed;
        }
        .dark .hover\:bg-blue-50:hover {
            background-color: #2c5282;
        }
        .dark .bg-green-100 {
            background-color: #276749;
        }
        .dark .text-green-700 {
            color: #9ae6b4;
        }
        .dark .bg-red-100 {
            background-color: #742a2a;
        }
        .dark .text-red-700 {
            color: #feb2b2;
        }
        .dark .shadow-md, .dark .shadow-lg, .dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .dark input, .dark select, .dark textarea {
            background-color: #4a5568;
            border-color: #718096;
            color: #e2e8f0;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: #63b3ed;
        }
    </style>
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
                        <a href="index.php#products" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            <i class="fas fa-box"></i>
                            <span>Produits</span>
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

    <!-- Settings Header -->
    <div class="pt-24 pb-6 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="bg-white p-2 rounded-full mr-4">
                        <i class="fas fa-cogs text-5xl text-blue-600"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-3xl font-bold">Paramètres du compte</h1>
                        <p class="text-blue-100">Personnalisez votre expérience</p>
                    </div>
                </div>
                <div>
                    <a href="profile.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-user-circle"></i>
                        <span>Retour au profil</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (!empty($success_message)): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar -->
                <div class="col-span-1">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-blue-600 px-4 py-3">
                            <h3 class="text-white font-medium">Options</h3>
                        </div>
                        <nav class="px-4 py-2">
                            <a href="#profile-info" class="flex items-center space-x-3 px-3 py-3 text-blue-600 bg-blue-50 rounded">
                                <i class="fas fa-user-edit"></i>
                                <span>Informations personnelles</span>
                            </a>
                            <a href="#security" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-lock"></i>
                                <span>Sécurité</span>
                            </a>
                            <a href="#preferences" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-sliders-h"></i>
                                <span>Préférences</span>
                            </a>
                            <a href="profile.php" class="flex items-center space-x-3 px-3 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                                <i class="fas fa-arrow-left"></i>
                                <span>Retour au profil</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-span-1 md:col-span-3">
                    <!-- Profile Information -->
                    <div id="profile-info" class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-edit text-blue-600 mr-3"></i>
                            Informations personnelles
                        </h2>
                        
                        <form action="settings.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                                    <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($user_firstname); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                    <input type="text" name="lastname" id="lastname" value="<?php echo htmlspecialchars($user_lastname); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_email); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="update_profile" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-300 flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Settings -->
                    <div id="security" class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-lock text-blue-600 mr-3"></i>
                            Sécurité
                        </h2>
                        
                        <form action="settings.php" method="POST" class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                                    <input type="password" name="new_password" id="new_password"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           minlength="8"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Le mot de passe doit contenir au moins 8 caractères</p>
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           minlength="8"
                                           required>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="update_password" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-300 flex items-center">
                                    <i class="fas fa-key mr-2"></i>
                                    Mettre à jour le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Preferences -->
                    <div id="preferences" class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-sliders-h text-blue-600 mr-3"></i>
                            Préférences
                        </h2>
                        
                        <form action="settings.php" method="POST" class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-800 mb-2">Thème d'affichage</h3>
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="theme" value="light" <?php echo (!isset($_SESSION['theme']) || $_SESSION['theme'] === 'light') ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <span class="text-gray-700">Clair</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="theme" value="dark" <?php echo (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark') ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <span class="text-gray-700">Sombre</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-800 mb-2">Notifications</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                                        <span class="text-gray-700">Promotions et offres spéciales</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                                        <span class="text-gray-700">Nouvelles sur les produits</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                                        <span class="text-gray-700">Mises à jour du système</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="update_theme" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-300 flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    Enregistrer les préférences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-10">
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
        
        // Smooth scroll to sections when clicking on sidebar links
        document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Update active link in sidebar
                    document.querySelectorAll('nav a').forEach(link => {
                        link.classList.remove('text-blue-600', 'bg-blue-50');
                        link.classList.add('text-gray-700', 'hover:bg-blue-50', 'hover:text-blue-600');
                    });
                    
                    this.classList.remove('text-gray-700', 'hover:bg-blue-50', 'hover:text-blue-600');
                    this.classList.add('text-blue-600', 'bg-blue-50');
                }
            });
        });
        
        // Thème sombre/clair
        document.querySelectorAll('input[name="theme"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            });
        });
    </script>
</body>
</html> 
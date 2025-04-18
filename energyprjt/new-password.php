<?php
session_start();
require_once 'config/database.php';

// Initialisation des variables
$message = '';
$error = '';
$valid_token = false;
$token = $_GET['token'] ?? '';

// Vérifier si le token est valide
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if ($reset) {
            $valid_token = true;
        } else {
            $error = "Ce lien de réinitialisation est invalide ou a expiré.";
        }
    } catch (PDOException $e) {
        $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
        error_log("Erreur lors de la vérification du token: " . $e->getMessage());
    }
} else {
    $error = "Token manquant. Veuillez utiliser le lien envoyé dans l'email.";
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation du mot de passe
    if (empty($password) || empty($password_confirm)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        try {
            // Récupérer l'email associé au token
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                // Hacher le nouveau mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe dans la base de données
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);
                
                // Supprimer tous les tokens de réinitialisation pour cet utilisateur
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$reset['email']]);
                
                $message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.";
            } else {
                $error = "Une erreur est survenue. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
            error_log("Erreur lors de la mise à jour du mot de passe: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - RéparationÉnergie</title>
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
                <div class="hidden md:flex items-center space-x-8">
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
                    <a href="register.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-user-plus"></i>
                        <span>S'inscrire</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- New Password Form -->
    <div class="pt-24 pb-16">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-lock mr-3"></i>
                        Créer un nouveau mot de passe
                    </h2>
                    <p class="text-blue-100 mt-2">
                        Veuillez choisir un nouveau mot de passe sécurisé
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $message; ?></p>
                            <p class="mt-2">
                                <a href="login.php" class="text-green-700 font-medium hover:underline">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Se connecter
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($valid_token && empty($message)): ?>
                <form method="POST" class="py-8 px-8 space-y-6">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nouveau mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Votre nouveau mot de passe">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Le mot de passe doit contenir au moins 8 caractères.</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Confirmez votre mot de passe">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer le nouveau mot de passe
                        </button>
                    </div>
                </form>
                <?php elseif (empty($message)): ?>
                <div class="py-8 px-8 text-center">
                    <a href="reset-password.php" class="text-blue-600 hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Retour à la page de réinitialisation
                    </a>
                </div>
                <?php endif; ?>
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
</body>
</html> 
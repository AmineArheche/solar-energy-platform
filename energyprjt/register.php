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

// Variables pour stocker les erreurs et les valeurs soumises
$errors = [];
$submitted_data = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'password' => '',
    'password_confirm' => ''
];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données soumises
    $submitted_data['firstname'] = cleanInput($_POST['firstname'] ?? '');
    $submitted_data['lastname'] = cleanInput($_POST['lastname'] ?? '');
    $submitted_data['email'] = cleanInput($_POST['email'] ?? '');
    $submitted_data['password'] = $_POST['password'] ?? '';
    $submitted_data['password_confirm'] = $_POST['password_confirm'] ?? '';
    
    // Validation du formulaire
    if (empty($submitted_data['firstname'])) {
        $errors['firstname'] = "Le prénom est requis";
    }
    
    if (empty($submitted_data['lastname'])) {
        $errors['lastname'] = "Le nom est requis";
    }
    
    if (empty($submitted_data['email'])) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($submitted_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    } else {
        // Vérifier si l'email existe déjà
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$submitted_data['email']]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors['email'] = "Cet email est déjà utilisé";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de la vérification de l'email";
        }
    }
    
    if (empty($submitted_data['password'])) {
        $errors['password'] = "Le mot de passe est requis";
    } elseif (strlen($submitted_data['password']) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    if ($submitted_data['password'] !== $submitted_data['password_confirm']) {
        $errors['password_confirm'] = "Les mots de passe ne correspondent pas";
    }
    
    // Si aucune erreur, créer l'utilisateur
    if (empty($errors)) {
        try {
            // Vérifier si la table users existe, sinon la créer
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                firstname VARCHAR(100) NOT NULL,
                lastname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Hacher le mot de passe
            $hashed_password = password_hash($submitted_data['password'], PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([
                $submitted_data['firstname'],
                $submitted_data['lastname'],
                $submitted_data['email'],
                $hashed_password
            ]);
            
            if ($success) {
                // Rediriger vers une page de succès ou la page de connexion
                header("Location: index.php?register=success");
                exit;
            } else {
                $errors['general'] = "Erreur lors de la création du compte";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - RéparationÉnergie</title>
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
                    <a href="register.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2 mr-2">
                        <i class="fas fa-user-plus"></i>
                        <span>S'inscrire</span>
                    </a>
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Se connecter</span>
                        </a>
                 
                </div>
            </div>
        </div>
    </nav>

    <!-- Registration Form Section -->
    <div class="pt-24 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-user-plus mr-3"></i>
                        Créer un compte
                    </h2>
                    <p class="text-blue-100 mt-2">
                        Rejoignez-nous pour accéder à des offres exclusives et suivre vos commandes
                    </p>
                </div>

                <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $errors['general']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="py-8 px-8 space-y-6">
                    <!-- First Name and Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-2">
                                Prénom <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="firstname" 
                                       name="firstname" 
                                       value="<?php echo htmlspecialchars($submitted_data['firstname']); ?>"
                                       class="w-full pl-10 pr-4 py-2 border <?php echo isset($errors['firstname']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Votre prénom">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (isset($errors['firstname'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['firstname']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="lastname" 
                                       name="lastname" 
                                       value="<?php echo htmlspecialchars($submitted_data['lastname']); ?>"
                                       class="w-full pl-10 pr-4 py-2 border <?php echo isset($errors['lastname']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Votre nom">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (isset($errors['lastname'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['lastname']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($submitted_data['email']); ?>"
                                   class="w-full pl-10 pr-4 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="votre.email@exemple.com">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password and Confirm Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full pl-10 pr-4 py-2 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Minimum 8 caractères">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['password']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmer le mot de passe <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       class="w-full pl-10 pr-4 py-2 border <?php echo isset($errors['password_confirm']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Confirmez votre mot de passe">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (isset($errors['password_confirm'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['password_confirm']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Terms and conditions -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   required
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-gray-700">
                                J'accepte les <a href="conditions.php" class="text-blue-600 hover:underline">conditions d'utilisation</a> et la <a href="confidentialite.php" class="text-blue-600 hover:underline">politique de confidentialité</a>
                            </label>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-300 flex items-center justify-center">
                            <i class="fas fa-user-plus mr-2"></i>
                            Créer mon compte
                        </button>
                    </div>

                    <div class="text-center mt-6">
                        <p class="text-gray-600">
                            Vous avez déjà un compte? <a href="login.php" class="text-blue-600 hover:underline">Se connecter</a>
                        </p>
                    </div>
                </form>
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
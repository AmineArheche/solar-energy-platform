<?php
require_once 'config/database.php';

session_start();

// Fonction de validation des entrées
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialisation des variables
$message = '';
$success = false;
$errors = [];

// Génération d'un nouveau token CSRF à chaque chargement du formulaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        // Validation et nettoyage des entrées
        $name = validateInput($_POST['name'] ?? '');
        $email = validateInput($_POST['email'] ?? '');
        $subject = validateInput($_POST['subject'] ?? '');
        $content = validateInput($_POST['message'] ?? '');

        // Validation des champs
        if (empty($name)) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen($name) > 255) {
            $errors[] = "Le nom est trop long (maximum 255 caractères).";
        }

        if (empty($email)) {
            $errors[] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        } elseif (strlen($email) > 255) {
            $errors[] = "L'email est trop long (maximum 255 caractères).";
        }

        if (empty($subject)) {
            $errors[] = "Le sujet est obligatoire.";
        } elseif (strlen($subject) > 255) {
            $errors[] = "Le sujet est trop long (maximum 255 caractères).";
        }

        if (empty($content)) {
            $errors[] = "Le message est obligatoire.";
        }

        // Protection contre le spam
        if (preg_match('/http|www|[<>]/', $content)) {
            $errors[] = "Le contenu du message contient des caractères non autorisés.";
        }

        // Si pas d'erreurs, enregistrement du message
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $subject, $content]);
                $success = true;
                $message = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
                
                // Réinitialisation du formulaire après succès
                $name = $email = $subject = $content = '';
                
                // Génération d'un nouveau token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (PDOException $e) {
                error_log("Erreur lors de l'envoi du message de contact : " . $e->getMessage());
                $errors[] = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer plus tard.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - SolarTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-sun text-2xl text-blue-600"></i>
                        <i class="fas fa-wind text-2xl text-blue-600"></i>
                        <span class="text-xl font-bold text-gray-800">Réparation<span class="text-blue-600">Énergie</span></span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i>
                        Accueil
                    </a>
                   
                    <a href="about.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-info-circle mr-1"></i>
                        À propos
                    </a>
                    <a href="contact.php" class="text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-envelope mr-1"></i>
                        Contact
                    </a>
                    <a href="register.php" class="bg-green-600 text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>S'inscrire
                        </a>
                       
                      
                </div>
                <div class="md:hidden flex items-center">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-home mr-1"></i>
                    Accueil
                </a>
                <a href="index.php#products" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-box mr-1"></i>
                    Produits
                </a>
                <a href="about.php" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-info-circle mr-1"></i>
                    À propos
                </a>
                <a href="contact.php" class="text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-envelope mr-1"></i>
                    Contact
                </a>
                <a href="admin/login.php" class="bg-blue-600 text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-user mr-1"></i>
                    Espace administrateur
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-4xl font-bold text-gray-900 mb-8 text-center">Contactez-nous</h1>

            <?php if ($message): ?>
                <div class="mb-8 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Informations de contact -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Nos coordonnées</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Adresse</h3>
                                    <p class="text-gray-600">
                                        Rés. AL ATLAS, Avenue Hassan II<br>
                                        Imouzzer-Kandar 31250<br>
                                        Maroc
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-phone text-blue-600 text-xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Téléphone</h3>
                                    <p class="text-gray-600">
                                        +212 651 441 563<br>
                                        +212 662 611 909
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-envelope text-blue-600 text-xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Email</h3>
                                    <p class="text-gray-600">elyamanihamza@gmail.com</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-users text-blue-600 text-xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Notre équipe</h3>
                                    <p class="text-gray-600">
                                        Fondateur: Hassan El Yamani<br>
                                        Chef de projet: Hamza El Yamani
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Envoyez-nous un message</h2>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom complet *</label>
                                <input type="text" name="name" id="name" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Sujet *</label>
                                <input type="text" name="subject" id="subject" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Message *</label>
                                <textarea name="message" id="message" rows="4" required
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <button type="submit"
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html> 
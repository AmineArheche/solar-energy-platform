<?php
session_start();
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si la connexion à la base de données est établie
if (!isset($conn) || !$conn) {
    die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
}

// Récupérer les messages de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = "SELECT m.*, u.first_name, u.last_name 
          FROM messages m 
          JOIN users u ON m.sender_id = u.id 
          WHERE m.receiver_id = ? 
          ORDER BY m.created_at DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Erreur lors de la préparation de la requête : " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête : " . $stmt->error);
}
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Traitement de l'envoi d'un nouveau message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    
    $errors = [];
    if (empty($receiver_id)) $errors[] = "Le destinataire est requis";
    if (empty($subject)) $errors[] = "Le sujet est requis";
    if (empty($content)) $errors[] = "Le message est requis";
    
    if (empty($errors)) {
        $query = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Erreur lors de la préparation de la requête : " . $conn->error);
        }
        $stmt->bind_param("iiss", $user_id, $receiver_id, $subject, $content);
        if ($stmt->execute()) {
            $success = "Message envoyé avec succès";
            // Recharger la page pour afficher le nouveau message
            header("Location: messages.php");
            exit();
        } else {
            $errors[] = "Erreur lors de l'envoi du message : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages - RéparationÉnergie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-xl font-bold text-blue-600">RéparationÉnergie</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="profile.php" class="text-gray-700 hover:text-blue-600 px-3 py-2">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                    <a href="mes-commandes.php" class="text-gray-700 hover:text-blue-600 px-3 py-2">
                        <i class="fas fa-shopping-bag"></i> Mes Commandes
                    </a>
                    <a href="messages.php" class="text-gray-700 hover:text-blue-600 px-3 py-2">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a href="logout.php" class="text-gray-700 hover:text-blue-600 px-3 py-2">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Mes Messages</h1>
                <button id="newMessageBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i> Nouveau Message
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Liste des messages -->
            <div class="space-y-4">
                <?php if (empty($messages)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Vous n'avez aucun message</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($message['subject']); ?></h3>
                                    <p class="text-sm text-gray-600">
                                        De: <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                </span>
                            </div>
                            <p class="mt-2 text-gray-700"><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Nouveau Message -->
    <div id="newMessageModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="fixed inset-0 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Nouveau Message</h3>
                        <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700">Destinataire</label>
                            <select name="receiver_id" id="receiver_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner un destinataire</option>
                                <?php
                                // Récupérer la liste des utilisateurs (sauf l'utilisateur actuel)
                                $query = "SELECT id, first_name, last_name FROM users WHERE id != ?";
                                $stmt = $conn->prepare($query);
                                if ($stmt) {
                                    $stmt->bind_param("i", $user_id);
                                    if ($stmt->execute()) {
                                        $result = $stmt->get_result();
                                        while ($user = $result->fetch_assoc()) {
                                            echo '<option value="' . $user['id'] . '">' . 
                                                 htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . 
                                                 '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Sujet</label>
                            <input type="text" name="subject" id="subject" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="content" id="content" rows="4" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" id="cancelMessage" 
                                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Annuler
                            </button>
                            <button type="submit" name="send_message"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact</h3>
                    <p class="text-gray-600">
                        <i class="fas fa-phone mr-2"></i> +33 1 23 45 67 89<br>
                        <i class="fas fa-envelope mr-2"></i> contact@reparationenergie.fr
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Notre Équipe</h3>
                    <p class="text-gray-600">
                        <i class="fas fa-user mr-2"></i> Jean Dupont - Fondateur<br>
                        <i class="fas fa-user mr-2"></i> Marie Martin - Chef de Projet
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Liens Rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-600 hover:text-blue-600">À propos</a></li>
                        <li><a href="contact.php" class="text-gray-600 hover:text-blue-600">Contact</a></li>
                        <li><a href="confidentialite.php" class="text-gray-600 hover:text-blue-600">Confidentialité</a></li>
                        <li><a href="conditions.php" class="text-gray-600 hover:text-blue-600">Conditions</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-8 pt-8 text-center text-gray-600">
                <p>&copy; <?php echo date('Y'); ?> RéparationÉnergie. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Gestion de la fenêtre modale
        const modal = document.getElementById('newMessageModal');
        const newMessageBtn = document.getElementById('newMessageBtn');
        const closeModal = document.getElementById('closeModal');
        const cancelMessage = document.getElementById('cancelMessage');

        newMessageBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeModal.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        cancelMessage.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Fermer la modale en cliquant en dehors
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    </script>
</body>
</html> 
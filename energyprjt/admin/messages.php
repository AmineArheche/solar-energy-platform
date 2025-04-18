<?php
require_once '../config/database.php';
require_once 'check_admin.php';

// Vérifier si la connexion à la base de données est établie
if (!isset($conn) || !$conn) {
    die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $message_id = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
        
        switch ($_POST['action']) {
            case 'delete':
                // Supprimer le message
                $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
                if (!$stmt) {
                    die("Erreur lors de la préparation de la requête : " . $conn->error);
                }
                $stmt->bind_param("i", $message_id);
                if (!$stmt->execute()) {
                    die("Erreur lors de l'exécution de la requête : " . $stmt->error);
                }
                $success_message = "Message supprimé avec succès.";
                break;
                
            case 'reply':
                // Envoyer une réponse
                $reply = filter_var($_POST['reply'], FILTER_SANITIZE_STRING);
                $stmt = $conn->prepare("UPDATE messages SET status = 'answered', admin_response = ?, updated_at = NOW() WHERE id = ?");
                if (!$stmt) {
                    die("Erreur lors de la préparation de la requête : " . $conn->error);
                }
                $stmt->bind_param("si", $reply, $message_id);
                if (!$stmt->execute()) {
                    die("Erreur lors de l'exécution de la requête : " . $stmt->error);
                }
                $success_message = "Réponse envoyée avec succès.";
                break;
                
            case 'mute':
                // Mettre l'utilisateur en sourdine
                $stmt = $conn->prepare("UPDATE users SET is_muted = 1 WHERE id = (SELECT user_id FROM messages WHERE id = ?)");
                $stmt->bind_param("i", $message_id);
                $stmt->execute();
                $success_message = "Utilisateur mis en sourdine.";
                break;
        }
    }
}

// Récupérer tous les messages avec les informations des utilisateurs
$query = "
    SELECT m.*, 
           u1.firstname as sender_first_name, 
           u1.lastname as sender_last_name,
           u2.firstname as receiver_first_name, 
           u2.lastname as receiver_last_name
    FROM messages m
    JOIN users u1 ON m.user_id = u1.id
    JOIN users u2 ON m.user_id = u2.id
    ORDER BY m.created_at DESC
";

$result = $conn->query($query);
if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . $conn->error);
}
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <?php include 'includes/admin_nav.php'; ?>

        <!-- Main Content -->
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des Messages</h1>
                </div>

                <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <!-- Messages List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expéditeur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <i class="fas fa-user-circle text-3xl text-gray-400"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <i class="fas fa-user-circle text-3xl text-gray-400"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($message['receiver_first_name'] . ' ' . $message['receiver_last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($message['subject']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" name="action" value="delete" 
                                                    class="text-red-600 hover:text-red-900 mr-3">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de réponse -->
    <div id="replyModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" name="message_id" id="replyMessageId">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Répondre au message
                                </h3>
                                <div class="mt-2">
                                    <textarea name="reply" rows="4" 
                                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                              placeholder="Votre réponse..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Envoyer la réponse
                        </button>
                        <button type="button" 
                                onclick="hideReplyModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showReplyModal(messageId) {
            document.getElementById('replyMessageId').value = messageId;
            document.getElementById('replyModal').classList.remove('hidden');
        }

        function hideReplyModal() {
            document.getElementById('replyModal').classList.add('hidden');
        }
    </script>
</body>
</html> 
<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté
$auth->requireLogin();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $message_id = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
        
        switch ($_POST['action']) {
            case 'delete':
                // Supprimer le message
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
                $stmt->execute([$message_id, $_SESSION['user_id']]);
                $success_message = "Message supprimé avec succès.";
                break;
                
            case 'withdraw':
                // Vérifier si la colonne is_visible existe
                $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'is_visible'");
                $columnExists = $stmt->rowCount() > 0;

                if ($columnExists) {
                    // Retirer le message (le rendre invisible)
                    $stmt = $pdo->prepare("UPDATE messages SET is_visible = 0 WHERE id = ? AND user_id = ?");
                    $stmt->execute([$message_id, $_SESSION['user_id']]);
                    $success_message = "Message retiré avec succès.";
                } else {
                    // Si la colonne n'existe pas, supprimer le message
                    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
                    $stmt->execute([$message_id, $_SESSION['user_id']]);
                    $success_message = "Message supprimé avec succès.";
                }
                break;
        }
    }
}

// Récupérer les messages de l'utilisateur
try {
    // Vérifier si la colonne is_visible existe
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'is_visible'");
    $columnExists = $stmt->rowCount() > 0;

    if ($columnExists) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.firstname, u.lastname, u.email 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.user_id = ? AND m.is_visible = 1
            ORDER BY m.created_at DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT m.*, u.firstname, u.lastname, u.email 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.user_id = ?
            ORDER BY m.created_at DESC
        ");
    }
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des messages : " . $e->getMessage());
    $messages = [];
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
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <?php include '../includes/nav.php'; ?>

        <!-- Main Content -->
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Mes Messages</h1>
                    <a href="../contact.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Nouveau message
                    </a>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        </div>
                                        <?php if (isset($message['admin_response']) && !empty($message['admin_response'])): ?>
                                        <div class="mt-2 text-sm text-gray-500 bg-gray-50 p-2 rounded">
                                            <strong>Réponse :</strong><br>
                                            <?php echo nl2br(htmlspecialchars($message['admin_response'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (isset($message['status']) && $message['status'] === 'answered'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Répondu
                                        </span>
                                        <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            En attente
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <!-- Bouton Retirer -->
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="withdraw">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                                    <i class="fas fa-eye-slash"></i> Retirer
                                                </button>
                                            </form>
                                            
                                            <!-- Bouton Supprimer -->
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
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
</body>
</html> 
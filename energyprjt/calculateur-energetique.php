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

// Initialisation des variables
$devices = [];
$total_consumption_wh = 0;
$calculation_done = false;

// Traitement de l'ajout d'un appareil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_device"])) {
    $device_name = $_POST["device_name"];
    $device_power = floatval($_POST["device_power"]);
    $device_hours = floatval($_POST["device_hours"]);
    $device_quantity = intval($_POST["device_quantity"]);
    
    // Calcul de la consommation journalière de l'appareil
    $daily_consumption = $device_power * $device_hours * $device_quantity;
    
    // Ajout de l'appareil à la liste
    $device = [
        "name" => $device_name,
        "power" => $device_power,
        "hours" => $device_hours,
        "quantity" => $device_quantity,
        "consumption" => $daily_consumption
    ];
    
    // Récupération des appareils existants s'il y en a
    if (isset($_POST["saved_devices"])) {
        $devices = json_decode($_POST["saved_devices"], true);
    }
    
    $devices[] = $device;
    $total_consumption_wh = array_sum(array_column($devices, "consumption"));
}

// Supprimer un appareil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_device"])) {
    if (isset($_POST["saved_devices"])) {
        $devices = json_decode($_POST["saved_devices"], true);
        $device_index = intval($_POST["device_index"]);
        
        if (isset($devices[$device_index])) {
            array_splice($devices, $device_index, 1);
            $total_consumption_wh = array_sum(array_column($devices, "consumption"));
        }
    }
}

// Réinitialiser la liste des appareils
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_devices"])) {
    $devices = [];
    $total_consumption_wh = 0;
}

// Vérification de la soumission du formulaire de calcul
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["calculate_solar"])) {
    // Récupération des appareils existants
    if (isset($_POST["saved_devices"])) {
        $devices = json_decode($_POST["saved_devices"], true);
        $total_consumption_wh = array_sum(array_column($devices, "consumption"));
    }
    
    // Récupération des paramètres solaires
    $heures_ensoleillement = floatval($_POST["heures_ensoleillement"]);
    $efficacite_panneau = floatval($_POST["efficacite_panneau"]);
    $pertes_systeme = floatval($_POST["pertes_systeme"]);
    $tension_systeme = floatval($_POST["tension_systeme"]);
    $jours_autonomie = intval($_POST["jours_autonomie"]);
    $profondeur_decharge = floatval($_POST["profondeur_decharge"]);
    
    // Conversion de Wh en kWh
    $consommation_quotidienne = $total_consumption_wh / 1000;
    
    // Calcul de la puissance nécessaire des panneaux solaires
    $puissance_necessaire = ($total_consumption_wh) / ($heures_ensoleillement * ($efficacite_panneau / 100) * (1 - $pertes_systeme / 100));
    
    // Calcul du nombre de panneaux (en supposant des panneaux standards de 300W)
    $puissance_panneau = 300; // Watts
    $nombre_panneaux = ceil($puissance_necessaire / $puissance_panneau);
    
    // Calcul de la capacité de batterie nécessaire
    $capacite_batterie = ($total_consumption_wh * $jours_autonomie) / 
                        ($tension_systeme * ($profondeur_decharge / 100));
    
    // Préparation des résultats
    $resultat = true;
    $calculation_done = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculateur Énergétique - RéparationÉnergie</title>
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
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-blue-500 hover:text-white">
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
                        <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-white hover:bg-blue-600">
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

    <!-- Calculateur Header -->
    <div class="pt-24 pb-6 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-4">Calculateur de besoins énergétiques solaires</h1>
            <p class="text-blue-100 max-w-3xl mx-auto">
                Estimez la puissance des panneaux solaires et la capacité de batterie nécessaires pour votre installation énergétique
            </p>
        </div>
    </div>

    <!-- Calculateur Content -->
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Content -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <!-- Section 1: Gestion des appareils -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-plug text-blue-600 mr-2"></i>
                        1. Ajouter des appareils électriques
                    </h2>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="device_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom de l'appareil:
                                </label>
                                <input type="text" 
                                       id="device_name" 
                                       name="device_name" 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div>
                                <label for="device_power" class="block text-sm font-medium text-gray-700 mb-2">
                                    Puissance (Watts):
                                </label>
                                <input type="number" 
                                       step="0.1" 
                                       id="device_power" 
                                       name="device_power" 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div>
                                <label for="device_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                    Heures d'utilisation par jour:
                                </label>
                                <input type="number" 
                                       step="0.1" 
                                       id="device_hours" 
                                       name="device_hours" 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div>
                                <label for="device_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantité:
                                </label>
                                <input type="number" 
                                       id="device_quantity" 
                                       name="device_quantity" 
                                       value="1" 
                                       min="1" 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>
                        
                        <input type="hidden" name="saved_devices" value="<?php echo htmlspecialchars(json_encode($devices)); ?>">
                        <div class="flex justify-center">
                            <button type="submit" name="add_device" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 flex items-center">
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter l'appareil
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!empty($devices)): ?>
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Liste des appareils</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appareil</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puissance (W)</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heures/jour</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consommation (Wh)</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($devices as $index => $device): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($device["name"]); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($device["power"]); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($device["hours"]); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($device["quantity"]); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($device["consumption"]); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                                        <input type="hidden" name="device_index" value="<?php echo $index; ?>">
                                                        <input type="hidden" name="saved_devices" value="<?php echo htmlspecialchars(json_encode($devices)); ?>">
                                                        <button type="submit" name="delete_device" class="text-red-600 hover:text-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900">Consommation totale:</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo $total_consumption_wh; ?> Wh</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo number_format($total_consumption_wh / 1000, 2); ?> kWh</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="mt-4 flex justify-end">
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <button type="submit" name="reset_devices" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-300 flex items-center">
                                        <i class="fas fa-redo mr-2"></i>
                                        Réinitialiser la liste
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section 2: Calcul des besoins solaires -->
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sun text-blue-600 mr-2"></i>
                        2. Calculer les besoins solaires
                    </h2>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="heures_ensoleillement" class="block text-sm font-medium text-gray-700 mb-2">
                                    Heures d'ensoleillement par jour:
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           step="0.1" 
                                           id="heures_ensoleillement" 
                                           name="heures_ensoleillement" 
                                           value="5"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-sun text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Nombre moyen d'heures d'ensoleillement dans votre région</p>
                            </div>

                            <div>
                                <label for="efficacite_panneau" class="block text-sm font-medium text-gray-700 mb-2">
                                    Efficacité des panneaux solaires (%):
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           step="0.1" 
                                           id="efficacite_panneau" 
                                           name="efficacite_panneau" 
                                           value="18" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">L'efficacité typique des panneaux est entre 15% et 22%</p>
                            </div>

                            <div>
                                <label for="pertes_systeme" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pertes du système (%):
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           step="0.1" 
                                           id="pertes_systeme" 
                                           name="pertes_systeme" 
                                           value="15" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-arrow-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Pertes dues aux câbles, à l'onduleur, etc.</p>
                            </div>

                            <div>
                                <label for="tension_systeme" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tension du système (Volts):
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           id="tension_systeme" 
                                           name="tension_systeme" 
                                           value="12" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-plug text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Généralement 12V, 24V ou 48V</p>
                            </div>

                            <div>
                                <label for="jours_autonomie" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jours d'autonomie de la batterie:
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           id="jours_autonomie" 
                                           name="jours_autonomie" 
                                           value="2" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-day text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Nombre de jours que vos batteries peuvent alimenter le système sans soleil</p>
                            </div>

                            <div>
                                <label for="profondeur_decharge" class="block text-sm font-medium text-gray-700 mb-2">
                                    Profondeur de décharge maximale de la batterie (%):
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           id="profondeur_decharge" 
                                           name="profondeur_decharge" 
                                           value="80" 
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-battery-half text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Pourcentage jusqu'auquel les batteries peuvent être déchargées sans dommage</p>
                            </div>

                            <div>
                                <label for="consommation_quotidienne" class="block text-sm font-medium text-gray-700 mb-2">
                                    Consommation d'énergie quotidienne (kWh/jour):
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           id="consommation_quotidienne" 
                                           name="consommation_quotidienne" 
                                           value="<?php echo number_format($total_consumption_wh / 1000, 2); ?>"
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
                                           readonly>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-bolt text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Calculé automatiquement à partir des appareils ajoutés</p>
                            </div>
                        </div>

                        <input type="hidden" name="saved_devices" value="<?php echo htmlspecialchars(json_encode($devices)); ?>">
                        <div class="pt-4 flex justify-center">
                            <button type="submit" name="calculate_solar" class="bg-blue-600 text-white py-3 px-8 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 flex items-center" <?php echo empty($devices) ? 'disabled' : ''; ?>>
                                <i class="fas fa-calculator mr-2"></i>
                                Calculer
                            </button>
                        </div>
                    </form>
                </div>

                <?php if (isset($resultat) && $resultat): ?>
                <div class="mt-12 bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                        Résultats du calcul
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-lg font-semibold text-gray-700 mb-2">Puissance nécessaire</div>
                            <div class="text-3xl font-bold text-blue-600"><?php echo round($puissance_necessaire, 2); ?> <span class="text-sm font-normal">Watts</span></div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-lg font-semibold text-gray-700 mb-2">Nombre de panneaux</div>
                            <div class="text-3xl font-bold text-blue-600"><?php echo $nombre_panneaux; ?> <span class="text-sm font-normal">panneaux de 300W</span></div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-lg font-semibold text-gray-700 mb-2">Capacité de batterie</div>
                            <div class="text-3xl font-bold text-blue-600"><?php echo round($capacite_batterie, 2); ?> <span class="text-sm font-normal">Ah à <?php echo $tension_systeme; ?>V</span></div>
                        </div>
                    </div>
                    <div class="mt-6 text-gray-600 text-sm">
                        <p class="mb-2"><i class="fas fa-info-circle text-blue-500 mr-2"></i> Ces résultats sont des estimations. Pour une installation réelle, consultez un professionnel.</p>
                        <p><i class="fas fa-lightbulb text-yellow-500 mr-2"></i> Conseil: N'oubliez pas de tenir compte des variations saisonnières d'ensoleillement dans votre région.</p>
                    </div>
                </div>

                <!-- Détails du calcul -->
                <div class="mt-8 bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-calculator text-blue-600 mr-2"></i>
                        Détails du calcul
                    </h2>

                    <!-- Données entrées -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">✅ Données entrées</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paramètre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valeur</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Consommation quotidienne</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($consommation_quotidienne, 2); ?> kWh/jour</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Heures d'ensoleillement</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $heures_ensoleillement; ?> h</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Efficacité des panneaux</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $efficacite_panneau; ?> %</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pertes du système</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $pertes_systeme; ?> %</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tension du système</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $tension_systeme; ?> V</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Autonomie batterie</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $jours_autonomie; ?> jours</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Profondeur de décharge (DOD)</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $profondeur_decharge; ?> %</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Calcul de la puissance des panneaux -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">📌 1. Puissance des panneaux solaires nécessaires</h3>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <p class="text-sm text-gray-700 mb-2"><strong>Formule :</strong></p>
                            <p class="text-sm text-gray-700 mb-2">Puissance panneau (W) = Consommation quotidienne (Wh) / (Heures d'ensoleillement × Efficacité × (1 - Pertes))</p>
                        </div>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p>1. Consommation quotidienne : <?php echo number_format($consommation_quotidienne, 2); ?> kWh = <?php echo $total_consumption_wh; ?> Wh</p>
                            <p>2. Efficacité : <?php echo $efficacite_panneau; ?>% = <?php echo $efficacite_panneau/100; ?></p>
                            <p>3. Pertes : <?php echo $pertes_systeme; ?>% = <?php echo $pertes_systeme/100; ?> → (1 - <?php echo $pertes_systeme/100; ?>) = <?php echo (1 - $pertes_systeme/100); ?></p>
                            <p>4. Calcul : <?php echo $total_consumption_wh; ?> / (<?php echo $heures_ensoleillement; ?> × <?php echo $efficacite_panneau/100; ?> × <?php echo (1 - $pertes_systeme/100); ?>) = <?php echo round($puissance_necessaire, 2); ?> W</p>
                        </div>
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-700"><strong>✅ Résultat :</strong> Il vous faut environ <strong><?php echo round($puissance_necessaire, 2); ?> W</strong> de panneaux, soit environ <strong><?php echo $nombre_panneaux; ?> panneaux de 300W</strong>.</p>
                        </div>
                    </div>

                    <!-- Calcul de la capacité des batteries -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">🔋 2. Capacité des batteries nécessaires</h3>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <p class="text-sm text-gray-700 mb-2"><strong>Formule :</strong></p>
                            <p class="text-sm text-gray-700 mb-2">Capacité batterie (Wh) = (Consommation quotidienne × jours autonomie) / Profondeur de décharge</p>
                            <p class="text-sm text-gray-700">Capacité (Ah) = Capacité (Wh) / Tension du système</p>
                        </div>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p>1. Calcul en Wh : (<?php echo $total_consumption_wh; ?> × <?php echo $jours_autonomie; ?>) / <?php echo $profondeur_decharge/100; ?> = <?php echo round($total_consumption_wh * $jours_autonomie / ($profondeur_decharge/100), 2); ?> Wh</p>
                            <p>2. Conversion en Ah : <?php echo round($total_consumption_wh * $jours_autonomie / ($profondeur_decharge/100), 2); ?> / <?php echo $tension_systeme; ?> = <?php echo round($capacite_batterie, 2); ?> Ah</p>
                        </div>
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-700"><strong>✅ Résultat :</strong> Il vous faut une batterie (ou plusieurs en série/parallèle) de <strong><?php echo $tension_systeme; ?>V et <?php echo round($capacite_batterie, 2); ?> Ah</strong>.</p>
                        </div>
                    </div>

                    <!-- Résumé des résultats -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">🛠 Résumé des résultats du calcul</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Composant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Résultat approximatif</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détail</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Puissance panneau</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600"><?php echo round($puissance_necessaire, 2); ?> W</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">Pour générer <?php echo number_format($consommation_quotidienne, 2); ?> kWh/jour</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Capacité batterie</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600"><?php echo $tension_systeme; ?>V – <?php echo round($capacite_batterie, 2); ?> Ah</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">Pour tenir <?php echo $jours_autonomie; ?> jours sans soleil</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Autonomie</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600"><?php echo $jours_autonomie; ?> jours</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">Selon votre saisie</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Efficacité globale</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600"><?php echo round($efficacite_panneau * (1 - $pertes_systeme/100), 1); ?> %</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">Prend en compte les pertes</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-8 text-center">
                <a href="profile.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour à mon profil
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-10">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="border-t border-gray-200 pt-8 text-center">
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
    </script>
</body>
</html> 
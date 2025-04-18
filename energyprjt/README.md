# Solar Products - Système de Gestion des Produits Solaires

## Description
Solar Products est une plateforme web complète pour la gestion et la vente de produits solaires. Le système permet aux utilisateurs de parcourir, commander et gérer leurs produits solaires, tandis que les administrateurs peuvent gérer l'inventaire, les commandes et les messages.

## Fonctionnalités Principales

### Pour les Utilisateurs
1. **Authentification**
   - Inscription et connexion des utilisateurs
   - Gestion du profil utilisateur
   - Système de mot de passe sécurisé

2. **Catalogue de Produits**
   - Affichage des produits solaires disponibles
   - Filtrage par catégorie
   - Recherche de produits
   - Détails complets des produits

3. **Gestion des Commandes**
   - Création de commandes
   - Suivi des commandes en cours
   - Historique des commandes
   - Statut des commandes

4. **Système de Messagerie**
   - Envoi de messages à l'administrateur
   - Réception des réponses
   - Interface de messagerie intuitive

### Pour les Administrateurs
1. **Gestion des Produits**
   - Ajout de nouveaux produits
   - Modification des produits existants
   - Gestion des stocks
   - Catégorisation des produits

2. **Gestion des Commandes**
   - Visualisation de toutes les commandes
   - Mise à jour du statut des commandes
   - Gestion des livraisons

3. **Gestion des Utilisateurs**
   - Liste des utilisateurs
   - Détails des utilisateurs
   - Gestion des permissions

4. **Système de Messagerie**
   - Réception des messages des utilisateurs
   - Envoi de réponses
   - Gestion des conversations

## Structure du Projet
```
energyprjt/
├── admin/                  # Interface d'administration
│   ├── products.php        # Gestion des produits
│   ├── orders.php          # Gestion des commandes
│   ├── users.php           # Gestion des utilisateurs
│   └── messages.php        # Gestion des messages
├── config/
│   └── database.php        # Configuration de la base de données
├── includes/               # Fichiers inclus
│   ├── header.php          # En-tête commun
│   └── footer.php          # Pied de page commun
├── assets/                 # Ressources statiques
│   ├── css/                # Feuilles de style
│   ├── js/                 # Scripts JavaScript
│   └── images/             # Images du site
├── index.php               # Page d'accueil
├── products.php            # Catalogue des produits
├── cart.php                # Panier d'achat
├── checkout.php            # Processus de paiement
├── profile.php             # Profil utilisateur
├── orders.php              # Commandes utilisateur
├── messages.php            # Messagerie
├── login.php               # Connexion
├── register.php            # Inscription
└── README.md               # Documentation
```

## Technologies Utilisées
- **Frontend**
  - HTML5
  - CSS3 (Tailwind CSS)
  - JavaScript
  - Font Awesome pour les icônes

- **Backend**
  - PHP 7.4+
  - MySQL/MariaDB
  - mysqli pour la connexion à la base de données

- **Sécurité**
  - Protection contre les injections SQL
  - Validation des données
  - Gestion sécurisée des sessions
  - Protection CSRF

## Installation
1. Cloner le dépôt
2. Configurer la base de données dans `config/database.php`
3. Importer le fichier SQL de la base de données
4. Configurer le serveur web (Apache/Nginx)
5. Accéder au site via l'URL configurée

## Configuration de la Base de Données
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'solar_products');
define('DB_CHARSET', 'utf8mb4');
```

## Fonctionnalités Détaillées

### Système de Messagerie
- Interface utilisateur intuitive
- Envoi de messages à l'administrateur
- Réception des réponses
- Historique des conversations
- Notifications en temps réel

### Gestion des Commandes
- Processus de commande en 4 étapes
- Validation des stocks
- Calcul automatique des prix
- Suivi des commandes
- Notifications par email

### Catalogue de Produits
- Affichage par catégories
- Filtres avancés
- Recherche par mots-clés
- Détails techniques des produits
- Galerie d'images

### Interface d'Administration
- Tableau de bord complet
- Statistiques en temps réel
- Gestion des stocks
- Rapports détaillés
- Export de données

## Sécurité
- Protection contre les injections SQL
- Validation des données côté serveur
- Gestion sécurisée des sessions
- Protection CSRF
- Chiffrement des mots de passe
- Limitation des tentatives de connexion

## Performance
- Optimisation des requêtes SQL
- Mise en cache des données
- Compression des ressources
- Chargement différé des images
- Minification des fichiers CSS/JS

## Maintenance
- Journalisation des erreurs
- Sauvegarde automatique
- Mise à jour des stocks
- Nettoyage des données
- Optimisation de la base de données

## Support
Pour toute question ou problème, veuillez contacter l'administrateur via le système de messagerie intégré.

## Licence
Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.


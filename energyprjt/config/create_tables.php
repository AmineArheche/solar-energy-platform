<?php
require_once 'database.php';

try {
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");

    // Check if categories exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $categoriesCount = $stmt->fetchColumn();

    if ($categoriesCount == 0) {
        // Insert default categories
        $pdo->exec("INSERT INTO categories (name, description) VALUES 
            ('Panneaux Solaires', 'Panneaux photovoltaïques de haute qualité'),
            ('Batteries', 'Solutions de stockage d\'énergie'),
            ('Onduleurs', 'Convertisseurs solaires'),
            ('Accessoires', 'Accessoires et pièces de montage')");
        
        echo "Catégories créées avec succès.<br>";
    }

    // Check if products exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $productsCount = $stmt->fetchColumn();

    if ($productsCount == 0) {
        // Insert sample products
        $pdo->exec("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES 
            (1, 'Panneau Solaire 400W', 'Panneau solaire monocristallin haute performance', 299.99, 10, 'images/products/panel.jpg'),
            (2, 'Batterie Lithium 5kWh', 'Batterie de stockage lithium-ion', 2499.99, 5, 'images/products/battery.jpg'),
            (3, 'Onduleur 5000W', 'Onduleur solaire hybride', 1299.99, 8, 'images/products/inverter.jpg')");
        
        echo "Produits de test créés avec succès.<br>";
    }

    echo "Configuration de la base de données terminée.";
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?> 
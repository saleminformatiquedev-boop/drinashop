<?php
require_once __DIR__ . '/includes/db.php';

try {
    echo "<h1>Mise à jour de la Base de Données</h1>";

    // 1. Ajouter la colonne customer_comment à orders
    try {
        $db->exec("ALTER TABLE orders ADD COLUMN customer_comment TEXT DEFAULT ''");
        echo "<p>✅ Colonne 'customer_comment' ajoutée avec succès à la table 'orders'.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "<p>⚠️ La colonne 'customer_comment' existe déjà.</p>";
        } else {
            echo "<p>❌ Erreur (customer_comment) : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // 2. Ajouter la colonne stock à products
    try {
        $db->exec("ALTER TABLE products ADD COLUMN stock INTEGER DEFAULT 0");
        echo "<p>✅ Colonne 'stock' ajoutée avec succès à la table 'products'.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "<p>⚠️ La colonne 'stock' existe déjà.</p>";
        } else {
            echo "<p>❌ Erreur (stock) : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // 3. Ajouter la colonne extra_images à products
    try {
        $db->exec("ALTER TABLE products ADD COLUMN extra_images TEXT DEFAULT '[]'");
        echo "<p>✅ Colonne 'extra_images' ajoutée avec succès à la table 'products'.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "<p>⚠️ La colonne 'extra_images' existe déjà.</p>";
        } else {
            echo "<p>❌ Erreur (extra_images) : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    echo "<br><p><strong style='color:red;'>Important :</strong> Veuillez supprimer ce fichier (update_db.php) du serveur pour des raisons de sécurité.</p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur Générale</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

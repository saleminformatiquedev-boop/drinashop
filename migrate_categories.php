<?php
require_once __DIR__ . '/includes/db.php';

echo "<h1>Migration des catégories...</h1>";

try {
    // 1. Add category column if not exists
    $columns = $db->query("PRAGMA table_info(products)")->fetchAll(PDO::FETCH_ASSOC);
    $hasCategory = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'category') {
            $hasCategory = true;
            break;
        }
    }
    
    if (!$hasCategory) {
        $db->exec("ALTER TABLE products ADD COLUMN category TEXT");
        echo "<p>Colonne 'category' ajoutée.</p>";
    } else {
        echo "<p>Colonne 'category' déjà présente.</p>";
    }

    // 2. Fetch products and extract category from image path
    $stmt = $db->query("SELECT id, image FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $db->prepare("UPDATE products SET category = ? WHERE id = ?");
    $count = 0;
    
    foreach ($products as $p) {
        $img_path = $p['image'];
        if ($img_path && strpos($img_path, 'produits/') !== false) {
            $parts = explode('produits/', $img_path);
            if (count($parts) > 1) {
                $rest = $parts[1];
                $cat_folder = explode('/', $rest)[0];
                
                // Nettoyer les chiffres à la fin (ex: "Couffins 2" -> "Couffins")
                $cat_clean = preg_replace('/\s*\d+$/', '', $cat_folder);
                $cat_clean = trim($cat_clean);
                
                if (!empty($cat_clean)) {
                    $updateStmt->execute([$cat_clean, $p['id']]);
                    $count++;
                }
            }
        }
    }
    
    echo "<p>✅ Succès ! $count produits ont été mis à jour avec leur nouvelle catégorie.</p>";
    echo "<p><strong style='color:red;'>Veuillez supprimer ce fichier (migrate_categories.php) du serveur après utilisation.</strong></p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

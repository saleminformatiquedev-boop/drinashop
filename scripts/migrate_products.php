<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csv_parser.php';

echo "Début de la migration des produits...\n";

try {
    $db->beginTransaction();

    $products = get_all_products();
    $stmt = $db->prepare("INSERT OR IGNORE INTO products (id, title, description, price, image) VALUES (?, ?, ?, ?, ?)");

    $count = 0;
    foreach ($products as $p) {
        $stmt->execute([$p['id'], $p['title'], $p['description'], $p['price'], $p['image']]);
        $count++;
    }

    $db->commit();
    echo "Migration réussie ! $count produits insérés.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
}

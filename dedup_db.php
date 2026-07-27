<?php
require_once __DIR__ . '/includes/db.php';

echo "<h1>Suppression des produits en double (par image)...</h1>";

try {
    $ids_to_delete = ['10821', '10837', '10841', '10847', '10848', '10850', '10857', '10859', '10863', '10865', '10868', '10870', '10872', '10880', '10886', '10888', '10896', '10905', '10921', '10923', '10925', '10927', '10950', '10954', '10956', '10958', '10960', '10962', '10966', '10968', '10982', '10987', '10989', '10998', '11000', '11005', '11015', '11017', '11019', '11021', '11023', '11029', '11031', '11033', '11037', '11039', '11041', '11043', '11047', '11052', '11053', '11055', '11057', '11059', '11061', '11063', '11065', '11075', '11076', '11078', '11080', '11084', '11086', '11088', '11090', '11092', '11094', '11096', '11100', '11102', '11104', '11106', '11109', '11110', '11112', '11114', '11116', '11119', '11121', '11123'];
    
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $count = 0;
    foreach ($ids_to_delete as $id) {
        $stmt->execute([$id]);
        $count++;
    }
    
    echo "<p>✅ Succès ! $count produits en double ont été supprimés.</p>";
    echo "<p><strong style='color:red;'>Veuillez supprimer ce fichier (dedup_db.php) du serveur.</strong></p>";
    
} catch (Exception $e) {
    echo "<h1>Erreur</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

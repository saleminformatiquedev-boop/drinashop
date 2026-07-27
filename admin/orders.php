<?php
require_once __DIR__ . '/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    $success = "Statut de la commande #$order_id mis à jour avec succès.";
}

$stmt = $db->query("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 3rem 2rem; min-height: 80vh; max-width: 1200px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem; color: #fbbf24;">Gestion des Commandes</h1>

    <?php if (isset($success)): ?>
        <div style="background: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; flex-direction: column; gap: 2rem;">
        <?php foreach ($orders as $order): ?>
            <div id="order-<?= $order['id'] ?>" class="card">
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <div>
                            <h2 style="margin-bottom: 0.5rem;">Commande #<?= $order['id'] ?></h2>
                            <p style="color: #94a3b8; font-size: 0.9rem;">Passée le <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
                            <?php if ($order['user_id']): ?>
                                <p style="color: #94a3b8; font-size: 0.9rem;">Client : <?= htmlspecialchars($order['user_name']) ?> (<?= htmlspecialchars($order['user_email']) ?>)</p>
                            <?php else: ?>
                                <p style="color: #94a3b8; font-size: 0.9rem;">Client : Invité</p>
                            <?php endif; ?>
                        </div>
                        <a href="print_order.php?id=<?= $order['id'] ?>" target="_blank" style="background: rgba(255,255,255,0.1); color: #fff; padding: 0.5rem 1rem; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; height: fit-content;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            Imprimer
                        </a>
                    </div>
                    
                    <form method="POST" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 0.5rem; border-radius: 5px; flex-grow: 1;">
                            <option value="En attente" <?= $order['status'] === 'En attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="Expédiée" <?= $order['status'] === 'Expédiée' ? 'selected' : '' ?>>Expédiée</option>
                            <option value="Livrée" <?= $order['status'] === 'Livrée' ? 'selected' : '' ?>>Livrée</option>
                            <option value="Annulée" <?= $order['status'] === 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                        <button type="submit" style="background: var(--primary-color); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; flex-grow: 1;">Mettre à jour</button>
                    </form>
                </div>

                <div class="order-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div>
                        <h3 style="color: #cbd5e1; margin-bottom: 1rem; font-size: 1.1rem;">Informations de livraison</h3>
                        <p style="white-space: pre-line; color: #94a3b8;"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <p style="color: #94a3b8; margin-top: 0.5rem;">📞 <?= htmlspecialchars($order['phone']) ?></p>
                        <?php if (!empty($order['customer_comment'])): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: rgba(0,0,0,0.2); border-left: 3px solid var(--primary-color); border-radius: 4px;">
                                <strong style="color: #fff; display: block; margin-bottom: 0.3rem;">Commentaire du client :</strong>
                                <p style="color: #94a3b8; white-space: pre-wrap; font-style: italic;"><?= htmlspecialchars($order['customer_comment']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 style="color: #cbd5e1; margin-bottom: 1rem; font-size: 1.1rem;">Articles commandés</h3>
                        <?php
                        $stmtItem = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $stmtItem->execute([$order['id']]);
                        $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <ul style="list-style: none; padding: 0; margin: 0 0 1rem 0;">
                            <?php foreach ($items as $item): ?>
                                <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #94a3b8; font-size: 0.95rem;">
                                    <span><?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_name']) ?></span>
                                    <span><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                            <span style="font-weight: bold; font-size: 1.2rem;">Total</span>
                            <span style="font-weight: bold; font-size: 1.2rem; color: #22c55e;"><?= number_format($order['total'], 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($orders)): ?>
            <p style="text-align: center; color: #94a3b8;">Aucune commande.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

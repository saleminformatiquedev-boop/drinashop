<?php
require_once 'includes/db.php';
requireLogin();
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 6rem 2rem; min-height: 80vh; max-width: 1000px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem; font-size: 2rem;"><?= __('profile') ?></h1>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Infos -->
        <div class="card" style="height: fit-content;">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);"><?= __('my_info') ?></h2>
            <p style="margin-bottom: 0.5rem;"><strong><?= __('name') ?> :</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p style="margin-bottom: 0.5rem;"><strong><?= __('email_address') ?> :</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p style="margin-bottom: 0.5rem;"><strong><?= __('member_since') ?> :</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
            <a href="<?= BASE_URL ?>/logout" style="display: inline-block; margin-top: 1rem; color: #ef4444; text-decoration: none; font-weight: bold;"><?= __('logout') ?></a>
        </div>

        <!-- Commandes -->
        <div class="card">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);"><?= __('order_history') ?></h2>
            
            <?php if (empty($orders)): ?>
                <p style="color: #94a3b8;"><?= __('no_orders') ?></p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($orders as $order): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--bg-cream);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                                <div>
                                    <span style="font-weight: bold; color: var(--secondary-color);"><?= __('order') ?> #<?= $order['id'] ?></span>
                                    <span style="color: var(--text-muted); font-size: 0.9rem; margin-inline-start: 1rem;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                                <div>
                                    <span style="background: <?= $order['status'] === 'En attente' ? 'rgba(234, 179, 8, 0.2)' : 'rgba(34, 197, 94, 0.2)' ?>; color: <?= $order['status'] === 'En attente' ? '#eab308' : '#22c55e' ?>; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.9rem;"><?= htmlspecialchars($order['status']) ?></span>
                                </div>
                            </div>

                            <!-- Fetch order items -->
                            <?php
                            $stmtItem = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                            $stmtItem->execute([$order['id']]);
                            $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <ul style="list-style: none; padding: 0; margin: 0 0 1rem 0;">
                                <?php foreach ($items as $item): ?>
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--text-color);">
                                        <span><?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_name']) ?></span>
                                        <span><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div style="display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 1.1rem; color: var(--secondary-color);">
                                <span><?= __('total') ?></span>
                                <span><?= number_format($order['total'], 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

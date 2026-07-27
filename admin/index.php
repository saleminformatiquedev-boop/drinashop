<?php
require_once __DIR__ . '/admin_header.php';

// Stats
$stmt = $db->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $stmt->fetch()['count'];

$stmt = $db->query("SELECT SUM(total) as sum FROM orders WHERE status != 'Annulée'");
$revenue = $stmt->fetch()['sum'] ?? 0;

$stmt = $db->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

// Recent orders
$stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 3rem 2rem; min-height: 80vh; max-width: 1200px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem; color: #fbbf24;">Tableau de bord Administrateur</h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <div class="card">
            <h3 style="color: #94a3b8; margin-bottom: 0.5rem;">Chiffre d'affaires</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #22c55e;"><?= number_format($revenue, 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></p>
        </div>
        <div class="card">
            <h3 style="color: #94a3b8; margin-bottom: 0.5rem;">Commandes Totales</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #fff;"><?= $total_orders ?></p>
        </div>
        <div class="card">
            <h3 style="color: #94a3b8; margin-bottom: 0.5rem;">Clients Inscrits</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #fff;"><?= $total_users ?></p>
        </div>
    </div>

    <h2 style="margin-bottom: 1.5rem;">Dernières commandes</h2>
    <div class="card" style="padding: 0; overflow: hidden;">
      <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 600px;">
            <thead>
                <tr style="background: var(--bg-cream); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 1rem; color: var(--secondary-color);">ID</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Client</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Total</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Statut</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">#<?= $order['id'] ?></td>
                    <td style="padding: 1rem;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td style="padding: 1rem; font-weight: bold;"><?= number_format($order['total'], 2, ',', ' ') ?> <?= htmlspecialchars(get_currency()) ?></td>
                    <td style="padding: 1rem;">
                        <span style="background: <?= $order['status'] === 'En attente' ? 'rgba(234, 179, 8, 0.2)' : 'rgba(34, 197, 94, 0.2)' ?>; color: <?= $order['status'] === 'En attente' ? '#eab308' : '#22c55e' ?>; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.9rem;"><?= htmlspecialchars($order['status']) ?></span>
                    </td>
                    <td style="padding: 1rem;">
                        <a href="<?= BASE_URL ?>/admin/orders#order-<?= $order['id'] ?>" style="color: var(--primary-color); text-decoration: none;">Voir détail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($recent_orders)): ?>
                <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #94a3b8;">Aucune commande pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

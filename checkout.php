<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$success = false;
$error = '';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $error = __('empty_cart');
}

// Validation de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if (empty($address) || empty($phone)) {
        $error = "Veuillez renseigner votre adresse et votre numéro de téléphone.";
    } else {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $product = get_product_by_id($item['id']);
            if ($product) {
                if (isset($product['stock']) && $item['quantity'] > $product['stock']) {
                    $error = "Désolé, le produit '{$product['title']}' n'a plus assez de stock (restant : {$product['stock']}). Veuillez ajuster votre panier.";
                    break;
                }
                $total += $product['price'] * $item['quantity'];
            }
        }

        if (empty($error)) {
            try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO orders (user_id, total, shipping_address, phone, customer_comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total, $address, $phone, $comment]);
            $order_id = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($_SESSION['cart'] as $item) {
                $product = get_product_by_id($item['id']);
                if ($product) {
                    $stmtItem->execute([$order_id, $product['id'], $product['title'], $item['quantity'], $product['price']]);
                    $stmtStock->execute([$item['quantity'], $product['id']]);
                }
            }

            $db->commit();
            
            $_SESSION['cart'] = [];
            $success = true;

        } catch (Exception $e) {
            $db->rollBack();
            $error = "Une erreur est survenue lors de la validation de votre commande.";
        }
        }
    }
}
?>

<main style="padding: 6rem 2rem; min-height: 80vh; max-width: 600px; margin: 0 auto;">
    
    <?php if ($success): ?>
        <div class="card" style="text-align: center;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <h1 style="color: #22c55e; margin-bottom: 1rem;"><?= __('order_success') ?></h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= __('order_success_desc') ?></p>
            <a href="<?= BASE_URL ?>/boutique" class="btn-primary"><?= __('back_to_shop') ?></a>
        </div>
    <?php else: ?>
        
        <div class="card">
            <h1 style="margin-bottom: 2rem;"><?= __('checkout_title') ?></h1>
            
            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!isLoggedIn()): ?>
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 1rem; border-radius: 10px; margin-bottom: 2rem; color: #93c5fd;">
                    <strong>Astuce :</strong> <a href="<?= BASE_URL ?>/login" style="color: #60a5fa; text-decoration: underline;">Connectez-vous</a> pour enregistrer cette commande sur votre compte, ou continuez en tant qu'invité.
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/checkout" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="form-group">
                    <label><?= __('address_label') ?></label>
                    <textarea name="address" required rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label><?= __('phone_label') ?></label>
                    <input type="tel" name="phone" required>
                </div>
                
                <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <div class="form-group">
                        <label>Commentaire pour la commande (facultatif)</label>
                        <textarea name="comment" rows="3" placeholder="Informations pour la livraison, message spécifique..." style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px; font-family: inherit; background: var(--bg-light); color: var(--text-color);"></textarea>
                    </div>

                    <button type="submit" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?> class="btn-primary" style="width: 100%; margin-top: 1rem; opacity: <?= empty($_SESSION['cart']) ? '0.5' : '1' ?>;"><?= __('checkout') ?></button>
                </div>
            </form>
        </div>

    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

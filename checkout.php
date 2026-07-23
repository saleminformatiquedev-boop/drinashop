<?php
include 'includes/header.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulons la sauvegarde de la commande
    // On vide le panier
    $_SESSION['cart'] = [];
    $success = true;
}
?>

<main style="padding-top: 4rem;">
  <section class="container">
    <?php if ($success): ?>
        <h1 class="section-title" style="color: var(--accent-color);">Commande Confirmée ! 🎉</h1>
        <div class="content-section" style="text-align: center;">
            <p>Merci pour votre achat ! Votre commande a bien été enregistrée.</p>
            <p>Vous paierez le livreur directement à la réception (Paiement à la livraison).</p>
            <a href="/boutique.php" class="btn-primary" style="margin-top: 2rem;">Retourner à la boutique</a>
        </div>
        <script>
            // Forcer la mise à jour du compteur à 0 côté frontend
            document.querySelector('.cart-items-count').innerText = '0';
        </script>
    <?php else: ?>
        <h1 class="section-title">Finaliser la Commande</h1>
        
        <?php if ($cart_count == 0): ?>
            <div class="content-section" style="text-align: center;">
                <p>Votre panier est vide.</p>
                <a href="/boutique.php" class="btn-primary">Découvrir nos produits</a>
            </div>
        <?php else: ?>
            <div class="content-section">
            <p style="margin-bottom: 2rem; color: var(--accent-color); font-weight: bold;">Moyen de paiement : Paiement à la livraison 🚚</p>
            <form method="POST" action="checkout.php">
                <div class="form-group">
                <label for="fullname">Nom et Prénom</label>
                <input type="text" id="fullname" name="fullname" required placeholder="Jean Dupont" />
                </div>
                <div class="form-group">
                <label for="address">Adresse de livraison complète</label>
                <input type="text" id="address" name="address" required placeholder="123 rue de la Paix, 75000 Paris" />
                </div>
                <div class="form-group">
                <label for="phone">Numéro de téléphone</label>
                <input type="tel" id="phone" name="phone" required placeholder="06 12 34 56 78" />
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1.2rem;">Confirmer ma commande</button>
            </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

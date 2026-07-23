<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get the current page to highlight nav
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drinashop</title>
  <link rel="stylesheet" href="/src/style.css" />
</head>
<body>
  <nav>
    <div style="display: flex; align-items: center; gap: 1rem;">
      <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
      </button>
      <a href="/" class="logo">DRINASHOP</a>
    </div>
    <ul id="nav-links">
      <li><a href="/index.php" <?= $current_page == 'index.php' || $current_page == '' ? 'class="active"' : '' ?>>Accueil</a></li>
      <li><a href="/boutique.php" <?= $current_page == 'boutique.php' ? 'class="active"' : '' ?>>Boutique</a></li>
      <li><a href="/about.php" <?= $current_page == 'about.php' ? 'class="active"' : '' ?>>Qui sommes-nous</a></li>
      <li><a href="/contact.php" <?= $current_page == 'contact.php' ? 'class="active"' : '' ?>>Contact</a></li>
    </ul>
    <button class="cart-btn" onclick="toggleCart()">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span class="cart-items-count"><?= $cart_count ?></span>
    </button>
  </nav>

  <!-- Side Cart Panel -->
  <div id="side-cart" class="side-cart">
    <div class="cart-header">
        <h2>Votre Panier</h2>
        <button class="close-cart" onclick="toggleCart()">&times;</button>
    </div>
    <div id="cart-items" class="cart-items">
        <!-- Rempli en JS via AJAX -->
    </div>
    <div class="cart-footer">
        <div class="cart-total">Total: <span id="cart-total-price">0</span> €</div>
        <a href="/checkout.php" class="btn-primary" style="width: 100%; text-align: center; margin-top: 1rem; display: block;">Commander (Paiement à la livraison)</a>
    </div>
  </div>
  <div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>

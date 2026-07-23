<?php include 'includes/header.php'; ?>

<main>
  <section class="hero parallax-wrapper">
    <div class="blob blob-1 parallax-layer" style="--parallax-speed: -200px;"></div>
    <div class="blob blob-2 parallax-layer" style="--parallax-speed: -100px;"></div>
    
    <div class="hero-content">
      <h1>Découvrez l'Excellence</h1>
      <p>Des produits premium avec un paiement à la livraison sécurisé.</p>
      <a href="/boutique.php" class="btn-primary">Voir la boutique</a>
    </div>
  </section>

  <section class="container">
    <h2 class="section-title">Nos Produits Phares</h2>
    <div id="products-grid" class="products-grid">
      <?php
      require_once 'includes/csv_parser.php';
      $products = get_all_products();
      // Only show 3 products on home page
      $featured = array_slice($products, 0, 3);
      foreach ($featured as $product): ?>
        <div class="product-card">
          <img src="<?= htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/400') ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
          <div class="product-info">
            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
            <div class="product-footer">
              <span class="product-price"><?= number_format($product['price'], 2) ?> €</span>
              <button class="btn-add-cart" data-id="<?= htmlspecialchars($product['id']) ?>" onclick="addToCart('<?= htmlspecialchars($product['id']) ?>', this)">
                Ajouter au panier
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/header.php'; ?>

<main style="padding-top: 4rem;">
  <section class="container">
    <h1 class="section-title">Notre Boutique</h1>
    <p style="text-align: center; color: #94a3b8; margin-bottom: 3rem;">Tous nos produits sont extraits des sources XML (PHP backend).</p>
    
    <div id="products-grid" class="products-grid">
      <?php
      require_once 'includes/csv_parser.php';
      $products = get_all_products();
      
      if (empty($products)): ?>
          <p>Aucun produit trouvé.</p>
      <?php else: ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card" id="<?= htmlspecialchars($product['id']) ?>">
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
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

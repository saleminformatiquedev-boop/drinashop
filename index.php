<?php 
$page_title = 'Drinashop - Boutique de produits artisanaux des îles Kerkennah';
$page_description = 'Bienvenue sur Drinashop. Découvrez et achetez notre sélection authentique de produits artisanaux des îles Kerkennah en ligne.';
include 'includes/header.php'; 
?>

<main>
  <section class="hero parallax-wrapper">

    <div class="hero-content">
      <h1><?= __('hero_title') ?></h1>
      <p><?= __('hero_desc') ?></p>
      <a href="<?= BASE_URL ?>/boutique" class="btn-primary"><?= __('hero_btn') ?></a>
    </div>
  </section>

  <section class="features">
    <h2 class="section-title"><?= __('features_title') ?></h2>
    <div class="features-grid">
      <div class="feature-card">
        <h3><?= __('feat_1_title') ?></h3>
        <p><?= __('feat_1_desc') ?></p>
      </div>
      <div class="feature-card">
        <h3><?= __('feat_2_title') ?></h3>
        <p><?= __('feat_2_desc') ?></p>
      </div>
      <div class="feature-card">
        <h3><?= __('feat_3_title') ?></h3>
        <p><?= __('feat_3_desc') ?></p>
      </div>
    </div>
  </section>

  <section class="container">
    <h2 class="section-title"><?= __('featured_products_title') ?></h2>
    <div id="products-grid" class="products-grid">
      <?php
      $stmt = $db->query("SELECT * FROM products LIMIT 8");
      $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($featured_products as $product):
          $title_col = ($current_lang === 'fr') ? 'title' : 'title_' . $current_lang;
          $desc_col = ($current_lang === 'fr') ? 'description' : 'description_' . $current_lang;
          $p_title = !empty($product[$title_col]) ? $product[$title_col] : $product['title'];
          $p_desc = !empty($product[$desc_col]) ? $product[$desc_col] : $product['description'];
          
          $product['title'] = $p_title;
          $product['description'] = $p_desc;
          
          $img1 = BASE_URL . ($product['image'] ?: '/public/imagesProduits/placeholder.jpg');
          $extra = json_decode($product['extra_images'] ?? '[]', true);
          $img2 = (is_array($extra) && count($extra) > 0) ? (BASE_URL . $extra[0]) : $img1;
      ?>  <div class="product-card">
          <div style="position: relative; overflow: hidden; cursor: pointer;" onclick="openQuickView('<?= htmlspecialchars(rawurlencode(json_encode($product))) ?>')">
              <img src="<?= htmlspecialchars($img1) ?>" data-img1="<?= htmlspecialchars($img1) ?>" data-img2="<?= htmlspecialchars($img2) ?>" alt="<?= htmlspecialchars($p_title) ?>" class="product-image" style="transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'; this.src=this.getAttribute('data-img2');" onmouseout="this.style.transform='scale(1)'; this.src=this.getAttribute('data-img1');">
              <div style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.6); color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; opacity: 0; transition: opacity 0.3s;" class="quick-view-badge">Aperçu rapide</div>
          </div>
          <div class="product-info">
            <?php if ($product['promo_price']): ?>
                <div style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); z-index: 2;"><?= __('promo') ?></div>
            <?php endif; ?>
            <h3 class="product-title"><?= htmlspecialchars($p_title) ?></h3>
            <p class="product-desc"><?= htmlspecialchars(substr(str_replace('<br>', ' ', $p_desc), 0, 80)) ?>...</p>
            <div class="product-footer">
              <span class="product-price">
                <?php if ($product['promo_price']): ?>
                  <del style="color: var(--text-muted); font-size: 0.85em; display: block; margin-bottom: -0.2rem;"><?= number_format($product['price'], 2) ?> <?= htmlspecialchars(get_currency()) ?></del>
                  <span style="color: #ef4444; font-weight: bold; display: block;"><?= number_format($product['promo_price'], 2) ?> <?= htmlspecialchars(get_currency()) ?></span>
                <?php else: ?>
                  <?= number_format($product['price'], 2) ?> <?= htmlspecialchars(get_currency()) ?>
                <?php endif; ?>
              </span>
              <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end; width: 100%; margin-top: 0.5rem;">
                <?php 
                $stock = intval($product['stock'] ?? 0);
                if (isset($product['stock']) && $stock <= 0): ?>
                    <div style="color: #ef4444; font-weight: bold; font-size: 0.9rem; padding: 0.5rem;">Rupture de stock</div>
                <?php else: ?>
                    <div class="product-qty-control">
                      <button type="button" onclick="const input = document.getElementById('qty-<?= htmlspecialchars($product['id']) ?>'); if(input.value > 1) input.value = parseInt(input.value) - 1;">-</button>
                      <input type="number" id="qty-<?= htmlspecialchars($product['id']) ?>" value="1" min="1" max="<?= $stock > 0 ? $stock : 99 ?>" readonly>
                      <button type="button" onclick="const input = document.getElementById('qty-<?= htmlspecialchars($product['id']) ?>'); if(input.value < <?= $stock > 0 ? $stock : 99 ?>) input.value = parseInt(input.value) + 1;">+</button>
                    </div>
                    <button class="btn-add-cart" data-id="<?= htmlspecialchars($product['id']) ?>" onclick="addToCart('<?= htmlspecialchars($product['id']) ?>', this)"><?= __('add_to_cart') ?></button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

<?php 
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$page_title = $category_filter ? "Drinashop - " . htmlspecialchars($category_filter) : "Boutique de produits artisanaux - Drinashop";
$page_description = "Parcourez notre catalogue complet de produits artisanaux des îles Kerkennah. " . ($category_filter ? "Découvrez notre catégorie $category_filter." : "Trouvez les meilleures créations authentiques.");
include 'includes/header.php'; 
?>

<main style="padding-top: 4rem;">
  <section class="container">
    <h1 class="section-title"><?= __('our_shop') ?></h1>
    <?php
    $search = trim($_GET['search'] ?? '');
    $selectedCategory = trim($_GET['category'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    // Build query conditions
    $conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = "(title LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if (!empty($selectedCategory)) {
        $conditions[] = "category = :category";
        $params[':category'] = $selectedCategory;
    }
    
    $whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    // Count total products for pagination
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products $whereClause");
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);

    // Fetch products
    $stmt = $db->prepare("SELECT * FROM products $whereClause ORDER BY title ASC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate ALL categories from all products for the menu
    $allStmt = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
    $categories = $allStmt->fetchAll(PDO::FETCH_COLUMN);
    sort($categories);
    ?>
    
    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 2rem;">
        <form method="GET" style="display: flex; gap: 0.5rem; width: 100%; max-width: 400px; margin-bottom: 1rem;">
            <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un produit..." class="search-bar" style="flex: 1;">
            <button type="submit" class="btn-primary" style="padding: 0.5rem 1.5rem;">🔍</button>
        </form>
        <?php if (!empty($search)): ?>
            <a href="<?= BASE_URL ?>/boutique" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none;">Réinitialiser la recherche</a>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($categories)): ?>
    <div class="category-menu" style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 3rem; flex-wrap: wrap;">
        <a href="?search=<?= urlencode($search) ?>" class="btn-category <?= empty($selectedCategory) ? 'active' : '' ?>" style="padding: 0.5rem 1.5rem; border: <?= empty($selectedCategory) ? 'none' : '1px solid var(--border-color)' ?>; border-radius: 20px; background: <?= empty($selectedCategory) ? 'var(--primary-color)' : 'transparent' ?>; color: <?= empty($selectedCategory) ? 'white' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold; transition: all 0.3s;"><?= htmlspecialchars(__('all_categories')) ?></a>
        <?php foreach ($categories as $cat): 
            $isActive = ($selectedCategory === $cat);
        ?>
            <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($cat) ?>" class="btn-category <?= $isActive ? 'active' : '' ?>" style="padding: 0.5rem 1.5rem; border: <?= $isActive ? 'none' : '1px solid var(--border-color)' ?>; border-radius: 20px; background: <?= $isActive ? 'var(--primary-color)' : 'transparent' ?>; color: <?= $isActive ? 'white' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold; transition: all 0.3s;"><?= htmlspecialchars(__($cat)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div id="products-grid" class="products-grid">
      <?php if (empty($products)): ?>
          <p>Aucun produit trouvé.</p>
      <?php else: ?>
          <?php foreach ($products as $product): 
              $cat = $product['category'] ?? '';
              
              $title_col = ($current_lang === 'fr') ? 'title' : 'title_' . $current_lang;
              $desc_col = ($current_lang === 'fr') ? 'description' : 'description_' . $current_lang;
              $p_title = !empty($product[$title_col]) ? $product[$title_col] : $product['title'];
              $p_desc = !empty($product[$desc_col]) ? $product[$desc_col] : $product['description'];
              
              $product['title'] = $p_title;
              $product['description'] = $p_desc;
              
              $img1 = BASE_URL . ($product['image'] ?: '/public/imagesProduits/placeholder.jpg');
              $extra = json_decode($product['extra_images'] ?? '[]', true);
              $img2 = (is_array($extra) && count($extra) > 0) ? (BASE_URL . $extra[0]) : $img1;
          ?>
            <article class="product-card" id="<?= htmlspecialchars($product['id']) ?>" data-category="<?= htmlspecialchars($cat) ?>" itemscope itemtype="https://schema.org/Product">
              <meta itemprop="productID" content="<?= htmlspecialchars($product['id']) ?>">
              <div style="position: relative; overflow: hidden; cursor: pointer;" onclick="openQuickView('<?= htmlspecialchars(rawurlencode(json_encode($product))) ?>')">
                  <img itemprop="image" src="<?= htmlspecialchars($img1) ?>" data-img1="<?= htmlspecialchars($img1) ?>" data-img2="<?= htmlspecialchars($img2) ?>" alt="<?= htmlspecialchars($p_title) ?>" class="product-image" style="transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'; this.src=this.getAttribute('data-img2');" onmouseout="this.style.transform='scale(1)'; this.src=this.getAttribute('data-img1');">
                  <div style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.6); color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; opacity: 0; transition: opacity 0.3s;" class="quick-view-badge">Aperçu rapide</div>
              </div>
              <div class="product-info">
                <?php if ($product['promo_price']): ?>
                    <div style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); z-index: 2;"><?= __('promo') ?></div>
                <?php endif; ?>
                <h3 class="product-title" itemprop="name"><?= htmlspecialchars($p_title) ?></h3>
                <meta itemprop="description" content="<?= htmlspecialchars(strip_tags(str_replace('<br>', ' ', $p_desc))) ?>">
                <p class="product-desc"><?= htmlspecialchars(substr(strip_tags(str_replace('<br>', ' ', $p_desc)), 0, 80)) ?>...</p>
                <div class="product-footer" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                  <meta itemprop="priceCurrency" content="TND">
                  <span class="product-price">
                    <?php if ($product['promo_price']): ?>
                      <del style="color: var(--text-muted); font-size: 0.85em; display: block; margin-bottom: -0.2rem;"><?= number_format($product['price'], 2) ?> <?= htmlspecialchars(get_currency()) ?></del>
                      <span style="color: #ef4444; font-weight: bold; display: block;"><span itemprop="price" content="<?= $product['promo_price'] ?>"><?= number_format($product['promo_price'], 2) ?></span> <?= htmlspecialchars(get_currency()) ?></span>
                    <?php else: ?>
                      <span itemprop="price" content="<?= $product['price'] ?>"><?= number_format($product['price'], 2) ?></span> <?= htmlspecialchars(get_currency()) ?>
                    <?php endif; ?>
                  </span>
                  <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end; width: 100%; margin-top: 0.5rem;">
                    <?php 
                    $stock = intval($product['stock'] ?? 0);
                    if (isset($product['stock']) && $stock <= 0): ?>
                        <link itemprop="availability" href="https://schema.org/OutOfStock" />
                        <div style="color: #ef4444; font-weight: bold; font-size: 0.9rem; padding: 0.5rem;">Rupture de stock</div>
                    <?php else: ?>
                        <link itemprop="availability" href="https://schema.org/InStock" />
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
            </article>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: 3rem; display: flex; justify-content: center; gap: 0.5rem;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item">&laquo;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item">&raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
  </section>
</main>



<?php include 'includes/footer.php'; ?>

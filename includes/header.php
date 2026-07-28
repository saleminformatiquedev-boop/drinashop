<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lang.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get the current page to highlight nav
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_page = 'index';
if (strpos($current_path, '/boutique') !== false) {
    $current_page = 'boutique';
} elseif (strpos($current_path, '/about') !== false || strpos($current_path, '/qui-sommes-nous') !== false) {
    $current_page = 'about';
} elseif (strpos($current_path, '/contact') !== false) {
    $current_page = 'contact';
}
?>
<?php
// Default SEO values if not defined by the page
if (!isset($page_title)) $page_title = 'Drinashop - Boutique de produits artisanaux des îles Kerkennah';
if (!isset($page_description)) $page_description = 'Découvrez notre sélection de produits artisanaux authentiques des îles Kerkennah sur Drinashop.';

// Generate Canonical URL safely
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$canonical_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (isset($_GET['category']) && strpos($canonical_path, '/category/') === false) {
    $canonical_path = rtrim($canonical_path, '/') . '/category/' . urlencode($_GET['category']);
}
$canonical_url = $protocol . $_SERVER['HTTP_HOST'] . $canonical_path;
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- SEO Tags -->
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_description) ?>" />
  <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>" />
  <link rel="icon" href="<?= BASE_URL ?>/public/imagesProduits/slider/label.jpg">
  
  <!-- Open Graph / Social Media -->
  <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>" />
  <meta property="og:image" content="<?= BASE_URL ?>/public/images/logo.png" />
  <meta property="og:site_name" content="Drinashop" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/src/style.css" />
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
    window.shopCurrency = "<?= htmlspecialchars(get_currency()) ?>";
    window.itemAddedText = "<?= htmlspecialchars(__('item_added')) ?>";
  </script>
</head>
<body>
  <nav>
    <div style="display: flex; align-items: center; gap: 1rem;">
      <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
      </button>
      <a href="<?= BASE_URL ?>/" class="logo">DRINASHOP</a>
    </div>
    <ul id="nav-links" class="nav-links">
      <li><a href="<?= BASE_URL ?>/" <?= $current_page === 'index' ? 'class="active"' : '' ?>><?= __('home') ?></a></li>
      <li><a href="<?= BASE_URL ?>/boutique" <?= $current_page === 'boutique' ? 'class="active"' : '' ?>><?= __('shop') ?></a></li>
      <li><a href="<?= BASE_URL ?>/about" <?= $current_page === 'about' ? 'class="active"' : '' ?>><?= __('about') ?></a></li>
      <li><a href="<?= BASE_URL ?>/contact" <?= $current_page === 'contact' ? 'class="active"' : '' ?>><?= __('contact') ?></a></li>
    </ul>
    <div style="display: flex; align-items: center; gap: 1rem;">
      <select onchange="window.location.href='?lang='+this.value" style="background: var(--bg-light); color: var(--secondary-color); border: 1px solid var(--border-color); padding: 0.3rem; border-radius: 5px; font-family: inherit;">
          <option value="fr" <?= $current_lang === 'fr' ? 'selected' : '' ?>>FR</option>
          <option value="en" <?= $current_lang === 'en' ? 'selected' : '' ?>>EN</option>
          <option value="ar" <?= $current_lang === 'ar' ? 'selected' : '' ?>>AR</option>
      </select>
      
      <div class="user-menu-container">
        <button class="user-btn" onclick="toggleUserMenu(event)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </button>
        <div class="user-dropdown" id="user-dropdown-menu">
          <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/" style="color: var(--primary-color); font-weight: bold; border-bottom: 1px solid var(--border-color);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 8px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg> <?= __('admin') ?></a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/profil"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 8px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> <?= __('profile') ?></a>
            <a href="<?= BASE_URL ?>/logout"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 8px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> <?= __('logout') ?></a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/login"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 8px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg> <?= __('login') ?></a>
            <a href="<?= BASE_URL ?>/register" style="border-top: 1px solid var(--border-color);">S'inscrire</a>
          <?php endif; ?>
        </div>
      </div>
      <button class="cart-btn" onclick="toggleCart()">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span class="cart-items-count"><?= $cart_count ?></span>
    </button>
  </nav>

  <?php
  // Fetch and display the slider below the nav
  $slider_stmt = $db->query("SELECT * FROM slider ORDER BY slide_order ASC");
  $slides = $slider_stmt->fetchAll(PDO::FETCH_ASSOC);
  
  if (!empty($slides)):
  ?>
  <div class="parallax-slider" id="header-parallax-slider">
      <!-- Floating decorations moved inside slide -->
      <?php foreach ($slides as $index => $slide): ?>
      <div class="parallax-slide <?= $index === 0 ? 'active' : '' ?>" style="background-image: url('<?= htmlspecialchars($slide['image_url']) ?>');">
          
          <div class="floating-decorations slide-floating" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
              <?php
              $imgDir = ($index === 1) ? 'slider2' : 'slider';
              $imagesDir = dirname(__DIR__) . "/public/imagesProduits/{$imgDir}/*.png";
              $all_images = glob($imagesDir);
              
              if ($all_images && count($all_images) > 0) {
                  shuffle($all_images);
                  $selected_images = array_slice($all_images, 0, 5);
              } else {
                  $selected_images = [];
              }
              
              foreach ($selected_images as $img_path):
                  $img = str_replace(dirname(__DIR__), '', $img_path);
                  $speed = mt_rand(15, 45) / 10; // Speed entre 1.5 et 4.5
                  
                  // Position totalement aléatoire
                  $top = mt_rand(5, 85);
                  $left = mt_rand(5, 85); 
                  $width = mt_rand(100, 180);
              ?>
                  <img src="<?= BASE_URL ?><?= htmlspecialchars($img) ?>" class="floating-item" data-speed="<?= $speed ?>" style="position: absolute; top: <?= $top ?>%; left: <?= $left ?>%; width: <?= $width ?>px; object-fit: contain;" alt="">
              <?php endforeach; ?>
          </div>

          <div class="parallax-content <?= $index === 0 ? 'label-content' : '' ?>">
              <?php if ($index === 0): ?>
                  <img src="<?= BASE_URL ?>/public/imagesProduits/slider/label.jpg" alt="Drina Shop Kerkennah" style="max-width: 450px; max-height: 60vh; width: 100%; height: auto; object-fit: contain; border-radius: 10px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
              <?php else: ?>
                  <h2><?= htmlspecialchars($slide['title']) ?></h2>
                  <p><?= htmlspecialchars($slide['subtitle']) ?></p>
                  <?php if (!empty($slide['link_url'])): ?>
                      <a href="<?= htmlspecialchars($slide['link_url']) ?>" class="btn-primary" style="display: inline-block; padding: 1rem 2rem; text-decoration: none; border-radius: 8px;"><?= htmlspecialchars($slide['link_text']) ?></a>
                  <?php endif; ?>
              <?php endif; ?>
          </div>
      </div>
      <?php endforeach; ?>
      <?php if (count($slides) > 1): ?>
          <button class="slider-prev">&lsaquo;</button>
          <button class="slider-next">&rsaquo;</button>
      <?php endif; ?>
  </div>
  <script src="<?= BASE_URL ?>/src/slider.js"></script>
  <?php endif; ?>

  <!-- Side Cart Panel -->
  <div id="side-cart" class="side-cart">
    <div class="cart-header">
        <h2><?= __('cart') ?></h2>
        <button class="close-cart" onclick="toggleCart()">&times;</button>
    </div>
    <div id="cart-items" class="cart-items">
        <!-- Rempli en JS via AJAX -->
    </div>
    <div class="cart-footer">
        <div class="cart-total"><?= __('total') ?>: <span id="cart-total-price">0</span> <?= htmlspecialchars(get_currency()) ?></div>
        <a href="<?= BASE_URL ?>/checkout" class="btn-primary" style="width: 100%; text-align: center; margin-top: 1rem; display: block;"><?= __('checkout') ?></a>
    </div>
  </div>
  <div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>

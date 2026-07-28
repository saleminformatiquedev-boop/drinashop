<!-- Quick View Modal -->
<div id="quick-view-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; padding: 1rem;">
    <div class="card" style="width: 100%; max-width: 900px; max-height: 90vh; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 2rem; position: relative; background: var(--bg-cream);">
        <button onclick="closeQuickView()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-color); z-index: 10;">&times;</button>
        
        <div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 1rem;">
            <div id="qv-img-container" style="width: 100%; height: 350px; border-radius: 10px; border: 1px solid var(--border-color); background: white; overflow: hidden; cursor: zoom-in; position: relative;">
                <img id="qv-main-img" src="" alt="" style="width: 100%; height: 100%; object-fit: contain; transition: transform 0.1s; padding: 1rem;">
            </div>
            <div id="qv-thumbnails" style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem;"></div>
        </div>
        
        <div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 1rem; justify-content: center;">
            <h2 id="qv-title" style="font-family: var(--font-heading); color: var(--secondary-color);"></h2>
            <div id="qv-price-container" style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);"></div>
            <p id="qv-desc" style="color: var(--text-muted); line-height: 1.6;"></p>
            <div id="qv-stock" style="font-weight: bold;"></div>
            <div id="qv-action" style="margin-top: 1rem;"></div>
        </div>
    </div>
</div>

<script>
window.CURRENCY = "<?= htmlspecialchars(get_currency()) ?>";
window.BASE_URL = "<?= BASE_URL ?>";
window.I18N = {
    no_description: "<?= __('no_description') ?>",
    out_of_stock: "<?= __('out_of_stock') ?>",
    in_stock: "<?= __('in_stock') ?>",
    available: "<?= __('available') ?>",
    add_to_cart: "<?= __('add_to_cart') ?>"
};

function openQuickView(productStr) {
    const p = JSON.parse(decodeURIComponent(productStr));
    document.getElementById('quick-view-modal').style.display = 'flex';
    document.getElementById('qv-title').innerText = p.title;
    document.getElementById('qv-desc').innerHTML = p.description || window.I18N.no_description;
    
    // Images
    const mainImg = document.getElementById('qv-main-img');
    const thumbContainer = document.getElementById('qv-thumbnails');
    const imgContainer = document.getElementById('qv-img-container');
    
    // Zoom effect (Loupe)
    mainImg.style.transform = 'scale(1)';
    imgContainer.onmousemove = function(e) {
        const rect = imgContainer.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        mainImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        mainImg.style.transform = 'scale(2.5)';
    };
    imgContainer.onmouseleave = function() {
        mainImg.style.transformOrigin = 'center';
        mainImg.style.transform = 'scale(1)';
    };
    
    mainImg.src = window.BASE_URL + p.image;
    thumbContainer.innerHTML = '';
    
    let allImages = [p.image];
    try {
        if (p.extra_images) {
            const extras = JSON.parse(p.extra_images);
            if (Array.isArray(extras)) {
                allImages = allImages.concat(extras);
            }
        }
    } catch(e) {}
    
    if (allImages.length > 1) {
        allImages.forEach(img => {
            const thumb = document.createElement('img');
            thumb.src = window.BASE_URL + img;
            thumb.style = "width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; opacity: 0.7;";
            thumb.onclick = () => {
                mainImg.src = window.BASE_URL + img;
                Array.from(thumbContainer.children).forEach(c => { c.style.borderColor = 'transparent'; c.style.opacity = '0.7'; });
                thumb.style.borderColor = 'var(--primary-color)';
                thumb.style.opacity = '1';
            };
            thumbContainer.appendChild(thumb);
        });
        thumbContainer.children[0].style.borderColor = 'var(--primary-color)';
        thumbContainer.children[0].style.opacity = '1';
    }
    
    // Prix
    let priceHtml = '';
    if (p.promo_price) {
        priceHtml = `<del style="color: var(--text-muted); font-size: 1rem;">${parseFloat(p.price).toFixed(2)} ${window.CURRENCY}</del> <span style="color: #ef4444;">${parseFloat(p.promo_price).toFixed(2)} ${window.CURRENCY}</span>`;
    } else {
        priceHtml = `${parseFloat(p.price).toFixed(2)} ${window.CURRENCY}`;
    }
    document.getElementById('qv-price-container').innerHTML = priceHtml;
    
    // Stock & Action
    const stockDiv = document.getElementById('qv-stock');
    const actionDiv = document.getElementById('qv-action');
    const stock = parseInt(p.stock || 0);
    
    if (stock <= 0) {
        stockDiv.innerHTML = `<span style="color: #ef4444;">❌ ${window.I18N.out_of_stock}</span>`;
        actionDiv.innerHTML = `<button class="btn-primary" style="opacity: 0.5; cursor: not-allowed; width: 100%;" disabled>${window.I18N.out_of_stock}</button>`;
    } else {
        stockDiv.innerHTML = `<span style="color: #22c55e;">✅ ${window.I18N.in_stock} (${stock} ${window.I18N.available})</span>`;
        actionDiv.innerHTML = `<button class="btn-primary" onclick="addToCart('${p.id}', this); closeQuickView();" style="width: 100%;">🛒 ${window.I18N.add_to_cart}</button>`;
    }
}

function closeQuickView() {
    document.getElementById('quick-view-modal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('quick-view-modal');
    if (e.target === modal) {
        closeQuickView();
    }
});
</script>

  <footer>
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
      <p>&copy; <?= date('Y') ?> Drinashop. <?= __('footer_text') ?></p>
      <a href="<?= BASE_URL ?>/conditions" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: bold;"><?= __('terms_conditions') ?></a>
    </div>
  </footer>

  <script src="<?= BASE_URL ?>/src/cart.js?v=<?= time() ?>"></script>
  <script src="<?= BASE_URL ?>/src/spa.js?v=<?= time() ?>"></script>
  <script src="<?= BASE_URL ?>/src/parallax.js?v=<?= time() ?>"></script>
  <script src="<?= BASE_URL ?>/src/slider.js?v=<?= time() ?>"></script>
  <script>
      function toggleMobileMenu() {
          const navLinks = document.getElementById('nav-links');
          navLinks.classList.toggle('open');
      }

      function toggleUserMenu(event) {
          event.stopPropagation();
          const dropdown = document.getElementById('user-dropdown-menu');
          dropdown.classList.toggle('show');
      }

      // Close dropdowns when clicking outside
      window.addEventListener('click', function(e) {
          const dropdown = document.getElementById('user-dropdown-menu');
          if (dropdown && dropdown.classList.contains('show') && !e.target.closest('.user-menu-container')) {
              dropdown.classList.remove('show');
          }
      });
      
      // Initialize parallax if present
      if (typeof initParallax === 'function') {
          initParallax();
      }
  </script>
</body>
</html>

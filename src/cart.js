function toggleCart() {
    const cart = document.getElementById('side-cart');
    const overlay = document.getElementById('cart-overlay');
    cart.classList.toggle('open');
    overlay.classList.toggle('open');
    
    if (cart.classList.contains('open')) {
        loadCart();
    }
}

function addToCart(productId, btnElement) {
    const originalText = btnElement.innerHTML;
    
    // Check if there is a quantity input next to it
    let qty = 1;
    const qtyInput = document.getElementById('qty-' + productId);
    if (qtyInput) {
        qty = parseInt(qtyInput.value) || 1;
    }

    btnElement.innerHTML = '✓ ...';
    btnElement.style.background = '#22c55e';
    btnElement.disabled = true;

    const formData = new FormData();
    formData.append('id', productId);
    formData.append('quantity', qty);

    fetch(window.BASE_URL + '/ajax_cart.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            btnElement.innerHTML = "Ajouté ! ✔";
            updateCartCount();
            
            // Trigger Parallax Notification
            showParallaxNotification(btnElement, qty);
            
            // Refresh cart data in background (without opening)
            loadCart();
            
        } else {
            alert("Erreur lors de l'ajout.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        setTimeout(() => {
            btnElement.innerText = originalText;
            btnElement.disabled = false;
        }, 1500);
    });
}

function loadCart() {
    fetch(window.BASE_URL + '/ajax_cart.php?action=get')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('cart-items');
        const totalPrice = document.getElementById('cart-total-price');
        
        container.innerHTML = '';
        
        if (data.items.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#94a3b8; margin-top:2rem;">Votre panier est vide.</p>';
        } else {
            data.items.forEach(item => {
                container.innerHTML += `
                    <div class="cart-item">
                        <img src="${window.BASE_URL}${item.image || 'https://via.placeholder.com/100'}" alt="${item.title}" class="cart-item-img">
                        <div class="cart-item-details">
                            <h4>${item.title}</h4>
                            <div class="cart-item-actions">
                                <span class="cart-item-price">${item.price} ${window.shopCurrency || '€'}</span>
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateQty('${item.id}', ${item.quantity - 1})">-</button>
                                    <span class="qty-value">${item.quantity}</span>
                                    <button class="qty-btn" onclick="updateQty('${item.id}', ${item.quantity + 1})">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        totalPrice.innerText = data.total.toFixed(2);
        document.querySelector('.cart-items-count').innerText = data.count;
    });
}

function updateQty(productId, newQty) {
    if (newQty < 0) return;
    
    const formData = new FormData();
    formData.append('id', productId);
    
    if (newQty === 0) {
        fetch(window.BASE_URL + '/ajax_cart.php?action=remove', { method: 'POST', body: formData })
            .then(() => loadCart());
    } else {
        formData.append('quantity', newQty);
        fetch(window.BASE_URL + '/ajax_cart.php?action=update', { method: 'POST', body: formData })
            .then(() => loadCart());
    }
}

function updateCartCount() {
    fetch(window.BASE_URL + '/ajax_cart.php?action=get')
    .then(response => response.json())
    .then(data => {
        document.querySelector('.cart-items-count').innerText = data.count;
        
        // Jiggle animation on cart icon
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.classList.add('jiggle');
            setTimeout(() => cartIcon.classList.remove('jiggle'), 300);
        }
    });
}

function showParallaxNotification(btnElement, qty) {
    const rect = btnElement.getBoundingClientRect();
    const notification = document.createElement('div');
    notification.className = 'parallax-cart-notification';
    notification.innerHTML = `+${qty} 🛒`;
    
    // Set initial position at the button
    notification.style.left = `${rect.left + rect.width / 2}px`;
    notification.style.top = `${rect.top}px`;
    
    document.body.appendChild(notification);
    
    // Force reflow
    void notification.offsetWidth;
    
    // Animate it up and floating
    notification.style.transform = `translate(-50%, -100px) scale(1.5)`;
    notification.style.opacity = '0';
    
    setTimeout(() => {
        notification.remove();
    }, 1000);
}

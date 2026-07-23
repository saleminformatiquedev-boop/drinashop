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
    const originalText = btnElement.innerText;
    btnElement.innerText = "Ajout en cours...";
    btnElement.disabled = true;

    const formData = new FormData();
    formData.append('id', productId);

    fetch('/ajax_cart.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            btnElement.innerText = "Ajouté ! ✔";
            updateCartCount();
            // Automatically open cart to show feedback
            const cart = document.getElementById('side-cart');
            if(!cart.classList.contains('open')){
                toggleCart();
            } else {
                loadCart();
            }
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
    fetch('/ajax_cart.php?action=get')
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
                        <img src="${item.image || 'https://via.placeholder.com/100'}" alt="${item.title}" class="cart-item-img">
                        <div class="cart-item-details">
                            <h4>${item.title}</h4>
                            <div class="cart-item-actions">
                                <span class="cart-item-price">${item.price} €</span>
                                <div class="qty-control">
                                    <button onclick="updateQty('${item.id}', ${item.quantity - 1})">-</button>
                                    <span>${item.quantity}</span>
                                    <button onclick="updateQty('${item.id}', ${item.quantity + 1})">+</button>
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
        fetch('/ajax_cart.php?action=remove', { method: 'POST', body: formData })
            .then(() => loadCart());
    } else {
        formData.append('quantity', newQty);
        fetch('/ajax_cart.php?action=update', { method: 'POST', body: formData })
            .then(() => loadCart());
    }
}

function updateCartCount() {
    fetch('/ajax_cart.php?action=get')
    .then(response => response.json())
    .then(data => {
        document.querySelector('.cart-items-count').innerText = data.count;
    });
}

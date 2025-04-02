document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.id;
            const quantity = this.closest('.card').querySelector('.quantity-input')?.value || 1;
            updateCart(bookId, quantity, 'add');
        });
    });

    // Quantity controls
    document.querySelectorAll('.plus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
        });
    });

    document.querySelectorAll('.minus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });

    // Cart page specific functionality
    if (document.querySelector('.cart-page')) {
        setupCartPage();
    }
});

function updateCart(bookId, quantity, action) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bookId: bookId,
            quantity: quantity,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartUI(data.count, data.total);
            showAlert('Book added to cart!', 'success');
        } else {
            showAlert('Failed to update cart', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

function updateCartUI(count, total) {
    // Update cart badge
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
    }

    // Update cart total if on cart page
    const cartTotalElement = document.querySelector('.cart-total');
    if (cartTotalElement) {
        cartTotalElement.textContent = `$${total.toFixed(2)}`;
    }
}

function setupCartPage() {
    // Update quantity in cart
    document.querySelectorAll('.cart-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const bookId = this.dataset.id;
            const newQuantity = this.value;
            updateCart(bookId, newQuantity, 'update');
        });
    });

    // Remove item from cart
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.id;
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                updateCart(bookId, 0, 'remove');
                this.closest('.cart-item').remove();
            }
        });
    });

    // Checkout button
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '1000';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}
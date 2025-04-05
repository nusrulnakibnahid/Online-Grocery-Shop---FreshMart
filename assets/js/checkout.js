// checkout.js - Checkout-related JavaScript for FreshMart Online Grocery Shop

// Load Checkout Summary
function loadCheckoutSummary() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const checkoutSummary = document.getElementById('checkout-summary');

    if (cart.length === 0) {
        checkoutSummary.innerHTML = '<p>Your cart is empty.</p>';
        return;
    }

    const total = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);
    checkoutSummary.innerHTML = `
        <h4>Order Summary</h4>
        <ul>
            ${cart.map(item => `
                <li>${item.title} x ${item.quantity} - $${item.price * item.quantity}</li>
            `).join('')}
        </ul>
        <hr>
        <h5>Total: $${total.toFixed(2)}</h5>
    `;
}

// Setup Checkout Form Handlers
function setupCheckoutFormHandlers() {
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Order placed successfully!');
            localStorage.removeItem('cart');
            window.location.href = 'index.html';
        });
    }
}
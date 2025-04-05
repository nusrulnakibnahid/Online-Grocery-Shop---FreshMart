// cart.js - Cart-related JavaScript for FreshMart Online Grocery Shop

// Base API URL for products
const API_BASE_URL = 'https://fakestoreapi.com/products';

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function () {
    loadCartItems();
    updateCartCount();
});

// Load Cart Items
async function loadCartItems() {
    const cartItemsContainer = document.getElementById('cart-items');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];

    console.log('Cart Data:', cart); // Debug cart data

    if (cart.length === 0) {
        // If the cart is empty, display a message
        cartItemsContainer.innerHTML = '<p>Your cart is empty.</p>';
        return;
    }

    try {
        // Fetch product details for items in the cart
        const productIds = cart.map(item => item.id);
        const response = await axios.get(API_BASE_URL);
        const products = response.data.filter(product => productIds.includes(product.id));

        console.log('API Products:', products); // Debug API response

        // Generate HTML for each cart item
        cartItemsContainer.innerHTML = cart.map(cartItem => {
            const product = products.find(p => p.id === cartItem.id);
            if (!product) {
                console.error(`Product with ID ${cartItem.id} not found in API response.`);
                return ''; // Skip this product if not found
            }
            return `
                <div class="cart-item mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="${product.image}" class="img-fluid" alt="${product.title}">
                        </div>
                        <div class="col-md-6">
                            <h5>${product.title}</h5>
                            <p>$${product.price.toFixed(2)}</p>
                        </div>
                        <div class="col-md-4">
                            <div class="quantity-control d-flex align-items-center">
                                <button class="btn btn-outline-secondary" onclick="updateQuantity(${cartItem.id}, -1)">-</button>
                                <input type="text" class="form-control mx-2 text-center" value="${cartItem.quantity}" disabled>
                                <button class="btn btn-outline-secondary" onclick="updateQuantity(${cartItem.id}, 1)">+</button>
                            </div>
                            <button class="btn btn-danger mt-2 w-100" onclick="removeFromCart(${cartItem.id})">Remove</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Error loading cart items:', error);
    }
}

// Update Quantity of a Cart Item
function updateQuantity(productId, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const product = cart.find(item => item.id === productId);

    if (product) {
        product.quantity += change;

        // If the quantity is less than 1, remove the product from the cart
        if (product.quantity < 1) {
            cart = cart.filter(item => item.id !== productId);
        }

        // Save the updated cart back to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Reload the cart items
        loadCartItems();

        // Update the cart count in the navbar
        updateCartCount();
    }
}

// Remove a Product from the Cart
function removeFromCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => item.id !== productId);

    // Save the updated cart back to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // Reload the cart items
    loadCartItems();

    // Update the cart count in the navbar
    updateCartCount();
}

// Update Cart Count in the Navbar
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
}
// products.js - Product-related JavaScript for FreshMart Online Grocery Shop

// Base API URL
const API_BASE_URL = 'https://fakestoreapi.com';

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function () {
    initializeApp();
});

// Initialize Application
function initializeApp() {
    if (window.location.pathname.includes('products.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        const searchQuery = urlParams.get('search');

        if (category) {
            loadProductsByCategory(category);
        } else if (searchQuery) {
            searchProducts(searchQuery);
        } else {
            loadAllProducts();
        }
    }

    if (window.location.pathname.includes('product-details.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        if (productId) {
            loadProductDetails(productId);
        } else {
            window.location.href = 'products.html';
        }
    }

    // Update cart count on page load
    updateCartCount();
}

// Load All Products
async function loadAllProducts() {
    const productsContainer = document.getElementById('products-container');
    try {
        const response = await axios.get(`${API_BASE_URL}/products`);
        productsContainer.innerHTML = response.data.map(product => `
            <div class="col-md-4 mb-4">
                <div class="card product-card">
                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                    <div class="card-body">
                        <h5 class="card-title">${product.title}</h5>
                        <p class="card-text">${product.description}</p>
                        <p class="product-price">$${product.price}</p>
                        <div class="quantity-control mb-3">
                            <label for="quantity-${product.id}" class="form-label">Quantity:</label>
                            <input type="number" class="form-control" id="quantity-${product.id}" value="1" min="1">
                        </div>
                        <button class="btn btn-primary w-100" onclick="addToCart(${product.id}, '${product.title}', ${product.price}, '${product.image}', document.getElementById('quantity-${product.id}').value)">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Load Products by Category
async function loadProductsByCategory(category) {
    const productsContainer = document.getElementById('products-container');
    try {
        const response = await axios.get(`${API_BASE_URL}/products/category/${category}`);
        productsContainer.innerHTML = response.data.map(product => `
            <div class="col-md-4 mb-4">
                <div class="card product-card">
                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                    <div class="card-body">
                        <h5 class="card-title">${product.title}</h5>
                        <p class="card-text">${product.description}</p>
                        <p class="product-price">$${product.price}</p>
                        <div class="quantity-control mb-3">
                            <label for="quantity-${product.id}" class="form-label">Quantity:</label>
                            <input type="number" class="form-control" id="quantity-${product.id}" value="1" min="1">
                        </div>
                        <button class="btn btn-primary w-100" onclick="addToCart(${product.id}, '${product.title}', ${product.price}, '${product.image}', document.getElementById('quantity-${product.id}').value)">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading products by category:', error);
    }
}

// Search Products
async function searchProducts(query) {
    const productsContainer = document.getElementById('products-container');
    try {
        const response = await axios.get(`${API_BASE_URL}/products`);
        const filteredProducts = response.data.filter(product =>
            product.title.toLowerCase().includes(query.toLowerCase())
        );
        productsContainer.innerHTML = filteredProducts.map(product => `
            <div class="col-md-4 mb-4">
                <div class="card product-card">
                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                    <div class="card-body">
                        <h5 class="card-title">${product.title}</h5>
                        <p class="card-text">${product.description}</p>
                        <p class="product-price">$${product.price}</p>
                        <div class="quantity-control mb-3">
                            <label for="quantity-${product.id}" class="form-label">Quantity:</label>
                            <input type="number" class="form-control" id="quantity-${product.id}" value="1" min="1">
                        </div>
                        <button class="btn btn-primary w-100" onclick="addToCart(${product.id}, '${product.title}', ${product.price}, '${product.image}', document.getElementById('quantity-${product.id}').value)">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error searching products:', error);
    }
}

// Load Product Details
async function loadProductDetails(productId) {
    const productDetailsContainer = document.getElementById('product-details');
    try {
        const response = await axios.get(`${API_BASE_URL}/products/${productId}`);
        const product = response.data;
        productDetailsContainer.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${product.image}" class="img-fluid" alt="${product.title}">
                </div>
                <div class="col-md-6">
                    <h2>${product.title}</h2>
                    <p>${product.description}</p>
                    <p class="product-price">$${product.price}</p>
                    <div class="quantity-control mb-3">
                        <label for="quantity-${product.id}" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="quantity-${product.id}" value="1" min="1">
                    </div>
                    <button class="btn btn-success w-100" onclick="addToCart(${product.id}, '${product.title}', ${product.price}, '${product.image}', document.getElementById('quantity-${product.id}').value)">
                        Add to Cart
                    </button>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading product details:', error);
    }
}

// Add to Cart Functionality
function addToCart(productId, productName, productPrice, productImage, quantity) {
    // Get the cart from localStorage or initialize an empty array
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Check if the product already exists in the cart
    const existingProduct = cart.find(item => item.id === productId);

    if (existingProduct) {
        // If the product exists, update the quantity
        existingProduct.quantity += parseInt(quantity, 10);
    } else {
        // If the product doesn't exist, add it to the cart
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            quantity: parseInt(quantity, 10),
        });
    }

    // Save the updated cart back to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // Update the cart count in the navbar
    updateCartCount();

    // Show a success message
    alert(`${productName} added to cart!`);
}

// Update Cart Count in the Navbar
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
}
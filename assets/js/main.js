// main.js - Main JavaScript for FreshMart Online Grocery Shop

// Base API URL
const API_BASE_URL = 'https://fakestoreapi.com';

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
});

// Initialize Application
function initializeApp() {
    checkAuthStatus();
    loadCategoriesForNav();
    updateCartCount();
    
    if (window.location.pathname.includes('index.html') || window.location.pathname === '/') {
        loadFeaturedCategories();
        loadFeaturedProducts();
        loadSpecialOffers();
    }
    
    if (window.location.pathname.includes('products.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        category ? loadProductsByCategory(category) : loadAllProducts();
        loadCategoriesForSidebar();
    }
    
    if (window.location.pathname.includes('product-details.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        productId ? loadProductDetails(productId) : window.location.href = 'products.html';
    }
    
    if (window.location.pathname.includes('cart.html')) {
        loadCartItems();
    }
    
    if (window.location.pathname.includes('checkout.html')) {
        loadCheckoutSummary();
        setupCheckoutFormHandlers();
    }
    
    if (window.location.pathname.includes('profile.html')) {
        loadUserProfile();
    }
    
    if (window.location.pathname.includes('orders.html')) {
        loadUserOrders();
    }
}

// Setup Event Listeners
function setupEventListeners() {
    const searchBtn = document.getElementById('search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', handleSearch);
    }
    
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logoutUser);
    }
}

// Load Categories for Navbar
async function loadCategoriesForNav() {
    const categoryDropdown = document.getElementById('category-dropdown');
    if (!categoryDropdown) return;
    
    try {
        const response = await axios.get(`${API_BASE_URL}/products/categories`);
        categoryDropdown.innerHTML = response.data.map(category => 
            `<li><a class="dropdown-item" href="products.html?category=${category}">${category}</a></li>`
        ).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load Featured Products
async function loadFeaturedProducts() {
    const featuredProductsContainer = document.getElementById('featured-products');
    try {
        const response = await axios.get(`${API_BASE_URL}/products?limit=4`);
        featuredProductsContainer.innerHTML = response.data.map(product => `
            <div class="col-md-3">
                <div class="card">
                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                    <div class="card-body">
                        <h5 class="card-title">${product.title}</h5>
                        <p class="card-text">$${product.price}</p>
                        <a href="product-details.html?id=${product.id}" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading featured products:', error);
    }
}

// Load Special Offers
async function loadSpecialOffers() {
    const specialOffersContainer = document.getElementById('special-offers');
    try {
        const response = await axios.get(`${API_BASE_URL}/products?limit=4`);
        specialOffersContainer.innerHTML = response.data.map(product => `
            <div class="col-md-3">
                <div class="card border-success">
                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                    <div class="card-body">
                        <h5 class="card-title">${product.title}</h5>
                        <p class="card-text text-success">$${(product.price * 0.9).toFixed(2)} <small class="text-muted">(10% Off)</small></p>
                        <a href="product-details.html?id=${product.id}" class="btn btn-success">Buy Now</a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading special offers:', error);
    }
}

// Update Cart Count
function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    cartCount.textContent = cart.length;
}

// Handle Search
function handleSearch() {
    const searchInput = document.getElementById('search-input').value;
    if (searchInput.trim()) {
        window.location.href = `products.html?search=${searchInput}`;
    }
}

// Check Authentication Status
function checkAuthStatus() {
    const user = JSON.parse(localStorage.getItem('user'));
    const authButtons = document.getElementById('auth-buttons');
    const userProfile = document.getElementById('user-profile');
    
    if (user) {
        document.getElementById('username').textContent = user.name;
        authButtons.classList.add('d-none');
        userProfile.classList.remove('d-none');
    } else {
        authButtons.classList.remove('d-none');
        userProfile.classList.add('d-none');
    }
}

// Logout User
function logoutUser() {
    localStorage.removeItem('user');
    window.location.reload();
}

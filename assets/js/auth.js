// auth.js - Authentication-related JavaScript for FreshMart Online Grocery Shop

const API_BASE_URL = 'https://fakestoreapi.com/auth/login'; // Replace with your actual API URL

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.pathname.includes('login.html')) {
        setupLoginForm();
    }
    if (window.location.pathname.includes('register.html')) {
        setupRegisterForm();
    }
});

// Setup Login Form
function setupLoginForm() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            try {
                const response = await axios.post(`${API_BASE_URL}/auth/login`, {
                    username: email,
                    password,
                });
                localStorage.setItem('user', JSON.stringify(response.data));
                window.location.href = 'index.html';
            } catch (error) {
                alert('Login failed. Please check your credentials.');
                console.error('Login error:', error);
            }
        });
    }
}

// Setup Register Form
function setupRegisterForm() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;

            try {
                const response = await axios.post(`${API_BASE_URL}/users`, {
                    name,
                    email,
                    password,
                });
                
                if (response.data) {
                    alert('Registration successful! Redirecting to login page.');
                    localStorage.setItem('user', JSON.stringify(response.data));
                    window.location.href = 'login.html';
                } else {
                    throw new Error('Registration failed.');
                }
            } catch (error) {
                alert('Registration failed. Please try again.');
                console.error('Registration error:', error);
            }
        });
    }
}

// Logout User
function logoutUser() {
    localStorage.removeItem('user');
    window.location.href = 'login.html';
}

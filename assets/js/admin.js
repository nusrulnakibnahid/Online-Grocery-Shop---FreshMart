// admin.js - Admin-related JavaScript for FreshMart Online Grocery Shop


const API_BASE_URL = 'https://fakestoreapi.com/auth/login';
// Load Admin Dashboard Data
async function loadDashboardData() {
    try {
        const [productsResponse, usersResponse, ordersResponse] = await Promise.all([
            axios.get(`${API_BASE_URL}/products`),
            axios.get(`${API_BASE_URL}/users`),
            axios.get(`${API_BASE_URL}/orders`),
        ]);

        document.getElementById('total-products').textContent = productsResponse.data.length;
        document.getElementById('total-users').textContent = usersResponse.data.length;
        document.getElementById('total-orders').textContent = ordersResponse.data.length;
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Load Products for Admin
async function loadAdminProducts() {
    const productsContainer = document.getElementById('admin-products');
    try {
        const response = await axios.get(`${API_BASE_URL}/products`);
        productsContainer.innerHTML = response.data.map(product => `
            <tr>
                <td>${product.id}</td>
                <td>${product.title}</td>
                <td>${product.category}</td>
                <td>$${product.price}</td>
                <td>
                    <button class="btn btn-sm btn-warning">Edit</button>
                    <button class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading admin products:', error);
    }
}
<!-- HEADER -->
<h1 align="center">
  🛒 FreshMart – Online Grocery Shop
</h1>
<p align="center">
  <strong>A complete online grocery store solution built using PHP, MySQL, JS & Bootstrap</strong><br/>
  ✨ Shop Fresh | Manage Smart | Deliver Fast ✨
</p>

<p align="center">
  <img src="https://img.shields.io/github/languages/top/yourusername/Online-Grocery-Shop---FreshMart?style=for-the-badge" alt="Top Language" />
  <img src="https://img.shields.io/github/repo-size/yourusername/Online-Grocery-Shop---FreshMart?style=for-the-badge" alt="Repo Size" />
  <img src="https://img.shields.io/github/license/yourusername/Online-Grocery-Shop---FreshMart?style=for-the-badge" alt="License" />
  <img src="https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge" alt="Status" />
</p>

---

## 📖 About the Project

**FreshMart** is a simple yet powerful online grocery store platform that allows customers to explore grocery items, add them to a cart, place orders, and track their purchases. The admin panel provides features to manage products, categories, orders, users, and reports — all from a clean, user-friendly interface.

---

## 🌟 Key Features

### 🧑‍💼 User Panel
- 🛒 Browse & search products with categories
- ➕ Add to cart, update quantity, remove items
- ✅ Checkout & order confirmation
- 🔐 Login, Register, and Logout
- 👤 Manage profile and order history

### 🛠️ Admin Panel
- 📦 Manage products, categories, and discounts
- 👥 View and manage users
- 📑 View orders and their details
- 📊 Access reports and dashboards

---

## 🖼️ UI Preview

![localhost_3000_index php (1)](https://github.com/user-attachments/assets/27b5e889-8767-4f26-97fa-f5c24b25d657)



---

## 📁 Project Structure

```
📦 Online-Grocery-Shop---FreshMart
├── admin/                  → Admin Panel Pages
├── api/                    → Cart APIs (add, remove, update, cancel)
├── assets/
│   ├── css/                → Stylesheets
│   ├── js/                 → Scripts
│   └── images/             → UI/UX assets
├── includes/               → DB configs & reusable PHP
├── cart.php
├── checkout.php
├── grocery_shop.sql        → MySQL DB dump
├── index.php
├── login.php / logout.php
├── profile.php
├── orders.php
├── register.php
└── ...and more
```

---

## 🧰 Tech Stack

| Technology     | Role                          |
|----------------|-------------------------------|
| 🐘 PHP         | Backend scripting              |
| 🛢 MySQL       | Database                       |
| 🎨 HTML/CSS    | Markup and styling             |
| ⚙️ Bootstrap   | Responsive design              |
| 🔁 JavaScript  | Frontend interaction           |
| 💡 Axios       | API requests (optional)        |

---

## ⚙️ Getting Started

### 🔧 Requirements
- [XAMPP](https://www.apachefriends.org/)
- PHP 7.0 or higher
- MySQL / phpMyAdmin

### 🚀 Setup Instructions

```bash
# 1. Clone the repository
git clone https://github.com/nusrulnakibnahid/Online-Grocery-Shop---FreshMart.git

# 2. Move project to XAMPP htdocs directory
mv Online-Grocery-Shop---FreshMart /xampp/htdocs/

# 3. Import the database
# - Open phpMyAdmin
# - Create DB named freshmart
# - Import grocery_shop.sql

# 4. Run the project in your browser
http://localhost/Online-Grocery-Shop---FreshMart/
```

---

## 🔐 Admin Login Info

Use the following default credentials to access the admin panel:

```bash
📧 Email:    nahid@gmail.com
🔑 Password: nahid1054
```

You can change them directly in the `users` table of your database.

---

## 🎯 Future Improvements

- 🔍 Product search & filter with live suggestions
- 🛎️ Notification system for orders
- 💬 User feedback & ratings
- 📦 Inventory tracking system
- 🌐 Multi-language support
- 📱 Mobile responsive PWA support

---

## 🤝 Contributing

Contributions are welcome! 🚀

1. Fork the project
2. Create your branch: `git checkout -b feature/YourFeature`
3. Commit changes: `git commit -m 'Add your message'`
4. Push to the branch: `git push origin feature/YourFeature`
5. Create a Pull Request

---


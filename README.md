# Gulio E-Commerce Platform

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-success.svg)](https://github.com)

A comprehensive, production-ready e-commerce platform built with PHP and MySQL. Features include product management, shopping cart, order processing, payment integration, and a complete admin dashboard.

**🌐 Live Website**: [https://ghostwhite-dog-517526.hostingersite.com](https://ghostwhite-dog-517526.hostingersite.com)

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Project Structure](#-project-structure)
- [Security](#-security)
- [API Documentation](#-api-documentation)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

## ✨ Features

### E-Commerce Core
- ✅ **Product Catalog Management** - Add, edit, delete, and manage products with images
- ✅ **Advanced Search & Filtering** - Search by name, category, price range with sorting options
- ✅ **Shopping Cart System** - Full cart management with quantity updates and persistence
- ✅ **Order Management** - Complete order lifecycle from creation to fulfillment
- ✅ **Payment Processing** - Integrated payment gateway (Paystack) support
- ✅ **Invoice Generation** - Automatic invoice generation for completed orders
- ✅ **Coupon System** - Discount codes and coupon management
- ✅ **Inventory Management** - Stock tracking, low stock alerts, and availability management

### User Management
- ✅ **User Authentication** - Secure registration, login, and session management
- ✅ **Role-Based Access Control** - Admin, Vendor, and User roles with permissions
- ✅ **Profile Management** - User profiles with preferences and settings
- ✅ **Order History** - Complete order tracking and history for customers

### Admin Dashboard
- ✅ **Product Management** - Full CRUD operations for products
- ✅ **Order Management** - View, update, and manage all orders
- ✅ **Bulk Operations** - Bulk delete, featured status updates
- ✅ **Statistics** - Order statistics and product overview
- ✅ **Image Upload** - Secure image upload with validation

### Security Features
- ✅ **CSRF Protection** - All forms protected with CSRF tokens
- ✅ **SQL Injection Prevention** - PDO prepared statements throughout
- ✅ **XSS Prevention** - Input sanitization and output escaping
- ✅ **Password Hashing** - Bcrypt with secure cost factor
- ✅ **Secure Sessions** - HttpOnly, Secure, SameSite cookie settings
- ✅ **Input Validation** - Comprehensive validation and sanitization

## 🛠 Technology Stack

### Backend
- **PHP** 7.4+ - Server-side scripting
- **MySQL/MariaDB** 5.7+ - Relational database
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling with Grid and Flexbox
- **JavaScript (ES6+)** - Client-side interactivity
- **Font Awesome 6.0** - Icons
- **Google Fonts (Inter)** - Typography

### Security & Tools
- **CSRF Protection** - Token-based form protection
- **Password Hashing** - Bcrypt implementation
- **Session Management** - Secure session handling
- **Logging System** - Comprehensive error and activity logging

## 📦 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- PHP Extensions:
  - PDO
  - PDO_MySQL
  - JSON
  - Session
  - Fileinfo
  - GD (for image processing)

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/gulio-ecommerce.git
cd gulio-ecommerce
```

### Step 2: Configure Database

1. Create a MySQL database:
```sql
CREATE DATABASE gulio_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update `config/config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gulio_production');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
```

### Step 3: Set Permissions

```bash
chmod 755 logs/
chmod 644 config/config.php
chmod 755 assets/images/products/
```

### Step 4: Run Database Setup

Visit `https://ghostwhite-dog-517526.hostingersite.com/setup.php` in your browser and follow the setup wizard to:
- Test database connection
- Run database migrations
- Create initial admin user (optional)

### Step 5: Access the Application

- **Frontend**: `https://ghostwhite-dog-517526.hostingersite.com/`
- **Admin Dashboard**: `https://ghostwhite-dog-517526.hostingersite.com/admin/`
- **Setup Wizard**: `https://ghostwhite-dog-517526.hostingersite.com/setup.php`

## ⚙️ Configuration

### Environment Configuration

The application uses `config/config.php` for configuration. Key settings:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gulio_production');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Application Settings
define('APP_ENV', 'production'); // or 'development'
define('APP_URL', 'https://ghostwhite-dog-517526.hostingersite.com');
```

### Security Configuration

Security settings are managed in `config/app.php`:
- Session lifetime
- CSRF token settings
- Password hashing algorithm
- File upload limits

### Payment Configuration

Configure payment gateway in `includes/paystack.php`:
- API keys
- Webhook URLs
- Currency settings

## 📖 Usage

### Admin Dashboard

1. **Login**: Access `/admin/` with admin credentials
2. **Add Products**: Click "Add Product" button
3. **Manage Orders**: View and update order status
4. **Bulk Operations**: Select multiple products for bulk actions

### Customer Features

1. **Browse Products**: Visit the shop page
2. **Search & Filter**: Use search bar and filters
3. **Add to Cart**: Click "Add to Cart" on product pages
4. **Checkout**: Proceed to checkout and complete order
5. **View Orders**: Access order history in "My Orders"

## 📁 Project Structure

```
gulio-ecommerce/
├── admin/
│   └── index.php              # Admin dashboard
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── images/
│   │   └── products/         # Product images
│   └── js/
│       ├── app.js            # Main application logic
│       ├── chatbot.js        # Chatbot functionality
│       └── geolocation.js    # Location services
├── config/
│   ├── app.php               # Application configuration
│   └── config.php            # Database configuration
├── database/
│   └── migrations/          # Database migration files
│       ├── 001_create_users_table.sql
│       ├── 002_create_vendors_table.sql
│       ├── 008_create_products_table.sql
│       └── ...
├── includes/
│   ├── auth.php              # Authentication system
│   ├── cart.php              # Shopping cart functionality
│   ├── database.php           # Database connection
│   ├── helpers.php            # Helper functions
│   ├── orders.php             # Order management
│   ├── products.php           # Product management
│   ├── security.php           # Security utilities
│   └── ...
├── pages/
│   ├── cart.php              # Shopping cart page
│   ├── checkout.php          # Checkout page
│   ├── login.php             # Login page
│   ├── my-orders.php          # Order history
│   ├── products.php           # Product listing
│   └── ...
├── .htaccess                  # Apache configuration
├── index.php                  # Main entry point
├── setup.php                  # Database setup wizard
├── README.md                  # This file
├── PRODUCTION.md              # Production deployment guide
├── SETUP_GUIDE.md             # Detailed setup instructions
└── CHANGELOG.md               # Version history
```

## 🔒 Security

### Implemented Security Measures

- **CSRF Protection**: All forms use CSRF tokens
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Prevention**: Input sanitization and output escaping
- **Password Security**: Bcrypt hashing with cost factor 12
- **Session Security**: HttpOnly, Secure, SameSite cookies
- **Input Validation**: Comprehensive validation on all inputs
- **File Upload Security**: File type and size validation
- **Error Handling**: Secure error messages in production

### Security Best Practices

1. **Change Default Passwords**: Update all default admin passwords
2. **Enable HTTPS**: Use SSL/TLS certificates
3. **Regular Updates**: Keep PHP and dependencies updated
4. **Database Backups**: Regular automated backups
5. **Access Control**: Limit admin access to trusted IPs
6. **Error Logging**: Monitor error logs regularly

See [PRODUCTION.md](PRODUCTION.md) for detailed security configuration.

## 📡 API Documentation

### Authentication Endpoints

#### Login
```php
POST /index.php
{
    "action": "login",
    "email": "user@example.com",
    "password": "password123"
}
```

#### Register
```php
POST /index.php
{
    "action": "register",
    "email": "user@example.com",
    "password": "password123",
    "name": "John Doe"
}
```

### Cart Endpoints

#### Add to Cart
```php
POST /index.php
{
    "action": "add_to_cart",
    "product_id": 1,
    "quantity": 2
}
```

#### Update Cart
```php
POST /index.php
{
    "action": "update_cart",
    "item_id": 1,
    "quantity": 3
}
```

### Order Endpoints

#### Create Order
```php
POST /index.php
{
    "action": "create_order",
    "customer_info": {...},
    "shipping_address": {...},
    "items": [...]
}
```

## 🚀 Deployment

### Production Deployment Checklist

- [ ] Database configured and migrations run
- [ ] Admin user created with secure password
- [ ] HTTPS/SSL enabled
- [ ] Error reporting disabled
- [ ] Logging configured
- [ ] File permissions set correctly
- [ ] Security headers configured
- [ ] Database backups scheduled
- [ ] Environment set to 'production'

See [PRODUCTION.md](PRODUCTION.md) for detailed deployment instructions.

### Quick Deployment

1. Upload files to web server
2. Configure database in `config/config.php`
3. Set proper file permissions
4. Run `setup.php` to initialize database
5. Access admin dashboard and configure

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit your changes**: `git commit -m 'Add amazing feature'`
4. **Push to the branch**: `git push origin feature/amazing-feature`
5. **Open a Pull Request**

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write clear commit messages

### Reporting Issues

Use the GitHub Issues tracker to report bugs or request features. Include:
- Description of the issue
- Steps to reproduce
- Expected vs actual behavior
- PHP and MySQL versions
- Error messages/logs

## 📄 License

This project is proprietary software. All rights reserved.

See [LICENSE](LICENSE) file for details.

## 📞 Support

### Documentation
- [Setup Guide](SETUP_GUIDE.md) - Detailed installation instructions
- [Production Guide](PRODUCTION.md) - Production deployment guide
- [Changelog](CHANGELOG.md) - Version history and changes

### Getting Help
- **Live Website**: [https://ghostwhite-dog-517526.hostingersite.com](https://ghostwhite-dog-517526.hostingersite.com)
- **Issues**: [GitHub Issues](https://github.com/yourusername/gulio-ecommerce/issues)
- **Email**: support@gulio.com
- **Documentation**: Check the `/docs` directory

## 🎯 Roadmap

### Planned Features
- [ ] Multi-vendor marketplace support
- [ ] Advanced analytics dashboard
- [ ] Email notifications system
- [ ] Mobile app API
- [ ] Advanced reporting
- [ ] Inventory alerts
- [ ] Customer reviews and ratings
- [ ] Wishlist functionality

### Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

---

**Built with ❤️ for modern e-commerce**

🌐 **Live Website**: [https://ghostwhite-dog-517526.hostingersite.com](https://ghostwhite-dog-517526.hostingersite.com)

For more information, visit the [documentation](PRODUCTION.md) or [open an issue](https://github.com/yourusername/gulio-ecommerce/issues).

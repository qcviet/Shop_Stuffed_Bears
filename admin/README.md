# Admin Panel Setup Guide - Shop Gau Yeu

## Overview
This admin panel provides a secure interface for managing your e-commerce website. It includes user management, product management, order tracking, and system statistics.

## Initial Setup

### 1. Create Your First Admin User
1. Navigate to `admin/create_admin.php` in your browser
2. Fill in the form with your admin credentials:
   - Username: Choose a secure username
   - Email: Your email address
   - Full Name: Your full name
   - Password: Choose a strong password
3. Click "Tạo tài khoản Admin" (Create Admin Account)
4. **IMPORTANT**: Delete the `create_admin.php` file after creating your admin user for security

### 2. Access Admin Panel
1. Go to `admin/login.php`
2. Enter your admin credentials
3. You'll be redirected to the admin dashboard

## Features

### Dashboard
- Real-time statistics (users, products, orders, revenue)
- Quick access to all admin functions
- Recent activity overview

### User Management
- View all registered users
- Manage user roles and permissions
- Edit user information

### Product Management
- Add/edit/delete products
- Manage product categories
- Track product inventory

### Order Management
- View all orders
- Update order status
- Track payment status

### Category Management
- Create/edit/delete product categories
- Organize your product catalog

## Security Features

- Separate admin session management
- Role-based access control
- Secure password hashing
- SQL injection protection
- XSS protection

## File Structure

```
admin/
├── index.php              # Main admin entry point
├── login.php              # Admin login form
├── logout.php             # Logout functionality
├── create_admin.php       # Initial admin user creation (DELETE AFTER USE)
├── dashboard-admin.php    # Main dashboard interface
├── pages/                 # Admin page content
│   ├── dashboard-content.php
│   ├── users.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   └── settings.php
└── README.md              # This file
```

## Troubleshooting

### Can't Access Admin Panel?
- Ensure you have created an admin user first
- Check that your database connection is working
- Verify that the `users` table exists and has the correct structure

### Login Issues?
- Make sure you're using the correct username/password
- Check that your user has the 'admin' role in the database
- Clear your browser cookies if you're having session issues

### Database Connection Issues?
- Check your database configuration in `config/database.php`
- Ensure MySQL/PDO extensions are enabled
- Verify database credentials and permissions

## Security Recommendations

1. **Delete `create_admin.php`** after creating your first admin user
2. **Use strong passwords** for admin accounts
3. **Regularly backup** your database
4. **Monitor access logs** for suspicious activity
5. **Keep your system updated** with security patches

## Support

If you encounter any issues, check:
1. PHP error logs
2. Database connection status
3. File permissions
4. Session configuration

For additional help, refer to the main project documentation or contact your system administrator. 
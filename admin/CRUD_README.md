# Admin Panel CRUD Operations

This document describes the CRUD (Create, Read, Update, Delete) functionality implemented in the admin panel for managing products, categories, users, and orders.

## Features Implemented

### 1. Product Management
- **Add New Product**: Create new products with name, category, price, stock, and description
- **Edit Product**: Modify existing product details
- **Delete Product**: Remove products from the system
- **View Products**: List all products with pagination
- **Product Statistics**: Display product count and status

### 2. Category Management
- **Add New Category**: Create new product categories
- **Edit Category**: Modify category name and description
- **Delete Category**: Remove categories (only if no products are assigned)
- **View Categories**: List all categories with product counts
- **Category Statistics**: Display total categories and products

### 3. User Management
- **Add New User**: Create new user accounts with roles
- **Edit User**: Modify user information (username, email, full name, etc.)
- **Delete User**: Remove users (admin users cannot be deleted)
- **View Users**: List all users with pagination
- **User Statistics**: Display user counts by status

### 4. Order Management
- **Update Order Status**: Change order status (Pending, Processing, Shipped, Completed, Cancelled)
- **Delete Order**: Remove orders from the system
- **View Orders**: List all orders with pagination
- **Order Statistics**: Display order counts by status

## File Structure

```
admin/
├── actions/
│   ├── product_actions.php    # Product CRUD operations
│   ├── category_actions.php   # Category CRUD operations
│   ├── user_actions.php       # User CRUD operations
│   └── order_actions.php      # Order CRUD operations
├── assets/
│   └── js/
│       └── admin-crud.js      # Frontend JavaScript for CRUD operations
├── pages/
│   ├── products.php           # Products management page
│   ├── categories.php         # Categories management page
│   ├── users.php              # Users management page
│   └── orders.php             # Orders management page
└── dashboard-admin.php        # Main admin dashboard
```

## API Endpoints

### Products
- `GET actions/product_actions.php?action=list` - Get all products
- `GET actions/product_actions.php?action=get&product_id=X` - Get specific product
- `POST actions/product_actions.php` - Create/Update product
- `POST actions/product_actions.php` - Delete product

### Categories
- `GET actions/category_actions.php?action=list` - Get all categories
- `GET actions/category_actions.php?action=get&category_id=X` - Get specific category
- `POST actions/category_actions.php` - Create/Update category
- `POST actions/category_actions.php` - Delete category

### Users
- `GET actions/user_actions.php?action=list` - Get all users
- `GET actions/user_actions.php?action=get&user_id=X` - Get specific user
- `POST actions/user_actions.php` - Create/Update user
- `POST actions/user_actions.php` - Delete user

### Orders
- `GET actions/order_actions.php?action=list` - Get all orders
- `GET actions/order_actions.php?action=get&order_id=X` - Get specific order
- `POST actions/order_actions.php` - Update order status
- `POST actions/order_actions.php` - Delete order

## Usage

### Adding a New Product
1. Navigate to Products page in admin panel
2. Click "Add New Product" button
3. Fill in the required fields (Product Name, Category)
4. Optionally fill in Price, Stock, and Description
5. Click "Save Product"

### Editing a Product
1. Click the edit button (pencil icon) next to any product
2. Modify the fields as needed
3. Click "Save Product"

### Deleting a Product
1. Click the delete button (trash icon) next to any product
2. Confirm the deletion in the popup dialog

### Managing Categories
- Similar workflow for categories
- Categories with existing products cannot be deleted

### Managing Users
- Similar workflow for users
- Admin users cannot be deleted
- Password is required for new users, optional for updates

### Managing Orders
- Click the edit button to update order status
- Enter new status in the prompt dialog
- Valid statuses: "Chờ xác nhận", "Đang giao", "Đã giao", "Đã hủy"

## Security Features

1. **Input Validation**: All inputs are validated on both client and server side
2. **SQL Injection Prevention**: Using prepared statements
3. **XSS Prevention**: Output escaping for user data
4. **Role-based Access**: Admin-only access to CRUD operations
5. **Data Integrity**: Foreign key constraints and validation

## Error Handling

- Client-side validation with immediate feedback
- Server-side validation with detailed error messages
- Graceful error handling for database operations
- User-friendly error messages displayed as alerts

## Dependencies

- jQuery 3.7.1 for AJAX operations
- Bootstrap 5.3.0 for UI components
- Bootstrap Icons for icons
- PHP PDO for database operations

## Browser Compatibility

- Modern browsers with ES6+ support
- Chrome, Firefox, Safari, Edge
- Mobile responsive design

## Troubleshooting

### Common Issues

1. **Products not loading**: Check database connection and table structure
2. **Modal not opening**: Ensure jQuery and Bootstrap are loaded
3. **AJAX errors**: Check browser console for detailed error messages
4. **Permission denied**: Ensure admin authentication is working

### Debug Mode

Enable debug mode by setting error reporting in action files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Check browser console for JavaScript errors and network tab for AJAX requests. 
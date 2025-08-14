# Shop Gau Yeu - Database Setup Guide

## 🚀 Complete Setup Instructions

### 1. XAMPP Configuration

#### Enable PDO MySQL Extension
1. Open XAMPP Control Panel
2. Click "Config" button for Apache
3. Select "php.ini"
4. Find the line: `;extension=pdo_mysql`
5. Remove the semicolon to make it: `extension=pdo_mysql`
6. Save the file
7. Restart Apache in XAMPP Control Panel

#### Alternative Method (if above doesn't work):
1. Navigate to: `C:\xampp\php\php.ini`
2. Open with Notepad or any text editor
3. Find: `;extension=pdo_mysql`
4. Change to: `extension=pdo_mysql`
5. Save and restart Apache

### 2. Database Setup

#### Option A: Automatic Setup (Recommended)
1. Ensure XAMPP is running (Apache + MySQL)
2. Open browser and go to: `http://localhost/shopgauyeu/config/init_database.php`
3. The script will automatically:
   - Create the database
   - Create all tables
   - Insert sample data
   - Create admin user

#### Option B: Manual Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database named: `shopgauyeu`
3. Import the `data.sql` file
4. Run the initialization script for sample data

### 3. Test Database Connection

After setup, test the connection:
- Visit: `http://localhost/shopgauyeu/test_database.php`

### 4. Default Login Credentials

#### Admin Panel
- **URL**: `http://localhost/shopgauyeu/admin`
- **Username**: `admin`
- **Password**: `admin123`

#### Sample User
- **Username**: `user`
- **Password**: `user123`

## 📁 Database Structure

### Tables Created:
1. **users** - User accounts and authentication
2. **categories** - Product categories
3. **products** - Product information
4. **product_images** - Product images
5. **orders** - Customer orders
6. **order_items** - Items in each order
7. **cart** - Shopping cart
8. **cart_items** - Items in cart

### Sample Data:
- 5 categories (Gấu Bông, Blind Box, Quà Tặng, Hoạt Hình, Phụ Kiện)
- 10 sample products
- Admin and sample user accounts

## 🔧 Troubleshooting

### Common Issues:

#### 1. "could not find driver" Error
- **Solution**: Enable PDO MySQL extension in php.ini
- **Check**: Run `php -m | grep pdo` in terminal

#### 2. Database Connection Failed
- **Solution**: Ensure MySQL is running in XAMPP
- **Check**: XAMPP Control Panel → MySQL → Start

#### 3. Permission Denied
- **Solution**: Run XAMPP as Administrator
- **Alternative**: Check folder permissions

#### 4. Port Conflicts
- **Solution**: Change Apache/MySQL ports in XAMPP
- **Default**: Apache (80), MySQL (3306)

## 📋 Verification Checklist

- [ ] XAMPP installed and running
- [ ] PDO MySQL extension enabled
- [ ] Database `shopgauyeu` created
- [ ] All tables created successfully
- [ ] Sample data inserted
- [ ] Admin user created
- [ ] Test script runs without errors
- [ ] Admin panel accessible
- [ ] Main website loads correctly

## 🎯 Next Steps

After successful setup:
1. Customize the website content
2. Add your own products
3. Configure payment methods
4. Set up email notifications
5. Customize the design

## 📞 Support

If you encounter issues:
1. Check the error logs in XAMPP
2. Verify all requirements are met
3. Ensure proper file permissions
4. Test with the provided test scripts

---

**Note**: This setup creates a fully functional e-commerce database with all necessary tables and sample data for immediate use. 
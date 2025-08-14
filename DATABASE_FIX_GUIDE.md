# Database Connection Fix Guide

## Problem
The login and registration forms appear to do nothing when clicked because PHP cannot connect to the MySQL database. This is due to missing PHP extensions.

## Root Cause
- ❌ PDO MySQL extension is NOT loaded
- ❌ MySQLi extension is NOT loaded
- ✅ PDO extension is loaded

## Solution Steps

### Step 1: Enable PDO MySQL Extension

1. **Find your php.ini file:**
   - Open XAMPP Control Panel
   - Click "Config" button for Apache
   - Select "PHP (php.ini)"
   - This will open the php.ini file in a text editor

2. **Enable PDO MySQL extension:**
   - Search for `;extension=pdo_mysql` (line with semicolon)
   - Remove the semicolon to make it: `extension=pdo_mysql`
   - Save the file

3. **Enable MySQLi extension (optional but recommended):**
   - Search for `;extension=mysqli` (line with semicolon)
   - Remove the semicolon to make it: `extension=mysqli`
   - Save the file

### Step 2: Restart Services

1. **Restart Apache:**
   - In XAMPP Control Panel, click "Stop" for Apache
   - Wait a few seconds
   - Click "Start" for Apache

2. **Verify MySQL is running:**
   - Make sure MySQL is started in XAMPP Control Panel
   - If not, click "Start" for MySQL

### Step 3: Verify Database Exists

1. **Open phpMyAdmin:**
   - Go to `http://localhost/phpmyadmin`
   - Login with username: `root`, password: (leave empty)

2. **Check if database exists:**
   - Look for database named `dtshopgau`
   - If it doesn't exist, create it:
     - Click "New" on the left sidebar
     - Enter database name: `dtshopgau`
     - Click "Create"

3. **Import database structure:**
   - If the database is empty, import the `data.sql` file:
     - Select the `dtshopgau` database
     - Click "Import" tab
     - Choose file: `data.sql` from your project
     - Click "Go"

### Step 4: Test the Fix

1. **Run the test script:**
   ```bash
   php check_php_extensions.php
   ```

2. **Expected results:**
   - ✅ PDO MySQL extension is loaded
   - ✅ MySQLi extension is loaded
   - ✅ Database connection successful

3. **Test login page:**
   - Go to `http://localhost/shopgauyeu/index.php?page=login`
   - The form should now work properly

## Alternative Solutions

### If you can't modify php.ini:

1. **Use a different database driver:**
   - Modify `config/database.php` to use MySQLi instead of PDO
   - Update all models to use MySQLi

2. **Use a different database:**
   - Switch to SQLite which doesn't require additional extensions
   - Modify the database configuration accordingly

## Verification Commands

After making changes, run these commands to verify:

```bash
# Check PHP extensions
php check_php_extensions.php

# Test database connection
php test_form_submission.php

# Check PHP syntax
php -l views/users/login.php
php -l views/users/register.php
```

## Common Issues

1. **"could not find driver" error:**
   - PDO MySQL extension not enabled
   - Follow Step 1 above

2. **"Access denied" error:**
   - MySQL credentials incorrect
   - Check username/password in `config/database.php`

3. **"Database doesn't exist" error:**
   - Create the database in phpMyAdmin
   - Import the database structure

## After Fix

Once the database connection is working:

1. **Login functionality** will work properly
2. **Registration functionality** will work properly  
3. **User profile management** will work properly
4. **All forms** will submit and process correctly

The login and registration forms should now respond when clicked and properly process user input. 
# PDO MySQL Setup Guide for XAMPP

## The Problem
Your application is getting a "could not find driver" error because the PDO MySQL extension is not enabled in XAMPP.

## Solution: Enable PDO MySQL Extension

### Step 1: Open XAMPP Control Panel
1. Start XAMPP Control Panel
2. Make sure Apache is running

### Step 2: Edit php.ini
1. In XAMPP Control Panel, click the **Config** button next to Apache
2. Select **php.ini** from the dropdown menu
3. This will open the php.ini file in a text editor

### Step 3: Enable PDO MySQL Extension
1. In the php.ini file, press **Ctrl+F** to search
2. Search for: `;extension=pdo_mysql`
3. You will find a line that looks like this:
   ```ini
   ;extension=pdo_mysql
   ```
4. **Remove the semicolon (;) at the beginning** so it becomes:
   ```ini
   extension=pdo_mysql
   ```

### Step 4: Save and Restart
1. Save the php.ini file
2. In XAMPP Control Panel, click **Stop** for Apache
3. Wait a few seconds, then click **Start** for Apache
4. The PDO MySQL extension should now be enabled

### Step 5: Verify Installation
1. Open your browser and go to: `http://localhost/shopgauyeu/check_database_structure.php`
2. You should see "✅ PDO MySQL Driver Available" instead of the error message

## Alternative: Manual Database Setup

If you prefer to set up the database manually:

### Step 1: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" on the left sidebar
3. Enter database name: `dtshopgau`
4. Click "Create"

### Step 2: Import SQL Structure
1. Select the `dtshopgau` database
2. Click "Import" tab
3. Click "Choose File" and select `data.sql` from your project folder
4. Click "Go" to import the tables

## After Enabling PDO MySQL

Once the PDO MySQL extension is enabled:

1. **Test the database connection:**
   - Go to: `http://localhost/shopgauyeu/check_database_structure.php`
   - This will show you the exact database structure

2. **Initialize the database:**
   - Run: `php config/init_database.php`
   - This will create all tables and sample data

3. **Test user registration/login:**
   - Go to: `http://localhost/shopgauyeu/register`
   - Try to register a new user

## Common Issues

### Issue: "Could not find driver"
- **Cause:** PDO MySQL extension not enabled
- **Solution:** Follow the steps above to enable the extension

### Issue: "Unknown database"
- **Cause:** Database `dtshopgau` doesn't exist
- **Solution:** Create the database manually or run the initialization script

### Issue: "Table doesn't exist"
- **Cause:** Tables haven't been created
- **Solution:** Run the database initialization script after enabling PDO MySQL

## Verification Commands

After enabling PDO MySQL, you can verify with these commands:

```bash
# Check if PDO MySQL is enabled
php -m | grep pdo

# Test database connection
php check_database_structure.php

# Initialize database
php config/init_database.php
```

## Need Help?

If you're still having issues after following these steps:

1. Check XAMPP error logs
2. Verify MySQL service is running
3. Make sure you're using the correct database name (`dtshopgau`)
4. Confirm the php.ini file was saved and Apache was restarted 
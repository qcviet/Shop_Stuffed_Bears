# User Authentication System - Shop Gau Yeu

## Overview

This document describes the complete user authentication and profile management system for Shop Gau Yeu, built with PHP, MySQL, and modern frontend technologies using BEM CSS methodology.

## Features

### 🔐 Authentication
- **User Registration**: Complete registration form with validation
- **User Login**: Secure login with remember me functionality
- **Password Management**: Password visibility toggle and strength indicator
- **Session Management**: Secure session handling with role-based access
- **Logout**: Secure logout with session cleanup

### 👤 Profile Management
- **Profile Information**: View and edit personal information
- **Password Change**: Secure password change functionality
- **Order History**: View individual order tracking
- **Responsive Design**: Mobile-friendly interface

### 🎨 UI/UX Features
- **BEM CSS Methodology**: Organized, maintainable CSS structure
- **Separated Assets**: CSS and JS files organized in assets folder
- **Modern Design**: Beautiful gradients and animations
- **Form Validation**: Real-time client-side validation
- **Responsive Layout**: Works on all device sizes

## File Structure

```
views/users/
├── login.php              # User login page
├── register.php           # User registration page
├── profile.php            # User profile management
└── logout.php             # User logout handler

assets/
├── css/
│   ├── variables.css      # CSS variables and colors
│   ├── user-auth.css      # Authentication page styles (BEM)
│   └── user-profile.css   # Profile page styles (BEM)
├── js/
│   ├── user-auth.js       # Authentication functionality
│   └── user-profile.js    # Profile management functionality
└── css/header.css         # Updated header with user menu

views/users/header.php     # Updated header with dynamic navigation
```

## CSS Architecture - BEM Methodology

### BEM Naming Convention
- **Block**: `.user-auth`, `.user-profile`
- **Element**: `.user-auth__form`, `.user-profile__card`
- **Modifier**: `.user-auth__button--primary`, `.user-profile__alert--error`

### Benefits
- **No Conflicts**: Prefixed classes avoid conflicts with existing styles
- **Maintainable**: Clear, organized structure
- **Scalable**: Easy to extend and modify
- **Reusable**: Components can be shared across pages

## JavaScript Architecture

### Namespace Pattern
```javascript
window.UserAuth = window.UserAuth || {};
window.UserProfile = window.UserProfile || {};
```

### Features
- **Form Validation**: Real-time validation with visual feedback
- **Password Toggle**: Show/hide password functionality
- **Tab Switching**: Dynamic tab navigation in profile
- **Auto-hide Alerts**: Automatic alert dismissal
- **Keyboard Shortcuts**: Ctrl+Enter to submit, Escape to clear
- **Remember Me**: Username persistence across sessions

## Database Integration

### Required Methods in AppController
```php
// User authentication
$appController->loginUser($username, $password);
$appController->createUser($userData);
$appController->isUsernameExists($username);
$appController->isEmailExists($email);

// User management
$appController->getUserById($userId);
$appController->updateUser($updateData);
$appController->getUserOrders($userId);
```

### Database Schema Requirements
```sql
-- Users table should have these fields:
- user_id (PRIMARY KEY)
- username (UNIQUE)
- email (UNIQUE)
- password (HASHED)
- full_name
- phone
- address
- role (ENUM: 'user', 'admin')
- created_at
- updated_at

-- Orders table should have these fields:
- order_id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- total_amount
- status
- created_at
```

## Usage Examples

### Including CSS and JS
```php
<!-- In PHP files -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/variables.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-auth.css">
<script src="<?php echo BASE_URL; ?>/assets/js/user-auth.js"></script>
```

### Using BEM Classes
```html
<!-- Login form -->
<div class="user-auth">
    <div class="user-auth__container">
        <div class="user-auth__card">
            <form class="user-auth__form">
                <div class="user-auth__form-group">
                    <input class="user-auth__input" type="text">
                </div>
                <button class="user-auth__button user-auth__button--primary">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
```

### JavaScript Functionality
```javascript
// Show custom alert
UserAuth.showAlert('Success message', 'success');

// Validate form
UserAuth.validateInput(inputElement);

// Clear validation
UserAuth.clearAllValidation();
```

## Security Features

### Session Management
- Separate sessions for admin and user
- Secure session handling
- Automatic session cleanup on logout

### Password Security
- Password hashing using PHP's `password_hash()`
- Password strength validation
- Secure password change functionality

### Form Security
- CSRF protection (implement in forms)
- Input sanitization
- SQL injection prevention through PDO

### Remember Me
- Secure token generation
- Encrypted cookie storage
- Automatic cleanup of expired tokens

## Responsive Design

### Mobile-First Approach
- Responsive breakpoints: 768px, 1024px
- Touch-friendly interface
- Optimized for mobile devices

### CSS Media Queries
```css
@media (max-width: 768px) {
    .user-auth__container {
        max-width: 95%;
    }
    
    .user-profile__order-header {
        flex-direction: column;
    }
}
```

## Browser Compatibility

### Supported Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### JavaScript Features
- ES6+ syntax with fallbacks
- Modern DOM APIs
- LocalStorage for data persistence

## Performance Optimization

### CSS Optimization
- Minified CSS files
- Efficient selectors
- Reduced specificity conflicts

### JavaScript Optimization
- Namespace pattern prevents global pollution
- Event delegation for dynamic content
- Efficient DOM queries

### Asset Loading
- External CSS and JS files
- Browser caching enabled
- Optimized loading order

## Error Handling

### Client-Side Validation
- Real-time form validation
- Visual feedback for errors
- User-friendly error messages

### Server-Side Validation
- PHP validation for all inputs
- Database constraint checking
- Secure error handling

### Error Messages
- Vietnamese language support
- Clear, actionable messages
- Consistent error styling

## Future Enhancements

### Planned Features
- Email verification system
- Password reset functionality
- Social media login integration
- Two-factor authentication
- Profile picture upload
- Address book management

### Technical Improvements
- API endpoints for AJAX requests
- Progressive Web App features
- Offline functionality
- Push notifications

## Troubleshooting

### Common Issues

1. **CSS Not Loading**
   - Check file paths in PHP includes
   - Verify CSS file permissions
   - Clear browser cache

2. **JavaScript Errors**
   - Check browser console for errors
   - Verify JS file loading order
   - Ensure jQuery/Bootstrap loaded

3. **Form Validation Issues**
   - Check required field attributes
   - Verify validation rules
   - Test with different browsers

4. **Session Problems**
   - Check PHP session configuration
   - Verify session storage permissions
   - Clear browser cookies

### Debug Mode
```php
// Enable debug mode in config
define('DEBUG_MODE', true);

// Show detailed errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

### Code Standards
- Follow BEM CSS methodology
- Use consistent PHP coding standards
- Include proper documentation
- Test on multiple browsers

### File Naming
- Use kebab-case for file names
- Descriptive, meaningful names
- Follow established conventions

### Documentation
- Update this README for changes
- Include inline code comments
- Document new features

## License

This user authentication system is part of the Shop Gau Yeu project and follows the same licensing terms.

---

**Note**: This system is designed to work with the existing Shop Gau Yeu infrastructure. Ensure all dependencies and database connections are properly configured before implementation. 
# RequestQuote Module v2.0.0 - Installation & Testing Guide

## 🚀 Complete Installation Steps

### Step 1: Prepare the Module
1. Ensure all files are in the correct directory structure
2. Check file permissions (755 for directories, 644 for files)
3. Verify all required files are present

### Step 2: Install in PrestaShop
1. Go to **Modules > Module Manager**
2. Click **Upload a module**
3. Upload the entire `requestquote` folder or zip file
4. Click **Install** when the module appears

### Step 3: Configure the Module
1. After installation, click **Configure**
2. Enable the module by setting **Enable Quote Requests** to **Yes**
3. Set **Require Phone Number** as needed (Optional/Required)
4. Click **Save**

### Step 4: Verify Admin Panel
1. Go to **Sell** menu in admin
2. Look for **Quote Requests** tab
3. If not visible, try:
   - Clear cache: **Advanced Parameters > Performance > Clear cache**
   - Reinstall the module
   - Check error logs

## 🧪 Testing Checklist

### Frontend Testing

#### Product Page Tests
- [ ] Visit any product page
- [ ] Verify prices are hidden
- [ ] Verify "Add to Cart" button is hidden
- [ ] Verify product images are still visible
- [ ] Verify "Request Quote" button appears
- [ ] Click "Request Quote" button
- [ ] Modal should open without errors

#### Form Testing
- [ ] Fill out the quote form with valid data
- [ ] Test required field validation
- [ ] Test email format validation
- [ ] Test phone requirement (if enabled)
- [ ] Submit form and verify success message
- [ ] Check form resets after submission
- [ ] Modal should close automatically after 3 seconds

#### Quick View Testing
- [ ] Open product quick view
- [ ] Verify quote button appears in quick view
- [ ] Test form submission from quick view
- [ ] Ensure unique modal IDs work correctly

### Backend Testing

#### Admin Panel
- [ ] Navigate to **Sell > Quote Requests**
- [ ] Verify quote requests appear in the list
- [ ] Click **View** on a quote request
- [ ] Verify all quote details are displayed correctly
- [ ] Test search and filter functionality
- [ ] Test bulk delete functionality

#### Email Notifications
- [ ] Submit a quote request
- [ ] Check if admin receives email notification
- [ ] Verify email contains correct information

### Technical Testing

#### Database
- [ ] Check if `ps_requestquote_quotes` table exists
- [ ] Verify table structure is correct
- [ ] Confirm quote data is saved properly

#### Security
- [ ] Test CSRF token validation
- [ ] Verify input sanitization
- [ ] Check SQL injection prevention

## 🔧 Troubleshooting

### Common Issues & Solutions

#### "Request Quote" button not appearing
```bash
# Clear PrestaShop cache
rm -rf var/cache/*

# Check module is enabled
# Go to Module Manager > Search "Request Quote" > Configure > Enable
```

#### Admin tab not showing in Sell menu
```php
// Reinstall the module or run this SQL manually:
INSERT INTO `ps_tab` (`id_parent`, `class_name`, `module`, `active`, `position`) 
VALUES ((SELECT `id_tab` FROM `ps_tab` WHERE `class_name` = 'AdminParentSell'), 'AdminRequestQuote', 'requestquote', 1, 0);
```

#### Modal gets stuck or doesn't open
```javascript
// Check browser console for JavaScript errors
// Ensure Bootstrap modal is loaded
// Verify unique modal IDs are working
```

#### AJAX form submission fails
```bash
# Check server error logs
# Verify file permissions on controllers
# Test AJAX endpoint directly: /modules/requestquote/views/controllers/front/quote.php
```

#### Images not showing
```css
/* The module should preserve images, but if not, add this CSS: */
.product-cover,
.product-images,
.product-thumbnails {
    display: block !important;
}
```

### Debug Mode
Enable debug mode in PrestaShop:
```php
// In config/defines.inc.php
define('_PS_MODE_DEV_', true);
```

### File Permissions
```bash
# Set correct permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

## 📋 Required Files Checklist

```
requestquote/
├── requestquote.php ✓
├── config.xml ✓
├── index.php ✓
├── README.md ✓
├── install.md ✓
├── CHANGELOG.md ✓
├── classes/
│   ├── RequestQuoteQuote.php ✓
│   └── index.php ✓
└── views/
    ├── controllers/
    │   ├── admin/
    │   │   └── AdminRequestQuoteController.php ✓
    │   └── front/
    │       ├── quote.php ✓
    │       └── index.php ✓
    ├── templates/
    │   ├── admin/
    │   │   └── requestquote_quotes/
    │   │       └── view.tpl ✓
    │   └── hook/
    │       ├── product-actions.tpl ✓
    │       └── product-additional-info.tpl ✓
    ├── css/
    │   ├── requestquote.css ✓
    │   ├── admin.css ✓
    │   └── index.php ✓
    └── js/
        ├── requestquote.js ✓
        ├── admin.js ✓
        └── index.php ✓
```

## 🎯 Success Criteria

The module is fully functional when:

1. **Frontend**: 
   - Product prices are hidden
   - Quote button appears on all product pages
   - Modal form opens and submits successfully
   - Success/error messages work properly

2. **Backend**:
   - Admin tab appears in Sell menu
   - Quote requests are listed correctly
   - Quote details can be viewed
   - Email notifications are sent

3. **Security**:
   - CSRF protection works
   - Input validation prevents malicious data
   - SQL injection protection is active

4. **Performance**:
   - Page load times are not significantly impacted
   - AJAX requests complete within reasonable time
   - No JavaScript errors in browser console

## 📞 Support

If you encounter issues not covered in this guide:

1. Check PrestaShop error logs
2. Enable debug mode for detailed error messages
3. Verify all file permissions are correct
4. Ensure PrestaShop version compatibility (9.0.0+)
5. Test in a clean PrestaShop installation

The module should work seamlessly with any PrestaShop 9.0.0+ installation and standard themes. 
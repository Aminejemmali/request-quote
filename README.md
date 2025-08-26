# RequestQuote Module for PrestaShop 9.0.0

A comprehensive PrestaShop module that transforms your e-commerce store into a quote-based system by hiding prices and replacing the "Add to Cart" functionality with a quote request form.

## 🚀 Features

- **Complete Price Hiding**: Automatically hides all product prices, discounts, and pricing information
- **Quote Request Form**: Professional modal form for customers to request quotes
- **Product Image Preservation**: Ensures product images remain visible while hiding pricing elements
- **Admin Management**: Complete admin interface to view and manage quote requests
- **Email Notifications**: Automatic email notifications for new quote requests
- **Mobile Responsive**: Fully responsive design that works on all devices
- **AJAX Form Submission**: Smooth user experience with real-time form validation
- **Configurable Options**: Enable/disable module and set phone number requirements
- **Multi-language Support**: Ready for translation to multiple languages
- **Security**: CSRF token protection and input sanitization

## 📋 Requirements

- PrestaShop 9.0.0 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Modern web browser with JavaScript enabled

## 🛠 Installation

1. **Download the Module**
   - Download the module files
   - Ensure all files are present in the correct directory structure

2. **Upload to PrestaShop**
   - Upload the entire `requestquote` folder to your PrestaShop `/modules/` directory
   - Or use the PrestaShop admin interface to upload the zip file

3. **Install via Admin**
   - Go to **Modules > Module Manager** in your PrestaShop admin
   - Search for "Request Quote"
   - Click **Install**

4. **Configure the Module**
   - After installation, click **Configure**
   - Enable the module
   - Set phone number requirement (optional/required)
   - Save settings

## ⚙️ Configuration

### Module Settings

1. **Enable Quote Requests**: Turn the module on/off
2. **Require Phone Number**: Make phone number mandatory in quote forms

### Admin Management

- Navigate to **Sell > Quote Requests** to view all submitted quotes
- View detailed information for each quote request
- Reply to customers directly via email

## 🎯 How It Works

### For Customers

1. **Browse Products**: Customers can browse products normally
2. **View Images**: All product images and descriptions remain visible
3. **Request Quote**: Instead of "Add to Cart", customers see "Request Quote" button
4. **Fill Form**: Modal form opens with fields for name, email, phone, and notes
5. **Submit Request**: Form is submitted via AJAX with instant feedback
6. **Confirmation**: Success message confirms quote request submission

### For Store Owners

1. **Receive Notifications**: Email notifications for new quote requests
2. **Admin Interface**: View all quotes in organized admin panel
3. **Customer Details**: Access complete customer and product information
4. **Direct Response**: Reply to customers directly from admin interface

## 📁 File Structure

```
requestquote/
├── classes/
│   ├── RequestQuoteQuote.php      # Quote data model
│   └── index.php                  # Security file
├── views/
│   ├── controllers/
│   │   ├── admin/
│   │   │   └── AdminRequestQuoteController.php
│   │   └── front/
│   │       └── quote.php          # AJAX handler
│   ├── templates/
│   │   ├── admin/
│   │   │   └── requestquote_quotes/
│   │   │       └── view.tpl       # Admin quote view
│   │   └── hook/
│   │       ├── product-actions.tpl
│   │       └── product-additional-info.tpl
│   ├── css/
│   │   └── requestquote.css       # Module styles
│   └── js/
│       └── requestquote.js        # Module JavaScript
├── config.xml                     # Module configuration
├── requestquote.php              # Main module file
└── README.md                     # This file
```

## 🔧 Customization

### Styling

Edit `views/css/requestquote.css` to customize:
- Button appearance
- Modal design
- Form styling
- Responsive behavior

### Templates

Modify template files in `views/templates/hook/` to:
- Change form layout
- Add custom fields
- Modify button text
- Customize messaging

### Functionality

Extend the module by:
- Adding custom fields to the quote form
- Implementing additional email templates
- Creating custom admin reports
- Adding integration with CRM systems

## 🐛 Troubleshooting

### Common Issues

**Request button not appearing:**
- Check if module is enabled in configuration
- Verify theme compatibility
- Clear PrestaShop cache

**Images not showing:**
- Module preserves product images by design
- Check browser console for CSS conflicts
- Verify theme-specific image classes

**404 errors on form submission:**
- Ensure all module files are uploaded correctly
- Check file permissions
- Verify .htaccess configuration

**Email notifications not working:**
- Check PrestaShop email configuration
- Verify SMTP settings
- Check server email logs

### Debug Mode

Enable debug mode by adding to your PrestaShop configuration:
```php
define('_PS_MODE_DEV_', true);
```

## 🔒 Security Features

- CSRF token protection on all forms
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- Secure file permissions

## 📧 Support

For support and questions:
- Check the troubleshooting section above
- Review PrestaShop logs for errors
- Ensure all requirements are met
- Verify file permissions and uploads

## 📝 License

This module is released under the MIT License. See the license file for details.

## 🔄 Version History

### Version 2.0.0 (Latest)
- **MAJOR UPDATE**: Complete module overhaul and bug fixes
- Fixed request button appearing only in quick preview
- Fixed modal getting stuck issues
- Fixed admin panel not appearing in Sell menu
- Enhanced hook integration for all product page locations
- Improved AJAX form handling with proper error management
- Added unique modal IDs to prevent conflicts
- Enhanced security with improved CSRF token validation
- Better image preservation while hiding prices
- Improved email notification system
- Added comprehensive installation and testing guides
- Enhanced admin interface with better quote management
- Mobile responsive design improvements
- Performance optimizations

### Version 1.0.0
- Initial release
- Basic price hiding functionality
- Quote request form
- Admin interface
- Email notifications
- Security features

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Technical Details

### Database Schema

The module creates a table `requestquote_quotes` with the following structure:
- `id_quote` (Primary Key)
- `id_product` (Product ID)
- `id_shop` (Shop ID)
- `client_name` (Customer name)
- `email` (Customer email)
- `phone` (Customer phone - optional)
- `note` (Additional notes)
- `date_add` (Creation date)
- `date_upd` (Last update date)

### Hooks Used

- `displayProductActions`: Main quote button and form
- `displayHeader`: CSS/JS injection and global styling
- `displayProductAdditionalInfo`: Alternative product page layout
- `displayProductPriceBlock`: Price hiding
- `displayAfterProductThumbs`: Additional product modifications
- `displayProductButtons`: Product button modifications
- `displayProductListFunctionalButtons`: Product list modifications

This module provides a complete solution for transforming your PrestaShop store into a quote-based system while maintaining professional appearance and user experience. 
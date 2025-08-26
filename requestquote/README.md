# RequestQuote Module for PrestaShop 9.0.0

A comprehensive PrestaShop module that allows customers to request quotes for products instead of purchasing them directly. This module replaces the standard add-to-cart functionality with a professional quote request system.

## Features

### Front Office
- **Product Page Modifications**: Hides price, quantity selector, and add-to-cart button
- **Quote Request Button**: Prominent "Request Quote" button with modern styling
- **Modal Form**: Professional quote request form with the following fields:
  - Client Name (required)
  - Email Address (required, validated)
  - Phone Number (optional, configurable)
  - Product Information (auto-filled, read-only)
  - Additional Notes (optional, 1000 character limit)
- **Real-time Validation**: Client-side form validation with visual feedback
- **AJAX Submission**: Secure form submission without page reload
- **CSRF Protection**: Built-in security token protection

### Back Office
- **Admin Management**: Complete quote request management interface
- **Grid View**: Sortable and filterable table of all quote requests
- **Bulk Actions**: Delete multiple quotes at once
- **Detailed View**: View complete quote request details
- **Search & Filter**: Advanced filtering by date, product, client, etc.
- **Export Functionality**: Export quote data in various formats
- **Statistics Dashboard**: Overview of quote request metrics

### Security Features
- **CSRF Token Protection**: Prevents cross-site request forgery
- **Input Validation**: Server-side validation and sanitization
- **SQL Injection Protection**: Prepared statements for database queries
- **XSS Prevention**: Output sanitization and proper escaping
- **Access Control**: Admin-only access to management functions

## Installation

### Prerequisites
- PrestaShop 9.0.0 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Installation Steps

1. **Download the Module**
   - Extract the `requestquote` folder to your PrestaShop `modules` directory

2. **Install via Back Office**
   - Go to **Modules > Module Manager** in your PrestaShop back office
   - Search for "RequestQuote"
   - Click **Install**

3. **Configure the Module**
   - Go to **Modules > Module Manager > RequestQuote > Configure**
   - Enable/disable quote requests
   - Configure phone number requirement
   - Save settings

4. **Verify Installation**
   - Check that the admin tab "Quote Requests" appears under **Sell** menu
   - Visit a product page to see the quote request button

## Configuration

### Module Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Enable Quote Requests | Master switch for the module | Enabled |
| Require Phone Number | Make phone field mandatory | Optional |

### Hook Configuration

The module automatically registers the following hooks:
- `displayProductActions` - Main product page integration
- `displayProductAdditionalInfo` - Alternative product page integration
- `actionFrontControllerSetMedia` - CSS/JS asset injection
- `displayHeader` - Alternative asset injection
- `displayBackOfficeHeader` - Admin asset injection

## Usage

### For Customers

1. **Browse Products**: Navigate to any product page
2. **Request Quote**: Click the "Request Quote" button
3. **Fill Form**: Complete the quote request form
4. **Submit**: Click "Submit Quote Request"
5. **Confirmation**: Receive confirmation message

### For Administrators

1. **Access Management**: Go to **Sell > Quote Requests**
2. **View Quotes**: Browse all submitted quote requests
3. **Filter & Search**: Use advanced filtering options
4. **Manage Quotes**: View details, update status, or delete quotes
5. **Export Data**: Export quote data for external processing

## Database Structure

The module creates a custom table `ps_requestquote_quotes` with the following structure:

```sql
CREATE TABLE `ps_requestquote_quotes` (
  `id_quote` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL DEFAULT 1,
  `client_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_quote`),
  KEY `id_product` (`id_product`),
  KEY `id_shop` (`id_shop`),
  KEY `date_add` (`date_add`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## File Structure

```
requestquote/
├── classes/
│   └── RequestQuoteQuote.php          # Quote request model class
├── views/
│   ├── controllers/
│   │   ├── admin/
│   │   │   └── AdminRequestQuoteController.php  # Admin controller
│   │   └── front/
│   │       └── quote.php              # Front controller for AJAX
│   ├── css/
│   │   ├── requestquote.css           # Front office styles
│   │   └── admin.css                  # Back office styles
│   ├── js/
│   │   ├── requestquote.js            # Front office JavaScript
│   │   └── admin.js                   # Back office JavaScript
│   └── templates/
│       └── hook/
│           ├── product-actions.tpl    # Main product page template
│           └── product-additional-info.tpl  # Alternative template
├── config.xml                         # Module configuration
├── index.php                          # Security file
├── requestquote.php                   # Main module file
└── README.md                          # This file
```

## Customization

### Styling
- Modify `views/css/requestquote.css` for front office appearance
- Modify `views/css/admin.css` for back office appearance

### Templates
- Customize `views/templates/hook/product-actions.tpl` for product page layout
- Modify form fields and validation in the templates

### JavaScript
- Extend `views/js/requestquote.js` for additional front office functionality
- Modify `views/js/admin.js` for enhanced admin features

### Hooks
- The module uses standard PrestaShop hooks for maximum compatibility
- Can be easily integrated with custom themes

## Security Considerations

### CSRF Protection
- Each form submission includes a unique CSRF token
- Tokens are stored in secure cookies
- Automatic token validation on server side

### Input Validation
- Client-side validation for immediate feedback
- Server-side validation for security
- SQL injection prevention through prepared statements
- XSS prevention through output sanitization

### Access Control
- Admin functions require proper authentication
- AJAX endpoints validate user permissions
- Database queries use shop context isolation

## Troubleshooting

### Common Issues

1. **Quote Request Button Not Visible**
   - Check if module is enabled in configuration
   - Verify hook registration in module manager
   - Check theme compatibility

2. **Form Submission Errors**
   - Verify CSRF token is being generated
   - Check JavaScript console for errors
   - Ensure AJAX endpoint is accessible

3. **Admin Tab Missing**
   - Reinstall the module
   - Check admin permissions
   - Verify database table creation

4. **Styling Issues**
   - Clear browser cache
   - Check CSS file paths
   - Verify theme compatibility

### Debug Mode

Enable PrestaShop debug mode to see detailed error messages:
1. Go to **Advanced Parameters > Performance**
2. Set **Debug mode** to **Yes**
3. Check error logs for specific issues

## Support

### Documentation
- This README provides comprehensive usage information
- Check PrestaShop documentation for general module development
- Review the code comments for technical details

### Development
- The module follows PrestaShop coding standards
- Uses modern PHP practices and security measures
- Compatible with PrestaShop 9.0.0+ architecture

### Updates
- Check for module updates regularly
- Backup your data before updating
- Test updates in a development environment first

## License

This module is provided as-is for educational and commercial use. Please ensure compliance with PrestaShop licensing terms.

## Changelog

### Version 1.0.0
- Initial release
- Basic quote request functionality
- Admin management interface
- Security features implementation
- Responsive design support

## Contributing

To contribute to this module:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Credits

- **Developer**: Your Name
- **Framework**: PrestaShop 9.0.0
- **Icons**: Font Awesome (included with PrestaShop)
- **Styling**: Bootstrap 4 (included with PrestaShop)

---

**Note**: This module is designed for PrestaShop 9.0.0 and follows current best practices for security, performance, and user experience. 
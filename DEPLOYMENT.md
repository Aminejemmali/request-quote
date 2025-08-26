# RequestQuote Module v2.1.0 - Deployment Guide

## 🚀 Quick Deployment (5 Minutes)

### Step 1: Upload Files
Upload these files to your PrestaShop `/modules/requestquote/` directory:
- `requestquote.php` (main module file)
- `config.xml` (module configuration)
- `views/controllers/front/ajax.php` (AJAX handler)
- `index.php` (security file)

### Step 2: Install Module
1. Go to **Modules > Module Manager**
2. Search for "Request Quote"
3. Click **Install**
4. Click **Configure** and enable the module

### Step 3: Test
1. Visit any product page
2. Verify prices are hidden
3. Verify "Request Quote" button appears
4. Click button and test form submission

## ✅ What This Version Does

### Frontend Features
- ✅ Hides all product prices automatically
- ✅ Shows clean "Request Quote" button on product pages
- ✅ Works in quick preview without getting stuck
- ✅ Simple modal form for quote requests
- ✅ AJAX form submission with instant feedback
- ✅ Mobile responsive design

### Backend Features
- ✅ Simple enable/disable configuration
- ✅ Database storage of quote requests
- ✅ No complex admin panel (data stored in database)

### Technical Features
- ✅ Single file implementation (no template dependencies)
- ✅ Inline CSS and JavaScript (no external files)
- ✅ Simple database structure
- ✅ Error handling and validation
- ✅ Works with all PrestaShop themes

## 📋 Minimal File Structure

```
requestquote/
├── requestquote.php          # Main module (contains everything)
├── config.xml               # Module configuration
├── views/controllers/front/
│   └── ajax.php             # Form handler
└── index.php               # Security file
```

## 🔧 Configuration

The module has minimal configuration:
- **Enable/Disable**: Turn the module on/off
- **Automatic**: Everything else works automatically

## 📊 Database

The module creates one simple table:
- `ps_requestquote_quotes` with fields:
  - id_quote, id_product, client_name, email, phone, message, date_add

## 🎯 Testing Checklist

- [ ] Module installs without errors
- [ ] Product prices are hidden
- [ ] "Request Quote" button appears on product pages
- [ ] Button works in quick preview
- [ ] Modal opens when button is clicked
- [ ] Form validates required fields
- [ ] Form submits successfully via AJAX
- [ ] Success message appears
- [ ] Modal closes after submission
- [ ] Data is saved to database

## 🚨 Troubleshooting

**Button not appearing?**
- Check if module is enabled in configuration
- Clear PrestaShop cache

**Modal not opening?**
- Check browser console for JavaScript errors
- Ensure no JavaScript conflicts

**Form not submitting?**
- Check that AJAX controller file exists
- Verify database permissions

## 🎉 Deployment Complete!

This simplified version is designed for immediate deployment with minimal setup. The module contains all necessary functionality in a single file and requires no complex configuration or additional files.

**Ready for Production Use** ✅ 
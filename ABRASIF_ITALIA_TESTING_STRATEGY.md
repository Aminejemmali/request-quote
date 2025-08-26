# Abrasif Italia - UI Testing Strategy & Deployment Guide

## 1. Testing Overview

This comprehensive testing strategy ensures that Abrasif Italia UI changes are thoroughly validated before production deployment, maintaining PrestaShop compatibility and optimal user experience.

### Testing Objectives
- **Functionality**: All UI components work as expected
- **Compatibility**: Full PrestaShop integration without conflicts
- **Performance**: Optimal loading times and resource usage
- **Accessibility**: WCAG 2.1 compliance for inclusive design
- **Responsiveness**: Consistent experience across all devices
- **Browser Support**: Cross-browser compatibility

## 2. Pre-Deployment Testing Environments

### Development Environment Setup
```bash
# Local Development Setup
git clone [your-prestashop-repo]
cd prestashop-site

# Install dependencies
composer install
npm install

# Setup local database
mysql -u root -p < database/prestashop_dev.sql

# Configure local environment
cp config/parameters.yml.dist config/parameters.yml
# Edit parameters.yml with local database settings

# Enable development mode
php bin/console prestashop:dev:enable

# Clear caches
php bin/console cache:clear --env=dev
```

### Staging Environment
- **URL**: staging.abrasiitalia.com
- **Purpose**: Final pre-production testing
- **Database**: Copy of production (anonymized)
- **Features**: Identical to production environment

### Testing Environment Checklist
- [ ] PrestaShop version matches production
- [ ] All modules installed and configured
- [ ] Sample products with images loaded
- [ ] Request Quote module active and functional
- [ ] SSL certificate configured
- [ ] Error reporting enabled for debugging

## 3. CSS Integration Testing

### Step 1: CSS Validation
```bash
# Install CSS validation tools
npm install -g css-validator stylelint

# Validate CSS syntax
css-validator css/abrasif-italia-prestashop-base.css

# Lint CSS for best practices
stylelint "css/**/*.css" --config .stylelintrc.json
```

### Step 2: PrestaShop Integration Test
```php
<?php
// Create test script: test_css_integration.php

// Test CSS loading
function testCSSLoading() {
    $cssFile = 'css/abrasif-italia-prestashop-base.css';
    
    if (!file_exists($cssFile)) {
        echo "❌ CSS file not found: $cssFile\n";
        return false;
    }
    
    $cssContent = file_get_contents($cssFile);
    
    // Test for required CSS classes
    $requiredClasses = [
        '.header-nav',
        '.product-miniature',
        '.footer-container',
        '.request-quote-btn',
        '.btn-primary'
    ];
    
    foreach ($requiredClasses as $class) {
        if (strpos($cssContent, $class) === false) {
            echo "❌ Missing required class: $class\n";
            return false;
        }
    }
    
    echo "✅ CSS integration test passed\n";
    return true;
}

// Test PrestaShop class compatibility
function testPrestaShopCompatibility() {
    $conflicts = [];
    
    // Check for potential conflicts
    $cssContent = file_get_contents('css/abrasif-italia-prestashop-base.css');
    
    // Look for overridden core classes without !important
    $coreClasses = ['.container', '.row', '.col-', '.navbar', '.modal'];
    
    foreach ($coreClasses as $class) {
        if (preg_match("/$class[^{]*{[^}]*(?<!!)important/", $cssContent)) {
            $conflicts[] = $class;
        }
    }
    
    if (empty($conflicts)) {
        echo "✅ No PrestaShop compatibility conflicts detected\n";
        return true;
    } else {
        echo "⚠️  Potential conflicts with: " . implode(', ', $conflicts) . "\n";
        return false;
    }
}

// Run tests
testCSSLoading();
testPrestaShopCompatibility();
?>
```

### Step 3: Request Quote Module Preservation Test
```php
<?php
// test_request_quote_preservation.php

function testRequestQuotePreservation() {
    $cssFile = 'css/abrasif-italia-prestashop-base.css';
    $cssContent = file_get_contents($cssFile);
    
    // Test that request-quote classes are preserved
    $preservedClasses = [
        '.request-quote-btn',
        '.request-quote-section',
        '.request-quote-form',
        '.quote-modal',
        '.quote-modal-content'
    ];
    
    $modified = [];
    $preserved = [];
    
    foreach ($preservedClasses as $class) {
        if (strpos($cssContent, $class) !== false) {
            // Check if it's only color/brand modifications
            $pattern = "/$class[^{]*{[^}]*(?:background|color|border)[^}]*}/";
            if (preg_match($pattern, $cssContent)) {
                $preserved[] = $class;
            } else {
                $modified[] = $class;
            }
        }
    }
    
    echo "✅ Preserved classes: " . implode(', ', $preserved) . "\n";
    
    if (!empty($modified)) {
        echo "⚠️  Modified classes: " . implode(', ', $modified) . "\n";
        return false;
    }
    
    return true;
}

testRequestQuotePreservation();
?>
```

## 4. Visual Regression Testing

### Automated Screenshot Testing
```javascript
// test/visual-regression.js
const puppeteer = require('puppeteer');
const pixelmatch = require('pixelmatch');
const PNG = require('pngjs').PNG;
const fs = require('fs');

async function visualRegressionTest() {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // Test pages
    const testPages = [
        { name: 'homepage', url: 'http://localhost/prestashop' },
        { name: 'category', url: 'http://localhost/prestashop/category/1' },
        { name: 'product', url: 'http://localhost/prestashop/product/1' },
        { name: 'cart', url: 'http://localhost/prestashop/cart' }
    ];
    
    for (const testPage of testPages) {
        await page.goto(testPage.url);
        await page.waitForSelector('.product-miniature', { timeout: 5000 });
        
        // Take screenshot
        await page.screenshot({
            path: `screenshots/current-${testPage.name}.png`,
            fullPage: true
        });
        
        // Compare with baseline if exists
        const baselinePath = `screenshots/baseline-${testPage.name}.png`;
        const currentPath = `screenshots/current-${testPage.name}.png`;
        
        if (fs.existsSync(baselinePath)) {
            const baseline = PNG.sync.read(fs.readFileSync(baselinePath));
            const current = PNG.sync.read(fs.readFileSync(currentPath));
            const { width, height } = baseline;
            const diff = new PNG({ width, height });
            
            const numDiffPixels = pixelmatch(
                baseline.data, current.data, diff.data,
                width, height, { threshold: 0.1 }
            );
            
            if (numDiffPixels > 100) {
                console.log(`❌ Visual regression detected in ${testPage.name}: ${numDiffPixels} different pixels`);
                fs.writeFileSync(`screenshots/diff-${testPage.name}.png`, PNG.sync.write(diff));
            } else {
                console.log(`✅ Visual test passed for ${testPage.name}`);
            }
        }
    }
    
    await browser.close();
}

visualRegressionTest();
```

### Manual Visual Testing Checklist

#### Homepage Testing
- [ ] Header logo displays correctly (60px height)
- [ ] Navigation menu has red background with proper hover effects
- [ ] Search bar has red border and green focus state
- [ ] Product cards display with proper spacing and hover effects
- [ ] Footer has dark gradient background with red-green top border
- [ ] Request quote buttons use brand colors

#### Product Page Testing
- [ ] Product images display correctly (250px height)
- [ ] Product information layout is clean and readable
- [ ] Request quote button is prominent and functional
- [ ] Quick view is completely hidden
- [ ] Price information is hidden (request quote only)
- [ ] Product badges (New, Sale) use brand colors

#### Category Page Testing
- [ ] Product grid layout is responsive
- [ ] Filter sidebar (if present) maintains styling
- [ ] Pagination controls use brand colors
- [ ] Product cards maintain consistent spacing
- [ ] Category breadcrumbs display correctly

#### Cart/Checkout Testing
- [ ] Cart page maintains brand styling
- [ ] Checkout process uses brand colors for buttons
- [ ] Form elements have consistent styling
- [ ] Success/error messages use appropriate colors

## 5. Responsive Testing

### Device Testing Matrix
```javascript
// test/responsive.js
const devices = [
    { name: 'Mobile Portrait', width: 375, height: 667 },
    { name: 'Mobile Landscape', width: 667, height: 375 },
    { name: 'Tablet Portrait', width: 768, height: 1024 },
    { name: 'Tablet Landscape', width: 1024, height: 768 },
    { name: 'Desktop Small', width: 1200, height: 800 },
    { name: 'Desktop Large', width: 1920, height: 1080 }
];

async function responsiveTest() {
    const browser = await puppeteer.launch();
    
    for (const device of devices) {
        const page = await browser.newPage();
        await page.setViewport({
            width: device.width,
            height: device.height
        });
        
        await page.goto('http://localhost/prestashop');
        
        // Test responsive elements
        const tests = [
            // Header responsiveness
            {
                selector: '.header-nav .navbar-brand img',
                property: 'max-height',
                expected: device.width < 768 ? '45px' : '60px'
            },
            // Navigation responsiveness
            {
                selector: '.main-menu .navbar-nav .nav-link',
                property: 'padding',
                expected: device.width < 768 ? '12px 15px' : '15px 20px'
            },
            // Product card images
            {
                selector: '.product-miniature .product-thumbnail img',
                property: 'height',
                expected: device.width < 576 ? '180px' : device.width < 768 ? '200px' : '250px'
            }
        ];
        
        for (const test of tests) {
            const element = await page.$(test.selector);
            if (element) {
                const computedStyle = await page.evaluate((el, prop) => {
                    return window.getComputedStyle(el)[prop];
                }, element, test.property);
                
                console.log(`${device.name} - ${test.selector}: ${computedStyle}`);
            }
        }
        
        await page.close();
    }
    
    await browser.close();
}
```

### Manual Responsive Testing
- [ ] **Mobile (320px-767px)**: Navigation collapses, product grid single column
- [ ] **Tablet (768px-1199px)**: Product grid 2-3 columns, header elements scale
- [ ] **Desktop (1200px+)**: Full layout, all hover effects work
- [ ] **Touch devices**: Buttons have adequate touch targets (44px minimum)

## 6. Performance Testing

### CSS Performance Metrics
```bash
# Install performance testing tools
npm install -g lighthouse cli
npm install -g web-vitals-cli

# Run Lighthouse performance audit
lighthouse http://localhost/prestashop --only-categories=performance --output=json --output-path=./lighthouse-report.json

# Test Web Core Vitals
web-vitals http://localhost/prestashop
```

### Performance Testing Checklist
- [ ] **First Contentful Paint (FCP)**: < 1.8 seconds
- [ ] **Largest Contentful Paint (LCP)**: < 2.5 seconds
- [ ] **Cumulative Layout Shift (CLS)**: < 0.1
- [ ] **CSS File Size**: < 100KB (compressed)
- [ ] **Font Loading**: Optimized with font-display: swap
- [ ] **Critical CSS**: Above-the-fold styles inlined

### CSS Optimization Tests
```bash
# Test CSS minification
npm install -g clean-css-cli
cleancss -o css/abrasif-italia.min.css css/abrasif-italia-prestashop-base.css

# Test gzip compression
gzip -c css/abrasif-italia.min.css | wc -c

# Test unused CSS removal
npm install -g purgecss
purgecss --css css/abrasif-italia-prestashop-base.css --content templates/**/*.tpl --output css/
```

## 7. Accessibility Testing

### Automated Accessibility Testing
```bash
# Install accessibility testing tools
npm install -g pa11y axe-cli

# Run Pa11y accessibility test
pa11y http://localhost/prestashop --standard WCAG2AA

# Run axe-core accessibility test
axe http://localhost/prestashop --tags wcag2a,wcag2aa
```

### Manual Accessibility Testing
- [ ] **Color Contrast**: All text meets 4.5:1 ratio minimum
- [ ] **Focus Indicators**: All interactive elements have visible focus states
- [ ] **Keyboard Navigation**: All functionality accessible via keyboard
- [ ] **Screen Reader**: Test with NVDA/JAWS/VoiceOver
- [ ] **Alt Text**: All images have descriptive alt attributes
- [ ] **Semantic HTML**: Proper heading hierarchy and landmarks

### Accessibility Testing Checklist
```html
<!-- Test these elements specifically -->
<nav aria-label="Menu principale">         <!-- Navigation labels -->
<button aria-expanded="false">             <!-- Dropdown states -->
<img alt="Descriptive text">               <!-- Image alternatives -->
<form role="search">                       <!-- Form roles -->
<h1>, <h2>, <h3> hierarchy                 <!-- Heading structure -->
<a aria-label="Descriptive link text">    <!-- Link context -->
```

## 8. Browser Compatibility Testing

### Supported Browsers
- **Chrome**: Latest 2 versions
- **Firefox**: Latest 2 versions
- **Safari**: Latest 2 versions
- **Edge**: Latest 2 versions
- **Mobile Safari**: iOS 12+
- **Chrome Mobile**: Android 8+

### Cross-Browser Testing Tools
```javascript
// Using Playwright for cross-browser testing
const { chromium, firefox, webkit } = require('playwright');

async function crossBrowserTest() {
    const browsers = [
        { name: 'Chromium', browser: chromium },
        { name: 'Firefox', browser: firefox },
        { name: 'WebKit', browser: webkit }
    ];
    
    for (const { name, browser } of browsers) {
        const browserInstance = await browser.launch();
        const page = await browserInstance.newPage();
        
        await page.goto('http://localhost/prestashop');
        
        // Test CSS features
        const cssTests = [
            'CSS Grid support',
            'CSS Custom Properties support',
            'Flexbox support',
            'Transform support'
        ];
        
        for (const test of cssTests) {
            const supported = await page.evaluate((testName) => {
                // Test specific CSS features
                switch (testName) {
                    case 'CSS Grid support':
                        return CSS.supports('display', 'grid');
                    case 'CSS Custom Properties support':
                        return CSS.supports('color', 'var(--test)');
                    case 'Flexbox support':
                        return CSS.supports('display', 'flex');
                    case 'Transform support':
                        return CSS.supports('transform', 'translateY(-4px)');
                    default:
                        return false;
                }
            }, test);
            
            console.log(`${name} - ${test}: ${supported ? '✅' : '❌'}`);
        }
        
        await browserInstance.close();
    }
}
```

## 9. Load Testing

### CSS Loading Performance
```javascript
// test/load-performance.js
async function loadTest() {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // Monitor network activity
    await page.setRequestInterception(true);
    
    let cssLoadTime = 0;
    
    page.on('request', request => {
        if (request.url().includes('abrasif-italia')) {
            console.log('CSS loading started');
            cssLoadTime = Date.now();
        }
        request.continue();
    });
    
    page.on('response', response => {
        if (response.url().includes('abrasif-italia')) {
            const loadTime = Date.now() - cssLoadTime;
            console.log(`CSS loaded in ${loadTime}ms`);
            
            if (loadTime > 500) {
                console.log('⚠️  CSS load time exceeds 500ms');
            } else {
                console.log('✅ CSS load time acceptable');
            }
        }
    });
    
    await page.goto('http://localhost/prestashop');
    await browser.close();
}
```

## 10. Deployment Testing Strategy

### Pre-Deployment Checklist
- [ ] All automated tests pass
- [ ] Visual regression tests pass
- [ ] Performance metrics meet requirements
- [ ] Accessibility tests pass
- [ ] Cross-browser compatibility verified
- [ ] Mobile responsiveness confirmed
- [ ] Request Quote functionality preserved
- [ ] PrestaShop cache cleared
- [ ] Backup created

### Staged Deployment Process

#### Phase 1: Staging Deployment
```bash
# Deploy to staging environment
rsync -avz css/ staging.abrasiitalia.com:/var/www/html/css/
ssh staging.abrasiitalia.com "cd /var/www/html && php bin/console cache:clear"

# Run staging tests
npm run test:staging
```

#### Phase 2: Canary Deployment (Optional)
```bash
# Deploy to subset of production traffic
# Monitor error rates and performance
# Rollback if issues detected
```

#### Phase 3: Production Deployment
```bash
# Create production backup
ssh production.abrasiitalia.com "mysqldump -u user -p prestashop > backup_$(date +%Y%m%d_%H%M%S).sql"

# Deploy CSS files
rsync -avz css/ production.abrasiitalia.com:/var/www/html/css/

# Clear PrestaShop caches
ssh production.abrasiitalia.com "cd /var/www/html && php bin/console cache:clear --env=prod"

# Verify deployment
curl -I https://abrasiitalia.com/css/abrasif-italia-prestashop-base.css
```

### Post-Deployment Monitoring
- [ ] Monitor error logs for CSS-related issues
- [ ] Check Google Search Console for crawl errors
- [ ] Monitor Core Web Vitals in Google Analytics
- [ ] Test request quote functionality
- [ ] Verify mobile user experience
- [ ] Check social media sharing previews

### Rollback Plan
```bash
# If issues are detected, immediate rollback
ssh production.abrasiitalia.com "cd /var/www/html && git checkout HEAD~1 -- css/"
ssh production.abrasiitalia.com "cd /var/www/html && php bin/console cache:clear --env=prod"
```

This comprehensive testing strategy ensures that the Abrasif Italia UI changes are thoroughly validated and safely deployed while maintaining PrestaShop compatibility and optimal user experience. 
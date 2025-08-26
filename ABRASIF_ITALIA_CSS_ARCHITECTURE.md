# Abrasif Italia - Modular CSS/SCSS Architecture Guide

## 1. Architecture Overview

This guide outlines a scalable, modular CSS architecture for Abrasif Italia that respects PrestaShop's structure while providing maximum maintainability and flexibility.

### Design Principles
- **PrestaShop Compatibility**: Works with existing PrestaShop classes and structure
- **Modular Design**: Component-based architecture for easy maintenance
- **Scalability**: Easy to extend and modify without breaking existing styles
- **Performance**: Optimized loading and minimal CSS bloat
- **BEM Methodology**: Clear naming conventions for custom components

## 2. File Structure

```
/css/abrasif-italia/
├── main.scss                    # Main entry point
├── _config.scss                 # Configuration and variables
├── _mixins.scss                 # Reusable mixins and functions
├── base/
│   ├── _reset.scss             # Normalize/reset styles
│   ├── _typography.scss        # Font loading and base typography
│   ├── _variables.scss         # CSS custom properties
│   └── _utilities.scss         # Utility classes
├── components/
│   ├── _buttons.scss           # Button components
│   ├── _forms.scss             # Form elements
│   ├── _cards.scss             # Product cards and content cards
│   ├── _modals.scss            # Modal components
│   ├── _badges.scss            # Badges and labels
│   └── _navigation.scss        # Navigation components
├── layout/
│   ├── _header.scss            # Header and top navigation
│   ├── _footer.scss            # Footer styles
│   ├── _sidebar.scss           # Sidebar layouts
│   └── _grid.scss              # Grid system enhancements
├── pages/
│   ├── _home.scss              # Homepage specific styles
│   ├── _product.scss           # Product page styles
│   ├── _category.scss          # Category page styles
│   ├── _checkout.scss          # Checkout process styles
│   └── _account.scss           # User account pages
├── vendor/
│   ├── _prestashop-overrides.scss # PrestaShop specific overrides
│   └── _request-quote.scss     # Request quote module integration
└── responsive/
    ├── _mobile.scss            # Mobile-first responsive styles
    ├── _tablet.scss            # Tablet-specific styles
    └── _desktop.scss           # Desktop enhancements
```

## 3. Configuration File (_config.scss)

```scss
// ========================================
// ABRASIF ITALIA CONFIGURATION
// ========================================

// Brand Colors
$ai-colors: (
  'primary': #e31e24,
  'primary-dark': #c41e3a,
  'primary-light': #ff4757,
  'secondary': #28a745,
  'secondary-dark': #1e7e34,
  'secondary-light': #40c057,
  'white': #ffffff,
  'black': #000000,
  'gray-50': #f8f9fa,
  'gray-100': #e9ecef,
  'gray-300': #dee2e6,
  'gray-500': #6c757d,
  'gray-700': #495057,
  'gray-900': #343a40
);

// Typography
$ai-fonts: (
  'primary': ('Roboto', 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif),
  'headings': ('Montserrat', 'Open Sans', Arial, sans-serif)
);

$ai-font-sizes: (
  'xs': 0.75rem,
  'sm': 0.875rem,
  'base': 1rem,
  'lg': 1.125rem,
  'xl': 1.25rem,
  '2xl': 1.5rem,
  '3xl': 1.875rem,
  '4xl': 2.25rem
);

// Spacing
$ai-spacing: (
  '0': 0,
  '1': 0.25rem,
  '2': 0.5rem,
  '3': 0.75rem,
  '4': 1rem,
  '5': 1.25rem,
  '6': 1.5rem,
  '8': 2rem,
  '10': 2.5rem,
  '12': 3rem,
  '16': 4rem,
  '20': 5rem
);

// Border Radius
$ai-border-radius: (
  'none': 0,
  'sm': 0.125rem,
  'base': 0.375rem,
  'md': 0.375rem,
  'lg': 0.5rem,
  'xl': 1rem,
  'full': 9999px
);

// Shadows
$ai-shadows: (
  'sm': 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075),
  'base': 0 0.5rem 1rem rgba(0, 0, 0, 0.15),
  'lg': 0 1rem 3rem rgba(0, 0, 0, 0.175),
  'xl': 0 1.25rem 3rem rgba(0, 0, 0, 0.25)
);

// Breakpoints (matches PrestaShop/Bootstrap)
$ai-breakpoints: (
  'xs': 0,
  'sm': 576px,
  'md': 768px,
  'lg': 992px,
  'xl': 1200px,
  'xxl': 1400px
);

// Transitions
$ai-transitions: (
  'fast': 0.15s ease-in-out,
  'base': 0.3s cubic-bezier(0.4, 0, 0.2, 1),
  'slow': 0.5s ease-in-out
);
```

## 4. Mixins and Functions (_mixins.scss)

```scss
// ========================================
// ABRASIF ITALIA MIXINS & FUNCTIONS
// ========================================

// Color function
@function ai-color($color-name) {
  @return map-get($ai-colors, $color-name);
}

// Spacing function
@function ai-space($size) {
  @return map-get($ai-spacing, $size);
}

// Font size function
@function ai-font-size($size) {
  @return map-get($ai-font-sizes, $size);
}

// Responsive breakpoint mixin
@mixin ai-breakpoint($breakpoint) {
  $value: map-get($ai-breakpoints, $breakpoint);
  
  @if $value != null {
    @media (min-width: $value) {
      @content;
    }
  }
}

// Button mixin
@mixin ai-button($bg-color: 'primary', $text-color: 'white', $size: 'base') {
  background-color: ai-color($bg-color);
  color: ai-color($text-color);
  border: none;
  border-radius: map-get($ai-border-radius, 'lg');
  font-family: map-get($ai-fonts, 'headings');
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: map-get($ai-transitions, 'base');
  cursor: pointer;
  
  @if $size == 'sm' {
    padding: ai-space('2') ai-space('4');
    font-size: ai-font-size('sm');
  } @else if $size == 'lg' {
    padding: ai-space('4') ai-space('6');
    font-size: ai-font-size('lg');
  } @else {
    padding: ai-space('3') ai-space('5');
    font-size: ai-font-size('base');
  }
  
  &:hover,
  &:focus {
    background-color: ai-color($bg-color + '-dark');
    transform: translateY(-2px);
    box-shadow: map-get($ai-shadows, 'base');
  }
  
  &:active {
    transform: translateY(-1px);
  }
  
  &:disabled {
    background-color: ai-color('gray-500');
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }
}

// Card mixin
@mixin ai-card($padding: '4', $shadow: 'base') {
  background-color: ai-color('white');
  border: 2px solid ai-color('gray-100');
  border-radius: map-get($ai-border-radius, 'xl');
  padding: ai-space($padding);
  box-shadow: map-get($ai-shadows, $shadow);
  transition: map-get($ai-transitions, 'base');
  
  &:hover {
    border-color: ai-color('primary');
    box-shadow: map-get($ai-shadows, 'lg');
    transform: translateY(-4px);
  }
}

// Text styling mixin
@mixin ai-text($size: 'base', $weight: 400, $color: 'gray-700') {
  font-size: ai-font-size($size);
  font-weight: $weight;
  color: ai-color($color);
  line-height: 1.6;
}

// Flexbox utilities
@mixin ai-flex($direction: row, $align: center, $justify: flex-start, $wrap: nowrap) {
  display: flex;
  flex-direction: $direction;
  align-items: $align;
  justify-content: $justify;
  flex-wrap: $wrap;
}

// Grid utilities
@mixin ai-grid($columns: auto-fit, $min-width: 250px, $gap: '4') {
  display: grid;
  grid-template-columns: repeat($columns, minmax($min-width, 1fr));
  gap: ai-space($gap);
}

// Focus styles for accessibility
@mixin ai-focus-ring($color: 'primary') {
  &:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(ai-color($color), 0.25);
  }
}

// Hover lift effect
@mixin ai-hover-lift($distance: -4px) {
  transition: transform map-get($ai-transitions, 'base'), box-shadow map-get($ai-transitions, 'base');
  
  &:hover {
    transform: translateY($distance);
    box-shadow: map-get($ai-shadows, 'lg');
  }
}
```

## 5. Component Architecture

### Button Component (_buttons.scss)

```scss
// ========================================
// BUTTON COMPONENTS
// ========================================

// Base button class (enhances PrestaShop .btn)
.ai-btn {
  @include ai-button();
  
  // Size variants
  &--sm { @include ai-button($size: 'sm'); }
  &--lg { @include ai-button($size: 'lg'); }
  
  // Color variants
  &--primary { @include ai-button('primary', 'white'); }
  &--secondary { @include ai-button('secondary', 'white'); }
  &--outline-primary {
    background-color: transparent;
    color: ai-color('primary');
    border: 2px solid ai-color('primary');
    
    &:hover {
      background-color: ai-color('primary');
      color: ai-color('white');
    }
  }
  
  // Full width
  &--full { width: 100%; }
  
  // Icon buttons
  &--icon {
    @include ai-flex(row, center, center);
    
    .ai-icon {
      margin-right: ai-space('2');
      
      &:last-child {
        margin-right: 0;
        margin-left: ai-space('2');
      }
      
      &:only-child {
        margin: 0;
      }
    }
  }
}

// PrestaShop button enhancements
.btn {
  &.btn-primary {
    @include ai-button('primary', 'white');
  }
  
  &.btn-secondary {
    @include ai-button('secondary', 'white');
  }
}
```

### Card Component (_cards.scss)

```scss
// ========================================
// CARD COMPONENTS
// ========================================

// Base card class
.ai-card {
  @include ai-card();
  
  // Card variants
  &--no-hover {
    &:hover {
      transform: none;
      border-color: ai-color('gray-100');
      box-shadow: map-get($ai-shadows, 'base');
    }
  }
  
  &--interactive {
    cursor: pointer;
    
    &:hover {
      border-color: ai-color('primary');
      box-shadow: map-get($ai-shadows, 'xl');
      transform: translateY(-8px);
    }
  }
  
  // Card header
  &__header {
    padding-bottom: ai-space('4');
    border-bottom: 1px solid ai-color('gray-100');
    margin-bottom: ai-space('4');
    
    .ai-card__title {
      @include ai-text('xl', 600, 'gray-900');
      margin: 0;
      font-family: map-get($ai-fonts, 'headings');
    }
  }
  
  // Card body
  &__body {
    @include ai-text('base', 400, 'gray-700');
  }
  
  // Card footer
  &__footer {
    padding-top: ai-space('4');
    border-top: 1px solid ai-color('gray-100');
    margin-top: ai-space('4');
    @include ai-flex(row, center, space-between);
  }
}

// Product card enhancements (works with PrestaShop .product-miniature)
.product-miniature {
  @include ai-card('4', 'base');
  
  .ai-product {
    &__image {
      position: relative;
      overflow: hidden;
      border-radius: map-get($ai-border-radius, 'xl') map-get($ai-border-radius, 'xl') 0 0;
      margin: -#{ai-space('4')} -#{ai-space('4')} ai-space('4') -#{ai-space('4')};
      
      img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform map-get($ai-transitions, 'base');
      }
      
      &::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(ai-color('primary'), 0.8), rgba(ai-color('secondary'), 0.8));
        opacity: 0;
        transition: opacity map-get($ai-transitions, 'base');
      }
    }
    
    &__title {
      @include ai-text('lg', 600, 'gray-900');
      font-family: map-get($ai-fonts, 'headings');
      margin-bottom: ai-space('2');
      
      a {
        color: inherit;
        text-decoration: none;
        transition: color map-get($ai-transitions, 'base');
        
        &:hover {
          color: ai-color('primary');
        }
      }
    }
    
    &__description {
      @include ai-text('sm', 400, 'gray-500');
      margin-bottom: ai-space('4');
    }
    
    &__actions {
      @include ai-flex(row, center, space-between);
      margin-top: auto;
    }
  }
  
  &:hover {
    .ai-product__image {
      img {
        transform: scale(1.1);
      }
      
      &::after {
        opacity: 1;
      }
    }
  }
}
```

## 6. Integration Strategy

### Development Workflow

1. **Setup SCSS Compilation**
   ```bash
   npm install -g sass
   sass --watch scss:css --style compressed
   ```

2. **PrestaShop Integration**
   ```php
   // In your module or theme
   public function hookDisplayHeader()
   {
       $this->context->controller->addCSS($this->_path.'css/abrasif-italia.css');
   }
   ```

3. **Build Process**
   ```json
   // package.json
   {
     "scripts": {
       "build-css": "sass scss/main.scss css/abrasif-italia.css --style compressed",
       "watch-css": "sass --watch scss:css --style compressed",
       "dev": "sass --watch scss:css --style expanded --source-map"
     }
   }
   ```

### File Loading Order

1. PrestaShop core CSS
2. Bootstrap CSS (if used)
3. Theme CSS
4. **Abrasif Italia CSS** (our custom styles)
5. Module-specific CSS
6. Request Quote CSS (preserved)

## 7. Maintenance Guidelines

### Adding New Components

1. Create component file in `components/` directory
2. Follow BEM naming convention with `ai-` prefix
3. Use configuration variables and mixins
4. Test with existing PrestaShop classes
5. Document usage examples

### Modifying Existing Styles

1. Always check PrestaShop compatibility
2. Use CSS specificity carefully (avoid excessive !important)
3. Test across different PrestaShop versions
4. Maintain backward compatibility
5. Update documentation

### Performance Optimization

1. **Critical CSS**: Inline above-the-fold styles
2. **Code Splitting**: Load page-specific styles as needed
3. **Purge Unused CSS**: Remove unused styles in production
4. **Compression**: Use compressed CSS in production
5. **Caching**: Implement proper cache headers

## 8. Testing Strategy

### Browser Testing
- Chrome/Chromium (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### PrestaShop Version Testing
- PrestaShop 1.7.x
- PrestaShop 8.x
- PrestaShop 9.x

### Device Testing
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (320px - 767px)

### Automated Testing
```bash
# CSS Validation
npm install -g css-validator
css-validator css/abrasif-italia.css

# Accessibility Testing
npm install -g pa11y
pa11y --runner axe http://your-prestashop-site.com

# Performance Testing
npm install -g lighthouse
lighthouse http://your-prestashop-site.com --only-categories=performance
```

This modular architecture ensures scalability, maintainability, and full PrestaShop compatibility while providing the professional Abrasif Italia brand experience. 
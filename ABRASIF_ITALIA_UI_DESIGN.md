# Abrasif Italia - Professional E-commerce UI Design System

## 1. Design Philosophy & Brand Identity

### Brand Positioning
Abrasif Italia represents professional cleaning machines and industrial products, requiring a design that conveys:
- **Reliability & Trust**: Clean, structured layouts with consistent spacing
- **Industrial Strength**: Bold typography and solid visual elements
- **Professional Service**: Sophisticated color usage and premium feel
- **Italian Excellence**: Refined aesthetics with attention to detail

### Color Palette Strategy
```css
Primary Colors:
- Abrasif Red (#e31e24): Action buttons, highlights, brand elements
- Professional Green (#28a745): Success states, secondary actions, trust indicators
- Pure White (#ffffff): Background, content areas, clean spaces

Supporting Colors:
- Dark Red (#c41e3a): Hover states, depth
- Dark Green (#1e7e34): Active states, emphasis
- Light Gray (#f8f9fa): Section backgrounds, subtle divisions
- Medium Gray (#6c757d): Text, icons, secondary information
- Dark Gray (#343a40): Primary text, strong contrast
```

### Typography Hierarchy
```css
Primary Font: 'Roboto' - Modern, readable, professional
Secondary Font: 'Montserrat' - Bold headings, navigation, buttons
- Headers: Montserrat 600-700 weight
- Body Text: Roboto 400 weight
- Navigation: Montserrat 500 weight, uppercase
- Buttons: Montserrat 600 weight, uppercase
```

## 2. Header Component Design

### Structure & Layout
```
┌─────────────────────────────────────────────────────────────┐
│ TOP BAR: Contact Info | Language | Currency | Account       │
├─────────────────────────────────────────────────────────────┤
│ MAIN HEADER: Logo | Search Bar | Cart | User Account        │
├─────────────────────────────────────────────────────────────┤
│ NAVIGATION: Home | Products | Services | About | Contact     │
└─────────────────────────────────────────────────────────────┘
```

### Visual Specifications
- **Top Bar**: White background (#ffffff), red accent border (3px #e31e24)
- **Main Header**: White background with light shadow, 60px logo height
- **Navigation**: Red background (#e31e24) with hover animations
- **Mobile**: Collapsible hamburger menu with slide animations

### Interactive Elements
- Logo: Subtle scale effect on hover (1.05x)
- Navigation items: Underline animation with green accent
- Search bar: Focus states with green border and shadow
- Cart/Account: Scale and color transitions

## 3. Footer Component Design

### Structure & Layout
```
┌─────────────────────────────────────────────────────────────┐
│ MAIN FOOTER                                                 │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────┐ │
│ │ Company     │ │ Products    │ │ Support     │ │ Contact │ │
│ │ Information │ │ Categories  │ │ & Services  │ │ Details │ │
│ └─────────────┘ └─────────────┘ └─────────────┘ └─────────┘ │
├─────────────────────────────────────────────────────────────┤
│ BOTTOM BAR: Copyright | Legal Links | Social Media         │
└─────────────────────────────────────────────────────────────┘
```

### Visual Specifications
- **Background**: Dark gradient (gray-dark to black)
- **Accent**: Red-to-green gradient top border (4px)
- **Typography**: White text with red headings
- **Links**: Smooth color transitions to green on hover

## 4. Product Display Components

### Product Cards
- **Clean Design**: Rounded corners (12px), subtle shadows
- **Image Treatment**: 250px height, object-fit cover, hover scale
- **Information Layout**: Structured padding, clear typography hierarchy
- **Hover Effects**: Lift animation (-8px), border color change to red
- **Badge System**: New (green), Sale (red) with rounded badges

### Integration with Request Quote System
- **Preserved Classes**: All existing `.request-quote-*` classes maintained
- **Color Harmony**: Request quote buttons use brand red/green palette
- **Modal Styling**: Professional modal with brand colors and clean form design

## 5. Responsive Design Strategy

### Breakpoint System
```css
Mobile First Approach:
- Base: 320px+ (mobile)
- Small: 576px+ (large mobile)
- Medium: 768px+ (tablet)
- Large: 992px+ (desktop)
- Extra Large: 1200px+ (large desktop)
```

### Adaptive Elements
- **Navigation**: Hamburger menu below 768px
- **Product Grid**: 1 column mobile, 2 tablet, 3+ desktop
- **Typography**: Responsive font scaling
- **Spacing**: Proportional padding/margins

## 6. Accessibility & Standards

### WCAG 2.1 Compliance
- **Color Contrast**: Minimum 4.5:1 ratio for normal text
- **Focus Indicators**: Visible focus states for all interactive elements
- **Semantic HTML**: Proper heading hierarchy, landmark roles
- **Keyboard Navigation**: Full keyboard accessibility
- **Screen Reader**: Proper ARIA labels and descriptions

### Performance Considerations
- **CSS Organization**: Modular structure for efficient loading
- **Font Loading**: Optimized Google Fonts loading with display: swap
- **Critical CSS**: Above-the-fold styles inlined
- **Progressive Enhancement**: Graceful degradation for older browsers

## 7. Integration Guidelines

### PrestaShop Compatibility
- **Theme Agnostic**: Works with Classic and custom themes
- **Hook Integration**: Proper PrestaShop hook utilization
- **Override Safety**: Non-destructive styling approach
- **Module Compatibility**: Respects existing module styles

### Request Quote Module Preservation
- **Protected Classes**: All `.request-quote-*` classes preserved
- **Color Integration**: Request quote buttons adapted to brand palette
- **Modal Enhancement**: Improved styling while maintaining functionality
- **Form Consistency**: Unified form styling across the site

## 8. Maintenance & Scalability

### CSS Architecture
- **Modular Structure**: Component-based organization
- **Variable System**: CSS custom properties for easy updates
- **Naming Convention**: BEM methodology for clarity
- **Documentation**: Comprehensive code comments

### Future-Proofing
- **Flexible Grid**: CSS Grid and Flexbox for layout
- **Scalable Components**: Reusable design patterns
- **Theme Variations**: Easy color scheme modifications
- **Extension Ready**: Prepared for additional features

This design system provides a solid foundation for Abrasif Italia's e-commerce presence while maintaining the existing request quote functionality and ensuring professional, scalable, and accessible user experience. 
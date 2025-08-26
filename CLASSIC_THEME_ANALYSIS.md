# 🔍 Analyse Complète du Thème Classic PrestaShop

## 📁 **Structure CSS du Thème Classic**

### **Fichiers CSS Principaux :**
```
/themes/classic/assets/css/
├── theme.css                    # CSS principal compilé (minifié)
├── custom.css                   # CSS personnalisé (modifications sûres)
├── rtl.css                     # Support RTL (droite vers gauche)
└── autoload/
    ├── bootstrap.css           # Bootstrap personnalisé
    ├── font-awesome.css        # Icônes
    └── modules/               # CSS des modules
```

### **Sources SCSS (Développement) :**
```
/themes/classic/assets/scss/
├── theme.scss                  # Point d'entrée principal
├── partials/
│   ├── _variables.scss        # Variables globales
│   ├── _mixins.scss          # Mixins utilitaires
│   ├── _commons.scss         # Styles de base
│   ├── _header.scss          # En-tête et navigation
│   ├── _footer.scss          # Pied de page
│   ├── _product.scss         # Pages produits
│   ├── _category.scss        # Pages catégories
│   ├── _checkout.scss        # Tunnel de commande
│   ├── _responsive.scss      # Media queries
│   └── _buttons.scss         # Boutons
└── vendor/
    └── bootstrap/            # Bootstrap customisé
```

## 🎨 **Composants CSS Clés du Thème Classic**

### **1. Variables de Base (_variables.scss)**
```scss
// Couleurs principales
$primary-color: #2fb5d2;        // Bleu principal
$secondary-color: #fbb700;      // Jaune secondaire
$success-color: #5cb85c;        // Vert succès
$danger-color: #d9534f;         // Rouge erreur
$warning-color: #f0ad4e;        // Orange avertissement

// Typographie
$font-family: 'Open Sans', Arial, sans-serif;
$font-size-base: 14px;
$line-height: 1.42857143;

// Layout
$container-max-width: 1140px;
$grid-gutter-width: 30px;

// Breakpoints
$screen-xs: 480px;
$screen-sm: 768px;
$screen-md: 992px;
$screen-lg: 1200px;
```

### **2. Header Structure**
```scss
// Header principal
.header-top {
    background: #fff;
    border-bottom: 1px solid #e6e6e6;
}

.header-nav {
    .navbar-brand {
        // Logo entreprise
    }
    .navbar-nav {
        // Navigation principale
    }
    .search-widget {
        // Barre de recherche
    }
    .cart-preview {
        // Panier et compte
    }
}
```

### **3. Product Cards Structure**
```scss
.product-miniature {
    border: 1px solid #e6e6e6;
    background: #fff;
    transition: all 0.3s ease;
    
    .thumbnail-container {
        position: relative;
        
        .product-thumbnail {
            img {
                width: 100%;
                height: auto;
            }
        }
        
        .highlighted-informations {
            // Badges (nouveau, promo, etc.)
        }
        
        .quick-view {
            // Bouton quick view (à supprimer)
        }
    }
    
    .product-description {
        padding: 15px;
        
        .h3 {
            // Nom du produit
        }
        
        .product-price-and-shipping {
            // Prix et frais de port
        }
        
        .product-list-actions {
            // Actions (ajouter au panier, etc.)
        }
    }
    
    &:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
}
```

### **4. Footer Structure**
```scss
.footer-container {
    background: #3a3a3a;
    color: #fff;
    
    .footer-top {
        // Liens et informations
    }
    
    .footer-bottom {
        // Copyright et mentions
    }
}
```

## 🎯 **Points Clés pour Customisation**

### **Sélecteurs Importants :**
```scss
// Navigation
.main-menu
.top-menu
.navbar-nav

// Produits
.product-miniature
.product-thumbnail
.product-price
.product-description
.quick-view (à supprimer)

// Boutons
.btn
.btn-primary
.btn-secondary
.add-to-cart

// Layout
.container
.row
.col-*
.header-top
.footer-container

// Modal
.modal
.modal-dialog
.modal-content
```

### **Hooks CSS Disponibles :**
```scss
// Hooks pour modules
.displayHeader
.displayNav
.displayTop
.displayHome
.displayFooter
.displayProductActions
.displayProductAdditionalInfo
```

## 🚀 **Stratégie de Customisation pour Abrasif Italia**

### **1. Couleurs à Override :**
- **Rouge principal** : #e31e24 (remplace #2fb5d2)
- **Vert secondaire** : #28a745 (remplace #fbb700)  
- **Blanc** : #ffffff (backgrounds)

### **2. Composants à Personnaliser :**
- Header avec logo Abrasif Italia
- Navigation avec couleurs entreprise
- Product cards avec hover effects propres
- Footer avec informations entreprise
- Suppression définitive quick view

### **3. Approche Technique :**
- Créer `/themes/classic/assets/css/abrasif-italia.css`
- Utiliser `!important` pour overrides spécifiques
- Respecter la structure existante
- Ajouter via hook displayHeader

## 📋 **Checklist d'Analyse :**
- ✅ Structure CSS identifiée
- ✅ Variables principales comprises
- ✅ Composants clés analysés
- ✅ Points d'override identifiés
- ✅ Stratégie définie

**Prêt pour la Phase 2 : Création du CSS Personnalisé !** 
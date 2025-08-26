<?php
/**
 * RequestQuote Module for PrestaShop 9.0.0
 * Simple and clean quote request system
 * 
 * @author Amine Jameli
 * @version 2.1.0
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuote extends Module
{
    public function __construct()
    {
        $this->name = 'requestquote';
        $this->tab = 'front_office_features';
        $this->version = '2.1.7';
        $this->author = 'Amine Jameli';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99'
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Demande de Devis';
        $this->description = 'Système de demande de devis qui masque les prix et affiche des boutons de devis.';
        $this->confirmUninstall = 'Êtes-vous sûr de vouloir désinstaller ce module ?';

        // Handle AJAX requests
        $this->handleAjaxRequest();
    }

    /**
     * Handle AJAX form submissions
     */
    private function handleAjaxRequest()
    {
        if (Tools::getValue('action') === 'submitQuote' && Tools::isSubmit('action')) {
            $response = ['success' => false, 'message' => ''];

            try {
                if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                    throw new Exception('Les demandes de devis sont désactivées');
                }

                // Get form data
                $productId = (int)Tools::getValue('product_id');
                $clientName = trim(Tools::getValue('client_name'));
                $email = trim(Tools::getValue('email'));
                $phone = trim(Tools::getValue('phone'));
                $message = trim(Tools::getValue('message'));

                // Validate required fields
                if (!$productId) {
                    throw new Exception('Produit non spécifié');
                }
                if (!$clientName) {
                    throw new Exception('Veuillez saisir votre nom');
                }
                if (!$email) {
                    throw new Exception('Veuillez saisir votre adresse email');
                }

                if (!Validate::isEmail($email)) {
                    throw new Exception('Adresse email invalide');
                }

                // Save to database
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'requestquote_quotes` 
                        (id_product, client_name, email, phone, message, date_add) 
                        VALUES (' . (int)$productId . ', "' . pSQL($clientName) . '", "' . pSQL($email) . '", 
                        "' . pSQL($phone) . '", "' . pSQL($message) . '", NOW())';

                if (Db::getInstance()->execute($sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Votre demande de devis a été envoyée avec succès !';
                } else {
                    throw new Exception('Erreur lors de l\'enregistrement de votre demande');
                }

            } catch (Exception $e) {
                $response['message'] = $e->getMessage();
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Vérifier les données existantes avant installation
        $existingQuotes = $this->checkDataIntegrity();
        if ($existingQuotes > 0) {
            // Créer une sauvegarde automatique
            $this->backupQuotes();
        }

        // Create database table (IF NOT EXISTS préserve les données)
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes` (
            `id_quote` int(11) NOT NULL AUTO_INCREMENT,
            `id_product` int(11) NOT NULL,
            `client_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(50) DEFAULT NULL,
            `message` text DEFAULT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_quote`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        // Register hooks
        $hooks = [
            'displayHeader',
            'displayProductActions',
            'displayProductAdditionalInfo',
            'displayProductListFunctionalButtons',
            'displayProductPriceBlock',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        // Set configuration
        Configuration::updateValue('REQUESTQUOTE_ENABLED', 1);

        return true;
    }

        public function uninstall()
    {
        // IMPORTANT: Ne pas supprimer la table pour préserver les données
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes`');
        
        // Remove configuration only
        Configuration::deleteByName('REQUESTQUOTE_ENABLED');

        // Clean up any existing admin tabs
        $id_tab = (int)Tab::getIdFromClassName('AdminRequestQuote');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        return parent::uninstall();
    }

        /**
     * Display header - Add CSS to hide prices and add modal
     */
    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        // Load Abrasif Italia branding CSS
        $this->context->controller->addCSS($this->_path.'abrasif-italia-minimal.css');

        $css = '<style>
        /* Hide ALL prices and add to cart across ALL pages */
        .product-price, .current-price, .regular-price, .discount-percentage,
        .product-add-to-cart, .add-to-cart, .btn-add-to-cart,
        .product-quantity, .product-variants, .product-customization,
        .price, .prices, .product-price-and-shipping,
        .product-miniature .price, .product-list-item .price,
        .thumbnail-container .price, .product-thumbnail .price,
        .quickview .price, .modal .price,
        .product-list .price, .products .price,
        .featured-products .price, .new-products .price,
        .category-products .price, .search-results .price,
        .js-product-price, .product-price-block,
        .discount, .discount-amount, .discount-rule,
        .unit-price, .unit-price-ratio,
        .has-discount .regular-price,
        .product-discount, .product-reduction,
        .price-drop, .on-sale,
        .our_price_display, .old_price,
        .reduction_percent, .reduction_amount,
        span[itemprop="price"], span[itemprop="lowPrice"], span[itemprop="highPrice"] {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Remove ALL quick preview traces, elements and hover effects */
        .quick-view, .quickview, .js-quick-view-btn, 
        .product-quickview, .quick-view-btn,
        .modal-quickview, .product-modal,
        .product-miniature .quick-view,
        .thumbnail-container .quick-view,
        .product-thumbnail .quick-view,
        .js-product-miniature .quick-view,
        .featured-products .quick-view,
        .product-list .quick-view,
        .products .quick-view,
        .category-products .quick-view,
        .search-results .quick-view,
        .new-products .quick-view,
        .highlighted-informations .quick-view,
        .product-actions .quick-view,
        .product-functional-buttons .quick-view,
        a[data-link-action="quickview"],
        button[data-link-action="quickview"],
        .btn[data-link-action="quickview"],
        [data-toggle="modal"][href*="quickview"],
        [data-target*="quickview"],
        .modal[id*="quickview"],
        .modal-dialog[id*="quickview"],
        .product-cover-modal,
        .js-product-cover-modal,
        .product-images-modal,
        .product-miniature:hover .quick-view,
        .product-thumbnail:hover .quick-view,
        .js-product-miniature:hover .quick-view {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        
        /* Remove quick view icons, buttons and hover effects completely */
        .material-icons:contains("zoom_in"),
        .fa-search-plus,
        .icon-zoom-in,
        .icon-eye,
        .product-miniature:hover::before,
        .product-thumbnail:hover::before,
        .product-miniature:hover::after,
        .product-thumbnail:hover::after {
            display: none !important;
        }
        
        /* Disable hover effects that might show quick view */
        .product-miniature:hover,
        .product-thumbnail:hover,
        .js-product-miniature:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        
        /* Quote button styling - Abrasif Italia branding */
        .request-quote-btn {
            background: #e31e24;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .request-quote-btn:hover {
            background: #c41e3a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(227, 30, 36, 0.3);
        }
        
        /* Simple modal */
        .quote-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        .quote-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        .quote-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
        .quote-form input, .quote-form textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .quote-submit {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .quote-submit:hover {
            background: #1e7e34;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        </style>';

        $js = '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Add modal to page
            if (!document.getElementById("quoteModal")) {
                var modal = document.createElement("div");
                modal.id = "quoteModal";
                modal.className = "quote-modal";
                modal.innerHTML = \'<div class="quote-modal-content"><span class="quote-close">&times;</span><h3>Demande de Devis</h3><div id="quote-messages" style="margin-bottom: 15px;"></div><form class="quote-form" id="quoteForm"><input type="text" name="client_name" placeholder="Votre nom *" required><input type="email" name="email" placeholder="Votre email *" required><input type="tel" name="phone" placeholder="Téléphone (optionnel)"><textarea name="message" placeholder="Votre message (optionnel)" rows="3"></textarea><input type="hidden" name="product_id" value=""><button type="submit" class="quote-submit">Envoyer la demande</button></form></div>\';
                document.body.appendChild(modal);
            }
            
            // Handle modal
            var modal = document.getElementById("quoteModal");
            var closeBtn = modal.querySelector(".quote-close");
            
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            // Handle quote buttons
            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("request-quote-btn")) {
                    e.preventDefault();
                    var productId = e.target.getAttribute("data-product-id");
                    modal.querySelector("input[name=product_id]").value = productId;
                    modal.style.display = "block";
                }
            });
            
            // Handle form submission
            document.getElementById("quoteForm").onsubmit = function(e) {
                e.preventDefault();
                
                var messagesDiv = document.getElementById("quote-messages");
                var submitBtn = this.querySelector("button[type=submit]");
                
                // Clear previous messages
                messagesDiv.innerHTML = "";
                
                // Show loading
                submitBtn.disabled = true;
                submitBtn.textContent = "Envoi en cours...";
                
                var formData = new FormData(this);
                formData.append("action", "submitQuote");
                
                // Use a simple AJAX request to current page
                var xhr = new XMLHttpRequest();
                xhr.open("POST", window.location.href, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        // Reset button
                        submitBtn.disabled = false;
                        submitBtn.textContent = "Envoyer la demande";
                        
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    messagesDiv.innerHTML = \'<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px;"><strong>Succès :</strong> \' + response.message + \'</div>\';
                                    setTimeout(function() {
                                        modal.style.display = "none";
                                        document.getElementById("quoteForm").reset();
                                        messagesDiv.innerHTML = "";
                                    }, 2000);
                                } else {
                                    messagesDiv.innerHTML = \'<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;"><strong>Erreur :</strong> \' + response.message + \'</div>\';
                                }
                            } catch (e) {
                                messagesDiv.innerHTML = \'<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px;"><strong>Succès :</strong> Votre demande a été envoyée !</div>\';
                                setTimeout(function() {
                                    modal.style.display = "none";
                                    document.getElementById("quoteForm").reset();
                                    messagesDiv.innerHTML = "";
                                }, 2000);
                            }
                        } else {
                            messagesDiv.innerHTML = \'<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;"><strong>Erreur :</strong> Erreur de connexion. Veuillez réessayer.</div>\';
                        }
                    }
                };
                xhr.send(formData);
            };
        });
        
        // Completely disable quick view functionality
        document.addEventListener("click", function(e) {
            // Block all quick view related clicks
            if (e.target.matches(".quick-view, .quickview, .js-quick-view-btn, [data-link-action=\\"quickview\\"]") ||
                e.target.closest(".quick-view, .quickview, .js-quick-view-btn, [data-link-action=\\"quickview\\"]")) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, true);
        
        // Remove quick view elements after page load
        setTimeout(function() {
            var quickViewElements = document.querySelectorAll(
                ".quick-view, .quickview, .js-quick-view-btn, [data-link-action=\\"quickview\\"], " +
                ".product-quickview, .quick-view-btn, .modal-quickview, .product-modal, " +
                "[data-toggle=\\"modal\\"][href*=\\"quickview\\"], [data-target*=\\"quickview\\"]"
            );
            quickViewElements.forEach(function(element) {
                element.remove();
            });
        }, 1000);
        </script>';

        return $css . $js;
    }

    /**
     * Display product actions - Show quote button
     */
    public function hookDisplayProductActions($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED') || !isset($params['product'])) {
            return '';
        }

        $product = $params['product'];
        return '<div class="text-center">
                    <button class="request-quote-btn" data-product-id="' . (int)$product->id . '">
                        Demander un Devis
                    </button>
                </div>';
    }

    /**
     * Display product additional info - Alternative quote button location
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->hookDisplayProductActions($params);
    }

    /**
     * Display product list functional buttons - Show quote button on product lists
     */
    public function hookDisplayProductListFunctionalButtons($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED') || !isset($params['product'])) {
            return '';
        }

        $product = $params['product'];
        return '<div class="product-actions-main">
                    <button class="request-quote-btn" data-product-id="' . (int)$product['id_product'] . '">
                        Demander un Devis
                    </button>
                </div>';
    }

    /**
     * Display product price block - Hide prices everywhere
     */
    public function hookDisplayProductPriceBlock($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        return '<style>
            .product-price, .current-price, .regular-price, .discount-percentage,
            .price, .prices, .product-price-and-shipping {
                display: none !important;
                visibility: hidden !important;
            }
        </style>';
    }

    /**
     * Get module configuration form and display quotes
     */
    public function getContent()
    {
        $output = '';

        // Handle quote deletion
        if (Tools::isSubmit('delete_quote')) {
            $id_quote = (int)Tools::getValue('id_quote');
            if (Db::getInstance()->delete('requestquote_quotes', 'id_quote = ' . $id_quote)) {
                $output .= $this->displayConfirmation('Devis supprimé avec succès.');
            } else {
                $output .= $this->displayError('Erreur lors de la suppression du devis.');
            }
        }

        // Handle settings update
        if (Tools::isSubmit('submit' . $this->name)) {
            $enabled = (int)Tools::getValue('REQUESTQUOTE_ENABLED');
            Configuration::updateValue('REQUESTQUOTE_ENABLED', $enabled);
            $output .= $this->displayConfirmation('Paramètres mis à jour avec succès.');
        }

        return $output . $this->displayForm() . $this->displayQuotes();
    }

    /**
     * Display configuration form
     */
    public function displayForm()
    {
                $form = [
            'form' => [
                'legend' => [
                    'title' => 'Paramètres',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => 'Activer les demandes de devis',
                        'name' => 'REQUESTQUOTE_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => 'Activé'
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => 'Désactivé'
                            ]
                        ],
                    ],
                ],
                'submit' => [
                    'title' => 'Enregistrer',
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->fields_value['REQUESTQUOTE_ENABLED'] = Configuration::get('REQUESTQUOTE_ENABLED');

        return $helper->generateForm([$form]);
    }

    /**
     * Display quotes in module configuration
     */
    public function displayQuotes()
    {
        // Get all quotes from database
        $sql = 'SELECT q.*, p.name as product_name 
                FROM `' . _DB_PREFIX_ . 'requestquote_quotes` q
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (q.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')
                ORDER BY q.date_add DESC';
        
        $quotes = Db::getInstance()->executeS($sql);

        $totalQuotes = count($quotes) ?: 0;
        $html = '<div class="panel" style="margin-top: 20px;">
                    <div class="panel-heading">
                        <i class="icon-list"></i>
                        Demandes de Devis (' . $totalQuotes . ' au total)
                        ' . ($totalQuotes > 0 ? '<span style="color: #28a745; margin-left: 10px;"><i class="icon-check"></i> Données préservées</span>' : '') . '
                    </div>
                    <div class="panel-body">';

        if ($quotes && count($quotes) > 0) {
            $html .= '<div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Produit</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>';

            foreach ($quotes as $quote) {
                $productName = $quote['product_name'] ?: 'Product #' . $quote['id_product'];
                $message = $quote['message'] ? (strlen($quote['message']) > 50 ? substr($quote['message'], 0, 47) . '...' : $quote['message']) : '-';
                
                                 $html .= '<tr>
                            <td>#' . (int)$quote['id_quote'] . '</td>
                            <td>' . htmlspecialchars($quote['client_name']) . '</td>
                            <td><a href="mailto:' . htmlspecialchars($quote['email']) . '">' . htmlspecialchars($quote['email']) . '</a></td>
                            <td>' . ($quote['phone'] ? htmlspecialchars($quote['phone']) : '-') . '</td>
                            <td>' . htmlspecialchars($productName) . '</td>
                            <td title="' . htmlspecialchars($quote['message']) . '">' . htmlspecialchars($message) . '</td>
                            <td>' . date('d/m/Y H:i', strtotime($quote['date_add'])) . '</td>
                            <td>
                                <a href="' . $_SERVER['REQUEST_URI'] . '&delete_quote=1&id_quote=' . (int)$quote['id_quote'] . '" 
                                   class="btn btn-danger btn-xs" 
                                   onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce devis ?\');">
                                    <i class="icon-trash"></i> Supprimer
                                </a>
                            </td>
                          </tr>';
            }

            $html .= '</tbody></table></div>';
        } else {
            $html .= '<div class="alert alert-info">
                        <i class="icon-info-circle"></i>
                        Aucune demande de devis trouvée pour le moment.
                      </div>';
        }

        $html .= '</div></div>';

                        return $html;
    }

    /**
     * Créer une sauvegarde des demandes de devis existantes
     */
    public function backupQuotes()
    {
        try {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'requestquote_quotes` ORDER BY date_add DESC';
            $quotes = Db::getInstance()->executeS($sql);
            
            if ($quotes && count($quotes) > 0) {
                $backupFile = _PS_MODULE_DIR_ . $this->name . '/backup_quotes_' . date('Y-m-d_H-i-s') . '.json';
                file_put_contents($backupFile, json_encode($quotes, JSON_PRETTY_PRINT));
                return $backupFile;
            }
        } catch (Exception $e) {
            // Sauvegarde échouée mais on continue
            return false;
        }
        return false;
    }

    /**
     * Vérifier l'intégrité des données existantes
     */
    public function checkDataIntegrity()
    {
        try {
            $sql = 'SELECT COUNT(*) as total FROM `' . _DB_PREFIX_ . 'requestquote_quotes`';
            $result = Db::getInstance()->getRow($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

 
}   
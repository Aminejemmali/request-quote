<?php
/**
 * RequestQuote Module for PrestaShop 9.0.0
 * 
 * This module allows customers to request quotes for products instead of purchasing them directly.
 * It hides the price and add-to-cart functionality and replaces it with a quote request form.
 * 
 * @author Amine Jameli
 * @version 1.0.0
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuote extends Module
{
    /**
     * Constructor - Initialize module properties
     */
    public function __construct()
    {
        $this->name = 'requestquote';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Amine Jameli';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99'
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Request Quote');
        $this->description = $this->l('Allow customers to request quotes for products instead of purchasing directly.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? All quote requests will be lost.');
    }

    /**
     * Install the module
     * Creates database table and registers hooks
     */
    public function install()
    {
        // Check if PrestaShop version is compatible
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Create database table for quote requests
        if (!$this->createQuoteRequestsTable()) {
            return false;
        }

        // Register hooks
        $hooks = [
            'displayProductActions',           // Hook for product page actions (Classic theme)
            'displayHeader',                   // Hook for header assets
            'displayProductAdditionalInfo',    // Hook to hide price and add to cart
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        // Set default configuration
        Configuration::updateValue('REQUESTQUOTE_ENABLED', 1);
        Configuration::updateValue('REQUESTQUOTE_REQUIRE_PHONE', 0);

        return parent::install();
    }

    /**
     * Uninstall the module
     * Removes database table, hooks, and admin tab
     */
    public function uninstall()
    {
        // Remove database table
        $this->dropQuoteRequestsTable();

        // Remove configuration values
        Configuration::deleteByName('REQUESTQUOTE_ENABLED');
        Configuration::deleteByName('REQUESTQUOTE_REQUIRE_PHONE');

        return parent::uninstall();
    }

    /**
     * Drop the database table for quote requests
     */
    private function dropQuoteRequestsTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Create the database table for quote requests
     */
    private function createQuoteRequestsTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes` (
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
            KEY `id_shop` (`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }





    /**
     * Hook: displayProductActions - Modify product page to show quote button instead of add to cart
     */
    public function hookDisplayProductActions($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        // Vérification des paramètres
        if (!isset($params['product']) || !is_object($params['product'])) {
            return '';
        }

        $product = $params['product'];
        
        // Génération d'un ID unique pour le modal
        $modalId = 'requestQuoteModal_' . $product->id;

        return '<div class="request-quote-section">
                    <button type="button" class="btn btn-primary btn-lg request-quote-btn" data-toggle="modal" data-target="#' . $modalId . '">
                        <i class="icon-quote-left"></i> Request Quote
                    </button>
                    
                    <!-- Modal Quote Request -->
                    <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Request Quote for ' . htmlspecialchars($product->name) . '</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form class="request-quote-form">
                                        <input type="hidden" name="product_id" value="' . $product->id . '">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Full Name *</label>
                                                    <input type="text" class="form-control" name="client_name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email *</label>
                                                    <input type="email" class="form-control" name="email" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="tel" class="form-control" name="phone">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Product</label>
                                                    <input type="text" class="form-control" value="' . htmlspecialchars($product->name) . '" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Additional Notes</label>
                                            <textarea class="form-control" name="note" rows="3" placeholder="Any specific requirements or questions..."></textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary submit-quote-btn">Submit Quote Request</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
    }





    /**
     * Hook: displayHeader - Inject CSS and JavaScript for quote request functionality
     */
    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        // Vérification du contexte pour éviter de charger sur toutes les pages
        if (isset($this->context->controller) && 
            property_exists($this->context->controller, 'controller_type') && 
            property_exists($this->context->controller, 'php_self')) {
            
            if ($this->context->controller->controller_type === 'front' && 
                $this->context->controller->php_self === 'product') {
                
                // CSS inline pour le style du bouton et du modal
                $css = '<style>
                    .request-quote-section {
                        margin: 20px 0;
                        text-align: center;
                    }
                    .request-quote-btn {
                        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                        border: none;
                        padding: 15px 30px;
                        font-size: 18px;
                        font-weight: 600;
                        border-radius: 50px;
                        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
                        transition: all 0.3s ease;
                        min-width: 200px;
                    }
                    .request-quote-btn:hover {
                        background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
                    }
                    .request-quote-btn i {
                        margin-right: 8px;
                    }
                    .modal-content {
                        border-radius: 12px;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    }
                    .modal-header {
                        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                        color: white;
                        border-radius: 12px 12px 0 0;
                    }
                    .modal-header .close {
                        color: white;
                        opacity: 0.8;
                    }
                    .modal-header .close:hover {
                        opacity: 1;
                    }
                    .form-group {
                        margin-bottom: 20px;
                    }
                    .form-control {
                        border: 2px solid #e9ecef;
                        border-radius: 8px;
                        padding: 12px 15px;
                        transition: border-color 0.3s ease;
                    }
                    .form-control:focus {
                        border-color: #007bff;
                        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                    }
                    .btn {
                        padding: 12px 25px;
                        font-weight: 600;
                        border-radius: 8px;
                        transition: all 0.3s ease;
                    }
                    .btn:hover {
                        transform: translateY(-1px);
                    }
                </style>';

                // JavaScript inline pour la gestion du formulaire
                $js = '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Gestion de la soumission du formulaire
                        document.querySelectorAll(".submit-quote-btn").forEach(function(btn) {
                            btn.addEventListener("click", function() {
                                var modal = this.closest(".modal");
                                var form = modal.querySelector(".request-quote-form");
                                
                                if (form.checkValidity()) {
                                    // Simulation de soumission (à remplacer par AJAX plus tard)
                                    this.innerHTML = "<i class=\"icon-spinner icon-spin\"></i> Submitting...";
                                    this.disabled = true;
                                    
                                    setTimeout(function() {
                                        alert("Quote request submitted successfully! We will contact you soon.");
                                        modal.querySelector(".close").click();
                                        btn.innerHTML = "Submit Quote Request";
                                        btn.disabled = false;
                                    }, 2000);
                                } else {
                                    form.reportValidity();
                                }
                            });
                        });
                    });
                </script>';

                return $css . $js;
            }
        }

        return '';
    }

    /**
     * Hook: displayProductAdditionalInfo - Hide price and add to cart functionality
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        // CSS pour masquer les éléments de prix et d'ajout au panier
        return '<style>
            .product-add-to-cart,
            .product-variants,
            .product-customization,
            .product-prices,
            .product-quantity,
            .product-actions {
                display: none !important;
            }
        </style>';
    }

    /**
     * Get module configuration form
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $enabled = (int)Tools::getValue('REQUESTQUOTE_ENABLED');
            $requirePhone = (int)Tools::getValue('REQUESTQUOTE_REQUIRE_PHONE');

            Configuration::updateValue('REQUESTQUOTE_ENABLED', $enabled);
            Configuration::updateValue('REQUESTQUOTE_REQUIRE_PHONE', $requirePhone);

            $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Display configuration form
     */
    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable Quote Requests'),
                    'name' => 'REQUESTQUOTE_ENABLED',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        ]
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Require Phone Number'),
                    'name' => 'REQUESTQUOTE_REQUIRE_PHONE',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'phone_on',
                            'value' => true,
                            'label' => $this->l('Required')
                        ],
                        [
                            'id' => 'phone_off',
                            'value' => false,
                            'label' => $this->l('Optional')
                        ]
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        $helper->fields_value['REQUESTQUOTE_ENABLED'] = Configuration::get('REQUESTQUOTE_ENABLED');
        $helper->fields_value['REQUESTQUOTE_REQUIRE_PHONE'] = Configuration::get('REQUESTQUOTE_REQUIRE_PHONE');

        return $helper->generateForm($fields_form);
    }
} 
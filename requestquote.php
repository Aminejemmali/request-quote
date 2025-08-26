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
        $this->version = '2.1.0';
        $this->author = 'Amine Jameli';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99'
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Request Quote');
        $this->description = $this->l('Simple quote request system that hides prices and shows quote buttons.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Create database table
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
        // Remove table
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes`');
        
        // Remove configuration
        Configuration::deleteByName('REQUESTQUOTE_ENABLED');

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

        $css = '
        <style>
        /* Hide prices and add to cart */
        .product-price, .current-price, .regular-price, .discount-percentage,
        .product-add-to-cart, .add-to-cart, .btn-add-to-cart,
        .product-quantity, .product-variants, .product-customization {
            display: none !important;
        }
        
        /* Quote button styling */
        .request-quote-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 0;
        }
        .request-quote-btn:hover {
            background: #0056b3;
            color: white;
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
        }
        </style>';

        $js = '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Add modal to page
            if (!document.getElementById("quoteModal")) {
                var modal = document.createElement("div");
                modal.id = "quoteModal";
                modal.className = "quote-modal";
                modal.innerHTML = `
                    <div class="quote-modal-content">
                        <span class="quote-close">&times;</span>
                        <h3>Request Quote</h3>
                        <form class="quote-form" id="quoteForm">
                            <input type="text" name="client_name" placeholder="Your Name *" required>
                            <input type="email" name="email" placeholder="Your Email *" required>
                            <input type="tel" name="phone" placeholder="Phone Number">
                            <textarea name="message" placeholder="Message" rows="3"></textarea>
                            <input type="hidden" name="product_id" value="">
                            <button type="submit" class="quote-submit">Send Quote Request</button>
                        </form>
                    </div>
                `;
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
                var formData = new FormData(this);
                formData.append("action", "submitQuote");
                
                fetch("' . $this->context->link->getModuleLink('requestquote', 'ajax') . '", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Quote request sent successfully!");
                        modal.style.display = "none";
                        document.getElementById("quoteForm").reset();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("Error sending request. Please try again.");
                });
            };
        });
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
                        Request Quote
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
     * Get module configuration form
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $enabled = (int)Tools::getValue('REQUESTQUOTE_ENABLED');
            Configuration::updateValue('REQUESTQUOTE_ENABLED', $enabled);
            $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Display configuration form
     */
    public function displayForm()
    {
        $form = [
            'form' => [
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
                ],
                'submit' => [
                    'title' => $this->l('Save'),
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
} 
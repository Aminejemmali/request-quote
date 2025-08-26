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
        try {
            // Check if PrestaShop version is compatible
            if (Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            // Create database table for quote requests
            if (!$this->createQuoteRequestsTable()) {
                PrestaShopLogger::addLog('RequestQuote: Failed to create database table', 3);
                return false;
            }

            // Register hooks with error handling
            $hooks = [
                'displayProductActions',           // Hook for product page actions (Classic theme)
                'displayProductAdditionalInfo',    // Alternative hook for product info
                'actionFrontControllerSetMedia',   // Hook to inject CSS/JS
                'displayHeader',                   // Hook for header assets
                'displayBackOfficeHeader',         // Hook for admin assets
            ];

            foreach ($hooks as $hook) {
                $hookResult = $this->registerHook($hook);
                if ($hookResult === false) {
                    PrestaShopLogger::addLog('RequestQuote: Failed to register hook ' . $hook, 3);
                    return false;
                }
            }

            // Create admin tab
            if (!$this->createAdminTab()) {
                PrestaShopLogger::addLog('RequestQuote: Failed to create admin tab', 3);
                return false;
            }

            // Set default configuration
            Configuration::updateValue('REQUESTQUOTE_ENABLED', 1);
            Configuration::updateValue('REQUESTQUOTE_REQUIRE_PHONE', 0);

            return parent::install();

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote install error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    /**
     * Uninstall the module
     * Removes database table, hooks, and admin tab
     */
    public function uninstall()
    {
        try {
            // Remove database table
            $this->dropQuoteRequestsTable();

            // Remove admin tab
            $this->removeAdminTab();

            // Remove configuration values
            Configuration::deleteByName('REQUESTQUOTE_ENABLED');
            Configuration::deleteByName('REQUESTQUOTE_REQUIRE_PHONE');

            return parent::uninstall();

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote uninstall error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    /**
     * Create the database table for quote requests
     */
    private function createQuoteRequestsTable()
    {
        try {
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
                KEY `id_shop` (`id_shop`),
                KEY `date_add` (`date_add`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

            $result = Db::getInstance()->execute($sql);
            if ($result === false) {
                PrestaShopLogger::addLog('RequestQuote: Database table creation failed', 3);
                return false;
            }

            return true;

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote: Database table creation error: ' . $e->getMessage(), 3);
            return false;
        }
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
     * Create admin tab for managing quote requests
     */
    private function createAdminTab()
    {
        try {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = 'AdminRequestQuote';
            $tab->name = array();
            
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = 'Quote Requests';
            }
            
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentSell');
            $tab->module = $this->name;
            
            $result = $tab->add();
            if ($result === false) {
                PrestaShopLogger::addLog('RequestQuote: Failed to add admin tab', 3);
                return false;
            }

            return true;

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote: Admin tab creation error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    /**
     * Remove admin tab
     */
    private function removeAdminTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminRequestQuote');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    /**
     * Hook: displayProductActions - Modify product page to show quote button instead of add to cart
     */
    public function hookDisplayProductActions($params)
    {
        try {
            if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                return '';
            }

            // Vérification des paramètres
            if (!isset($params['product']) || !is_object($params['product'])) {
                return '';
            }

            $product = $params['product'];
            
            // Génération du token CSRF
            $csrfToken = $this->generateCSRFToken();

            // Assignation des variables Smarty
            $this->context->smarty->assign([
                'product' => $product,
                'module_dir' => $this->_path,
                'csrf_token' => $csrfToken,
                'require_phone' => Configuration::get('REQUESTQUOTE_REQUIRE_PHONE')
            ]);

            return $this->display(__FILE__, 'product-actions.tpl');

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote hook error: ' . $e->getMessage(), 3);
            return '';
        }
    }

    /**
     * Hook: displayProductAdditionalInfo - Alternative hook for product info
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        try {
            if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                return '';
            }

            // Vérification des paramètres
            if (!isset($params['product']) || !is_object($params['product'])) {
                return '';
            }

            $product = $params['product'];
            
            // Génération du token CSRF
            $csrfToken = $this->generateCSRFToken();

            // Assignation des variables Smarty
            $this->context->smarty->assign([
                'product' => $product,
                'module_dir' => $this->_path,
                'csrf_token' => $csrfToken,
                'require_phone' => Configuration::get('REQUESTQUOTE_REQUIRE_PHONE')
            ]);

            return $this->display(__FILE__, 'product-additional-info.tpl');

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote additional info hook error: ' . $e->getMessage(), 3);
            return '';
        }
    }

    /**
     * Hook: actionFrontControllerSetMedia - Inject CSS and JS files
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return;
        }

        // Only load assets on product pages
        if (isset($this->context->controller) && 
            property_exists($this->context->controller, 'controller_type') && 
            property_exists($this->context->controller, 'php_self')) {
            
            if ($this->context->controller->controller_type === 'front' && 
                $this->context->controller->php_self === 'product') {
                
                $this->context->controller->addCSS($this->_path . 'views/css/requestquote.css');
                $this->context->controller->addJS($this->_path . 'views/js/requestquote.js');
            }
        }
    }

    /**
     * Hook: displayHeader - Alternative hook for header assets
     */
    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        // Only load assets on product pages
        if (isset($this->context->controller) && 
            property_exists($this->context->controller, 'controller_type') && 
            property_exists($this->context->controller, 'php_self')) {
            
            if ($this->context->controller->controller_type === 'front' && 
                $this->context->controller->php_self === 'product') {
                
                $this->context->controller->addCSS($this->_path . 'views/css/requestquote.css');
                $this->context->controller->addJS($this->_path . 'views/js/requestquote.js');
            }
        }

        return '';
    }

    /**
     * Hook: displayBackOfficeHeader - Inject admin assets
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('controller') === 'AdminRequestQuote') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    /**
     * Generate CSRF token for form protection
     */
    private function generateCSRFToken()
    {
        try {
            if (!isset($this->context->cookie->requestquote_csrf)) {
                $token = Tools::passwdGen(32);
                $this->context->cookie->requestquote_csrf = $token;
                $this->context->cookie->write();
            }
            
            return $this->context->cookie->requestquote_csrf ?? '';

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote CSRF generation error: ' . $e->getMessage(), 3);
            return '';
        }
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token)
    {
        try {
            if (!isset($this->context->cookie->requestquote_csrf) || empty($token)) {
                return false;
            }
            
            return hash_equals($this->context->cookie->requestquote_csrf, $token);

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote CSRF validation error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    /**
     * Get module configuration form
     */
    public function getContent()
    {
        try {
            $output = '';

            if (Tools::isSubmit('submit' . $this->name)) {
                $enabled = (int)Tools::getValue('REQUESTQUOTE_ENABLED');
                $requirePhone = (int)Tools::getValue('REQUESTQUOTE_REQUIRE_PHONE');

                Configuration::updateValue('REQUESTQUOTE_ENABLED', $enabled);
                Configuration::updateValue('REQUESTQUOTE_REQUIRE_PHONE', $requirePhone);

                $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
            }

            return $output . $this->displayForm();

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote getContent error: ' . $e->getMessage(), 3);
            return $this->displayError('An error occurred while loading the configuration.');
        }
    }

    /**
     * Display configuration form
     */
    public function displayForm()
    {
        try {
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

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote displayForm error: ' . $e->getMessage(), 3);
            return $this->displayError('An error occurred while generating the form.');
        }
    }
} 
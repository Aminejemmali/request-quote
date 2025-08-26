<?php
/**
 * RequestQuote Module - Compatible PHP 8.4 FPM + PrestaShop 9.0.0
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
        $this->description = $this->l('Allow customers to request quotes for products.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        try {
            // Vérification de base
            if (Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            // Création de la table avec gestion d'erreur
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

            $result = Db::getInstance()->execute($sql);
            if ($result === false) {
                PrestaShopLogger::addLog('RequestQuote: Failed to create database table', 3);
                return false;
            }

            // Enregistrement des hooks avec gestion d'erreur
            $hooks = ['displayProductActions', 'displayHeader'];
            foreach ($hooks as $hook) {
                $hookResult = $this->registerHook($hook);
                if ($hookResult === false) {
                    PrestaShopLogger::addLog('RequestQuote: Failed to register hook ' . $hook, 3);
                    return false;
                }
            }

            // Configuration par défaut
            Configuration::updateValue('REQUESTQUOTE_ENABLED', 1);
            Configuration::updateValue('REQUESTQUOTE_REQUIRE_PHONE', 0);

            return parent::install();

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote install error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function uninstall()
    {
        try {
            // Suppression de la table
            $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes`';
            Db::getInstance()->execute($sql);

            // Suppression de la configuration
            Configuration::deleteByName('REQUESTQUOTE_ENABLED');
            Configuration::deleteByName('REQUESTQUOTE_REQUIRE_PHONE');

            return parent::uninstall();

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote uninstall error: ' . $e->getMessage(), 3);
            return false;
        }
    }

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

    public function hookDisplayHeader($params)
    {
        try {
            if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                return '';
            }

            // Vérification du contexte
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

        } catch (Exception $e) {
            PrestaShopLogger::addLog('RequestQuote header hook error: ' . $e->getMessage(), 3);
            return '';
        }
    }

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

    private function displayForm()
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
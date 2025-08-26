<?php
/**
 * RequestQuote Module - Version Simplifiée pour Test
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
        // Vérification de base
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Création de la table
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
            PRIMARY KEY (`id_quote`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        // Enregistrement des hooks
        $hooks = ['displayProductActions', 'displayHeader'];
        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        // Configuration par défaut
        Configuration::updateValue('REQUESTQUOTE_ENABLED', 1);

        return parent::install();
    }

    public function uninstall()
    {
        // Suppression de la table
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'requestquote_quotes`';
        Db::getInstance()->execute($sql);

        // Suppression de la configuration
        Configuration::deleteByName('REQUESTQUOTE_ENABLED');

        return parent::uninstall();
    }

    public function hookDisplayProductActions($params)
    {
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            return '';
        }

        return '<div class="request-quote-section">
                    <button type="button" class="btn btn-primary">Request Quote</button>
                </div>';
    }

    public function hookDisplayHeader($params)
    {
        // Hook vide pour l'instant
        return '';
    }

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

    private function displayForm()
    {
        $fields_form[0]['form'] = [
            'legend' => ['title' => $this->l('Settings')],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable Quote Requests'),
                    'name' => 'REQUESTQUOTE_ENABLED',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => true, 'label' => $this->l('Enabled')],
                        ['id' => 'active_off', 'value' => false, 'label' => $this->l('Disabled')]
                    ],
                ],
            ],
            'submit' => ['title' => $this->l('Save'), 'class' => 'btn btn-default pull-right']
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

        return $helper->generateForm($fields_form);
    }
} 
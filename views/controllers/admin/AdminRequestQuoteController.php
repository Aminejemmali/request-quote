<?php
/**
 * Simple Admin Controller for Quote Requests
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminRequestQuoteController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'requestquote_quotes';
        $this->className = 'RequestQuoteQuote';
        $this->identifier = 'id_quote';
        $this->lang = false;
        $this->deleted = false;

        parent::__construct();

        $this->meta_title = $this->l('Quote Requests');

        // Define list fields
        $this->fields_list = [
            'id_quote' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'client_name' => [
                'title' => $this->l('Client Name'),
                'align' => 'left',
            ],
            'email' => [
                'title' => $this->l('Email'),
                'align' => 'left',
            ],
            'phone' => [
                'title' => $this->l('Phone'),
                'align' => 'left',
            ],
            'product_name' => [
                'title' => $this->l('Product'),
                'align' => 'left',
                'callback' => 'getProductName',
            ],
            'message' => [
                'title' => $this->l('Message'),
                'align' => 'left',
                'maxlength' => 50,
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'right',
                'type' => 'datetime',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ]
        ];

        $this->actions = ['view', 'delete'];
    }

    public function getProductName($id_product)
    {
        if (!$id_product) {
            return '-';
        }

        $product = new Product((int)$id_product, false, $this->context->language->id);
        return $product->name ?: $this->l('Unknown Product');
    }

    public function renderList()
    {
        // Add custom SQL to join with product table for better display
        $this->_select = 'p.name as product_name';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (a.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')';

        return parent::renderList();
    }

    public function renderView()
    {
        $id_quote = (int)Tools::getValue('id_quote');
        
        if (!$id_quote) {
            $this->errors[] = $this->l('Quote not found');
            return $this->renderList();
        }

        // Get quote data
        $sql = 'SELECT q.*, p.name as product_name 
                FROM `' . _DB_PREFIX_ . 'requestquote_quotes` q
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (q.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')
                WHERE q.id_quote = ' . (int)$id_quote;
        
        $quote = Db::getInstance()->getRow($sql);
        
        if (!$quote) {
            $this->errors[] = $this->l('Quote not found');
            return $this->renderList();
        }

        $this->context->smarty->assign([
            'quote' => $quote,
            'back_url' => $this->context->link->getAdminLink('AdminRequestQuote'),
        ]);

        return $this->createTemplate('quote_view.tpl')->fetch();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deleteSelection')) {
            $selection = Tools::getValue('requestquote_quotesBox');
            if (is_array($selection) && count($selection)) {
                foreach ($selection as $id_quote) {
                    $this->deleteQuote((int)$id_quote);
                }
                $this->confirmations[] = $this->l('Selected quotes deleted successfully.');
            }
        }

        return parent::postProcess();
    }

    protected function deleteQuote($id_quote)
    {
        return Db::getInstance()->delete('requestquote_quotes', 'id_quote = ' . (int)$id_quote);
    }
} 
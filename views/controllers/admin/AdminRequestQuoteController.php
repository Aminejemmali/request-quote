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
        $this->identifier = 'id_quote';
        $this->lang = false;

        parent::__construct();

        $this->meta_title = $this->l('Quote Requests');
    }

    public function renderList()
    {
        // Get all quotes from database
        $sql = 'SELECT q.*, p.name as product_name 
                FROM `' . _DB_PREFIX_ . 'requestquote_quotes` q
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (q.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')
                ORDER BY q.date_add DESC';
        
        $quotes = Db::getInstance()->executeS($sql);

        $this->context->smarty->assign([
            'quotes' => $quotes,
            'current_url' => $_SERVER['REQUEST_URI'],
        ]);

        return $this->createTemplate('quotes_list.tpl')->fetch();
    }

    public function renderView()
    {
        $id_quote = (int)Tools::getValue('id_quote');
        
        if (!$id_quote) {
            return $this->renderList();
        }

        // Get quote data
        $sql = 'SELECT q.*, p.name as product_name 
                FROM `' . _DB_PREFIX_ . 'requestquote_quotes` q
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (q.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')
                WHERE q.id_quote = ' . (int)$id_quote;
        
        $quote = Db::getInstance()->getRow($sql);
        
        if (!$quote) {
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
        // Handle delete action
        if (Tools::isSubmit('delete') && Tools::getValue('id_quote')) {
            $id_quote = (int)Tools::getValue('id_quote');
            if (Db::getInstance()->delete('requestquote_quotes', 'id_quote = ' . $id_quote)) {
                $this->confirmations[] = $this->l('Quote deleted successfully.');
            } else {
                $this->errors[] = $this->l('Error deleting quote.');
            }
        }

        return parent::postProcess();
    }
} 
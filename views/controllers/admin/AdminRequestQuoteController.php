<?php
/**
 * Admin Controller for Managing Quote Requests
 * Provides a grid interface to view and manage all quote requests
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminRequestQuoteController extends ModuleAdminController
{
    /**
     * Initialize controller
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'requestquote_quotes';
        $this->className = 'RequestQuoteQuote';
        $this->identifier = 'id_quote';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ]
        ];

        parent::__construct();

        $this->meta_title = $this->l('Quote Requests');
        $this->page_title = $this->l('Quote Requests');

        // Set fields list for the grid
        $this->fields_list = [
            'id_quote' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'search' => false,
            ],
            'product_name' => [
                'title' => $this->l('Product'),
                'align' => 'left',
                'search' => true,
                'callback' => 'getProductName',
            ],
            'client_name' => [
                'title' => $this->l('Client Name'),
                'align' => 'left',
                'search' => true,
            ],
            'email' => [
                'title' => $this->l('Email'),
                'align' => 'left',
                'search' => true,
            ],
            'phone' => [
                'title' => $this->l('Phone'),
                'align' => 'left',
                'search' => true,
            ],
            'note' => [
                'title' => $this->l('Note'),
                'align' => 'left',
                'search' => true,
                'maxlength' => 50,
                'callback' => 'truncateNote',
            ],
            'date_add' => [
                'title' => $this->l('Date Added'),
                'align' => 'right',
                'type' => 'datetime',
                'search' => true,
            ],
        ];

        // Set default order
        $this->_orderBy = 'date_add';
        $this->_orderWay = 'DESC';

        // Add filters
        $this->_filter = true;
        $this->_default_pagination = 25;
        $this->_pagination = [25, 50, 100];
    }

    /**
     * Get product name for display
     */
    public function getProductName($productId, $tr)
    {
        $product = new Product($productId, false, $this->context->language->id);
        return $product->name ?: 'Product #' . $productId;
    }

    /**
     * Truncate note text for display
     */
    public function truncateNote($note, $tr)
    {
        if (strlen($note) > 50) {
            return Tools::substr($note, 0, 47) . '...';
        }
        return $note;
    }

    /**
     * Override getList to join with product table
     */
    public function getList($id_lang, $order_by = null, $order_way = null, $filter = 0, $nb = 0)
    {
        parent::getList($id_lang, $order_by, $order_way, $filter, $nb);

        // Join with product table to get product names
        if (isset($this->_list) && is_array($this->_list)) {
            foreach ($this->_list as &$item) {
                $item['product_name'] = $this->getProductName($item['id_product'], null);
            }
        }
    }

    /**
     * Override renderList to add custom SQL
     */
    public function renderList()
    {
        // Add custom SQL to join with product table
        $this->_select = 'q.*, p.name as product_name';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` p ON (q.id_product = p.id_product AND p.id_lang = ' . (int)$this->context->language->id . ')';
        $this->_where = 'AND q.id_shop = ' . (int)$this->context->shop->id;

        return parent::renderList();
    }

    /**
     * Override renderView to show full quote details
     */
    public function renderView()
    {
        $quote = new RequestQuoteQuote(Tools::getValue('id_quote'));
        
        if (!Validate::isLoadedObject($quote)) {
            $this->errors[] = $this->l('Quote not found.');
            return;
        }

        $product = new Product($quote->id_product, false, $this->context->language->id);
        
        $this->tpl_view_vars = [
            'quote' => $quote,
            'product' => $product,
            'shop_name' => Shop::getShop($quote->id_shop)['name'],
        ];

        return parent::renderView();
    }

    /**
     * Override processDelete to add confirmation
     */
    public function processDelete()
    {
        if (Tools::isSubmit('delete' . $this->table)) {
            $id_quote = (int)Tools::getValue($this->identifier);
            $quote = new RequestQuoteQuote($id_quote);
            
            if (Validate::isLoadedObject($quote)) {
                if ($quote->delete()) {
                    $this->confirmations[] = $this->l('Quote request deleted successfully.');
                } else {
                    $this->errors[] = $this->l('Failed to delete quote request.');
                }
            } else {
                $this->errors[] = $this->l('Quote request not found.');
            }
        }
    }

    /**
     * Override processBulkDelete
     */
    public function processBulkDelete()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $deleted = 0;
            foreach ($this->boxes as $id_quote) {
                $quote = new RequestQuoteQuote((int)$id_quote);
                if (Validate::isLoadedObject($quote) && $quote->delete()) {
                    $deleted++;
                }
            }
            
            if ($deleted > 0) {
                $this->confirmations[] = sprintf($this->l('%d quote request(s) deleted successfully.'), $deleted);
            }
        }
    }

    /**
     * Add custom actions to the grid
     */
    public function displayViewLink($token, $id, $name = null)
    {
        $quote = new RequestQuoteQuote($id);
        if (Validate::isLoadedObject($quote)) {
            $href = self::$currentIndex . '&' . $this->identifier . '=' . $id . '&view' . $this->table . '&token=' . ($token ? $token : $this->token);
            return '<a href="' . $href . '" class="btn btn-default" title="' . $this->l('View') . '"><i class="icon-eye"></i> ' . $this->l('View') . '</a>';
        }
        return '';
    }

    /**
     * Override getFieldsValue to handle custom fields
     */
    public function getFieldsValue($obj)
    {
        $fields_value = parent::getFieldsValue($obj);
        
        if (isset($obj->id_quote) && $obj->id_quote) {
            $fields_value['product_name'] = $this->getProductName($obj->id_product, null);
        }
        
        return $fields_value;
    }
} 
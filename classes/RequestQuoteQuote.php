<?php
/**
 * Quote Request Model Class
 * Extends ObjectModel for database operations
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuoteQuote extends ObjectModel
{
    /**
     * Quote ID
     * @var int
     */
    public $id_quote;

    /**
     * Product ID
     * @var int
     */
    public $id_product;

    /**
     * Shop ID
     * @var int
     */
    public $id_shop;

    /**
     * Client name
     * @var string
     */
    public $client_name;

    /**
     * Client email
     * @var string
     */
    public $email;

    /**
     * Client phone (optional)
     * @var string
     */
    public $phone;

    /**
     * Additional notes (optional)
     * @var string
     */
    public $note;

    /**
     * Date added
     * @var string
     */
    public $date_add;

    /**
     * Date updated
     * @var string
     */
    public $date_upd;

    /**
     * Table name
     * @var string
     */
    public static $definition = [
        'table' => 'requestquote_quotes',
        'primary' => 'id_quote',
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'client_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255,
            ],
            'email' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEmail',
                'required' => true,
                'size' => 255,
            ],
            'phone' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isPhoneNumber',
                'required' => false,
                'size' => 50,
            ],
            'note' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    /**
     * Constructor
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    /**
     * Add a new quote request
     * @param bool $auto_date
     * @param bool $null_values
     * @return bool
     */
    public function add($auto_date = true, $null_values = false)
    {
        if ($auto_date && property_exists($this, 'date_add')) {
            $this->date_add = date('Y-m-d H:i:s');
        }
        if ($auto_date && property_exists($this, 'date_upd')) {
            $this->date_upd = date('Y-m-d H:i:s');
        }

        return parent::add($auto_date, $null_values);
    }

    /**
     * Update quote request
     * @param bool $null_values
     * @return bool
     */
    public function update($null_values = false)
    {
        if (property_exists($this, 'date_upd')) {
            $this->date_upd = date('Y-m-d H:i:s');
        }

        return parent::update($null_values);
    }

    /**
     * Get quote requests by product ID
     * @param int $id_product
     * @param int $id_shop
     * @return array
     */
    public static function getByProduct($id_product, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'requestquote_quotes` 
                WHERE `id_product` = ' . (int)$id_product . ' 
                AND `id_shop` = ' . (int)$id_shop . ' 
                ORDER BY `date_add` DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get quote requests by email
     * @param string $email
     * @param int $id_shop
     * @return array
     */
    public static function getByEmail($email, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'requestquote_quotes` 
                WHERE `email` = "' . pSQL($email) . '" 
                AND `id_shop` = ' . (int)$id_shop . ' 
                ORDER BY `date_add` DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get total quote requests count
     * @param int $id_shop
     * @return int
     */
    public static function getTotalCount($id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'requestquote_quotes` 
                WHERE `id_shop` = ' . (int)$id_shop;

        return (int)Db::getInstance()->getValue($sql);
    }

    /**
     * Get recent quote requests
     * @param int $limit
     * @param int $id_shop
     * @return array
     */
    public static function getRecent($limit = 10, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'requestquote_quotes` 
                WHERE `id_shop` = ' . (int)$id_shop . ' 
                ORDER BY `date_add` DESC 
                LIMIT ' . (int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Check if email has already requested quote for product
     * @param string $email
     * @param int $id_product
     * @param int $id_shop
     * @return bool
     */
    public static function emailExistsForProduct($email, $id_product, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'requestquote_quotes` 
                WHERE `email` = "' . pSQL($email) . '" 
                AND `id_product` = ' . (int)$id_product . ' 
                AND `id_shop` = ' . (int)$id_shop;

        return (bool)Db::getInstance()->getValue($sql);
    }

    /**
     * Get product information for this quote
     * @return Product|false
     */
    public function getProduct()
    {
        if ($this->id_product) {
            return new Product($this->id_product, false, Context::getContext()->language->id);
        }
        return false;
    }

    /**
     * Get shop information for this quote
     * @return array|false
     */
    public function getShop()
    {
        if ($this->id_shop) {
            return Shop::getShop($this->id_shop);
        }
        return false;
    }

    /**
     * Format date for display
     * @param string $field
     * @return string
     */
    public function getFormattedDate($field = 'date_add')
    {
        if (isset($this->$field) && $this->$field) {
            return Tools::displayDate($this->$field);
        }
        return '';
    }
} 
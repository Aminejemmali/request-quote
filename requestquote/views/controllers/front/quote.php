<?php
/**
 * Front Controller for Quote Requests
 * Handles AJAX submissions of quote request forms
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuoteQuoteModuleFrontController extends ModuleFrontController
{
    /**
     * Initialize controller
     */
    public function initContent()
    {
        parent::initContent();
        
        // Only allow AJAX requests
        if (!$this->isAjax()) {
            $this->ajax = false;
            $this->errors[] = $this->module->l('Invalid request method.');
            return;
        }

        // Check if module is enabled
        if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
            $this->ajax = false;
            $this->errors[] = $this->module->l('Quote requests are currently disabled.');
            return;
        }

        // Handle the request
        $this->processQuoteRequest();
    }

    /**
     * Process the quote request submission
     */
    private function processQuoteRequest()
    {
        try {
            // Validate CSRF token
            $csrfToken = Tools::getValue('csrf_token');
            if (!$this->module->validateCSRFToken($csrfToken)) {
                throw new Exception($this->module->l('Invalid security token. Please refresh the page and try again.'));
            }

            // Validate and sanitize input data
            $data = $this->validateAndSanitizeInput();

            // Save the quote request
            $quoteId = $this->saveQuoteRequest($data);

            // Return success response
            $this->ajax = true;
            $this->jsonResponse([
                'success' => true,
                'message' => $this->module->l('Your quote request has been submitted successfully. We will contact you soon.'),
                'quote_id' => $quoteId
            ]);

        } catch (Exception $e) {
            $this->ajax = false;
            $this->errors[] = $e->getMessage();
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate and sanitize input data
     */
    private function validateAndSanitizeInput()
    {
        $data = [];

        // Client Name (required)
        $clientName = Tools::getValue('client_name');
        if (empty($clientName) || strlen(trim($clientName)) < 2) {
            throw new Exception($this->module->l('Please provide a valid client name (minimum 2 characters).'));
        }
        $data['client_name'] = Tools::safeOutput(trim($clientName));

        // Email (required, valid email)
        $email = Tools::getValue('email');
        if (empty($email) || !Validate::isEmail($email)) {
            throw new Exception($this->module->l('Please provide a valid email address.'));
        }
        $data['email'] = Tools::safeOutput(trim($email));

        // Phone (optional, but validate if provided)
        $phone = Tools::getValue('phone');
        if (!empty($phone)) {
            if (strlen(trim($phone)) < 10) {
                throw new Exception($this->module->l('Phone number must be at least 10 characters long.'));
            }
            $data['phone'] = Tools::safeOutput(trim($phone));
        } else {
            $data['phone'] = null;
        }

        // Product ID (required, must be valid product)
        $productId = (int)Tools::getValue('product_id');
        if (!$productId || !Product::existsInDatabase($productId, 'product')) {
            throw new Exception($this->module->l('Invalid product selected.'));
        }
        $data['product_id'] = $productId;

        // Note (optional)
        $note = Tools::getValue('note');
        if (!empty($note)) {
            if (strlen(trim($note)) > 1000) {
                throw new Exception($this->module->l('Note cannot exceed 1000 characters.'));
            }
            $data['note'] = Tools::safeOutput(trim($note));
        } else {
            $data['note'] = null;
        }

        return $data;
    }

    /**
     * Save the quote request to database
     */
    private function saveQuoteRequest($data)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'requestquote_quotes` 
                (`id_product`, `id_shop`, `client_name`, `email`, `phone`, `note`, `date_add`, `date_upd`) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())';

        $params = [
            $data['product_id'],
            (int)$this->context->shop->id,
            $data['client_name'],
            $data['email'],
            $data['phone'],
            $data['note']
        ];

        if (!Db::getInstance()->execute($sql, $params)) {
            throw new Exception($this->module->l('Failed to save quote request. Please try again.'));
        }

        return Db::getInstance()->Insert_ID();
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 
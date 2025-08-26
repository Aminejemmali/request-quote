<?php
/**
 * Front Controller for Quote Requests
 * Handles AJAX form submissions and quote processing
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuoteQuoteModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;

    /**
     * Initialize controller
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Process page content
     */
    public function initContent()
    {
        parent::initContent();
        
        // Handle AJAX requests
        if (Tools::isSubmit('ajax')) {
            $this->processAjax();
            exit;
        }
        
        // For non-AJAX requests, redirect to home
        Tools::redirect('index.php');
    }

    /**
     * Process AJAX quote request
     */
    public function processAjax()
    {
        $response = ['success' => false, 'message' => ''];

        try {
            // Check if module is enabled
            if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                throw new Exception($this->module->l('Quote requests are currently disabled.', 'quote'));
            }

            // Validate CSRF token
            $token = Tools::getValue('csrf_token');
            if (!$token || $token !== Tools::getToken(false)) {
                throw new Exception($this->module->l('Invalid security token.', 'quote'));
            }

            // Get form data
            $productId = (int)Tools::getValue('product_id');
            $clientName = trim(Tools::getValue('client_name'));
            $email = trim(Tools::getValue('email'));
            $phone = trim(Tools::getValue('phone'));
            $note = trim(Tools::getValue('note'));

            // Validate required fields
            if (!$productId || !$clientName || !$email) {
                throw new Exception($this->module->l('Please fill in all required fields.', 'quote'));
            }

            // Validate email
            if (!Validate::isEmail($email)) {
                throw new Exception($this->module->l('Please enter a valid email address.', 'quote'));
            }

            // Validate phone if required
            if (Configuration::get('REQUESTQUOTE_REQUIRE_PHONE') && empty($phone)) {
                throw new Exception($this->module->l('Phone number is required.', 'quote'));
            }

            // Check if product exists
            $product = new Product($productId, false, $this->context->language->id);
            if (!Validate::isLoadedObject($product)) {
                throw new Exception($this->module->l('Product not found.', 'quote'));
            }

            // Create quote request
            $quote = new RequestQuoteQuote();
            $quote->id_product = $productId;
            $quote->id_shop = $this->context->shop->id;
            $quote->client_name = pSQL($clientName);
            $quote->email = pSQL($email);
            $quote->phone = $phone ? pSQL($phone) : null;
            $quote->note = $note ? pSQL($note) : null;

            if ($quote->add()) {
                // Send email notification (optional)
                $this->sendEmailNotification($quote, $product);
                
                $response['success'] = true;
                $response['message'] = $this->module->l('Your quote request has been submitted successfully! We will contact you soon.', 'quote');
            } else {
                throw new Exception($this->module->l('Failed to save quote request. Please try again.', 'quote'));
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * Send email notification for new quote request
     */
    private function sendEmailNotification($quote, $product)
    {
        try {
            $adminEmail = Configuration::get('PS_SHOP_EMAIL');
            
            if (!$adminEmail) {
                return false;
            }

            $templateVars = [
                '{client_name}' => $quote->client_name,
                '{email}' => $quote->email,
                '{phone}' => $quote->phone ?: 'N/A',
                '{product_name}' => $product->name,
                '{product_id}' => $quote->id_product,
                '{note}' => $quote->note ?: 'No additional notes',
                '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                '{date}' => date('Y-m-d H:i:s'),
            ];

            $subject = sprintf('[%s] New Quote Request for %s', 
                Configuration::get('PS_SHOP_NAME'), 
                $product->name
            );

            $message = "New quote request received:\n\n";
            $message .= "Client: {client_name}\n";
            $message .= "Email: {email}\n";
            $message .= "Phone: {phone}\n";
            $message .= "Product: {product_name} (ID: {product_id})\n";
            $message .= "Note: {note}\n";
            $message .= "Date: {date}\n";

            foreach ($templateVars as $key => $value) {
                $message = str_replace($key, $value, $message);
            }

            return Mail::Send(
                $this->context->language->id,
                'contact',
                $subject,
                ['message' => $message],
                $adminEmail,
                null,
                $quote->email,
                $quote->client_name
            );

        } catch (Exception $e) {
            // Log error but don't fail the quote submission
            PrestaShopLogger::addLog('RequestQuote email error: ' . $e->getMessage(), 3);
            return false;
        }
    }
} 
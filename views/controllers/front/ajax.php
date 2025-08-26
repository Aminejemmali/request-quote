<?php
/**
 * Simple AJAX Controller for Quote Requests
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestQuoteAjaxModuleFrontController extends ModuleFrontController
{
    public function displayAjax()
    {
        $response = ['success' => false, 'message' => ''];

        try {
            if (!Configuration::get('REQUESTQUOTE_ENABLED')) {
                throw new Exception('Quote requests are disabled');
            }

            if (Tools::getValue('action') !== 'submitQuote') {
                throw new Exception('Invalid action');
            }

            // Get form data
            $productId = (int)Tools::getValue('product_id');
            $clientName = trim(Tools::getValue('client_name'));
            $email = trim(Tools::getValue('email'));
            $phone = trim(Tools::getValue('phone'));
            $message = trim(Tools::getValue('message'));

            // Validate
            if (!$productId || !$clientName || !$email) {
                throw new Exception('Please fill in all required fields');
            }

            if (!Validate::isEmail($email)) {
                throw new Exception('Invalid email address');
            }

            // Save to database
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'requestquote_quotes` 
                    (id_product, client_name, email, phone, message, date_add) 
                    VALUES (' . (int)$productId . ', "' . pSQL($clientName) . '", "' . pSQL($email) . '", 
                    "' . pSQL($phone) . '", "' . pSQL($message) . '", NOW())';

            if (Db::getInstance()->execute($sql)) {
                $response['success'] = true;
                $response['message'] = 'Quote request sent successfully!';
            } else {
                throw new Exception('Failed to save quote request');
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
} 
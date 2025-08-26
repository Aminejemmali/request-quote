<?php
/**
 * Simple Admin Controller for Quote Requests in Sell Menu
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminRequestQuoteController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initContent()
    {
        // Redirect to module configuration where quotes are managed
        $link = $this->context->link->getAdminLink('AdminModules') . '&configure=requestquote';
        Tools::redirect($link);
    }
} 
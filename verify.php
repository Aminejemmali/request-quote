<?php
/**
 * RequestQuote Module Verification Script
 * Run this script to verify all module components are properly implemented
 */

// Prevent direct access
if (!defined('_PS_VERSION_')) {
    echo "This script can only be run from within PrestaShop.\n";
    exit;
}

class RequestQuoteVerifier
{
    private $errors = [];
    private $warnings = [];
    private $success = [];

    public function __construct()
    {
        echo "=== RequestQuote Module Verification ===\n\n";
    }

    public function runFullVerification()
    {
        $this->checkFiles();
        $this->checkDatabase();
        $this->checkConfiguration();
        $this->checkHooks();
        $this->checkAdminTab();
        $this->displayResults();
    }

    private function checkFiles()
    {
        echo "Checking required files...\n";
        
        $requiredFiles = [
            'requestquote.php',
            'config.xml',
            'index.php',
            'classes/RequestQuoteQuote.php',
            'classes/index.php',
            'views/controllers/admin/AdminRequestQuoteController.php',
            'views/controllers/front/quote.php',
            'views/controllers/front/index.php',
            'views/templates/admin/requestquote_quotes/view.tpl',
            'views/templates/hook/product-actions.tpl',
            'views/templates/hook/product-additional-info.tpl',
            'views/css/requestquote.css',
            'views/css/admin.css',
            'views/css/index.php',
            'views/js/requestquote.js',
            'views/js/admin.js',
            'views/js/index.php',
        ];

        $moduleDir = _PS_MODULE_DIR_ . 'requestquote/';
        
        foreach ($requiredFiles as $file) {
            $fullPath = $moduleDir . $file;
            if (file_exists($fullPath)) {
                $this->success[] = "✓ File exists: $file";
            } else {
                $this->errors[] = "✗ Missing file: $file";
            }
        }
    }

    private function checkDatabase()
    {
        echo "Checking database table...\n";
        
        $tableName = _DB_PREFIX_ . 'requestquote_quotes';
        
        $sql = 'SHOW TABLES LIKE "' . pSQL($tableName) . '"';
        $result = Db::getInstance()->executeS($sql);
        
        if ($result) {
            $this->success[] = "✓ Database table exists: $tableName";
            
            // Check table structure
            $sql = 'DESCRIBE ' . $tableName;
            $columns = Db::getInstance()->executeS($sql);
            
            $requiredColumns = [
                'id_quote', 'id_product', 'id_shop', 'client_name', 
                'email', 'phone', 'note', 'date_add', 'date_upd'
            ];
            
            $existingColumns = array_column($columns, 'Field');
            
            foreach ($requiredColumns as $column) {
                if (in_array($column, $existingColumns)) {
                    $this->success[] = "✓ Column exists: $column";
                } else {
                    $this->errors[] = "✗ Missing column: $column";
                }
            }
        } else {
            $this->errors[] = "✗ Database table missing: $tableName";
        }
    }

    private function checkConfiguration()
    {
        echo "Checking module configuration...\n";
        
        $configs = [
            'REQUESTQUOTE_ENABLED' => 'Module enabled status',
            'REQUESTQUOTE_REQUIRE_PHONE' => 'Phone requirement setting'
        ];
        
        foreach ($configs as $key => $description) {
            $value = Configuration::get($key);
            if ($value !== false) {
                $this->success[] = "✓ Configuration exists: $key ($description)";
            } else {
                $this->warnings[] = "⚠ Configuration missing: $key ($description)";
            }
        }
    }

    private function checkHooks()
    {
        echo "Checking hook registration...\n";
        
        $module = Module::getInstanceByName('requestquote');
        if (!$module) {
            $this->errors[] = "✗ Module not found or not installed";
            return;
        }
        
        $requiredHooks = [
            'displayProductActions',
            'displayHeader',
            'displayProductAdditionalInfo',
            'displayProductPriceBlock',
            'displayAfterProductThumbs',
            'displayProductButtons',
            'displayProductListFunctionalButtons',
            'displayRightColumnProduct',
            'displayLeftColumnProduct',
            'actionFrontControllerSetMedia',
        ];
        
        foreach ($requiredHooks as $hookName) {
            $hookId = Hook::getIdByName($hookName);
            if ($hookId) {
                $isRegistered = Hook::isModuleRegisteredOnHook($module, $hookId, Context::getContext()->shop->id);
                if ($isRegistered) {
                    $this->success[] = "✓ Hook registered: $hookName";
                } else {
                    $this->warnings[] = "⚠ Hook not registered: $hookName";
                }
            } else {
                $this->warnings[] = "⚠ Hook doesn't exist: $hookName";
            }
        }
    }

    private function checkAdminTab()
    {
        echo "Checking admin tab...\n";
        
        $tabId = Tab::getIdFromClassName('AdminRequestQuote');
        if ($tabId) {
            $tab = new Tab($tabId);
            if ($tab->active) {
                $this->success[] = "✓ Admin tab exists and is active";
            } else {
                $this->warnings[] = "⚠ Admin tab exists but is inactive";
            }
        } else {
            $this->errors[] = "✗ Admin tab not found";
        }
    }

    private function displayResults()
    {
        echo "\n=== VERIFICATION RESULTS ===\n\n";
        
        if (!empty($this->success)) {
            echo "SUCCESS (" . count($this->success) . " items):\n";
            foreach ($this->success as $item) {
                echo "  $item\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "WARNINGS (" . count($this->warnings) . " items):\n";
            foreach ($this->warnings as $item) {
                echo "  $item\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "ERRORS (" . count($this->errors) . " items):\n";
            foreach ($this->errors as $item) {
                echo "  $item\n";
            }
            echo "\n";
        }
        
        // Overall status
        if (empty($this->errors)) {
            if (empty($this->warnings)) {
                echo "🎉 MODULE IS FULLY COMPLETE AND READY TO USE!\n";
            } else {
                echo "✅ MODULE IS FUNCTIONAL (with minor warnings)\n";
            }
        } else {
            echo "❌ MODULE HAS CRITICAL ISSUES THAT NEED TO BE FIXED\n";
        }
        
        echo "\n=== NEXT STEPS ===\n";
        echo "1. Install the module via PrestaShop admin\n";
        echo "2. Configure the module settings\n";
        echo "3. Test on a product page\n";
        echo "4. Check admin panel for quote requests\n";
        echo "5. Verify email notifications work\n";
    }
}

// Run verification if called directly
if (php_sapi_name() === 'cli') {
    $verifier = new RequestQuoteVerifier();
    $verifier->runFullVerification();
} 
<?php
/**
 * Hook Method Verification Script
 * Verifies that all registered hooks have corresponding methods
 */

// Prevent direct access
if (!defined('_PS_VERSION_')) {
    echo "This script should be run from PrestaShop context.\n";
    exit;
}

class HookVerifier
{
    public function verifyHooks()
    {
        echo "=== Hook Method Verification ===\n\n";
        
        // Get the module
        $module = Module::getInstanceByName('requestquote');
        if (!$module) {
            echo "❌ Module 'requestquote' not found!\n";
            return false;
        }
        
        // Registered hooks
        $registeredHooks = [
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
        
        $allGood = true;
        
        foreach ($registeredHooks as $hookName) {
            $methodName = 'hook' . $hookName;
            
            if (method_exists($module, $methodName)) {
                echo "✅ $hookName -> $methodName() exists\n";
            } else {
                echo "❌ $hookName -> $methodName() MISSING!\n";
                $allGood = false;
            }
        }
        
        echo "\n";
        if ($allGood) {
            echo "🎉 All hooks have corresponding methods!\n";
        } else {
            echo "⚠️ Some hooks are missing methods - this will cause reset issues!\n";
        }
        
        return $allGood;
    }
}

// Run verification if called directly
if (php_sapi_name() === 'cli') {
    $verifier = new HookVerifier();
    $verifier->verifyHooks();
} 
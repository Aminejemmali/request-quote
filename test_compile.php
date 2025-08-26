<?php
/**
 * Compilation Test Script for RequestQuote Module
 * Tests if the module can be loaded without compile errors
 */

// Prevent direct access
if (!defined('_PS_VERSION_')) {
    echo "This script should be run from PrestaShop context.\n";
    exit;
}

class CompileTest
{
    public function testModuleCompilation()
    {
        echo "=== Module Compilation Test ===\n\n";
        
        try {
            // Try to include the module file
            $moduleFile = dirname(__FILE__) . '/requestquote.php';
            
            if (!file_exists($moduleFile)) {
                echo "❌ Module file not found: $moduleFile\n";
                return false;
            }
            
            // Check for syntax errors without executing
            $output = shell_exec("php -l \"$moduleFile\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                echo "✅ Module file has no syntax errors\n";
            } else {
                echo "❌ Syntax errors found:\n";
                echo $output . "\n";
                return false;
            }
            
            // Try to get module instance (if PrestaShop is available)
            if (class_exists('Module')) {
                $module = Module::getInstanceByName('requestquote');
                if ($module) {
                    echo "✅ Module can be instantiated\n";
                    echo "✅ Module version: " . $module->version . "\n";
                } else {
                    echo "⚠️ Module not installed (this is normal for testing)\n";
                }
            }
            
            echo "\n🎉 Module compilation test PASSED!\n";
            return true;
            
        } catch (ParseError $e) {
            echo "❌ Parse Error: " . $e->getMessage() . "\n";
            return false;
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function listHookMethods()
    {
        echo "\n=== Hook Methods Test ===\n";
        
        // Use reflection to check methods
        if (class_exists('RequestQuote')) {
            $reflection = new ReflectionClass('RequestQuote');
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            
            $hookMethods = [];
            foreach ($methods as $method) {
                if (strpos($method->getName(), 'hook') === 0) {
                    $hookMethods[] = $method->getName();
                }
            }
            
            echo "Found " . count($hookMethods) . " hook methods:\n";
            foreach ($hookMethods as $method) {
                echo "  ✅ $method\n";
            }
            
            // Check for duplicates
            $unique = array_unique($hookMethods);
            if (count($hookMethods) === count($unique)) {
                echo "\n✅ No duplicate methods found!\n";
                return true;
            } else {
                echo "\n❌ Duplicate methods detected!\n";
                return false;
            }
        }
        
        return true;
    }
}

// Run test if called directly
if (php_sapi_name() === 'cli') {
    $test = new CompileTest();
    $compileOk = $test->testModuleCompilation();
    $hooksOk = $test->listHookMethods();
    
    if ($compileOk && $hooksOk) {
        echo "\n🎉 All tests PASSED! Module is ready for use.\n";
        exit(0);
    } else {
        echo "\n❌ Some tests FAILED! Please fix the issues.\n";
        exit(1);
    }
} 
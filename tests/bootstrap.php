<?php
/**
 * PHPUnit bootstrap for hypePrototyper plugin tests.
 *
 * Layout assumption: plugin is installed at {elgg_root}/mod/hypeprototyper/
 *   tests/ -> mod/hypeprototyper/ -> mod/ -> elgg_root/
 */

$elggRoot = dirname(dirname(dirname(__DIR__)));

if (file_exists($elggRoot . '/vendor/autoload.php')) {
    require_once $elggRoot . '/vendor/autoload.php';
}

// Load Elgg test classes (UnitTestCase, IntegrationTestCase, etc.)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
if (is_dir($testClassesDir)) {
    spl_autoload_register(function ($class) use ($testClassesDir) {
        $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

// Plugin autoloader — registers hypeJunction\Prototyper\ PSR-0-style classes
$pluginRoot = dirname(__DIR__);
if (file_exists($pluginRoot . '/vendor/autoload.php')) {
    require_once $pluginRoot . '/vendor/autoload.php';
} elseif (file_exists($pluginRoot . '/autoloader.php')) {
    require_once $pluginRoot . '/autoloader.php';
}

// Fallback PSR-0 autoloader for classes/ directory (in case plugin isn't
// activated in test DB and Elgg's class loader hasn't picked it up yet).
spl_autoload_register(function ($class) use ($pluginRoot) {
    if (strpos($class, 'hypeJunction\\Prototyper\\') !== 0) {
        return;
    }
    $file = $pluginRoot . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

if (class_exists(\Elgg\Application::class)) {
    \Elgg\Application::loadCore();
}

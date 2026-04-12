<?php

$plugin_root = __DIR__;
if (file_exists("{$plugin_root}/vendor/autoload.php")) {
	require_once "{$plugin_root}/vendor/autoload.php";
}

/**
 * Plugin DI Container
 * @return \hypeJunction\Prototyper\Plugin
 */
function hypePrototyper() {
	return \hypeJunction\Prototyper\Plugin::factory();
}

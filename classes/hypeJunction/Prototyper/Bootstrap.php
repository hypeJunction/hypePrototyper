<?php

namespace hypeJunction\Prototyper;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		require_once dirname(dirname(dirname(__DIR__))) . '/autoloader.php';
		hypePrototyper()->boot();
	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// cropper.min.js is a UMD jQuery plugin — load as external files, not ESM
		elgg_register_external_file('js', 'jquery.cropper', '/mod/hypePrototyper/vendors/jquery.cropper/cropper.min.js');
		elgg_register_external_file('css', 'jquery.cropper', elgg_get_simplecache_url('jquery.cropper.css'));
	}
}

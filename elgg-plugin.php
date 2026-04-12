<?php

return [
	'bootstrap' => \hypeJunction\Prototyper\Bootstrap::class,
	'views' => [
		'default' => [
			'jquery.cropper.css' => __DIR__ . '/vendors/jquery.cropper/cropper.min.css',
			'jquery.cropper.js' => __DIR__ . '/vendors/jquery.cropper/cropper.min.js',
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'css/framework/prototyper/stylesheet' => [],
		],
		'admin.css' => [
			'css/framework/prototyper/stylesheet' => [],
		],
		'prototyper/input/before' => [
			'prototyper/elements/js' => [],
		],
		'input/file' => [
			'prototyper/ui/cropper' => [],
		],
	],
];

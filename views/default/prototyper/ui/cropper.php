<?php

$entity = elgg_extract('entity', $vars);
$name = elgg_extract('name', $vars);
$icon_sizes = elgg_extract('icon_sizes', $vars);

if (empty($icon_sizes)) {
	return;
}

elgg_load_external_file('css', 'jquery.cropper');
elgg_load_external_file('js', 'jquery.cropper');

elgg_import_esm('framework/prototyper_cropper');

$ratios = [];
foreach ($icon_sizes as $icon_size) {
	$ratios[] = (int) $icon_size['w'] / (int) $icon_size['h'];
}

$ratios = array_unique($ratios);
foreach ($ratios as $ratio) {
	$mod = elgg_format_element('p', ['class' => 'elgg-text-help'], elgg_echo('prototyper:ui:cropper_instructions'));
	foreach (['x1', 'y1', 'x2', 'y2'] as $coord) {
		$mod .= elgg_view('input/hidden', [
			'name' => "image_upload_crop_coords[{$name}][{$ratio}][{$coord}]",
			'value' => (int) $entity->{"_coord_{$ratio}_{$coord}"},
			"data-$coord" => true,
			'data-ratio' => $ratio,
		]);
	}

	echo elgg_format_element('div', [
		'class' => 'prototyper-image-upload-cropper',
		'data-ratio' => $ratio,
	], $mod);
}



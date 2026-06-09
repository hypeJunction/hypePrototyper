<?php

$field = elgg_extract('field', $vars);
$entity = elgg_extract('entity', $vars);

if (!$field instanceof hypeJunction\Prototyper\Elements\CategoryField) {
	return;
}

$name = $field->getShortname();

if (!$entity || !$name) {
	return;
}

elgg_import_esm('framework/prototyper');

$label = $field->getLabel();
$view = $field->getOutputView();

$relationships = $field->getValues($entity);
if (!count($relationships)) {
	return;
}

if ($relationships) {
	foreach ($relationships as $guid) {
		$entity = $guid ? get_entity((int) $guid) : null;
		if ($entity) {
			$entities[] = $entity;
		}
	}
}

if (empty($entities)) {
	return;
}

elgg_push_context('widgets');
$vars['full_view'] = false;
$output = elgg_view_entity_list($entities, $vars);
elgg_pop_context();

if (!$output) {
	return;
}

?>
<div class="prototyper-output-category">
	<label class="prototyper-label"><?= $label ?></label>
	<div class="elgg-output <?= $class ?>"><?= $output ?></div>
</div>
<?php

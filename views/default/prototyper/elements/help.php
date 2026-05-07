<?php

/**
 * Help text
 * @uses $vars['value'] Help text
 */

$value = elgg_extract('value', $vars);

if (empty($value)) {
	return;
}

echo elgg_format_element('div', [
	'class' => 'elgg-text-help',
], $value);

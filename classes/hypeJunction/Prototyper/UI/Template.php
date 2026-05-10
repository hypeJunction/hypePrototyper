<?php

namespace hypeJunction\Prototyper\UI;

/**
 * UI template helper.
 */
class Template {

	protected $data_type;

	protected $input_type;
	
	protected $sections = [
		'required' => true,
		'adminonly' => true,
		'access' => true,
		'multiple' => true,
		'label' => true,
		'help' => true,
		'optionsvalues' => false,
		'relationship' => false,
		'validation' => true,
		'value' => true,
	];

	/**
	 * Constructor
	 *
	 * @param string $data_type  Field data type
	 * @param string $input_type Input rendering type
	 * @param array  $params     Field config (may include ui_sections overrides)
	 */
	public function __construct($data_type = 'metadata', $input_type = 'text', $params = []) {
		$this->data_type = $data_type;
		$this->input_type = $input_type;
		$sections = (array) elgg_extract('ui_sections', $params, []);
		foreach ($this->sections as $name => $default) {
			$show = elgg_extract($name, $sections, $default);
			$this->$name = ($show) ? 'visible' : 'hidden';
		}
	}
}

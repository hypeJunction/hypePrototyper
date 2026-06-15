<?php

namespace hypeJunction\Prototyper;

/**
 * Config
 */
class Config {

	/**
	 * Input type definitions
	 * @var array
	 */
	private $types = [];

	/**
	 * Validation rule definitions
	 * @var array
	 */
	private $validation_rules = [];

	/**
	 * {@inheritdoc}
	 */
	public function getDefaults() {
		return [
			'default_language' => 'en',
		];
	}

	/**
	 * Returns a config value by name, falling back to a default.
	 *
	 * UI::buildPrototypeFromInput() calls $this->config->get('default_language',
	 * 'en'); the method was missing, which fataled the prototyper save flow.
	 *
	 * @param string $name    Config key
	 * @param mixed  $default Value to return when the key is not set
	 * @return mixed
	 */
	public function get($name, $default = null) {
		$defaults = $this->getDefaults();

		return array_key_exists($name, $defaults) ? $defaults[$name] : $default;
	}

	/**
	 * Define an input type
	 *
	 * @param string $type      Input type
	 * @param string $classname Class name
	 * @param array  $options   Default options
	 * @return void
	 */
	public function registerType($type, $classname, $options = []) {

		if (!class_exists($classname) || !is_callable([$classname, 'getDataType'])) {
			return;
		}

		$data_type = call_user_func([$classname, 'getDataType']);

		$options = (array) $options;
		$options['type'] = $type;
		$options['class_name'] = $classname;
		$options['data_type'] = $data_type;
		
		$this->types[$data_type][$type] = $options;
	}

	/**
	 * Returns a handler classname
	 *
	 * @param string $data_type Registered data type
	 * @param string $type      Registered input type
	 * @return boolean|array
	 */
	public function getType($data_type = 'metadata', $type = 'text') {
		// Some callers (legacy plugin code on PHP 8.x strict) pass array values
		// where strings are expected; isset() throws on array offsets.
		if (!is_scalar($data_type) || !is_scalar($type)) {
			return false;
		}

		if (isset($this->types[$data_type][$type])) {
			return $this->types[$data_type][$type];
		}

		return false;
	}

	/**
	 * Returns all registered types
	 * @return array
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * Registers a new validation rule for UI
	 *
	 * @param string $rule    Rule name
	 * @param mixed  $options Available options
	 * @return void
	 */
	public function registerValidationRule($rule, $options = '') {
		$this->validation_rules[$rule] = $options;
	}

	/**
	 * Returns validation rules
	 * @return array
	 */
	public function getValidationRules() {
		return $this->validation_rules;
	}
}

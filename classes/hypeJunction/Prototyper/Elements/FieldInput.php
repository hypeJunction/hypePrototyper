<?php

namespace hypeJunction\Prototyper\Elements;

/**
 * Field input rendering contract.
 */
interface FieldInput {

	/**
	 * Render input
	 *
	 * @param array $vars Input view vars
	 * @return string
	 */
	public function viewInput($vars = []);

	/**
	 * Get name of the input view
	 * @return string|false
	 */
	public function getInputView();

	/**
	 * Get vars to be passed to the input
	 *
	 * @param \ElggEntity $entity Entity
	 * @return array
	 */
	public function getInputVars(\ElggEntity $entity);
}

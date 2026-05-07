<?php

namespace hypeJunction\Prototyper\Elements;

/**
 * Sticky-form value contract.
 */
interface FieldStickyValues {

	/**
	 * Set a sticky value
	 *
	 * @param mixed $value Sticky value
	 * @return self
	 */
	public function setStickyValue($value = '');

	/**
	 * Get a sticky value
	 *
	 * @return mixed
	 */
	public function getStickyValue();
}

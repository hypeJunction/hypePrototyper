<?php

namespace hypeJunction\Prototyper\Elements;

/**
 * Entity-attribute-backed field type.
 */
class AttributeField extends Field {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {

		$sticky = $this->getStickyValue();
		if ($sticky) {
			return $sticky;
		}
		
		$name = $this->getShortname();
		switch ($name) {
			default:
				return ($entity->$name) ?: $this->getDefaultValue();

			case 'type':
				return $entity->getType();

			case 'subtype':
				return $entity->getSubtype();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMultiple() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccessInput() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(\ElggEntity $entity) {

		$validation = new ValidationStatus();

		$value = get_input($this->getShortname());
		if (is_string($value)) {
			$value = strip_tags($value);
		}

		if (is_null($value) || $value == '') {
			if ($this->isRequired()) {
				$validation->setFail(elgg_echo('prototyper:validate:error:required', [$this->getLabel()]));
			}
		} else {
			$validation = $this->applyValidationRules($value, $validation, $entity);
		}

		return $validation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {
		$shortname = $this->getShortname();
		$value = get_input($shortname, $entity->$shortname);
		if ($shortname === 'subtype') {
			$entity->setSubtype($value ?: $entity->getSubtype());
		} else {
			$entity->$shortname = $value;
		}
		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDataType() {
		return 'attribute';
	}
}

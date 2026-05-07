<?php

namespace hypeJunction\Prototyper;

/**
 * Profile prototype.
 */
class Profile {

	private $config;

	private $prototype;

	private $entityFactory;

	/**
	 * Constructor
	 *
	 * @param Config        $config        Plugin config
	 * @param Prototype     $prototype     Prototype service
	 * @param EntityFactory $entityFactory Entity factory
	 */
	public function __construct(Config $config, Prototype $prototype, EntityFactory $entityFactory) {
		$this->config = $config;
		$this->prototype = $prototype;
		$this->entityFactory = $entityFactory;
	}

	/**
	 * Returns a form element object
	 *
	 * @param mixed  $entity ElggEntity or an array of entity attributes
	 * @param string $action Action name (used as a plugin hook type)
	 * @param array  $params Additional context params to pass to the hook
	 * @return Elements\Profile
	 */
	public function with($entity = [], $action = 'all', array $params = []) {

		$entity = $this->entityFactory->build($entity);
		$fields = $this->prototype->fields($entity, $action, $params)
				->filter(function(Elements\Field $field) {
					return (!$field->isAdminOnly() || elgg_is_admin_logged_in());
				})
				->sort();

		return new Elements\Profile($entity, $action, $fields);
	}
}

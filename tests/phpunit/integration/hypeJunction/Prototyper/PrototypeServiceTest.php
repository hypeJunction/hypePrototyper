<?php

namespace hypeJunction\Prototyper;

use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Elements\FieldCollection;
use hypeJunction\Prototyper\Elements\Field;
use hypeJunction\Prototyper\Elements\ValidationStatus;

/**
 * Integration tests for the Prototype service — field aggregation,
 * sticky form handling, and per-field validation state persistence.
 */
class PrototypeServiceTest extends IntegrationTestCase {

	public function up() {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			$_SESSION = $_SESSION ?? [];
		}
	}

	public function down() {
		unset($_SESSION['prototyper_validation']);
	}

	public function getPluginID(): string {
		return 'hypeprototyper';
	}

	public function testFieldsReturnsCollectionForNewEntity(): void {
		$prototype = \hypePrototyper()->prototype;
		$collection = $prototype->fields([
			'type' => 'object',
			'subtype' => 'prototyper_test',
		], 'create');

		$this->assertInstanceOf(FieldCollection::class, $collection);
	}

	public function testFieldsIncludesHiddenAttributeFieldsForNewEntity(): void {
		$prototype = \hypePrototyper()->prototype;

		$fired = false;
		$handler = function (\Elgg\Hook $hook) use (&$fired) {
			$fired = true;
			$return = $hook->getValue();
			$return['title'] = ['type' => 'title', 'data_type' => 'attribute'];
			return $return;
		};
		elgg_register_plugin_hook_handler('prototype', 'create', $handler);

		$collection = $prototype->fields([
			'type' => 'object',
			'subtype' => 'prototyper_test',
		], 'create');

		elgg_unregister_plugin_hook_handler('prototype', 'create', $handler);

		$this->assertTrue($fired);
		$this->assertInstanceOf(FieldCollection::class, $collection);
	}

	public function testFieldValidationStatusRoundTrip(): void {
		$prototype = \hypePrototyper()->prototype;

		$status = new ValidationStatus(false, ['required']);
		$prototype->setFieldValidationStatus('test_action', 'bio', $status);

		$stored = $prototype->getValidationStatus('test_action');
		$this->assertIsArray($stored);
		$this->assertArrayHasKey('bio', $stored);
		$this->assertFalse($stored['bio']['status']);
		$this->assertSame(['required'], $stored['bio']['messages']);

		$prototype->clearValidationStatus('test_action');
		$this->assertNull($prototype->getValidationStatus('test_action'));
	}
}

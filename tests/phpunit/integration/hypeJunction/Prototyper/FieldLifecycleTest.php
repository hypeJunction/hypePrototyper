<?php

namespace hypeJunction\Prototyper;

use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Elements\AttributeField;
use hypeJunction\Prototyper\Elements\MetadataField;
use hypeJunction\Prototyper\Elements\ValidationStatus;

/**
 * Integration tests for the field validate / handle / getValues lifecycle
 * against real ElggEntity instances.
 */
class FieldLifecycleTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypeprototyper';
	}

	public function testAttributeFieldHandleSetsEntityAttribute(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

		set_input('title', 'Hello World');

		$field = new AttributeField(['shortname' => 'title']);
		$result = $field->handle($entity);

		$this->assertSame($entity->guid, $result->guid);
		$this->assertSame('Hello World', $entity->title);

		set_input('title', null);
	}

	public function testAttributeFieldValidateFailsWhenRequiredMissing(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

		set_input('title', '');
$field = new AttributeField([
			'shortname' => 'title',
			'required' => true,
			'label' => 'Title',
		]);

		$validation = $field->validate($entity);

		$this->assertInstanceOf(ValidationStatus::class, $validation);
		$this->assertFalse($validation->getStatus());
		$this->assertNotEmpty($validation->getMessages());

		set_input('title', null);
	}

	public function testAttributeFieldValidatePassesWhenValuePresent(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

		set_input('title', 'Some title');
$field = new AttributeField([
			'shortname' => 'title',
			'required' => true,
		]);

		$validation = $field->validate($entity);
		$this->assertTrue($validation->getStatus());

		set_input('title', null);
	}

	public function testMetadataFieldHandlePersists(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

set_input('bio', [
			'id' => [0 => ''],
			'name' => [0 => 'bio'],
			'value' => [0 => 'A short biography'],
			'access_id' => [0 => ACCESS_PUBLIC],
			'owner_guid' => [0 => $owner->guid],
		]);

		$field = new MetadataField(['shortname' => 'bio']);
		$field->handle($entity);

$mds = elgg_get_metadata([
			'guids' => (int) $entity->guid,
			'metadata_names' => 'bio',
			'limit' => 0,
		]);
		$this->assertNotEmpty($mds);
		$this->assertSame('A short biography', $mds[0]->value);

		set_input('bio', null);
	}

	public function testMetadataFieldValidateFailsWhenRequiredEmpty(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

		set_input('bio', []);
$field = new MetadataField([
			'shortname' => 'bio',
			'required' => true,
			'label' => 'Bio',
		]);

		$validation = $field->validate($entity);
		$this->assertFalse($validation->getStatus());

		set_input('bio', null);
	}

	public function testApplyValidationRulesTriggersHook(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);

		$called = false;
		$handler = function (\Elgg\Hook $hook) use (&$called) {
			$called = true;
			$return = $hook->getValue();
			$status = $return instanceof ValidationStatus ? $return : new ValidationStatus();
			$status->setFail('from-hook');
			return $status;
		};

		elgg_register_plugin_hook_handler('validate:type', 'prototyper', $handler);

		$field = new MetadataField(['shortname' => 'bio']);
		$field->addValidationRule('type', 'text');
		$result = $field->applyValidationRules('value', null, $entity);

		$this->assertTrue($called);
		$this->assertInstanceOf(ValidationStatus::class, $result);
		$this->assertFalse($result->getStatus());
		$this->assertContains('from-hook', $result->getMessages());

		elgg_unregister_plugin_hook_handler('validate:type', 'prototyper', $handler);
	}

	public function testMetadataFieldGetValuesReturnsFromEntity(): void {
		$owner = $this->createUser();
$entity = $this->createObject([
			'subtype' => 'prototyper_test',
			'owner_guid' => $owner->guid,
		]);
		$entity->favorite_color = 'green';

		$field = new MetadataField(['shortname' => 'favorite_color']);
		$values = $field->getValues($entity);

		$this->assertIsArray($values);
		$this->assertNotEmpty($values);
		// First value should carry 'green' either as an ElggMetadata or stdClass.
		$first = $values[0];
		$this->assertSame('green', is_object($first) ? $first->value : $first);
	}
}

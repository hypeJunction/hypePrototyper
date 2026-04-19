<?php

namespace hypeJunction\Prototyper;

use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Elements\AttributeField;
use hypeJunction\Prototyper\Elements\MetadataField;
use hypeJunction\Prototyper\Elements\RelationshipField;

/**
 * Verifies that the plugin boots, all baked-in field types are registered
 * with the Config service, and the FieldFactory can build them.
 */
class PluginBootTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypeprototyper';
	}

	public function testHypePrototyperServiceIsBootable(): void {
		$this->assertTrue(function_exists('hypePrototyper'));
		$plugin = \hypePrototyper();
		$this->assertInstanceOf(Plugin::class, $plugin);
	}

	public function testConfigHasRegisteredBuiltInTypes(): void {
		$config = \hypePrototyper()->config;

		$this->assertNotFalse($config->getType('attribute', 'title'));
		$this->assertNotFalse($config->getType('attribute', 'name'));
		$this->assertNotFalse($config->getType('attribute', 'description'));
		$this->assertNotFalse($config->getType('attribute', 'access'));

		$this->assertNotFalse($config->getType('metadata', 'text'));
		$this->assertNotFalse($config->getType('metadata', 'longtext'));
		$this->assertNotFalse($config->getType('metadata', 'checkboxes'));
		$this->assertNotFalse($config->getType('metadata', 'radio'));
		$this->assertNotFalse($config->getType('metadata', 'tags'));
		$this->assertNotFalse($config->getType('metadata', 'date'));
		$this->assertNotFalse($config->getType('metadata', 'email'));
		$this->assertNotFalse($config->getType('metadata', 'url'));

		$this->assertNotFalse($config->getType('annotation', 'text'));
		$this->assertNotFalse($config->getType('annotation', 'stars'));

		$this->assertNotFalse($config->getType('relationship', 'userpicker'));
		$this->assertNotFalse($config->getType('relationship', 'friendspicker'));
	}

	public function testFieldFactoryBuildsMetadataText(): void {
		$factory = \hypePrototyper()->fieldFactory;
		$field = $factory->build(['type' => 'text', 'shortname' => 'bio']);
		$this->assertInstanceOf(MetadataField::class, $field);
		$this->assertSame('bio', $field->getShortname());
	}

	public function testFieldFactoryBuildsAttributeTitle(): void {
		$factory = \hypePrototyper()->fieldFactory;
		$field = $factory->build(['type' => 'title', 'data_type' => 'attribute']);
		$this->assertInstanceOf(AttributeField::class, $field);
		$this->assertSame('title', $field->getShortname());
	}

	public function testFieldFactoryBuildsRelationshipUserpicker(): void {
		$factory = \hypePrototyper()->fieldFactory;
$field = $factory->build([
			'type' => 'userpicker',
			'data_type' => 'relationship',
			'shortname' => 'friends',
		]);
		$this->assertInstanceOf(RelationshipField::class, $field);
		$this->assertSame('friends', $field->getShortname());
	}

	public function testFieldFactoryReturnsFalseForUnknownType(): void {
		$factory = \hypePrototyper()->fieldFactory;
		$this->assertFalse($factory->build(['type' => 'nonexistent', 'data_type' => 'metadata']));
	}
}

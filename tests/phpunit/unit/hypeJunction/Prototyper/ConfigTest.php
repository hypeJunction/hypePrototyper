<?php

namespace hypeJunction\Prototyper;

use Elgg\UnitTestCase;
use hypeJunction\Prototyper\Elements\MetadataField;
use hypeJunction\Prototyper\Elements\AnnotationField;
use hypeJunction\Prototyper\Elements\AttributeField;

/**
 * Unit tests for the plugin Config container.
 * Uses a subclass that skips the parent hypeJunction\Config constructor
 * (which requires an ElggPlugin) so we can test in isolation.
 */
class ConfigTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	private function makeConfig(): Config {
		return (new \ReflectionClass(Config::class))->newInstanceWithoutConstructor();
	}

	public function testRegisterTypeStoresByDataType(): void {
		$config = $this->makeConfig();

		$config->registerType('text', MetadataField::CLASSNAME, ['value_type' => 'text']);

		$definition = $config->getType('metadata', 'text');
		$this->assertIsArray($definition);
		$this->assertSame('text', $definition['type']);
		$this->assertSame(MetadataField::CLASSNAME, $definition['class_name']);
		$this->assertSame('metadata', $definition['data_type']);
		$this->assertSame('text', $definition['value_type']);
	}

	public function testRegisterTypeIgnoresUnknownClass(): void {
		$config = $this->makeConfig();
		$config->registerType('ghost', '\\No\\Such\\Class');
		$this->assertFalse($config->getType('metadata', 'ghost'));
	}

	public function testGetTypeReturnsFalseForMissing(): void {
		$config = $this->makeConfig();
		$this->assertFalse($config->getType('metadata', 'nope'));
	}

	public function testRegisterSameTypeOnDifferentDataTypesCoexists(): void {
		$config = $this->makeConfig();
		$config->registerType('stars', MetadataField::CLASSNAME);
		$config->registerType('stars', AnnotationField::CLASSNAME);

		$meta = $config->getType('metadata', 'stars');
		$ann = $config->getType('annotation', 'stars');

		$this->assertSame(MetadataField::CLASSNAME, $meta['class_name']);
		$this->assertSame(AnnotationField::CLASSNAME, $ann['class_name']);
	}

	public function testGetTypesReturnsAllRegistered(): void {
		$config = $this->makeConfig();
		$config->registerType('title', AttributeField::CLASSNAME);
		$config->registerType('text', MetadataField::CLASSNAME);

		$types = $config->getTypes();
		$this->assertArrayHasKey('attribute', $types);
		$this->assertArrayHasKey('metadata', $types);
		$this->assertArrayHasKey('title', $types['attribute']);
		$this->assertArrayHasKey('text', $types['metadata']);
	}

	public function testValidationRuleRegistrationRoundTrip(): void {
		$config = $this->makeConfig();
		$config->registerValidationRule('type', ['text', 'int']);
		$config->registerValidationRule('required');

		$rules = $config->getValidationRules();
		$this->assertArrayHasKey('type', $rules);
		$this->assertArrayHasKey('required', $rules);
		$this->assertSame(['text', 'int'], $rules['type']);
	}

	public function testGetDefaultsContainsDefaultLanguage(): void {
		$config = $this->makeConfig();
		$defaults = $config->getDefaults();
		$this->assertIsArray($defaults);
		$this->assertArrayHasKey('default_language', $defaults);
	}
}

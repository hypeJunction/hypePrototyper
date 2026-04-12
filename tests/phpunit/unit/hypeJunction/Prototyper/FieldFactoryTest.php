<?php

namespace hypeJunction\Prototyper;

use Elgg\UnitTestCase;
use hypeJunction\Prototyper\Elements\MetadataField;
use hypeJunction\Prototyper\Elements\AttributeField;

/**
 * Unit tests for FieldFactory::build()
 */
class FieldFactoryTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	private function makeConfig(): Config {
		return (new \ReflectionClass(Config::class))->newInstanceWithoutConstructor();
	}

	public function testBuildFromStringShortcutResolvesDefaultDataType(): void {
		$config = $this->makeConfig();
		$config->registerType('text', MetadataField::CLASSNAME);

		$factory = new FieldFactory($config);
		$field = $factory->build('text');

		$this->assertInstanceOf(MetadataField::class, $field);
		$this->assertSame('text', $field->getType());
	}

	public function testBuildFallsBackToTextWhenInvalidArgument(): void {
		$config = $this->makeConfig();
		$config->registerType('text', MetadataField::CLASSNAME);

		$factory = new FieldFactory($config);
		$field = $factory->build(42);

		$this->assertInstanceOf(MetadataField::class, $field);
	}

	public function testBuildMergesDefaultsWithOverrides(): void {
		$config = $this->makeConfig();
		$config->registerType('text', MetadataField::CLASSNAME, [
			'value_type' => 'text',
			'required' => true,
		]);

		$factory = new FieldFactory($config);
		$field = $factory->build([
			'type' => 'text',
			'shortname' => 'bio',
		]);

		$this->assertInstanceOf(MetadataField::class, $field);
		$this->assertSame('bio', $field->getShortname());
		$this->assertSame('text', $field->getValueType());
	}

	public function testBuildReturnsFalseWhenClassNotRegistered(): void {
		$config = $this->makeConfig();
		$factory = new FieldFactory($config);

		$field = $factory->build(['type' => 'nonexistent']);
		$this->assertFalse($field);
	}

	public function testBuildRespectsExplicitDataType(): void {
		$config = $this->makeConfig();
		$config->registerType('title', AttributeField::CLASSNAME, [
			'shortname' => 'title',
		]);

		$factory = new FieldFactory($config);
		$field = $factory->build([
			'type' => 'title',
			'data_type' => 'attribute',
		]);

		$this->assertInstanceOf(AttributeField::class, $field);
		$this->assertSame('title', $field->getShortname());
	}
}

<?php

namespace hypeJunction\Prototyper\Elements;

use Elgg\UnitTestCase;

/**
 * Unit tests for the abstract Field base class.
 *
 * Field has abstract interface methods (getValues / validate / handle / getDataType)
 * plus many concrete methods. We exercise the concrete methods via a mock of the
 * abstract class AND via AttributeField, the simplest concrete subclass.
 */
class FieldTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	private function makeField(array $options = []): Field {
		// Abstract mock: auto-stubs interface methods. DO NOT pass onlyMethods
		// unless we need to stub CONCRETE methods on the parent.
		$mock = $this->getMockBuilder(Field::class)
			->setConstructorArgs([$options])
			->getMockForAbstractClass();

		$mock->method('getDataType')->willReturn('metadata');

		return $mock;
	}

	public function testConstructorAppliesOptions(): void {
		$field = $this->makeField([
			'shortname' => 'bio',
			'type' => 'longtext',
			'value_type' => 'text',
		]);

		$this->assertSame('bio', $field->getShortname());
		$this->assertSame('longtext', $field->getType());
		$this->assertSame('text', $field->getValueType());
	}

	public function testGetTypeDefaultsToText(): void {
		$field = $this->makeField();
		$this->assertSame('text', $field->getType());
	}

	public function testSetValueTypeSeedsTypeValidationRule(): void {
		$field = $this->makeField();
		$field->setValueType('int');
		$rules = $field->getValidationRules();
		$this->assertArrayHasKey('type', $rules);
		$this->assertSame('int', $rules['type']);
	}

	public function testAddValidationRuleIgnoresEmpty(): void {
		$field = $this->makeField();
		$field->addValidationRule('', 'whatever');
		$field->addValidationRule('maxlen', '');
		$this->assertSame([], $field->getValidationRules());
	}

	public function testAddValidationRuleStores(): void {
		$field = $this->makeField();
		$field->addValidationRule('maxlen', 50);
		$this->assertSame(50, $field->getValidationRule('maxlen'));
		$this->assertFalse($field->getValidationRule('minlen'));
	}

	public function testValidationRulesFromConstructorArray(): void {
		$field = $this->makeField([
			'validation_rules' => [
				'required' => true,
				'maxlen' => 200,
			],
		]);
		$this->assertSame(true, $field->getValidationRule('required'));
		$this->assertSame(200, $field->getValidationRule('maxlen'));
	}

	public function testSetValidationStoresStatus(): void {
		$field = $this->makeField();
		$field->setValidation(false, ['boom']);
		$status = $field->getValidation();
		$this->assertInstanceOf(ValidationStatus::class, $status);
		$this->assertFalse($status->getStatus());
		$this->assertSame(['boom'], $status->getMessages());
		$this->assertFalse($field->isValid());
		$this->assertSame(['boom'], $field->getValidationMessages());
	}

	public function testGetValidationReturnsEmptyWhenUnset(): void {
		$field = $this->makeField();
		$this->assertInstanceOf(ValidationStatus::class, $field->getValidation());
		$this->assertTrue($field->isValid());
	}

	public function testStickyValueRoundTrip(): void {
		$field = $this->makeField();
		$this->assertNull($field->getStickyValue());
		$field->setStickyValue(['name' => ['x'], 'value' => ['y']]);
		$this->assertSame(['name' => ['x'], 'value' => ['y']], $field->getStickyValue());
	}

	public function testFlagsFromStringSplit(): void {
		$field = $this->makeField(['flags' => 'a, b, c']);
		$flags = $field->getFlags();
		$this->assertContains('a', $flags);
		$this->assertContains('b', $flags);
		$this->assertContains('c', $flags);
	}

	public function testFlagsFromArray(): void {
		$field = $this->makeField(['flags' => ['foo', 'bar']]);
		$this->assertSame(['foo', 'bar'], $field->getFlags());
	}

	public function testUnknownOptionGoesToInputVars(): void {
		$field = $this->makeField(['placeholder' => 'type here']);
		$inputVars = $field->get('input_vars');
		$this->assertSame('type here', $inputVars->placeholder);
	}

	public function testBcDataIconSizesMapsToInputVars(): void {
		$field = $this->makeField(['data-icon-sizes' => '100x100']);
		$inputVars = $field->get('input_vars');
		$this->assertSame('100x100', $inputVars->icon_sizes);
	}

	public function testIsRequiredReadsInputVars(): void {
		$field = $this->makeField(['required' => true]);
		$this->assertTrue((bool) $field->isRequired());
	}

	public function testAdminOnlyHiddenOnProfileMultipleDefaults(): void {
		$field = $this->makeField();
		$this->assertNotTrue($field->isAdminOnly());
		$this->assertNotTrue($field->isHiddenOnProfile());
		$this->assertNotTrue($field->isMultiple());
	}

	public function testHasAccessInputDefaultFalse(): void {
		$field = $this->makeField();
		$this->assertFalse($field->hasAccessInput());
	}

	public function testInputViewFallsBackToTypeConvention(): void {
		$field = $this->makeField(['type' => 'text']);
		$this->assertSame('input/text', $field->getInputView());
	}

	public function testInputViewHonoursOverride(): void {
		$field = $this->makeField(['type' => 'text', 'input_view' => 'custom/input']);
		$this->assertSame('custom/input', $field->getInputView());
	}

	public function testOutputViewFallsBackToTypeConvention(): void {
		$field = $this->makeField(['type' => 'longtext']);
		$this->assertSame('output/longtext', $field->getOutputView());
	}

	public function testGetLabelRawReturnsKey(): void {
		$field = $this->makeField([
			'shortname' => 'bio',
		]);
		// entity_type / entity_subtype default empty, raw key collapses blanks
		$key = $field->getLabel('', true);
		$this->assertStringContainsString('label', $key);
		$this->assertStringContainsString('bio', $key);
	}

	public function testGetLabelReturnsFalseWhenDisabled(): void {
		$field = $this->makeField(['label' => false]);
		$this->assertFalse($field->getLabel('en'));
	}

	public function testGetHelpReturnsFalseWhenDisabled(): void {
		$field = $this->makeField(['help' => false]);
		$this->assertFalse($field->getHelp('en'));
	}

	public function testGetLabelRespectsStringOverride(): void {
		$field = $this->makeField(['label' => 'Biography']);
		$this->assertSame('Biography', $field->getLabel('en'));
	}

	public function testMockWithOnlyMethodsOverridesConcreteLabel(): void {
		// Demonstrates the SKILL.md pattern: when you need to stub a concrete
		// method on the abstract class, pass it via onlyMethods. Without it,
		// getMockForAbstractClass would only stub abstract methods and trying
		// to ->method('getLabel') would throw MethodCannotBeConfigured.
		$mock = $this->getMockBuilder(Field::class)
			->onlyMethods(['getLabel', 'getType', 'isMultiple', 'getShortname'])
			->getMockForAbstractClass();

		$mock->method('getLabel')->willReturn('Stubbed');
		$mock->method('getType')->willReturn('text');
		$mock->method('isMultiple')->willReturn(true);
		$mock->method('getShortname')->willReturn('stub');

		$this->assertSame('Stubbed', $mock->getLabel());
		$this->assertSame('text', $mock->getType());
		$this->assertTrue($mock->isMultiple());
		$this->assertSame('stub', $mock->getShortname());
	}

	public function testGetSetRoundTripOnKnownProperty(): void {
		$field = $this->makeField();
		$field->set('priority', 123);
		$this->assertSame(123, $field->get('priority'));
	}
}

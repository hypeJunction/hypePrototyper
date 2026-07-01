<?php

namespace hypeJunction\Prototyper\Elements;

use Elgg\UnitTestCase;

/**
 * Write-path regression coverage for MetadataField::getValues().
 *
 * The Elgg 7.x / PHP 8 migration surfaced a fatal TypeError in this method:
 * when a form was re-submitted with validation errors, the sticky value for a
 * single scalar field arrived as a string, and the old code unconditionally
 * did `$sticky['value']` — "Cannot access offset of type string on string".
 * This was invisible to GET render gating because it only fires on POST
 * re-render. See bd elgg-migrate-ifpdo and commit 0442b85.
 */
class MetadataFieldTest extends UnitTestCase {

	public function up() {}

	public function down() {}

	private function makeField(array $options = []): MetadataField {
		return new MetadataField($options);
	}

	/**
	 * A scalar sticky value (single submitted field) must not fatal and must be
	 * wrapped into a single metadata value object. This is the actual 7.x bug.
	 */
	public function testScalarStickyValueDoesNotFatal(): void {
		$field = $this->makeField(['shortname' => 'bio']);
		$field->setStickyValue('a plain string');

		$entity = $this->createMock(\ElggEntity::class);
		$values = $field->getValues($entity);

		$this->assertIsArray($values);
		$this->assertCount(1, $values);
		$this->assertSame('a plain string', $values[0]->value);
	}

	/**
	 * Numeric-zero is a legitimate scalar and must survive (not collapse to the
	 * default-value branch).
	 */
	public function testScalarZeroStickyValueIsPreserved(): void {
		$field = $this->makeField(['shortname' => 'count']);
		$field->setStickyValue('0');

		$entity = $this->createMock(\ElggEntity::class);
		$values = $field->getValues($entity);

		$this->assertCount(1, $values);
		$this->assertSame('0', $values[0]->value);
	}

	/**
	 * The structured (parallel-array) sticky shape — the normal multi-value
	 * metadata submit — must still be unpacked index-by-index.
	 */
	public function testStructuredStickyValueIsUnpacked(): void {
		$field = $this->makeField(['shortname' => 'skills']);
		$field->setStickyValue([
			'id' => [0 => '5'],
			'name' => [0 => 'skills'],
			'value' => [0 => 'php'],
			'access_id' => [0 => ACCESS_PUBLIC],
			'owner_guid' => [0 => 1],
		]);

		$entity = $this->createMock(\ElggEntity::class);
		$values = $field->getValues($entity);

		$this->assertCount(1, $values);
		$this->assertSame('php', $values[0]->value);
		$this->assertSame('skills', $values[0]->name);
		$this->assertSame('5', $values[0]->id);
	}

	/**
	 * A structured sticky value missing the optional sub-keys (id/name/access)
	 * must fall back rather than throw on an undefined offset.
	 */
	public function testStructuredStickyValueToleratesMissingSubKeys(): void {
		$field = $this->makeField(['shortname' => 'skills']);
		$field->setStickyValue([
			'value' => [0 => 'php'],
		]);

		$entity = $this->createMock(\ElggEntity::class);
		$values = $field->getValues($entity);

		$this->assertCount(1, $values);
		$this->assertSame('php', $values[0]->value);
		$this->assertSame('skills', $values[0]->name);
	}

	/**
	 * Empty sticky and no persisted entity should yield a single default-value
	 * placeholder, never an empty array.
	 */
	public function testEmptyStickyFallsBackToDefaultValue(): void {
		$field = $this->makeField(['shortname' => 'bio', 'value' => 'fallback']);
		$field->setStickyValue('');

		$entity = $this->createMock(\ElggEntity::class);
		$values = $field->getValues($entity);

		$this->assertCount(1, $values);
		$this->assertSame('fallback', $values[0]->value);
	}
}

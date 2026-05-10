<?php

namespace hypeJunction\Prototyper\Elements;

use Elgg\UnitTestCase;

/**
 * Unit tests for AttributeField — concrete subclass of Field, so we can
 * instantiate it directly without mocks. Entity-dependent behavior
 * (getValues / validate / handle against a real ElggEntity) is covered
 * by integration tests; this suite only pokes the stateless pieces.
 */
class AttributeFieldTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	public function testDataTypeIsAttribute(): void {
		$this->assertSame('attribute', AttributeField::getDataType());
	}

	public function testIsMultipleIsAlwaysFalse(): void {
		$f = new AttributeField(['multiple' => true]);
		$this->assertFalse($f->isMultiple());
	}

	public function testHasAccessInputIsFalse(): void {
		$f = new AttributeField();
		$this->assertFalse($f->hasAccessInput());
	}

	public function testShortnameRoundTrip(): void {
		$f = new AttributeField(['shortname' => 'title']);
		$this->assertSame('title', $f->getShortname());
	}

	public function testStickyValueOverridesLookup(): void {
		$f = new AttributeField(['shortname' => 'title']);
		$f->setStickyValue('from-sticky');
		$this->assertSame('from-sticky', $f->getStickyValue());
	}

	public function testDataTypesAreDistinctAcrossSubclasses(): void {
		$this->assertSame('attribute', AttributeField::getDataType());
		$this->assertSame('metadata', MetadataField::getDataType());
		$this->assertSame('annotation', AnnotationField::getDataType());
		$this->assertSame('relationship', RelationshipField::getDataType());
	}
}

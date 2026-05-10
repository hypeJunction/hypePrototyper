<?php

namespace hypeJunction\Prototyper\Elements;

use Elgg\UnitTestCase;

/**
 * Unit tests for ValidationStatus value object.
 * Pure PHP, no Elgg services touched.
 */
class ValidationStatusTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	public function testDefaultIsValid(): void {
		$v = new ValidationStatus();
		$this->assertTrue($v->getStatus());
		$this->assertTrue($v->isValid());
		$this->assertSame([], $v->getMessages());
	}

	public function testConstructWithFailure(): void {
		$v = new ValidationStatus(false, ['boom']);
		$this->assertFalse($v->getStatus());
		$this->assertFalse($v->isValid());
		$this->assertSame(['boom'], $v->getMessages());
	}

	public function testSetFailFlipsStatusAndAppends(): void {
		$v = new ValidationStatus();
		$v->setFail('nope');
		$this->assertFalse($v->getStatus());
		$this->assertSame(['nope'], $v->getMessages());
	}

	public function testSetSuccessRestoresStatusAndAppendsMessage(): void {
		$v = new ValidationStatus(false, ['bad']);
		$v->setSuccess('ok');
		$this->assertTrue($v->getStatus());
		$this->assertSame(['bad', 'ok'], $v->getMessages());
	}

	public function testAddEmptyMessageIgnored(): void {
		$v = new ValidationStatus();
		$v->addMessage('');
		$v->addMessage(null);
		$this->assertSame([], $v->getMessages());
	}

	public function testAddMessageAppends(): void {
		$v = new ValidationStatus();
		$v->addMessage('one');
		$v->addMessage('two');
		$this->assertSame(['one', 'two'], $v->getMessages());
	}

	public function testNonArrayMessagesNormalised(): void {
		$v = new ValidationStatus(true, 'not-an-array');
		$this->assertSame([], $v->getMessages());
	}

	public function testStatusCoercedToBool(): void {
		$v = new ValidationStatus(1);
		$this->assertTrue($v->getStatus());

		$v2 = new ValidationStatus(0);
		$this->assertFalse($v2->getStatus());
	}
}

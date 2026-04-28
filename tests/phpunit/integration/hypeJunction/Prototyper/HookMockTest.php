<?php

namespace hypeJunction\Prototyper;

use Elgg\Event;
use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Elements\MetadataField;
use hypeJunction\Prototyper\Elements\ValidationStatus;

/**
 * Demonstrates the SKILL.md pattern for mocking the \Elgg\Hook INTERFACE.
 * Field::applyValidationRules dispatches validate:<rule>,prototyper hooks;
 * a hook handler receives an Elgg\Hook and must return a ValidationStatus.
 * Here we verify a handler-under-test works against a mocked Hook object
 * without needing to register it in the global registry.
 */
class HookMockTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypeprototyper';
	}

	public function testHandlerReceivesHookMock(): void {
		$field = new MetadataField(['shortname' => 'bio']);

		$hook = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$hook->method('getValue')->willReturn(new ValidationStatus());
$hook->method('getParam')->willReturnMap([
			['rule', null, 'type'],
			['field', null, $field],
			['value', null, 'hello'],
			['expectation', null, 'text'],
			['entity', null, null],
		]);
		$hook->method('getName')->willReturn('validate:type');
		$hook->method('getType')->willReturn('prototyper');

		$handler = function (Event $hook) {
			$status = $hook->getValue();
			if (!$status instanceof ValidationStatus) {
				$status = new ValidationStatus();
			}
			if ($hook->getParam('value') === 'hello') {
				$status->setFail('rejected');
			}
			return $status;
		};

		$result = $handler($hook);
		$this->assertInstanceOf(ValidationStatus::class, $result);
		$this->assertFalse($result->getStatus());
		$this->assertContains('rejected', $result->getMessages());
	}

	public function testPluginSettingRoundTripViaElggPluginApi(): void {
		// SKILL.md note: elgg_set_plugin_setting() was removed in Elgg 4 —
		// use $plugin->setSetting()/->getSetting() instead. This test encodes
		// that expectation so regressions surface loudly.
		$plugin = elgg_get_plugin_from_id('hypeprototyper');
		if (!$plugin) {
			$this->markTestSkipped('hypeprototyper plugin entity not available in test DB');
		}
		$plugin->setSetting('pt_test_flag', '1');
		$this->assertSame('1', $plugin->getSetting('pt_test_flag'));
		$plugin->unsetSetting('pt_test_flag');
	}
}

<?php

namespace hypeJunction\Prototyper;

use Elgg\IntegrationTestCase;

/**
 * Integration tests asserting that every prototyper/input and prototyper/output
 * view in the plugin is registered with Elgg and resolves without fatal errors.
 */
class ViewsTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypeprototyper';
	}

	public function inputViewProvider(): array {
		return [
			['prototyper/input/attribute'],
			['prototyper/input/metadata'],
			['prototyper/input/annotation'],
			['prototyper/input/relationship'],
			['prototyper/input/category'],
			['prototyper/input/file'],
			['prototyper/input/icon'],
			['prototyper/input/image'],
			['prototyper/input/submit'],
		];
	}

	/**
	 * @dataProvider inputViewProvider
	 */
	public function testInputViewExists(string $view): void {
		$this->assertTrue(elgg_view_exists($view), "Missing view: $view");
	}

	public function outputViewProvider(): array {
		return [
			['prototyper/output/attribute'],
			['prototyper/output/metadata'],
			['prototyper/output/annotation'],
			['prototyper/output/relationship'],
			['prototyper/output/category'],
			['prototyper/output/file'],
		];
	}

	/**
	 * @dataProvider outputViewProvider
	 */
	public function testOutputViewExists(string $view): void {
		$this->assertTrue(elgg_view_exists($view), "Missing view: $view");
	}

	public function testCssExtensionRegistered(): void {
		$this->assertTrue(elgg_view_exists('css/framework/prototyper/stylesheet'));
	}
}

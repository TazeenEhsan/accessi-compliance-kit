<?php
/**
 * Smoke test for the plugin bootstrap.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Plugin;
use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Plugin
 */
class PluginTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\stubs( array( 'add_action' ) );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_instance_returns_the_same_singleton() {
		$first  = Plugin::instance();
		$second = Plugin::instance();

		$this->assertInstanceOf( Plugin::class, $first );
		$this->assertSame( $first, $second );
	}
}

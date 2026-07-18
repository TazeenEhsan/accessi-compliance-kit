<?php
/**
 * Smoke test for the plugin bootstrap.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Plugin;
use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Plugin
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

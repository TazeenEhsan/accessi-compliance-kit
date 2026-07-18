<?php
/**
 * Tests for FocusStatesFix.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Fixes\FocusStatesFix;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Fixes\FocusStatesFix
 */
class FocusStatesFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_enqueue_registers_the_frontend_fixes_stylesheet() {
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with(
				'accessi-compliance-kit-frontend-fixes',
				ACCESSI_COMPLIANCE_KIT_URL . 'assets/css/frontend-fixes.css',
				array(),
				ACCESSI_COMPLIANCE_KIT_VERSION
			);

		( new FocusStatesFix() )->enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_applies_to_is_global() {
		$this->assertSame( array( 'global' ), ( new FocusStatesFix() )->applies_to() );
	}
}

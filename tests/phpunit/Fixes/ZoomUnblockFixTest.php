<?php
/**
 * Tests for ZoomUnblockFix.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\ZoomUnblockFix;
use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Fixes\ZoomUnblockFix
 */
class ZoomUnblockFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_filter_head_html_removes_user_scalable_no_and_low_maximum_scale() {
		$html = '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">';

		$this->assertSame(
			'<meta name="viewport" content="width=device-width, initial-scale=1">',
			( new ZoomUnblockFix() )->filter_head_html( $html )
		);
	}

	public function test_filter_head_html_handles_single_quotes_zero_and_semicolons() {
		$html = "<meta content='width=device-width; user-scalable=0; maximum-scale=1.5' name='viewport' />";

		$this->assertSame(
			"<meta content='width=device-width' name='viewport' />",
			( new ZoomUnblockFix() )->filter_head_html( $html )
		);
	}

	public function test_filter_head_html_keeps_permissive_maximum_scale_and_user_scalable_yes() {
		$html = '<meta name="viewport" content="width=device-width, maximum-scale=5, user-scalable=yes">';

		$this->assertSame( $html, ( new ZoomUnblockFix() )->filter_head_html( $html ) );
	}

	public function test_filter_head_html_falls_back_to_a_default_when_nothing_is_left() {
		$html = '<meta name="viewport" content="user-scalable=no">';

		$this->assertSame(
			'<meta name="viewport" content="width=device-width, initial-scale=1">',
			( new ZoomUnblockFix() )->filter_head_html( $html )
		);
	}

	public function test_filter_head_html_leaves_other_markup_untouched() {
		$html = '<meta name="description" content="user-scalable=no"><title>Shop</title>';

		$this->assertSame( $html, ( new ZoomUnblockFix() )->filter_head_html( $html ) );
	}

	public function test_end_buffer_prints_the_filtered_output() {
		$fix = new ZoomUnblockFix();

		ob_start();
		$fix->start_buffer();
		echo '<meta name="viewport" content="width=device-width, user-scalable=no">';
		$fix->end_buffer();
		$output = ob_get_clean();

		$this->assertSame( '<meta name="viewport" content="width=device-width">', $output );
	}

	public function test_end_buffer_does_nothing_when_buffering_never_started() {
		ob_start();
		( new ZoomUnblockFix() )->end_buffer();

		$this->assertSame( '', ob_get_clean() );
	}

	public function test_id_and_context() {
		$fix = new ZoomUnblockFix();

		$this->assertSame( 'zoom_unblock', $fix->id() );
		$this->assertSame( array( 'global' ), $fix->applies_to() );
	}
}

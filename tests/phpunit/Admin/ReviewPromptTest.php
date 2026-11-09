<?php
/**
 * Tests for ReviewPrompt.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\ReviewPrompt;
use AccessibilityComplianceKitForWooCommerce\Scanner\ScanStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\ReviewPrompt
 */
class ReviewPromptTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Build a prompt whose storage reports a given completed-scan count.
	 *
	 * @param int $completed Completed scans.
	 * @return ReviewPrompt
	 */
	private function prompt( $completed ) {
		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'count_completed_scans' )->willReturn( $completed );

		return new ReviewPrompt( $storage );
	}

	public function test_hidden_below_three_completed_scans() {
		Functions\when( 'get_user_meta' )->justReturn( '' );

		$this->assertFalse( $this->prompt( 2 )->should_show( 1 ) );
	}

	public function test_shown_at_three_completed_scans_when_unanswered() {
		Functions\when( 'get_user_meta' )->justReturn( '' );

		$this->assertTrue( $this->prompt( 3 )->should_show( 1 ) );
	}

	public function test_hidden_forever_once_marked_done() {
		Functions\when( 'get_user_meta' )->justReturn( 'done' );

		$this->assertFalse( $this->prompt( 50 )->should_show( 1 ) );
	}

	public function test_hidden_while_snoozed_and_shown_again_after_expiry() {
		Functions\when( 'get_user_meta' )->justReturn( (string) ( time() + 3600 ) );
		$this->assertFalse( $this->prompt( 5 )->should_show( 1 ) );

		Functions\when( 'get_user_meta' )->justReturn( (string) ( time() - 3600 ) );
		$this->assertTrue( $this->prompt( 5 )->should_show( 1 ) );
	}

	public function test_review_url_points_at_the_plugin_reviews_page() {
		$this->assertStringStartsWith(
			'https://wordpress.org/support/plugin/tazeen-store-accessibility-kit-for-woocommerce/reviews/',
			ReviewPrompt::REVIEW_URL
		);
	}
}

<?php
/**
 * Tests for CheckoutLabelsFix.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Fixes\CheckoutLabelsFix;
use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Fixes\CheckoutLabelsFix
 */
class CheckoutLabelsFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_ensure_label_uses_placeholder_when_label_missing() {
		$fix  = new CheckoutLabelsFix();
		$args = array( 'placeholder' => 'Postcode / ZIP' );

		$result = $fix->ensure_label( $args, 'billing_postcode' );

		$this->assertSame( 'Postcode / ZIP', $result['label'] );
		$this->assertContains( 'screen-reader-text', $result['label_class'] );
	}

	public function test_ensure_label_leaves_existing_label_untouched() {
		$fix  = new CheckoutLabelsFix();
		$args = array(
			'label'       => 'Postcode',
			'placeholder' => 'ZIP',
		);

		$result = $fix->ensure_label( $args, 'billing_postcode' );

		$this->assertSame( 'Postcode', $result['label'] );
		$this->assertArrayNotHasKey( 'label_class', $result );
	}

	public function test_ensure_label_does_nothing_without_a_placeholder() {
		$fix  = new CheckoutLabelsFix();
		$args = array();

		$result = $fix->ensure_label( $args, 'billing_postcode' );

		$this->assertSame( array(), $result );
	}

	public function test_ensure_label_preserves_existing_label_classes() {
		$fix  = new CheckoutLabelsFix();
		$args = array(
			'placeholder' => 'City',
			'label_class' => array( 'custom-class' ),
		);

		$result = $fix->ensure_label( $args, 'billing_city' );

		$this->assertSame( array( 'custom-class', 'screen-reader-text' ), array_values( $result['label_class'] ) );
	}
}

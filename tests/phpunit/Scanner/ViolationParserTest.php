<?php
/**
 * Tests for ViolationParser.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Scanner\ViolationParser;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Scanner\ViolationParser
 */
class ViolationParserTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'sanitize_text_field' => function ( $value ) {
					return is_string( $value ) ? trim( $value ) : '';
				},
				'esc_url_raw'         => function ( $value ) {
					return is_string( $value ) ? $value : '';
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_parse_with_empty_results_returns_zeroed_summary() {
		$result = ViolationParser::parse( array() );

		$this->assertSame( array(), $result['violations'] );
		$this->assertSame(
			array(
				'critical' => 0,
				'serious'  => 0,
				'moderate' => 0,
				'minor'    => 0,
				'total'    => 0,
			),
			$result['summary']
		);
	}

	public function test_parse_with_mixed_severities_counts_each_bucket() {
		$raw = array(
			$this->fixture_violation( 'image-alt', 'critical' ),
			$this->fixture_violation( 'color-contrast', 'serious' ),
			$this->fixture_violation( 'landmark-one-main', 'moderate' ),
			$this->fixture_violation( 'region', 'minor' ),
			$this->fixture_violation( 'duplicate-id-active', 'serious' ),
		);

		$result = ViolationParser::parse( $raw );

		$this->assertCount( 5, $result['violations'] );
		$this->assertSame(
			array(
				'critical' => 1,
				'serious'  => 2,
				'moderate' => 1,
				'minor'    => 1,
				'total'    => 5,
			),
			$result['summary']
		);

		$this->assertSame( 'image-alt', $result['violations'][0]['rule'] );
		$this->assertSame( 'div.product > img', $result['violations'][0]['nodes'][0]['selector'] );
		$this->assertSame( '<img src="product.jpg">', $result['violations'][0]['nodes'][0]['html'] );
	}

	public function test_parse_defaults_unrecognized_impact_to_minor() {
		$raw = array(
			array(
				'rule'   => 'unknown-rule',
				'impact' => 'not-a-real-severity',
				'nodes'  => array(),
			),
		);

		$result = ViolationParser::parse( $raw );

		$this->assertSame( 'minor', $result['violations'][0]['impact'] );
		$this->assertSame( 1, $result['summary']['minor'] );
		$this->assertSame( 1, $result['summary']['total'] );
	}

	public function test_parse_skips_entries_without_a_rule_id() {
		$result = ViolationParser::parse( array( array( 'impact' => 'critical' ), 'not-an-array' ) );

		$this->assertSame( array(), $result['violations'] );
		$this->assertSame( 0, $result['summary']['total'] );
	}

	public function test_parse_truncates_long_node_html() {
		$raw = array(
			array(
				'rule'   => 'image-alt',
				'impact' => 'critical',
				'nodes'  => array(
					array(
						'selector' => 'img',
						'html'     => str_repeat( 'a', 600 ),
					),
				),
			),
		);

		$result = ViolationParser::parse( $raw );

		$this->assertSame( 500, strlen( $result['violations'][0]['nodes'][0]['html'] ) );
	}

	/**
	 * Build a fixture violation matching the scanner bundle's output shape
	 * (docs/database.md §4 / assets/js/src/scanner/runScan.js).
	 *
	 * @param string $rule   Rule id.
	 * @param string $impact Severity impact.
	 * @return array
	 */
	private function fixture_violation( $rule, $impact ) {
		return array(
			'rule'        => $rule,
			'impact'      => $impact,
			'description' => 'Elements must have sufficient color contrast',
			'help'        => 'Elements must meet minimum color contrast ratio thresholds',
			'help_url'    => 'https://dequeuniversity.com/rules/axe/4.7/' . $rule,
			'nodes'       => array(
				array(
					'selector'        => 'div.product > img',
					'html'            => '<img src="product.jpg">',
					'failure_summary' => 'Fix any of the following:',
				),
			),
		);
	}
}

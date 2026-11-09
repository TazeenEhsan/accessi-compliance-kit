<?php
/**
 * Tests for ScanExporter.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Scanner\ScanExporter;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Scanner\ScanExporter
 */
class ScanExporterTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'get_date_from_gmt' )->returnArg();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @dataProvider formula_prefixes
	 *
	 * @param string $value Dangerous cell value.
	 */
	public function test_sanitize_cell_prefixes_formula_triggers( $value ) {
		$this->assertSame( "'" . $value, ScanExporter::sanitize_cell( $value ) );
	}

	public function formula_prefixes() {
		return array(
			'equals' => array( '=HYPERLINK("http://evil","x")' ),
			'plus'   => array( '+1+1' ),
			'minus'  => array( '-2+3' ),
			'at'     => array( '@SUM(A1)' ),
			'tab'    => array( "\tcmd" ),
			'cr'     => array( "\rcmd" ),
		);
	}

	public function test_sanitize_cell_leaves_safe_values_and_integers_alone() {
		$this->assertSame( 'https://example.test/', ScanExporter::sanitize_cell( 'https://example.test/' ) );
		$this->assertSame( '', ScanExporter::sanitize_cell( '' ) );
		$this->assertSame( '0', ScanExporter::sanitize_cell( 0 ) );
	}

	public function test_build_csv_has_bom_header_quoted_rows_and_neutralised_cells() {
		$csv = ScanExporter::build_csv(
			array(
				array(
					'id'           => 7,
					'scan_type'    => 'single',
					'url'          => '=cmd|"x"',
					'started_at'   => '2026-01-02 03:04:05',
					'completed_at' => null,
					'status'       => 'complete',
					'summary'      => array(
						'critical' => 2,
						'minor'    => 1,
					),
					'triggered_by' => 1,
				),
			)
		);

		$this->assertSame( "\xEF\xBB\xBF", substr( $csv, 0, 3 ) );

		$lines = explode( "\r\n", trim( substr( $csv, 3 ) ) );

		$this->assertCount( 2, $lines );
		$this->assertStringStartsWith( '"Scan ID","Started', $lines[0] );
		$this->assertSame(
			'"7","2026-01-02 03:04:05","","\'=cmd|""x""","single","complete","2","0","0","1","1"',
			$lines[1]
		);
	}

	public function test_build_csv_with_no_scans_is_just_the_header() {
		$lines = explode( "\r\n", trim( substr( ScanExporter::build_csv( array() ), 3 ) ) );

		$this->assertCount( 1, $lines );
	}
}

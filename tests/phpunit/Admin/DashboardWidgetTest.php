<?php
/**
 * Tests for DashboardWidget.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Admin\DashboardWidget;
use AccessiComplianceKit\Scanner\ScanStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Admin\DashboardWidget
 */
class DashboardWidgetTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__'              => function ( $text ) {
					return $text;
				},
				'esc_html__'      => function ( $text ) {
					return $text;
				},
				'esc_html'        => function ( $text ) {
					return $text;
				},
				'esc_url'         => function ( $text ) {
					return $text;
				},
				'wp_kses_post'    => function ( $text ) {
					return $text;
				},
				'admin_url'       => function ( $path ) {
					return 'https://example.test/wp-admin/' . $path;
				},
				'add_query_arg'   => function ( $key, $value, $url ) {
					return $url . '?' . $key . '=' . $value;
				},
				'get_date_from_gmt' => function ( $gmt_date ) {
					return $gmt_date;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_add_widget_registers_nothing_when_user_cannot_scan() {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_add_dashboard_widget' )->never();

		( new DashboardWidget( $this->createMock( ScanStorage::class ) ) )->add_widget();

		$this->addToAssertionCount( 1 );
	}

	public function test_add_widget_registers_for_capable_users() {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'wp_add_dashboard_widget' )
			->once()
			->with( DashboardWidget::WIDGET_ID, \Mockery::type( 'string' ), \Mockery::type( 'array' ) );

		( new DashboardWidget( $this->createMock( ScanStorage::class ) ) )->add_widget();

		$this->addToAssertionCount( 1 );
	}

	public function test_render_shows_empty_state_when_no_scan_exists() {
		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'get_last_scan' )->willReturn( null );

		ob_start();
		( new DashboardWidget( $storage ) )->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'No scans yet.', $output );
		$this->assertStringContainsString( 'Run your first scan', $output );
	}

	public function test_render_shows_severity_counts_when_a_scan_exists() {
		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'get_last_scan' )->willReturn(
			array(
				'started_at' => '2026-07-10 12:00:00',
				'summary'    => array(
					'critical' => 3,
					'serious'  => 5,
					'moderate' => 2,
					'minor'    => 1,
				),
			)
		);

		ob_start();
		( new DashboardWidget( $storage ) )->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Last scan:', $output );
		$this->assertStringContainsString( '<strong>Critical:</strong> 3', $output );
		$this->assertStringContainsString( '<strong>Serious:</strong> 5', $output );
		$this->assertStringContainsString( '<strong>Moderate:</strong> 2', $output );
		$this->assertStringContainsString( '<strong>Minor:</strong> 1', $output );
		$this->assertStringContainsString( 'View full results', $output );
	}
}

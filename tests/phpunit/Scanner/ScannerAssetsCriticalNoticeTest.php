<?php
/**
 * Tests for ScannerAssets::add_critical_notice_node().
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Scanner\ScannerAssets;
use AccessiComplianceKit\Scanner\ScanStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Scanner\ScannerAssets
 */
class ScannerAssetsCriticalNoticeTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__'            => function ( $text ) {
					return $text;
				},
				'esc_html__'    => function ( $text ) {
					return $text;
				},
				'esc_attr__'    => function ( $text ) {
					return $text;
				},
				'esc_html'      => function ( $text ) {
					return $text;
				},
				'esc_url'       => function ( $text ) {
					return $text;
				},
				'admin_url'     => function ( $path ) {
					return 'https://example.test/wp-admin/' . $path;
				},
				'add_query_arg' => function ( $key, $value, $url ) {
					return $url . '?' . $key . '=' . $value;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_adds_no_node_when_user_cannot_scan() {
		Functions\when( 'current_user_can' )->justReturn( false );

		$storage    = $this->createMock( ScanStorage::class );
		$admin_bar = new FakeWpAdminBar();

		( new ScannerAssets( $storage ) )->add_critical_notice_node( $admin_bar );

		$this->assertArrayNotHasKey( 'accessi-compliance-kit-critical-notice', $admin_bar->nodes );
	}

	public function test_adds_no_node_when_there_is_no_last_scan() {
		Functions\when( 'current_user_can' )->justReturn( true );

		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'get_last_scan' )->willReturn( null );
		$admin_bar = new FakeWpAdminBar();

		( new ScannerAssets( $storage ) )->add_critical_notice_node( $admin_bar );

		$this->assertArrayNotHasKey( 'accessi-compliance-kit-critical-notice', $admin_bar->nodes );
	}

	public function test_adds_no_node_when_the_last_scan_has_no_critical_issues() {
		Functions\when( 'current_user_can' )->justReturn( true );

		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'get_last_scan' )->willReturn(
			array( 'summary' => array( 'critical' => 0, 'serious' => 4 ) )
		);
		$admin_bar = new FakeWpAdminBar();

		( new ScannerAssets( $storage ) )->add_critical_notice_node( $admin_bar );

		$this->assertArrayNotHasKey( 'accessi-compliance-kit-critical-notice', $admin_bar->nodes );
	}

	public function test_adds_a_node_when_the_last_scan_has_critical_issues() {
		Functions\when( 'current_user_can' )->justReturn( true );

		$storage = $this->createMock( ScanStorage::class );
		$storage->method( 'get_last_scan' )->willReturn(
			array(
				'id'      => 42,
				'summary' => array( 'critical' => 3 ),
			)
		);
		$admin_bar = new FakeWpAdminBar();

		( new ScannerAssets( $storage ) )->add_critical_notice_node( $admin_bar );

		$this->assertArrayHasKey( 'accessi-compliance-kit-critical-notice', $admin_bar->nodes );
		$this->assertStringContainsString( '3', $admin_bar->nodes['accessi-compliance-kit-critical-notice']['title'] );
	}
}

<?php
/**
 * Tests for ScanStorage.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Scanner\ScanStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessiComplianceKit\Scanner\ScanStorage
 */
class ScanStorageTest extends TestCase {

	/**
	 * Fake `$wpdb` double, reset before every test.
	 *
	 * @var FakeScanWpdb
	 */
	private $wpdb;

	/**
	 * Options captured via the stubbed `update_option()`.
	 *
	 * @var array
	 */
	private $options;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->wpdb    = new FakeScanWpdb();
		$this->options = array();
		$call_count    = 0;

		global $wpdb;
		$wpdb = $this->wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		Functions\stubs(
			array(
				'current_time'   => function () use ( &$call_count ) {
					// Each call a second later, so ordering by started_at is meaningful.
					return gmdate( 'Y-m-d H:i:s', 1_752_753_600 + $call_count++ );
				},
				'wp_json_encode' => function ( $data ) {
					return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				},
				'absint'         => function ( $value ) {
					return abs( (int) $value );
				},
			)
		);

		Functions\when( 'update_option' )->alias(
			function ( $key, $value ) {
				$this->options[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
			}
		);
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_create_scan_inserts_a_running_row() {
		$storage = new ScanStorage();

		$id = $storage->create_scan( 'https://example.test/shop/', 'single', 1 );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		$scan = $storage->get_scan( $id );

		$this->assertSame( 'running', $scan['status'] );
		$this->assertSame( 'https://example.test/shop/', $scan['url'] );
		$this->assertSame( array(), $scan['violations'] );
		$this->assertSame( array(), $scan['summary'] );
	}

	public function test_complete_scan_stores_violations_and_updates_last_scan_id() {
		$storage = new ScanStorage();
		$id      = $storage->create_scan( 'https://example.test/shop/', 'single', 1 );

		$violations = array(
			array(
				'rule'        => 'image-alt',
				'impact'      => 'critical',
				'description' => 'why it fails',
				'help'        => 'how to fix',
				'help_url'    => 'https://dequeuniversity.com/rules/axe/4.7/image-alt',
				'nodes'       => array(),
			),
		);
		$summary = array(
			'critical' => 1,
			'serious'  => 0,
			'moderate' => 0,
			'minor'    => 0,
			'total'    => 1,
		);

		$result = $storage->complete_scan( $id, $violations, $summary );
		$this->assertTrue( $result );

		$scan = $storage->get_scan( $id );

		$this->assertSame( 'complete', $scan['status'] );
		$this->assertSame( $violations, $scan['violations'] );
		$this->assertSame( $summary, $scan['summary'] );
		$this->assertSame( $id, $this->options['accessi_compliance_kit_last_scan_id'] );

		$last = $storage->get_last_scan();
		$this->assertSame( $id, $last['id'] );
	}

	public function test_fail_scan_marks_status_failed_with_reason() {
		$storage = new ScanStorage();
		$id      = $storage->create_scan( 'https://example.test/shop/', 'single', 1 );

		$result = $storage->fail_scan( $id, 'timeout' );
		$this->assertTrue( $result );

		$scan = $storage->get_scan( $id );

		$this->assertSame( 'failed', $scan['status'] );
		$this->assertSame( array( 'reason' => 'timeout' ), $scan['summary'] );
	}

	public function test_get_scan_returns_null_for_unknown_id() {
		$storage = new ScanStorage();

		$this->assertNull( $storage->get_scan( 999 ) );
	}

	public function test_get_recent_scans_orders_most_recent_first() {
		$storage = new ScanStorage();

		$first  = $storage->create_scan( 'https://example.test/one/', 'single', 1 );
		$second = $storage->create_scan( 'https://example.test/two/', 'single', 1 );

		$recent = $storage->get_recent_scans( 10 );

		$this->assertCount( 2, $recent );
		$this->assertSame( $second, $recent[0]['id'] );
		$this->assertSame( $first, $recent[1]['id'] );
	}
}

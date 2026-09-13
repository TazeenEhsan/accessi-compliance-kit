<?php
/**
 * Tests for EmailReminder.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Tests\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\EmailReminder;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AccessibilityComplianceKitForWooCommerce\Admin\EmailReminder
 */
class EmailReminderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'__' => function ( $text ) {
					return $text;
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_handle_option_updated_schedules_when_opted_in_and_not_already_scheduled() {
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\expect( 'wp_schedule_event' )
			->once()
			->withArgs(
				function ( $timestamp, $recurrence, $hook ) {
					return is_int( $timestamp )
						&& EmailReminder::SCHEDULE === $recurrence
						&& EmailReminder::CRON_HOOK === $hook;
				}
			);

		( new EmailReminder() )->handle_option_updated(
			array( 'email_reminder_opt_in' => false ),
			array( 'email_reminder_opt_in' => true )
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_handle_option_updated_does_not_reschedule_when_already_scheduled() {
		Functions\when( 'wp_next_scheduled' )->justReturn( 12345 );
		Functions\expect( 'wp_schedule_event' )->never();

		( new EmailReminder() )->handle_option_updated(
			array( 'email_reminder_opt_in' => true ),
			array( 'email_reminder_opt_in' => true )
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_handle_option_updated_unschedules_when_opted_out() {
		Functions\when( 'wp_next_scheduled' )->justReturn( 12345 );
		Functions\expect( 'wp_unschedule_event' )->once()->with( 12345, EmailReminder::CRON_HOOK );

		( new EmailReminder() )->handle_option_updated(
			array( 'email_reminder_opt_in' => true ),
			array( 'email_reminder_opt_in' => false )
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_handle_option_updated_leaves_unscheduled_state_alone_when_opted_out() {
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\expect( 'wp_unschedule_event' )->never();

		( new EmailReminder() )->handle_option_updated(
			array( 'email_reminder_opt_in' => false ),
			array( 'email_reminder_opt_in' => false )
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_handle_option_added_schedules_when_opted_in_from_the_start() {
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\expect( 'wp_schedule_event' )->once();

		( new EmailReminder() )->handle_option_added(
			'accessibility_compliance_kit_for_woocommerce_settings',
			array( 'email_reminder_opt_in' => true )
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_unschedule_static_method_clears_a_scheduled_event() {
		Functions\when( 'wp_next_scheduled' )->justReturn( 6789 );
		Functions\expect( 'wp_unschedule_event' )->once()->with( 6789, EmailReminder::CRON_HOOK );

		EmailReminder::unschedule();

		$this->addToAssertionCount( 1 );
	}

	public function test_unschedule_static_method_is_a_noop_when_nothing_is_scheduled() {
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\expect( 'wp_unschedule_event' )->never();

		EmailReminder::unschedule();

		$this->addToAssertionCount( 1 );
	}

	public function test_add_weekly_schedule_adds_the_custom_interval() {
		$schedules = ( new EmailReminder() )->add_weekly_schedule( array() );

		$this->assertArrayHasKey( EmailReminder::SCHEDULE, $schedules );
		$this->assertSame( WEEK_IN_SECONDS, $schedules[ EmailReminder::SCHEDULE ]['interval'] );
	}

	public function test_add_weekly_schedule_does_not_clobber_an_existing_schedule() {
		$existing  = array(
			EmailReminder::SCHEDULE => array(
				'interval' => 1,
				'display'  => 'existing',
			),
		);
		$schedules = ( new EmailReminder() )->add_weekly_schedule( $existing );

		$this->assertSame( $existing, $schedules );
	}

	public function test_send_reminder_skips_when_not_opted_in() {
		Functions\when( 'get_option' )->justReturn( array( 'email_reminder_opt_in' => false ) );
		Functions\expect( 'wp_mail' )->never();

		( new EmailReminder() )->send_reminder();

		$this->addToAssertionCount( 1 );
	}

	public function test_send_reminder_sends_when_opted_in() {
		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				if ( 'accessibility_compliance_kit_for_woocommerce_settings' === $key ) {
					return array( 'email_reminder_opt_in' => true );
				}

				return 'admin_email' === $key ? 'owner@example.test' : $default;
			}
		);
		Functions\when( 'add_query_arg' )->justReturn( 'https://example.test/wp-admin/admin.php?page=accessibility-compliance-kit-for-woocommerce' );
		Functions\when( 'admin_url' )->justReturn( 'https://example.test/wp-admin/admin.php' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\expect( 'wp_mail' )->once()->with( 'owner@example.test', \Mockery::type( 'string' ), \Mockery::type( 'string' ) );

		( new EmailReminder() )->send_reminder();

		$this->addToAssertionCount( 1 );
	}
}

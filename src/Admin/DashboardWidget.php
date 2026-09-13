<?php
/**
 * WP dashboard summary widget.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Scanner\ScanStorage;
use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;

/**
 * Adds a WP dashboard widget showing the last scan date and violation counts
 * by severity, with a link to the full plugin page (proposal §4.1, §11;
 * docs/admin.md §8).
 */
class DashboardWidget {

	const WIDGET_ID = 'accessibility_compliance_kit_for_woocommerce_dashboard_widget';

	/**
	 * Scan storage service.
	 *
	 * @var ScanStorage
	 */
	private $scan_storage;

	/**
	 * Constructor.
	 *
	 * @param ScanStorage $scan_storage Scan storage service.
	 */
	public function __construct( ScanStorage $scan_storage ) {
		$this->scan_storage = $scan_storage;
	}

	/**
	 * Hook the dashboard widget registration.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	/**
	 * Register the widget for users with the scan capability.
	 *
	 * @return void
	 */
	public function add_widget() {
		if ( ! Capabilities::can_scan() ) {
			return;
		}

		wp_add_dashboard_widget(
			self::WIDGET_ID,
			__( 'Accessibility Compliance', 'accessibility-compliance-kit-for-woocommerce' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Render the widget: last scan date + severity counts, or an empty state.
	 *
	 * @return void
	 */
	public function render() {
		$scan = $this->scan_storage->get_last_scan();

		if ( ! $scan ) {
			printf(
				'<p>%s</p>',
				wp_kses_post(
					sprintf(
						/* translators: %s: link to run the first scan. */
						__( 'No scans yet. %s', 'accessibility-compliance-kit-for-woocommerce' ),
						sprintf(
							'<a href="%s">%s</a>',
							esc_url( $this->admin_page_url() ),
							esc_html__( 'Run your first scan', 'accessibility-compliance-kit-for-woocommerce' )
						)
					)
				)
			);

			return;
		}

		printf(
			'<p>%s</p>',
			esc_html(
				sprintf(
					/* translators: %s: last scan date/time in the site's timezone. */
					__( 'Last scan: %s', 'accessibility-compliance-kit-for-woocommerce' ),
					get_date_from_gmt( $scan['started_at'], 'Y-m-d H:i' )
				)
			)
		);

		$summary = $scan['summary'];

		echo '<ul class="accessibility-compliance-kit-for-woocommerce-dashboard-widget-summary">';

		foreach ( $this->severity_labels() as $key => $label ) {
			printf(
				'<li><strong>%s:</strong> %d</li>',
				esc_html( $label ),
				isset( $summary[ $key ] ) ? (int) $summary[ $key ] : 0
			);
		}

		echo '</ul>';

		printf(
			'<p><a href="%s">%s</a></p>',
			esc_url( $this->admin_page_url() ),
			esc_html__( 'View full results', 'accessibility-compliance-kit-for-woocommerce' )
		);
	}

	/**
	 * Severity keys mapped to translated labels, in display order (proposal §4.1).
	 *
	 * @return array
	 */
	private function severity_labels() {
		return array(
			'critical' => __( 'Critical', 'accessibility-compliance-kit-for-woocommerce' ),
			'serious'  => __( 'Serious', 'accessibility-compliance-kit-for-woocommerce' ),
			'moderate' => __( 'Moderate', 'accessibility-compliance-kit-for-woocommerce' ),
			'minor'    => __( 'Minor', 'accessibility-compliance-kit-for-woocommerce' ),
		);
	}

	/**
	 * Build the URL to the full plugin admin page.
	 *
	 * @return string
	 */
	private function admin_page_url() {
		return add_query_arg( 'page', AdminMenu::MENU_SLUG, admin_url( 'admin.php' ) );
	}
}

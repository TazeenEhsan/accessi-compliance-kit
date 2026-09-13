<?php
/**
 * Creates the "Accessibility Statement" WordPress page from the bundled template.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Statement;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;
use AccessibilityComplianceKitForWooCommerce\Utils\Options;

/**
 * Generates the accessibility statement page and handles the
 * `accessibility_compliance_kit_for_woocommerce_generate_statement` AJAX action (proposal §4.1, §6 Phase 3).
 */
class StatementGenerator {

	/**
	 * Hook the AJAX handler.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_generate_statement', array( $this, 'handle_generate_statement' ) );
	}

	/**
	 * Handle the "Create statement page" AJAX request from the admin app.
	 *
	 * @return void
	 */
	public function handle_generate_statement() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_generate_statement', 'nonce' );

		if ( ! Capabilities::can_manage_settings() ) {
			wp_send_json_error(
				array(
					// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
					'message' => __( 'You are not allowed to generate the accessibility statement.', 'accessibility-compliance-kit-for-woocommerce' ),
				),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$raw_force_new = isset( $_POST['force_new'] ) ? sanitize_text_field( wp_unslash( $_POST['force_new'] ) ) : '';
		$force_new     = '1' === $raw_force_new;

		$result = $this->generate( $force_new );

		if ( 'error' === $result['status'] ) {
			wp_send_json_error( array( 'message' => $result['message'] ), 500 );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Create the statement page, or report the existing one instead of
	 * duplicating it when one is already on record (docs/admin.md §7).
	 *
	 * @param bool $force_new Create a new page even if one already exists.
	 * @return array {
	 *     @type string $status   'exists', 'created', or 'error'.
	 *     @type int    $pageId   The statement page's post ID (when not an error).
	 *     @type string $editLink Edit link for the page (when not an error).
	 *     @type string $message  Error message (only when `status` is 'error').
	 * }
	 */
	public function generate( $force_new = false ) {
		$existing_id = $this->existing_page_id();

		if ( $existing_id && ! $force_new ) {
			return array(
				'status'   => 'exists',
				'pageId'   => $existing_id,
				'editLink' => get_edit_post_link( $existing_id, 'raw' ),
			);
		}

		$page_id = $this->insert_page();

		if ( is_wp_error( $page_id ) ) {
			return array(
				'status'  => 'error',
				'message' => $page_id->get_error_message(),
			);
		}

		$settings                      = Options::get_settings();
		$settings['statement_page_id'] = $page_id;
		Options::update_settings( $settings );

		return array(
			'status'   => 'created',
			'pageId'   => $page_id,
			'editLink' => get_edit_post_link( $page_id, 'raw' ),
		);
	}

	/**
	 * Look up the stored statement page ID, treating it as absent if the page
	 * no longer exists (e.g. the merchant deleted it).
	 *
	 * @return int
	 */
	private function existing_page_id() {
		$settings = Options::get_settings();
		$page_id  = isset( $settings['statement_page_id'] ) ? absint( $settings['statement_page_id'] ) : 0;

		if ( $page_id && 'page' === get_post_type( $page_id ) ) {
			return $page_id;
		}

		return 0;
	}

	/**
	 * Insert the statement page as a draft, populated from the EN template.
	 *
	 * @return int|\WP_Error Inserted page ID, or a `WP_Error` on failure.
	 */
	private function insert_page() {
		return wp_insert_post(
			array(
				'post_title'   => __( 'Accessibility Statement', 'accessibility-compliance-kit-for-woocommerce' ),
				'post_content' => $this->render_template(),
				'post_status'  => 'draft',
				'post_type'    => 'page',
			),
			true
		);
	}

	/**
	 * Build the statement content from the bundled EN template.
	 *
	 * @return string
	 */
	private function render_template() {
		require_once __DIR__ . '/templates/en.php';

		return accessibility_compliance_kit_for_woocommerce_statement_template_en(
			array(
				'site_name'        => get_bloginfo( 'name' ),
				'contact_email'    => get_option( 'admin_email' ),
				'compliance_level' => __( 'WCAG 2.1 Level AA', 'accessibility-compliance-kit-for-woocommerce' ),
				'review_date'      => date_i18n( get_option( 'date_format' ) ),
			)
		);
	}
}

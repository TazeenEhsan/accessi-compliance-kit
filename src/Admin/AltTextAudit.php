<?php
/**
 * Product image alt-text audit: list and bulk-fill missing featured-image alt text.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;

/**
 * Backs the "Alt text" tab. Lists published products whose featured image has
 * no alt text (paginated, capped), and fills the empty ones from the product
 * title on request. Never overwrites a non-empty alt. Unlike the front-end
 * ProductImageAltFix, this edits the stored attachment alt so it persists even
 * if the fix is switched off, which is why it is a deliberate, confirmed action.
 */
class AltTextAudit {

	const ALT_META_KEY = '_wp_attachment_image_alt';

	const PER_PAGE = 20;

	const MAX_OFFSET = 10000;

	const FILL_BATCH_LIMIT = 200;

	/**
	 * Hook the AJAX handlers. No `nopriv` handlers (docs/security.md §4).
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_get_alt_audit', array( $this, 'handle_get_audit' ) );
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_fill_alt', array( $this, 'handle_fill_alt' ) );
	}

	/**
	 * Return one page of products with a missing featured-image alt.
	 *
	 * @return void
	 */
	public function handle_get_audit() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_get_alt_audit', 'nonce' );
		$this->require_capability();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$page   = isset( $_POST['page'] ) ? max( 1, absint( wp_unslash( $_POST['page'] ) ) ) : 1;
		$offset = min( self::MAX_OFFSET, ( $page - 1 ) * self::PER_PAGE );

		$rows = $this->query_missing( self::PER_PAGE, $offset );

		wp_send_json_success(
			array(
				'products' => array_map( array( $this, 'format_row' ), $rows ),
				'total'    => $this->count_missing(),
				'page'     => $page,
				'per_page' => self::PER_PAGE,
			)
		);
	}

	/**
	 * Fill empty alt text from the product title (at most FILL_BATCH_LIMIT
	 * images per request) and report how many remain.
	 *
	 * @return void
	 */
	public function handle_fill_alt() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_fill_alt', 'nonce' );
		$this->require_capability();

		$filled = 0;

		foreach ( $this->query_missing( self::FILL_BATCH_LIMIT, 0 ) as $row ) {
			if ( $this->fill_one( (int) $row['thumbnail_id'], (string) $row['post_title'] ) ) {
				++$filled;
			}
		}

		wp_send_json_success(
			array(
				'filled'    => $filled,
				'remaining' => $this->count_missing(),
			)
		);
	}

	/**
	 * Set the alt of one attachment from a title, only if it is currently empty.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $title         Product title.
	 * @return bool Whether a new alt was written.
	 */
	public function fill_one( $attachment_id, $title ) {
		$alt = sanitize_text_field( wp_strip_all_tags( $title ) );

		if ( $attachment_id <= 0 || '' === $alt ) {
			return false;
		}

		$current = get_post_meta( $attachment_id, self::ALT_META_KEY, true );

		if ( '' !== trim( (string) $current ) ) {
			return false;
		}

		return (bool) update_post_meta( $attachment_id, self::ALT_META_KEY, wp_slash( $alt ) );
	}

	/**
	 * Abort with 403 unless the current user may change settings.
	 *
	 * @return void
	 */
	private function require_capability() {
		if ( ! Capabilities::can_manage_settings() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to do that.', 'tazeen-store-accessibility-kit-for-woocommerce' ) ),
				403
			);
		}
	}

	/**
	 * Shape a query row for the React table.
	 *
	 * @param array $row Query row.
	 * @return array
	 */
	private function format_row( array $row ) {
		$product_id = (int) $row['ID'];
		$image_url  = wp_get_attachment_image_url( (int) $row['thumbnail_id'], 'thumbnail' );

		return array(
			'id'       => $product_id,
			'title'    => get_the_title( $product_id ),
			'editLink' => get_edit_post_link( $product_id, 'raw' ),
			'imageUrl' => $image_url ? $image_url : '',
		);
	}

	/**
	 * Count published products whose featured image has no alt text.
	 *
	 * @return int
	 */
	private function count_missing() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- bespoke join across posts/postmeta that WP_Query cannot express; prepared via $wpdb->prepare() with %i/%s placeholders.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i p
				INNER JOIN %i t ON t.post_id = p.ID AND t.meta_key = %s
				INNER JOIN %i att ON att.ID = CAST( t.meta_value AS UNSIGNED ) AND att.post_type = %s
				WHERE p.post_type = %s AND p.post_status = %s
				AND NOT EXISTS (
					SELECT 1 FROM %i a
					WHERE a.post_id = att.ID AND a.meta_key = %s AND TRIM( a.meta_value ) <> %s
				)',
				$wpdb->posts,
				$wpdb->postmeta,
				'_thumbnail_id',
				$wpdb->posts,
				'attachment',
				'product',
				'publish',
				$wpdb->postmeta,
				self::ALT_META_KEY,
				''
			)
		);

		return (int) $count;
	}

	/**
	 * Fetch products (ID, title, featured image ID) with a missing featured-image alt.
	 *
	 * @param int $limit  Maximum rows.
	 * @param int $offset Row offset.
	 * @return array[]
	 */
	private function query_missing( $limit, $offset ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- bespoke join across posts/postmeta that WP_Query cannot express; prepared via $wpdb->prepare() with %i/%s/%d placeholders.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT p.ID, p.post_title, att.ID AS thumbnail_id FROM %i p
				INNER JOIN %i t ON t.post_id = p.ID AND t.meta_key = %s
				INNER JOIN %i att ON att.ID = CAST( t.meta_value AS UNSIGNED ) AND att.post_type = %s
				WHERE p.post_type = %s AND p.post_status = %s
				AND NOT EXISTS (
					SELECT 1 FROM %i a
					WHERE a.post_id = att.ID AND a.meta_key = %s AND TRIM( a.meta_value ) <> %s
				)
				ORDER BY p.ID ASC
				LIMIT %d OFFSET %d',
				$wpdb->posts,
				$wpdb->postmeta,
				'_thumbnail_id',
				$wpdb->posts,
				'attachment',
				'product',
				'publish',
				$wpdb->postmeta,
				self::ALT_META_KEY,
				'',
				$limit,
				$offset
			),
			ARRAY_A
		);

		return $rows ? $rows : array();
	}
}

<?php
/**
 * Fallback alt attributes for product images (proposal §4.1, §10 prompt 1).
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When a product image's `alt` attribute is empty, falls back to the product
 * title. Never overwrites a non-empty alt (docs/frontend.md §4.1).
 */
class ProductImageAltFix extends AbstractFix {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'product_image_alt';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Product image alt text', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		return __( 'Fills in missing alt text on product images using the product title.', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'product' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'maybe_add_alt' ), 10, 2 );
	}

	/**
	 * Add a product-title fallback alt when the image has none.
	 *
	 * @param array    $attr       Existing attachment image attributes.
	 * @param \WP_Post $attachment Attachment post object.
	 * @return array
	 */
	public function maybe_add_alt( $attr, $attachment ) {
		if ( ! empty( $attr['alt'] ) ) {
			return $attr;
		}

		$product = $this->product_for_attachment( $attachment->ID );

		if ( ! $product ) {
			return $attr;
		}

		$attr['alt'] = esc_attr( $product->get_name() );

		return $attr;
	}

	/**
	 * Resolve the WooCommerce product that owns this attachment, if any,
	 * from the current product loop/singular context.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return \WC_Product|null
	 */
	private function product_for_attachment( $attachment_id ) {
		global $product;

		if ( $product instanceof \WC_Product && $this->attachment_belongs_to( $attachment_id, $product ) ) {
			return $product;
		}

		global $post;

		if ( $post instanceof \WP_Post && 'product' === get_post_type( $post ) ) {
			$post_product = function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : false;

			if ( $post_product instanceof \WC_Product && $this->attachment_belongs_to( $attachment_id, $post_product ) ) {
				return $post_product;
			}
		}

		return null;
	}

	/**
	 * Whether an attachment is the product's featured image or in its gallery.
	 *
	 * @param int         $attachment_id Attachment ID.
	 * @param \WC_Product $wc_product    Product to check against.
	 * @return bool
	 */
	private function attachment_belongs_to( $attachment_id, \WC_Product $wc_product ) {
		if ( (int) $wc_product->get_image_id() === (int) $attachment_id ) {
			return true;
		}

		$gallery_ids = array_map( 'intval', $wc_product->get_gallery_image_ids() );

		return in_array( (int) $attachment_id, $gallery_ids, true );
	}
}

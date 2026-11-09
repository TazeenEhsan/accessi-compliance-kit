<?php
/**
 * Removes zoom restrictions from the viewport meta tag (WCAG 1.4.4 Resize Text).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buffers everything printed during `wp_head` and rewrites any
 * `<meta name="viewport">` tag so it no longer disables pinch-zoom
 * (`user-scalable=no|0`) or caps zoom below 200% (`maximum-scale` under 2).
 * Only restrictions are removed; other viewport directives are untouched.
 * A viewport tag printed outside `wp_head` (e.g. hard-coded before it in a
 * theme's header template) cannot be reached.
 */
class ZoomUnblockFix extends AbstractFix {

	/**
	 * Smallest `maximum-scale` that still allows 200% zoom.
	 *
	 * @var float
	 */
	const MIN_MAX_SCALE = 2.0;

	/**
	 * Output-buffer nesting level recorded when buffering started.
	 *
	 * @var int
	 */
	private $buffer_level = 0;

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'zoom_unblock';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Allow pinch-zoom', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Removes user-scalable=no and low maximum-scale limits from the viewport tag so visitors can zoom (WCAG 1.4.4).', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'global' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'wp_head', array( $this, 'start_buffer' ), 0 );
		add_action( 'wp_head', array( $this, 'end_buffer' ), PHP_INT_MAX );
	}

	/**
	 * Start buffering `wp_head` output.
	 *
	 * @return void
	 */
	public function start_buffer() {
		ob_start();
		$this->buffer_level = ob_get_level();
	}

	/**
	 * Stop buffering and print the rewritten head markup. If another plugin has
	 * opened or closed buffers in between, the nesting level no longer matches
	 * and the buffer is left alone rather than risk losing output.
	 *
	 * @return void
	 */
	public function end_buffer() {
		if ( 0 === $this->buffer_level || ob_get_level() !== $this->buffer_level ) {
			return;
		}

		$this->buffer_level = 0;
		$html               = (string) ob_get_clean();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- re-emitting wp_head output already escaped by its producers; only viewport tags are modified, and only to remove directives.
		echo $this->filter_head_html( $html );
	}

	/**
	 * Rewrite every viewport meta tag found in a chunk of head HTML.
	 *
	 * @param string $html Head HTML.
	 * @return string
	 */
	public function filter_head_html( $html ) {
		return (string) preg_replace_callback(
			'/<meta\b[^>]*\bname\s*=\s*(["\'])viewport\1[^>]*>/i',
			array( $this, 'rewrite_tag' ),
			$html
		);
	}

	/**
	 * Rewrite one viewport meta tag.
	 *
	 * @param array $matches Regex matches; index 0 is the full tag.
	 * @return string
	 */
	private function rewrite_tag( array $matches ) {
		return (string) preg_replace_callback(
			'/\bcontent\s*=\s*(["\'])(.*?)\1/is',
			function ( array $content_match ) {
				return 'content=' . $content_match[1] . $this->clean_content( $content_match[2] ) . $content_match[1];
			},
			$matches[0],
			1
		);
	}

	/**
	 * Drop zoom-blocking directives from a viewport `content` value.
	 *
	 * @param string $content Original content value.
	 * @return string
	 */
	public function clean_content( $content ) {
		$kept = array();

		foreach ( preg_split( '/[,;]/', $content ) as $directive ) {
			$directive = trim( $directive );

			if ( '' === $directive || $this->blocks_zoom( $directive ) ) {
				continue;
			}

			$kept[] = $directive;
		}

		if ( empty( $kept ) ) {
			return 'width=device-width, initial-scale=1';
		}

		return implode( ', ', $kept );
	}

	/**
	 * Whether a single `key=value` viewport directive restricts zooming.
	 *
	 * @param string $directive Trimmed directive, e.g. `maximum-scale=1`.
	 * @return bool
	 */
	private function blocks_zoom( $directive ) {
		$parts = array_map( 'trim', explode( '=', $directive, 2 ) );
		$key   = strtolower( $parts[0] );
		$value = isset( $parts[1] ) ? strtolower( $parts[1] ) : '';

		if ( 'user-scalable' === $key ) {
			return in_array( $value, array( 'no', '0', 'false' ), true );
		}

		if ( 'maximum-scale' === $key ) {
			return is_numeric( $value ) && (float) $value < self::MIN_MAX_SCALE;
		}

		return false;
	}
}

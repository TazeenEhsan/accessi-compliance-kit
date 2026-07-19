<?php
/**
 * In-plugin "User Guide" admin page: documentation, troubleshooting, and FAQ.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Fixes\FixManager;
use AccessiComplianceKit\Utils\Capabilities;

/**
 * Registers the Accessibility → User Guide submenu and renders the React
 * mount point for the guide app (`assets/js/src/guide/`), which holds the
 * documentation content (accordion sections + FAQ) plus live search and
 * expand/collapse controls. Content mirrors
 * `plugin-foundation-files/launch/docs-page.md` and `faq.md` — keep them in sync.
 *
 * Fix labels/descriptions are localized from the real fix registry so the
 * guide stays in sync with the Settings screen automatically.
 */
class GuidePage {

	const MENU_SLUG = 'accessi-compliance-kit-guide';

	const HANDLE = 'accessi-compliance-kit-guide';

	const SUPPORT_URL = 'https://wordpress.org/support/plugin/accessi-compliance-kit/';

	/**
	 * Hook the submenu registration and the conditional asset enqueue.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Register the submenu page under Accessibility → User Guide.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page(
			AdminMenu::MENU_SLUG,
			__( 'User Guide', 'accessi-compliance-kit' ),
			__( 'User Guide', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue the guide bundle and shared admin stylesheet on the guide page only.
	 *
	 * The hook prefix comes from the parent menu's (translated) title, so only
	 * the stable `_page_{slug}` suffix is compared.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function maybe_enqueue( $hook_suffix ) {
		$suffix = '_page_' . self::MENU_SLUG;

		if ( substr( $hook_suffix, -strlen( $suffix ) ) !== $suffix ) {
			return;
		}

		$asset_file = ACCESSI_COMPLIANCE_KIT_PATH . 'build/guide.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'build/guide.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( self::HANDLE, 'accessi-compliance-kit', ACCESSI_COMPLIANCE_KIT_PATH . 'languages' );

		wp_enqueue_style(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'assets/css/admin.css',
			array(),
			ACCESSI_COMPLIANCE_KIT_VERSION
		);

		wp_localize_script( self::HANDLE, 'accessiComplianceKitGuide', $this->localized_data() );
	}

	/**
	 * Render the React mount point. All UI is rendered client-side.
	 *
	 * @return void
	 */
	public function render_page() {
		echo '<div id="accessi-compliance-kit-guide-root" class="wrap accessi-compliance-kit-guide"></div>';
	}

	/**
	 * Build the data localized to the guide React app.
	 *
	 * @return array
	 */
	private function localized_data() {
		return array(
			'dashboardUrl' => admin_url( 'admin.php?page=' . AdminMenu::MENU_SLUG ),
			'supportUrl'   => self::SUPPORT_URL,
			'fixes'        => $this->fixes_data(),
		);
	}

	/**
	 * Build the fix metadata (id, label, description, contexts) the guide's
	 * "The Six Fixes" section needs, straight from the fix registry.
	 *
	 * @return array
	 */
	private function fixes_data() {
		return array_map(
			function ( $fix ) {
				return array(
					'id'          => $fix->id(),
					'label'       => $fix->label(),
					'description' => $fix->description(),
					'contexts'    => $fix->applies_to(),
				);
			},
			FixManager::all_fixes()
		);
	}
}

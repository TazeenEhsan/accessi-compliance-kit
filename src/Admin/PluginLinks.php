<?php
/**
 * Adds quick links to the plugin's row on the Plugins screen.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prepends "Dashboard" and "User Guide" action links to the plugin's row on
 * the Plugins screen, so the plugin's admin page is one click away after
 * activation.
 */
class PluginLinks {

	/**
	 * Hook the action-links filter for this plugin's row.
	 *
	 * @return void
	 */
	public function register() {
		add_filter(
			'plugin_action_links_' . plugin_basename( ACCESSI_COMPLIANCE_KIT_FILE ),
			array( $this, 'add_action_links' )
		);
	}

	/**
	 * Prepend the plugin's own links before Deactivate/Delete.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			'dashboard' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=' . AdminMenu::MENU_SLUG ) ),
				esc_html__( 'Dashboard', 'accessi-compliance-kit' )
			),
			'guide'     => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=' . GuidePage::MENU_SLUG ) ),
				esc_html__( 'User Guide', 'accessi-compliance-kit' )
			),
		);

		return array_merge( $plugin_links, (array) $links );
	}
}

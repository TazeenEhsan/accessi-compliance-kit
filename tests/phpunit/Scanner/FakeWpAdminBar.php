<?php
/**
 * Minimal `WP_Admin_Bar` double that records added nodes.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Tests\Scanner;

/**
 * Stands in for `WP_Admin_Bar` so tests can assert on nodes added via `add_node()`.
 */
class FakeWpAdminBar {

	/**
	 * Nodes added via `add_node()`, keyed by node ID.
	 *
	 * @var array[]
	 */
	public $nodes = array();

	/**
	 * Record an added node.
	 *
	 * @param array $args Node arguments.
	 * @return void
	 */
	public function add_node( array $args ) {
		$this->nodes[ $args['id'] ] = $args;
	}
}

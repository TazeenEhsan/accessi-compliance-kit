<?php
/**
 * PHPUnit bootstrap. Uses Brain Monkey to stub WordPress functions rather than
 * booting a full WP install (docs/coding-guidelines.md §4).
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

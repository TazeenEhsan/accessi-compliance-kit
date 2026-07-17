<?php
/**
 * PHPUnit bootstrap. Uses Brain Monkey to stub WordPress functions rather than
 * booting a full WP install (docs/coding-guidelines.md §4).
 *
 * @package AccessiWoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

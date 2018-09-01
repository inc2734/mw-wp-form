<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Mw_Wp_Form
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/mw-wp-form.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

if ( ! function_exists( '_delete_all_data' ) ) {
	function _delete_all_data() {
		global $wpdb;

		$tables = array(
			'posts', 'postmeta', 'comments', 'commentmeta', 'term_relationships', 'termmeta',
		);
		$wptables = array();
		foreach ( $tables as $table ) {
			if ( isset( $wpdb->$table ) ) {
				$wptables[] = $wpdb->$table;
			}
		}

		foreach ( $wptables as $table ) {
			$wpdb->query( "DELETE FROM {$table}" );
		}

		foreach ( array(
			$wpdb->terms,
			$wpdb->term_taxonomy
		) as $table ) {
			$wpdb->query( "DELETE FROM {$table} WHERE term_id != 1" );
		}

		$wpdb->query( "UPDATE {$wpdb->term_taxonomy} SET count = 0" );

		$wpdb->query( "DELETE FROM {$wpdb->users} WHERE ID != 1" );
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE user_id != 1" );
	}
}

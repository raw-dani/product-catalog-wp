<?php
/**
 * Plugin deactivation handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Deactivator {

	/**
	 * Run on plugin deactivation
	 */
	public static function deactivate() {
		// Flush rewrite rules on deactivation to clean up CPT permalinks
		flush_rewrite_rules();
	}
}

<?php
/**
 * Plugin activation handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Activator {

	/**
	 * Run on plugin activation
	 */
	public static function activate() {
		// Register post types and taxonomies so rewrite rules can be flushed properly
		require_once REJATON_CATALOG_PATH . 'includes/class-post-types.php';
		require_once REJATON_CATALOG_PATH . 'includes/class-taxonomies.php';
		require_once REJATON_CATALOG_PATH . 'includes/class-template-repository.php';

		$post_types = new Rejaton_Catalog_Post_Types();
		$post_types->register_post_types();

		$taxonomies = new Rejaton_Catalog_Taxonomies();
		$taxonomies->register_taxonomies();

		// Seed initial default template if none exists
		Rejaton_Catalog_Template_Repository::ensure_default_template();

		// Seed default settings
		self::init_default_settings();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Setup default settings in options table
	 */
	private static function init_default_settings() {
		$existing = get_option( 'rejaton_catalog_settings', false );
		if ( false === $existing ) {
			$defaults = array(
				'products_per_page'         => 12,
				'desktop_columns'           => 3,
				'card_fields'               => array( 'tipe_pompa', 'debit', 'rentang_head', 'daya_panel_surya' ),
				'spec_table_fields'         => array( 'tipe_pompa', 'debit', 'rentang_head', 'daya_panel_surya', 'debit_m3_jam' ),
				'active_filters'            => array( 'search', 'category', 'tipe_pompa', 'debit', 'head', 'daya_panel_surya' ),
				'default_sorting'           => 'date_desc',
				'empty_token_policy'        => 'blank', // 'blank' or 'dash'
				'cta_text'                  => 'Konsultasikan via WhatsApp',
				'whatsapp_number'           => '',
				'whatsapp_message_template' => 'Halo CS Rejaton, saya tertarik dengan produk {product_name} ({product_url}). Mohon info spesifikasi dan penawaran teknisnya.',
				'product_slug'              => 'produk',
				'category_slug'             => 'kategori-produk',
			);
			update_option( 'rejaton_catalog_settings', $defaults );
		}
	}
}

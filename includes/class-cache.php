<?php
/**
 * Cache and Invalidation Handler for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Cache {

	const CACHE_GROUP = 'rejaton_catalog';

	public function init() {
		// Invalidate cache when product, template, or taxonomy changes
		add_action( 'save_post_rejaton_product', array( $this, 'flush_product_cache' ), 10, 1 );
		add_action( 'save_post_rejaton_template', array( $this, 'flush_template_cache' ), 10, 1 );
		add_action( 'save_post_rejaton_product_template', array( $this, 'flush_template_cache' ), 10, 1 );
		add_action( 'update_option_rejaton_catalog_settings', array( $this, 'flush_all_cache' ) );
		add_action( 'update_option_rejaton_custom_field_definitions', array( $this, 'flush_all_cache' ) );
	}

	public function flush_product_cache( $product_id ) {
		wp_cache_delete( 'product_render_' . $product_id, self::CACHE_GROUP );
		self::purge_litespeed_post( $product_id );
	}

	public function flush_template_cache( $template_id ) {
		// When a global template changes, flush all rendered descriptions and catalog pages
		self::flush_all_cache();
	}

	public function flush_all_cache() {
		wp_cache_flush_group( self::CACHE_GROUP );
		self::purge_litespeed_all();
	}

	/**
	 * LiteSpeed Cache integration
	 */
	public static function purge_litespeed_post( $post_id ) {
		if ( defined( 'LSCWP_V' ) || has_action( 'litespeed_purge_post' ) ) {
			do_action( 'litespeed_purge_post', $post_id );
		}
		// Also purge archive tag
		if ( has_action( 'litespeed_purge' ) ) {
			do_action( 'litespeed_purge', 'rejaton_catalog_archive' );
		}
	}

	public static function purge_litespeed_tag( $tag ) {
		if ( defined( 'LSCWP_V' ) || has_action( 'litespeed_purge' ) ) {
			do_action( 'litespeed_purge', $tag );
		}
	}

	public static function purge_litespeed_all() {
		if ( defined( 'LSCWP_V' ) || has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
	}
}

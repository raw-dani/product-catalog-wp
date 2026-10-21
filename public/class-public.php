<?php
/**
 * Public facing controller for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Public {

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'load_custom_templates' ) );
	}

	/**
	 * Defer frontend script to eliminate render-blocking resources for PageSpeed Insights
	 */
	public function add_defer_attribute( $tag, $handle ) {
		if ( 'rejaton-catalog-frontend' === $handle ) {
			if ( false === strpos( $tag, 'defer' ) ) {
				$tag = str_replace( ' src', ' defer src', $tag );
			}
		}
		return $tag;
	}

	public function register_assets() {
		wp_register_style(
			'rejaton-catalog-frontend',
			REJATON_CATALOG_URL . 'public/css/catalog.css',
			array(),
			REJATON_CATALOG_VERSION
		);

		wp_register_script(
			'rejaton-catalog-frontend',
			REJATON_CATALOG_URL . 'public/js/catalog.js',
			array( 'jquery' ),
			REJATON_CATALOG_VERSION,
			true
		);

		wp_localize_script( 'rejaton-catalog-frontend', 'rejaton_ajax_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'rejaton_catalog_public_nonce' ),
			'loading'  => __( 'Memuat data produk...', 'rejaton-product-catalog' ),
		) );

		// Enqueue automatically on archive or single product
		if ( is_singular( 'rejaton_product' ) || is_post_type_archive( 'rejaton_product' ) || is_tax( 'rejaton_product_cat' ) || is_tax( 'rejaton_product_tag' ) ) {
			wp_enqueue_style( 'rejaton-catalog-frontend' );
			wp_enqueue_script( 'rejaton-catalog-frontend' );
		}
	}

	/**
	 * Template loader: allows theme/child theme override
	 */
	public function load_custom_templates( $template ) {
		if ( is_singular( 'rejaton_product' ) ) {
			$theme_template = locate_template( array( 'single-rejaton_product.php', 'rejaton/single-product.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}
			return REJATON_CATALOG_PATH . 'public/templates/single-rejaton_product.php';
		}

		if ( is_post_type_archive( 'rejaton_product' ) || is_tax( 'rejaton_product_cat' ) || is_tax( 'rejaton_product_tag' ) ) {
			$theme_template = locate_template( array( 'archive-rejaton_product.php', 'rejaton/archive-product.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}
			return REJATON_CATALOG_PATH . 'public/templates/archive-rejaton_product.php';
		}

		return $template;
	}
}

<?php
/**
 * Shortcodes for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Shortcodes {

	public function init() {
		add_shortcode( 'rejaton_product_catalog', array( $this, 'render_catalog_shortcode' ) );
		add_shortcode( 'rejaton_product_detail', array( $this, 'render_detail_shortcode' ) );
	}

	/**
	 * Shortcode [rejaton_product_catalog]
	 * Attributes:
	 *  - columns: number of desktop columns (default 3)
	 *  - per_page: number of items per page (default 12)
	 *  - category: slug of category to prefilter
	 *  - show_filters: 'true'|'false'
	 *  - show_sorting: 'true'|'false'
	 */
	public function render_catalog_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'columns'      => (int) rejaton_get_option( 'desktop_columns', 3 ),
			'per_page'     => (int) rejaton_get_option( 'products_per_page', 12 ),
			'category'     => '',
			'show_filters' => 'true',
			'show_sorting' => 'true',
		), $atts, 'rejaton_product_catalog' );

		// Enqueue scripts & styles for catalog
		wp_enqueue_style( 'rejaton-catalog-frontend' );
		wp_enqueue_script( 'rejaton-catalog-frontend' );

		// Parse initial filter params from URL or shortcode attributes
		$request_params = Rejaton_Catalog_Filter_Controller::parse_filter_request( $_GET );
		if ( ! empty( $atts['category'] ) && empty( $request_params['category'] ) ) {
			$request_params['category'] = array( sanitize_title( $atts['category'] ) );
		}
		if ( ! empty( $atts['per_page'] ) && empty( $request_params['per_page'] ) ) {
			$request_params['per_page'] = absint( $atts['per_page'] );
		}

		$query = Rejaton_Catalog_Product_Query::get_products( $request_params );

		ob_start();
		$show_filters = ( 'true' === $atts['show_filters'] );
		$show_sorting = ( 'true' === $atts['show_sorting'] );
		$desktop_cols = max( 1, min( 4, (int) $atts['columns'] ) );
		$is_shortcode = true;

		require REJATON_CATALOG_PATH . 'public/templates/archive-rejaton_product.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode [rejaton_product_detail id="123"]
	 */
	public function render_detail_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts, 'rejaton_product_detail' );

		$product_id = absint( $atts['id'] );
		if ( ! $product_id ) {
			return '';
		}

		$post = get_post( $product_id );
		if ( ! $post || 'rejaton_product' !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		wp_enqueue_style( 'rejaton-catalog-frontend' );
		wp_enqueue_script( 'rejaton-catalog-frontend' );

		ob_start();
		// Set global post temporarily
		global $post;
		$orig_post = $post;
		$post = get_post( $product_id );
		setup_postdata( $post );

		require REJATON_CATALOG_PATH . 'public/templates/single-rejaton_product.php';

		$post = $orig_post;
		if ( $post ) {
			setup_postdata( $post );
		}
		return ob_get_clean();
	}
}

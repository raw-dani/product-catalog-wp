<?php
/**
 * SEO Integration for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_SEO_Integration {

	public function init() {
		add_action( 'wp_head', array( $this, 'output_meta_tags' ), 2 );
		add_filter( 'wp_robots', array( $this, 'filter_robots_for_search' ) );
	}

	/**
	 * Output SEO canonical and OpenGraph meta if no third-party SEO plugin is handling it
	 */
	public function output_meta_tags() {
		if ( ! is_singular( 'rejaton_product' ) && ! is_post_type_archive( 'rejaton_product' ) ) {
			return;
		}

		// Check if popular SEO plugins are active to prevent duplicate metadata
		if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'All_in_One_SEO_Pack' ) ) {
			return;
		}

		if ( is_singular( 'rejaton_product' ) ) {
			global $post;
			$product_id = $post->ID;
			$title      = get_the_title( $product_id ) . ' - ' . get_bloginfo( 'name' );
			$excerpt    = wp_strip_all_tags( get_the_excerpt( $product_id ) );
			$permalink  = get_permalink( $product_id );
			$thumb_id   = get_post_thumbnail_id( $product_id );
			$thumb_url  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';

			echo "\n<!-- Rejaton Product Catalog SEO -->\n";
			if ( $excerpt ) {
				echo '<meta name="description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
			}
			echo '<link rel="canonical" href="' . esc_url( $permalink ) . '" />' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
			if ( $excerpt ) {
				echo '<meta property="og:description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
			}
			echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />' . "\n";
			echo '<meta property="og:type" content="product" />' . "\n";
			if ( $thumb_url ) {
				echo '<meta property="og:image" content="' . esc_url( $thumb_url ) . '" />' . "\n";
			}
			echo "<!-- / Rejaton Product Catalog SEO -->\n\n";
		}
	}

	/**
	 * Set noindex,follow on heavily filtered/search archive URLs to avoid search engine index bloating
	 */
	public function filter_robots_for_search( $robots ) {
		if ( is_post_type_archive( 'rejaton_product' ) || is_tax( 'rejaton_product_cat' ) ) {
			// If URL contains filter parameters
			if ( ! empty( $_GET['debit_min'] ) || ! empty( $_GET['head_min'] ) || ! empty( $_GET['s_prod'] ) || ! empty( $_GET['daya_min'] ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
			}
		}
		return $robots;
	}
}

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
		add_action( 'wp_head', array( $this, 'output_schema_jsonld' ), 3 );
		add_filter( 'wp_robots', array( $this, 'filter_robots_for_search' ) );
	}

	/**
	 * Output SEO canonical, OpenGraph, and Twitter Cards meta if no third-party SEO plugin is active
	 */
	public function output_meta_tags() {
		if ( ! is_singular( 'rejaton_product' ) && ! is_post_type_archive( 'rejaton_product' ) && ! is_tax( 'rejaton_product_cat' ) && ! is_tax( 'rejaton_product_tag' ) ) {
			return;
		}

		// Prevent duplicate tags if major SEO plugins are active
		if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'All_in_One_SEO_Pack' ) || function_exists( 'seopress_init' ) ) {
			return;
		}

		if ( is_singular( 'rejaton_product' ) ) {
			global $post;
			$product_id = $post->ID;
			$title      = get_the_title( $product_id ) . ' | ' . get_bloginfo( 'name' );
			$raw_desc   = get_the_excerpt( $product_id );
			if ( empty( $raw_desc ) ) {
				$raw_desc = wp_strip_all_tags( $post->post_content );
			}
			$desc       = wp_trim_words( wp_strip_all_tags( $raw_desc ), 26, '...' );
			$permalink  = get_permalink( $product_id );
			$thumb_id   = get_post_thumbnail_id( $product_id );
			$thumb_url  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';

			echo "\n<!-- Rejaton Product Catalog SEO -->\n";
			if ( $desc ) {
				echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
			}
			echo '<link rel="canonical" href="' . esc_url( $permalink ) . '" />' . "\n";

			// OpenGraph
			echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";
			echo '<meta property="og:type" content="product" />' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
			if ( $desc ) {
				echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
			}
			echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
			if ( $thumb_url ) {
				echo '<meta property="og:image" content="' . esc_url( $thumb_url ) . '" />' . "\n";
				echo '<meta property="og:image:secure_url" content="' . esc_url( $thumb_url ) . '" />' . "\n";
			}

			// Twitter Card
			echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
			if ( $desc ) {
				echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
			}
			if ( $thumb_url ) {
				echo '<meta name="twitter:image" content="' . esc_url( $thumb_url ) . '" />' . "\n";
			}
			echo "<!-- / Rejaton Product Catalog SEO -->\n\n";
		} elseif ( is_post_type_archive( 'rejaton_product' ) ) {
			$title     = __( 'Katalog Produk Pompa Air Tenaga Surya', 'rejaton-product-catalog' ) . ' | ' . get_bloginfo( 'name' );
			$desc      = __( 'Katalog lengkap spesifikasi pompa air tenaga surya resmi, submersible, dan sistem solar pumping untuk pengadaan pertanian serta air bersih.', 'rejaton-product-catalog' );
			$permalink = get_post_type_archive_link( 'rejaton_product' );

			echo "\n<!-- Rejaton Product Catalog Archive SEO -->\n";
			echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
			echo '<link rel="canonical" href="' . esc_url( $permalink ) . '" />' . "\n";
			echo '<meta property="og:type" content="website" />' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
			echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
			echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />' . "\n";
			echo "<!-- / Rejaton Product Catalog Archive SEO -->\n\n";
		}
	}

	/**
	 * Output JSON-LD Structured Data (Schema.org Product & BreadcrumbList) for Google Search Rich Results
	 */
	public function output_schema_jsonld() {
		if ( ! is_singular( 'rejaton_product' ) ) {
			return;
		}

		global $post;
		$product_id = $post->ID;
		$title      = get_the_title( $product_id );
		$permalink  = get_permalink( $product_id );
		$thumb_id   = get_post_thumbnail_id( $product_id );
		$thumb_url  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : '';

		$desc = get_the_excerpt( $product_id );
		if ( empty( $desc ) ) {
			$desc = wp_strip_all_tags( $post->post_content );
		}
		$desc = wp_trim_words( wp_strip_all_tags( $desc ), 30, '...' );

		// Custom specifications from Rejaton Meta
		$tipe_pompa = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'tipe_pompa', '' );
		$debit      = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'debit', '' );
		$head_min   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_min', '' );
		$head_max   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_max', '' );
		$daya_panel = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'daya_panel_surya', '' );
		$head_range = rejaton_format_head_range( $head_min, $head_max, 'meter' );

		// Category
		$cats = get_the_terms( $product_id, 'rejaton_product_cat' );
		$cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

		// Product schema
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $title,
			'description' => $desc,
			'url'         => $permalink,
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => 'Rejaton',
			),
			'offers'      => array(
				'@type'         => 'Offer',
				'url'           => $permalink,
				'priceCurrency' => 'IDR',
				'price'         => '0',
				'availability'  => 'https://schema.org/InStock',
				'itemCondition' => 'https://schema.org/NewCondition',
			),
		);

		if ( $thumb_url ) {
			$schema['image'] = array( $thumb_url );
		}

		if ( $cat_name ) {
			$schema['category'] = $cat_name;
		}

		// Additional Property specs for Google Search Rich Snippets
		$additional_properties = array();
		if ( ! empty( $tipe_pompa ) ) {
			$additional_properties[] = array(
				'@type' => 'PropertyValue',
				'name'  => 'Tipe Pompa',
				'value' => $tipe_pompa,
			);
		}
		if ( ! empty( $debit ) ) {
			$additional_properties[] = array(
				'@type' => 'PropertyValue',
				'name'  => 'Debit Air (liter/detik)',
				'value' => $debit . ' l/s',
			);
		}
		if ( ! empty( $head_range ) ) {
			$additional_properties[] = array(
				'@type' => 'PropertyValue',
				'name'  => 'Rentang Head',
				'value' => $head_range,
			);
		}
		if ( ! empty( $daya_panel ) ) {
			$additional_properties[] = array(
				'@type' => 'PropertyValue',
				'name'  => 'Daya Panel Surya',
				'value' => $daya_panel . ' kWp',
			);
		}
		if ( ! empty( $additional_properties ) ) {
			$schema['additionalProperty'] = $additional_properties;
		}

		// BreadcrumbList schema
		$breadcrumb_schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => __( 'Beranda', 'rejaton-product-catalog' ),
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => __( 'Katalog Produk', 'rejaton-product-catalog' ),
					'item'     => get_post_type_archive_link( 'rejaton_product' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => $title,
					'item'     => $permalink,
				),
			),
		);

		echo "\n<!-- Rejaton Product Schema JSON-LD -->\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		echo "<!-- / Rejaton Product Schema JSON-LD -->\n\n";
	}

	/**
	 * Set noindex,follow on filtered/search query URLs to prevent duplicate content index bloat
	 */
	public function filter_robots_for_search( $robots ) {
		if ( is_post_type_archive( 'rejaton_product' ) || is_tax( 'rejaton_product_cat' ) || is_tax( 'rejaton_product_tag' ) ) {
			if ( ! empty( $_GET['s_prod'] ) || ! empty( $_GET['sort'] ) || ! empty( $_GET['cat'] ) || ! empty( $_GET['debit_min'] ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
			}
		}
		return $robots;
	}
}

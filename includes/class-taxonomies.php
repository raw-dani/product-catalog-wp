<?php
/**
 * Register Taxonomies for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Taxonomies {

	/**
	 * Register actions
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register taxonomies
	 */
	public function register_taxonomies() {
		$cat_slug = rejaton_get_option( 'category_slug', 'kategori-produk' );
		if ( empty( $cat_slug ) ) {
			$cat_slug = 'kategori-produk';
		}

		// Kategori Produk (Hierarkis)
		$cat_labels = array(
			'name'              => _x( 'Kategori Produk', 'taxonomy general name', 'rejaton-product-catalog' ),
			'singular_name'     => _x( 'Kategori Produk', 'taxonomy singular name', 'rejaton-product-catalog' ),
			'search_items'      => __( 'Cari Kategori', 'rejaton-product-catalog' ),
			'all_items'         => __( 'Semua Kategori', 'rejaton-product-catalog' ),
			'parent_item'       => __( 'Kategori Induk', 'rejaton-product-catalog' ),
			'parent_item_colon' => __( 'Kategori Induk:', 'rejaton-product-catalog' ),
			'edit_item'         => __( 'Edit Kategori', 'rejaton-product-catalog' ),
			'update_item'       => __( 'Perbarui Kategori', 'rejaton-product-catalog' ),
			'add_new_item'      => __( 'Tambah Kategori Baru', 'rejaton-product-catalog' ),
			'new_item_name'     => __( 'Nama Kategori Baru', 'rejaton-product-catalog' ),
			'menu_name'         => __( 'Kategori Produk', 'rejaton-product-catalog' ),
		);

		$cat_args = array(
			'hierarchical'      => true,
			'labels'            => $cat_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'         => $cat_slug,
				'with_front'   => false,
				'hierarchical' => true,
			),
			'show_in_rest'      => false,
		);

		register_taxonomy( 'rejaton_product_cat', array( 'rejaton_product' ), $cat_args );

		// Tag Produk (Non-hierarkis)
		$tag_labels = array(
			'name'          => _x( 'Tag Produk', 'taxonomy general name', 'rejaton-product-catalog' ),
			'singular_name' => _x( 'Tag Produk', 'taxonomy singular name', 'rejaton-product-catalog' ),
			'search_items'  => __( 'Cari Tag', 'rejaton-product-catalog' ),
			'all_items'     => __( 'Semua Tag', 'rejaton-product-catalog' ),
			'edit_item'     => __( 'Edit Tag', 'rejaton-product-catalog' ),
			'update_item'   => __( 'Perbarui Tag', 'rejaton-product-catalog' ),
			'add_new_item'  => __( 'Tambah Tag Baru', 'rejaton-product-catalog' ),
			'new_item_name' => __( 'Nama Tag Baru', 'rejaton-product-catalog' ),
			'menu_name'     => __( 'Tag Produk', 'rejaton-product-catalog' ),
		);

		$tag_args = array(
			'hierarchical'      => false,
			'labels'            => $tag_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'tag-produk',
				'with_front' => false,
			),
			'show_in_rest'      => false,
		);

		register_taxonomy( 'rejaton_product_tag', array( 'rejaton_product' ), $tag_args );
	}
}

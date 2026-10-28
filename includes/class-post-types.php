<?php
/**
 * Register Custom Post Types for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Post_Types {

	/**
	 * Register actions
	 */
	public function init() {
		// Run early on init so edit.php and post-new.php recognise the post type
		add_action( 'init', array( $this, 'register_post_types' ), 5 );
	}

	/**
	 * Register rejaton_product and rejaton_product_template
	 */
	public function register_post_types() {
		$product_slug = rejaton_get_option( 'product_slug', 'produk' );
		if ( empty( $product_slug ) ) {
			$product_slug = 'produk';
		}

		// 1. CPT rejaton_product
		$product_labels = array(
			'name'                  => _x( 'Produk Rejaton', 'Post type general name', 'rejaton-product-catalog' ),
			'singular_name'         => _x( 'Produk', 'Post type singular name', 'rejaton-product-catalog' ),
			'menu_name'             => _x( 'Katalog Rejaton', 'Admin Menu text', 'rejaton-product-catalog' ),
			'name_admin_bar'        => _x( 'Produk Rejaton', 'Add New on Toolbar', 'rejaton-product-catalog' ),
			'add_new'               => __( 'Tambah Produk', 'rejaton-product-catalog' ),
			'add_new_item'          => __( 'Tambah Produk Baru', 'rejaton-product-catalog' ),
			'new_item'              => __( 'Produk Baru', 'rejaton-product-catalog' ),
			'edit_item'             => __( 'Edit Produk', 'rejaton-product-catalog' ),
			'view_item'             => __( 'Lihat Produk', 'rejaton-product-catalog' ),
			'all_items'             => __( 'Semua Produk', 'rejaton-product-catalog' ),
			'search_items'          => __( 'Cari Produk', 'rejaton-product-catalog' ),
			'parent_item_colon'     => __( 'Induk Produk:', 'rejaton-product-catalog' ),
			'not_found'             => __( 'Tidak ada produk ditemukan.', 'rejaton-product-catalog' ),
			'not_found_in_trash'    => __( 'Tidak ada produk di kotak sampah.', 'rejaton-product-catalog' ),
			'featured_image'        => __( 'Foto Utama Produk', 'rejaton-product-catalog' ),
			'set_featured_image'    => __( 'Pilih foto utama produk', 'rejaton-product-catalog' ),
			'remove_featured_image' => __( 'Hapus foto utama', 'rejaton-product-catalog' ),
			'use_featured_image'    => __( 'Gunakan sebagai foto utama', 'rejaton-product-catalog' ),
		);

		$product_args = array(
			'labels'             => $product_labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => $product_slug,
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => $product_slug,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-clipboard',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'       => false, // Keamanan: Lindungi dari exposed REST API publik
		);

		register_post_type( 'rejaton_product', $product_args );

		// 2. Private CPT rejaton_product_template
		$template_labels = array(
			'name'               => _x( 'Template Deskripsi', 'Post type general name', 'rejaton-product-catalog' ),
			'singular_name'      => _x( 'Template', 'Post type singular name', 'rejaton-product-catalog' ),
			'menu_name'          => _x( 'Template Deskripsi', 'Admin Menu text', 'rejaton-product-catalog' ),
			'name_admin_bar'     => _x( 'Template Deskripsi', 'Add New on Toolbar', 'rejaton-product-catalog' ),
			'add_new'            => __( 'Tambah Template', 'rejaton-product-catalog' ),
			'add_new_item'       => __( 'Tambah Template Baru', 'rejaton-product-catalog' ),
			'edit_item'          => __( 'Edit Template', 'rejaton-product-catalog' ),
			'new_item'           => __( 'Template Baru', 'rejaton-product-catalog' ),
			'view_item'          => __( 'Lihat Template', 'rejaton-product-catalog' ),
			'all_items'          => __( 'Template Deskripsi', 'rejaton-product-catalog' ),
			'search_items'       => __( 'Cari Template', 'rejaton-product-catalog' ),
			'not_found'          => __( 'Tidak ada template ditemukan.', 'rejaton-product-catalog' ),
			'not_found_in_trash' => __( 'Tidak ada template di kotak sampah.', 'rejaton-product-catalog' ),
		);

		$template_args = array(
			'labels'              => $template_labels,
			'public'              => true, // Required by WordPress Core so edit.php and post-new.php are permitted
			'publicly_queryable'  => false, // Keeps frontend visitors from accessing template single URLs
			'exclude_from_search' => true,  // Hides from front-end site search
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=rejaton_product', // Nest neatly inside Katalog Rejaton
			'query_var'           => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor' ),
			'show_in_rest'        => false,
		);

		register_post_type( 'rejaton_product_template', $template_args );
	}
}

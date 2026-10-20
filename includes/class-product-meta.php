<?php
/**
 * Product Meta Handler for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Product_Meta {

	const PREFIX = '_rj_';

	/**
	 * Get product meta value by key
	 *
	 * @param int $product_id
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get_meta( $product_id, $key, $default = '' ) {
		$meta_key = self::PREFIX . $key;
		$val = get_post_meta( $product_id, $meta_key, true );
		if ( '' === $val || null === $val ) {
			return $default;
		}
		return $val;
	}

	/**
	 * Set product meta value
	 *
	 * @param int $product_id
	 * @param string $key
	 * @param mixed $value
	 * @return bool|int
	 */
	public static function set_meta( $product_id, $key, $value ) {
		$meta_key = self::PREFIX . $key;
		return update_post_meta( $product_id, $meta_key, $value );
	}

	/**
	 * Delete product meta
	 *
	 * @param int $product_id
	 * @param string $key
	 * @return bool
	 */
	public static function delete_meta( $product_id, $key ) {
		$meta_key = self::PREFIX . $key;
		return delete_post_meta( $product_id, $meta_key );
	}

	/**
	 * Get all resolved field values for a product (including derived calculations)
	 *
	 * @param int $product_id
	 * @return array
	 */
	public static function get_resolved_values( $product_id ) {
		$values = array();

		// Fetch standard system fields
		$tipe_pompa       = self::get_meta( $product_id, 'tipe_pompa', '' );
		$debit            = self::get_meta( $product_id, 'debit', '' );
		$head_min         = self::get_meta( $product_id, 'head_min', '' );
		$head_max         = self::get_meta( $product_id, 'head_max', '' );
		$daya_panel_surya = self::get_meta( $product_id, 'daya_panel_surya', '' );

		$values['tipe_pompa']       = $tipe_pompa;
		$values['debit']            = $debit;
		$values['head_min']         = $head_min;
		$values['head_max']         = $head_max;
		$values['daya_panel_surya'] = $daya_panel_surya;

		// Derived: Rentang Head
		$values['rentang_head'] = rejaton_format_head_range( $head_min, $head_max, '' );

		// Derived: Debit m3/jam
		$m3_jam = rejaton_calculate_debit_m3_jam( $debit );
		$values['debit_m3_jam'] = ( null !== $m3_jam ) ? $m3_jam : '';

		// Custom fields
		$custom_defs = Rejaton_Catalog_Field_Registry::get_custom_fields();
		foreach ( $custom_defs as $key => $def ) {
			$values[ $key ] = self::get_meta( $product_id, 'custom_' . $key, '' );
		}

		// Also product title & summary
		$post = get_post( $product_id );
		if ( $post ) {
			$values['product_name']    = $post->post_title;
			$values['product_summary'] = $post->post_excerpt;
		}

		return $values;
	}

	/**
	 * Get product gallery attachment IDs
	 *
	 * @param int $product_id
	 * @return array
	 */
	public static function get_gallery_ids( $product_id ) {
		$raw = self::get_meta( $product_id, 'product_gallery', array() );
		if ( is_string( $raw ) && ! empty( $raw ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
			return array_values( $ids );
		}
		if ( is_array( $raw ) ) {
			return array_filter( array_map( 'absint', $raw ) );
		}
		return array();
	}
}

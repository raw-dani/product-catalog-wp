<?php
/**
 * Custom Field Registry and Manager for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Field_Registry {

	const OPTION_CUSTOM_FIELDS = 'rejaton_custom_field_definitions';

	/**
	 * Get standard initial technical fields required by specification
	 *
	 * @return array
	 */
	public static function get_initial_fields() {
		return array(
			'tipe_pompa'        => array(
				'key'          => 'tipe_pompa',
				'label'        => __( 'Tipe Pompa', 'rejaton-product-catalog' ),
				'type'         => 'text',
				'unit'         => '',
				'token'        => '[Tipe Pompa]',
				'required'     => false,
				'description'  => __( 'Model atau tipe spesifik pompa air.', 'rejaton-product-catalog' ),
				'in_card'      => true,
				'in_filter'    => true,
				'in_spec'      => true,
				'is_system'    => true,
			),
			'debit'             => array(
				'key'          => 'debit',
				'label'        => __( 'Debit Air', 'rejaton-product-catalog' ),
				'type'         => 'number',
				'unit'         => 'liter/detik',
				'token'        => '[Debit]',
				'required'     => false,
				'description'  => __( 'Debit dalam liter/detik. Digunakan juga untuk menghitung m³/jam.', 'rejaton-product-catalog' ),
				'in_card'      => true,
				'in_filter'    => true,
				'in_spec'      => true,
				'is_system'    => true,
			),
			'head_min'          => array(
				'key'          => 'head_min',
				'label'        => __( 'Head Minimum', 'rejaton-product-catalog' ),
				'type'         => 'number',
				'unit'         => 'meter',
				'token'        => '',
				'required'     => false,
				'description'  => __( 'Head minimum pompa dalam satuan meter.', 'rejaton-product-catalog' ),
				'in_card'      => false,
				'in_filter'    => true,
				'in_spec'      => false,
				'is_system'    => true,
			),
			'head_max'          => array(
				'key'          => 'head_max',
				'label'        => __( 'Head Maksimum', 'rejaton-product-catalog' ),
				'type'         => 'number',
				'unit'         => 'meter',
				'token'        => '',
				'required'     => false,
				'description'  => __( 'Head maksimum pompa dalam satuan meter.', 'rejaton-product-catalog' ),
				'in_card'      => false,
				'in_filter'    => true,
				'in_spec'      => false,
				'is_system'    => true,
			),
			'rentang_head'      => array(
				'key'          => 'rentang_head',
				'label'        => __( 'Rentang Head', 'rejaton-product-catalog' ),
				'type'         => 'derived',
				'unit'         => 'meter',
				'token'        => '[Rentang Head]',
				'required'     => false,
				'description'  => __( 'Otomatis terbentuk dari Head Min dan Head Max.', 'rejaton-product-catalog' ),
				'in_card'      => true,
				'in_filter'    => false,
				'in_spec'      => true,
				'is_system'    => true,
			),
			'daya_panel_surya'  => array(
				'key'          => 'daya_panel_surya',
				'label'        => __( 'Daya Panel Surya', 'rejaton-product-catalog' ),
				'type'         => 'number',
				'unit'         => 'kWp',
				'token'        => '[Daya Panel Surya]',
				'required'     => false,
				'description'  => __( 'Rekomendasi kapasitas daya panel surya dalam satuan kWp.', 'rejaton-product-catalog' ),
				'in_card'      => true,
				'in_filter'    => true,
				'in_spec'      => true,
				'is_system'    => true,
			),
			'debit_m3_jam'      => array(
				'key'          => 'debit_m3_jam',
				'label'        => __( 'Debit m³/jam', 'rejaton-product-catalog' ),
				'type'         => 'derived',
				'unit'         => 'm³/jam',
				'token'        => '[Debit × 3,6]',
				'required'     => false,
				'description'  => __( 'Otomatis dihitung dari Debit liter/detik × 3,6.', 'rejaton-product-catalog' ),
				'in_card'      => false,
				'in_filter'    => false,
				'in_spec'      => true,
				'is_system'    => true,
			),
		);
	}

	/**
	 * Get custom user-defined field definitions
	 *
	 * @return array
	 */
	public static function get_custom_fields() {
		$custom = get_option( self::OPTION_CUSTOM_FIELDS, array() );
		return is_array( $custom ) ? $custom : array();
	}

	/**
	 * Save custom field definitions
	 *
	 * @param array $fields
	 * @return bool
	 */
	public static function save_custom_fields( array $fields ) {
		return update_option( self::OPTION_CUSTOM_FIELDS, $fields );
	}

	/**
	 * Get all active fields (system initial + custom user-defined)
	 *
	 * @return array
	 */
	public static function get_all_fields() {
		$initial = self::get_initial_fields();
		$custom  = self::get_custom_fields();
		return array_merge( $initial, $custom );
	}

	/**
	 * Get field definition by key
	 *
	 * @param string $key
	 * @return array|null
	 */
	public static function get_field( $key ) {
		$all = self::get_all_fields();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * Get all available template tokens mapped to their field keys or derived callbacks
	 *
	 * @return array token => array( 'key' => '', 'label' => '', 'unit' => '', 'type' => '' )
	 */
	public static function get_token_map() {
		$tokens = array(
			'[Tipe Pompa]'        => array( 'key' => 'tipe_pompa', 'label' => 'Tipe Pompa', 'unit' => '' ),
			'[Debit]'             => array( 'key' => 'debit', 'label' => 'Debit', 'unit' => 'liter/detik' ),
			'[Rentang Head]'      => array( 'key' => 'rentang_head', 'label' => 'Rentang Head', 'unit' => 'meter' ),
			'[Daya Panel Surya]'  => array( 'key' => 'daya_panel_surya', 'label' => 'Daya Panel Surya', 'unit' => 'kWp' ),
			'[Debit × 3,6]'       => array( 'key' => 'debit_m3_jam', 'label' => 'Debit m³/jam', 'unit' => 'm³/jam' ),
		);

		// Also register dynamic custom fields token if available
		$custom = self::get_custom_fields();
		foreach ( $custom as $field_key => $def ) {
			if ( ! empty( $def['token'] ) ) {
				$tokens[ $def['token'] ] = array(
					'key'   => $field_key,
					'label' => $def['label'],
					'unit'  => isset( $def['unit'] ) ? $def['unit'] : '',
				);
			}
		}

		return $tokens;
	}
}

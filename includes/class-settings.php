<?php
/**
 * Settings Manager for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Settings {

	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_rejaton_catalog_settings', array( $this, 'on_settings_updated' ), 10, 2 );
	}

	public function register_settings() {
		register_setting( 'rejaton_catalog_settings_group', 'rejaton_catalog_settings', array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );
	}

	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['products_per_page'] = isset( $input['products_per_page'] ) ? max( 1, absint( $input['products_per_page'] ) ) : 12;
		$sanitized['desktop_columns']   = isset( $input['desktop_columns'] ) ? max( 1, min( 4, absint( $input['desktop_columns'] ) ) ) : 3;

		// Card fields
		if ( isset( $input['card_fields'] ) && is_array( $input['card_fields'] ) ) {
			$sanitized['card_fields'] = array_map( 'sanitize_key', $input['card_fields'] );
		} else {
			$sanitized['card_fields'] = array();
		}

		// Spec table fields
		if ( isset( $input['spec_table_fields'] ) && is_array( $input['spec_table_fields'] ) ) {
			$sanitized['spec_table_fields'] = array_map( 'sanitize_key', $input['spec_table_fields'] );
		} else {
			$sanitized['spec_table_fields'] = array();
		}

		// Active filters
		if ( isset( $input['active_filters'] ) && is_array( $input['active_filters'] ) ) {
			$sanitized['active_filters'] = array_map( 'sanitize_key', $input['active_filters'] );
		} else {
			$sanitized['active_filters'] = array();
		}

		$sanitized['default_sorting'] = isset( $input['default_sorting'] ) ? sanitize_text_field( $input['default_sorting'] ) : 'date_desc';
		$sanitized['empty_token_policy'] = ( isset( $input['empty_token_policy'] ) && 'dash' === $input['empty_token_policy'] ) ? 'dash' : 'blank';

		$sanitized['cta_text'] = isset( $input['cta_text'] ) ? sanitize_text_field( $input['cta_text'] ) : 'Konsultasikan via WhatsApp';
		$sanitized['whatsapp_number'] = isset( $input['whatsapp_number'] ) ? sanitize_text_field( $input['whatsapp_number'] ) : '';
		$sanitized['whatsapp_message_template'] = isset( $input['whatsapp_message_template'] ) ? sanitize_textarea_field( $input['whatsapp_message_template'] ) : '';

		$sanitized['product_slug']  = ! empty( $input['product_slug'] ) ? sanitize_title( $input['product_slug'] ) : 'produk';
		$sanitized['category_slug'] = ! empty( $input['category_slug'] ) ? sanitize_title( $input['category_slug'] ) : 'kategori-produk';

		return $sanitized;
	}

	/**
	 * Flush rewrite rules if slug settings have been updated
	 *
	 * @param array $old_value
	 * @param array $value
	 */
	public function on_settings_updated( $old_value, $value ) {
		$old_product_slug = isset( $old_value['product_slug'] ) ? $old_value['product_slug'] : 'produk';
		$new_product_slug = isset( $value['product_slug'] ) ? $value['product_slug'] : 'produk';

		$old_category_slug = isset( $old_value['category_slug'] ) ? $old_value['category_slug'] : 'kategori-produk';
		$new_category_slug = isset( $value['category_slug'] ) ? $value['category_slug'] : 'kategori-produk';

		if ( $old_product_slug !== $new_product_slug || $old_category_slug !== $new_category_slug ) {
			// Flush rewrite rules agar URL baru segera aktif tanpa error 404
			flush_rewrite_rules();
		}
	}
}

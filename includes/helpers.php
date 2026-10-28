<?php
/**
 * Helper functions for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate m3/hour from liters/second.
 * Formula: m3/hour = liters/sec * 3.6
 *
 * @param float|int|string $liters_per_second
 * @param int $decimals
 * @return float|null
 */
function rejaton_calculate_debit_m3_jam( $liters_per_second, $decimals = 2 ) {
	if ( '' === $liters_per_second || null === $liters_per_second ) {
		return null;
	}

	if ( is_string( $liters_per_second ) ) {
		$liters_per_second = str_replace( ',', '.', trim( $liters_per_second ) );
	}

	if ( ! is_numeric( $liters_per_second ) ) {
		return null;
	}

	$val = (float) $liters_per_second;
	if ( $val < 0 ) {
		return null;
	}

	$result = $val * 3.6;
	return round( $result, $decimals );
}

/**
 * Format numeric value for Indonesian locale display.
 * Example: 2.5 -> 2,5 or 9.00 -> 9
 *
 * @param float|int|string $number
 * @param int $max_decimals
 * @return string
 */
function rejaton_format_number_locale( $number, $max_decimals = 2 ) {
	if ( '' === $number || null === $number || ! is_numeric( $number ) ) {
		return '';
	}

	$val = (float) $number;
	// Remove trailing zeros if whole number
	if ( floor( $val ) == $val ) {
		return number_format( $val, 0, ',', '.' );
	}

	return number_format( $val, $max_decimals, ',', '.' );
}

/**
 * Format head range string from min and max.
 *
 * @param float|int|string $head_min
 * @param float|int|string $head_max
 * @param string $unit
 * @return string
 */
function rejaton_format_head_range( $head_min, $head_max, $unit = 'meter' ) {
	$has_min = ( '' !== $head_min && null !== $head_min && is_numeric( $head_min ) );
	$has_max = ( '' !== $head_max && null !== $head_max && is_numeric( $head_max ) );

	if ( $has_min && $has_max ) {
		$min = rejaton_format_number_locale( $head_min );
		$max = rejaton_format_number_locale( $head_max );
		if ( (float) $head_min === (float) $head_max ) {
			return $max . ( $unit ? ' ' . $unit : '' );
		}
		return $min . '–' . $max . ( $unit ? ' ' . $unit : '' );
	} elseif ( $has_max ) {
		return rejaton_format_number_locale( $head_max ) . ( $unit ? ' ' . $unit : '' );
	} elseif ( $has_min ) {
		return rejaton_format_number_locale( $head_min ) . ( $unit ? ' ' . $unit : '' );
	}

	return '';
}

if ( ! function_exists( 'rejaton_get_option' ) ) {
	/**
	 * Get plugin option with default fallback.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function rejaton_get_option( $key, $default = false ) {
		$options = get_option( 'rejaton_catalog_settings', array() );
		if ( is_array( $options ) && isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}
		return $default;
	}
}

/**
 * Build WhatsApp CTA link with prefilled URL-encoded message.
 *
 * @param int $product_id
 * @param string $phone_number
 * @param string $custom_message
 * @return string
 */
function rejaton_get_whatsapp_url( $product_id = 0, $phone_number = '', $custom_message = '' ) {
	if ( empty( $phone_number ) ) {
		$phone_number = rejaton_get_option( 'whatsapp_number', '' );
	}

	// Sanitize phone number (keep digits only)
	$clean_phone = preg_replace( '/[^0-9]/', '', $phone_number );
	if ( empty( $clean_phone ) ) {
		return '';
	}

	// Normalize Indonesian 08xx to 628xx
	if ( substr( $clean_phone, 0, 1 ) === '0' ) {
		$clean_phone = '62' . substr( $clean_phone, 1 );
	}

	if ( empty( $custom_message ) ) {
		$template_msg = rejaton_get_option( 'whatsapp_message_template', 'Halo CS Rejaton, saya ingin berkonsultasi mengenai produk {product_name} ({product_url}).' );
		$product_name = $product_id ? get_the_title( $product_id ) : 'Katalog Produk';
		$product_url  = $product_id ? get_permalink( $product_id ) : home_url();

		$custom_message = str_replace(
			array( '{product_name}', '{product_url}' ),
			array( $product_name, $product_url ),
			$template_msg
		);
	}

	return 'https://wa.me/' . $clean_phone . '?text=' . rawurlencode( $custom_message );
}

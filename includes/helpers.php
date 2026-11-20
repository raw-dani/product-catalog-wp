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
 * Calculate liters/second from m3/hour.
 * Formula: liters/sec = (m3/hour) / 3.6
 *
 * @param float|int|string $m3_per_jam
 * @param int $decimals
 * @return float|null
 */
function rejaton_calculate_debit_liter_per_detik( $m3_per_jam, $decimals = 2 ) {
	if ( '' === $m3_per_jam || null === $m3_per_jam ) {
		return null;
	}

	if ( is_string( $m3_per_jam ) ) {
		$m3_per_jam = str_replace( ',', '.', trim( $m3_per_jam ) );
	}

	if ( ! is_numeric( $m3_per_jam ) ) {
		return null;
	}

	$val = (float) $m3_per_jam;
	if ( $val < 0 ) {
		return null;
	}

	$result = $val / 3.6;
	return round( $result, $decimals );
}

/**
 * Get Indonesian month name (1-12)
 *
 * @param int|null $month_num 1 to 12. If null, current month.
 * @return string
 */
function rejaton_get_indonesian_month_name( $month_num = null ) {
	if ( null === $month_num ) {
		if ( function_exists( 'current_datetime' ) ) {
			$month_num = (int) current_datetime()->format( 'n' );
		} else {
			$month_num = (int) date_i18n( 'n' );
		}
	}

	$months = array(
		1  => 'Januari',
		2  => 'Februari',
		3  => 'Maret',
		4  => 'April',
		5  => 'Mei',
		6  => 'Juni',
		7  => 'Juli',
		8  => 'Agustus',
		9  => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);

	$month_num = (int) $month_num;
	return isset( $months[ $month_num ] ) ? $months[ $month_num ] : '';
}

/**
 * Get current year in site timezone
 *
 * @return string
 */
function rejaton_get_current_year() {
	if ( function_exists( 'current_datetime' ) ) {
		return current_datetime()->format( 'Y' );
	}
	return date_i18n( 'Y' );
}

/**
 * Format numeric value for Indonesian locale display.
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

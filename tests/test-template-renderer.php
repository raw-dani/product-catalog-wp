<?php
/**
 * Standalone Unit Tests for Rejaton Product Catalog
 * Tests token parsing, debit conversion, head range formatting, and conditional blocks.
 */

define( 'ABSPATH', true );

// Mock WordPress functions if running standalone via CLI
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $str ) {
		return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $str ) {
		// Mock strip harmful tags
		return strip_tags( (string) $str, '<h3><h4><p><ol><ul><li><strong><em><a><span><br><table><thead><tbody><tr><th><td>' );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $key, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $key, $value ) {
		return true;
	}
}

if ( ! function_exists( 'rejaton_get_option' ) ) {
	function rejaton_get_option( $key, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = '' ) {
		return $text;
	}
}

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/class-field-registry.php';
require_once __DIR__ . '/../includes/class-template-validator.php';
require_once __DIR__ . '/../includes/class-template-renderer.php';

$tests_passed = 0;
$tests_total = 0;

function assert_test( $condition, $message ) {
	global $tests_passed, $tests_total;
	$tests_total++;
	if ( $condition ) {
		$tests_passed++;
		echo " [PASS] " . $message . "\n";
	} else {
		echo " [FAIL] " . $message . "\n";
	}
}

echo "=== Menjalankan Unit Test Rejaton Product Catalog ===\n\n";

// 1. Test Debit Conversion Formula: 2.5 liter/detik -> 9 m3/jam
$debit_res = rejaton_calculate_debit_m3_jam( 2.5 );
assert_test( 9.0 === (float) $debit_res, 'Debit 2.5 L/detik menghasilkan tepat 9.0 m³/jam' );

$debit_res_str = rejaton_calculate_debit_m3_jam( '2,5' );
assert_test( 9.0 === (float) $debit_res_str, 'Debit string dengan koma "2,5" dikonversi benar menjadi 9.0' );

$debit_zero = rejaton_calculate_debit_m3_jam( 0 );
assert_test( 0.0 === (float) $debit_zero, 'Debit 0 liter/detik valid dan menghasilkan 0.0' );

$debit_negative = rejaton_calculate_debit_m3_jam( -5 );
assert_test( null === $debit_negative, 'Debit negatif ditolak dan mengembalikan null' );

$debit_invalid = rejaton_calculate_debit_m3_jam( 'abc' );
assert_test( null === $debit_invalid, 'Debit non-numerik mengembalikan null' );

// 2. Test Format Head Range
$range_both = rejaton_format_head_range( 40, 80, 'meter' );
assert_test( '40–80 meter' === $range_both, 'Head min 40 dan max 80 diformat menjadi "40–80 meter"' );

$range_same = rejaton_format_head_range( 50, 50, 'meter' );
assert_test( '50 meter' === $range_same, 'Head min 50 dan max 50 menghasilkan "50 meter"' );

$range_min_only = rejaton_format_head_range( 30, '', 'meter' );
assert_test( '30 meter' === $range_min_only, 'Head hanya min diformat "30 meter"' );

$range_max_only = rejaton_format_head_range( '', 120, 'meter' );
assert_test( '120 meter' === $range_max_only, 'Head hanya max diformat "120 meter"' );

// 3. Test Template Token Validator
$valid_template = 'Pompa [Tipe Pompa] debit [Debit] dan [Rentang Head]';
$val_res = Rejaton_Catalog_Template_Validator::validate_content( $valid_template );
assert_test( count( $val_res['valid'] ) === 3 && empty( $val_res['invalid'] ), 'Validator mengenali semua token standar yang valid' );

$invalid_template = 'Pompa [Tipe Pompa] dengan [Token Asal Asalan]';
$val_res_inv = Rejaton_Catalog_Template_Validator::validate_content( $invalid_template );
assert_test( in_array( '[Token Asal Asalan]', $val_res_inv['invalid'] ), 'Validator mendeteksi token tidak dikenal [Token Asal Asalan]' );

// 4. Test Template Renderer & Sanitization
$template_content = '<p>Pompa [Tipe Pompa] memiliki debit [Debit] l/detik ([Debit × 3,6] m³/jam) dan daya [Daya Panel Surya] kWp.</p>';
$data = array(
	'tipe_pompa'       => 'PS2-1800 CS-F',
	'debit'            => 2.5,
	'debit_m3_jam'     => 9,
	'daya_panel_surya' => 1.8,
	'rentang_head'     => '40–80 meter',
);

$rendered = Rejaton_Catalog_Template_Renderer::parse_content( $template_content, $data, false );
assert_test( strpos( $rendered, 'PS2-1800 CS-F' ) !== false, 'Render menghasilkan nama tipe pompa yang tepat' );
assert_test( strpos( $rendered, '9 m³/jam' ) !== false, 'Render menghasilkan nilai debit m³/jam yang tepat' );
assert_test( strpos( $rendered, '1,8' ) !== false || strpos( $rendered, '1.8' ) !== false, 'Render menampilkan format angka daya yang tepat' );

// 5. Test Conditional Block [IF:field]
$cond_template = 'Pompa [Tipe Pompa][IF:daya_panel_surya] Rekomendasi Solar: [Daya Panel Surya] kWp[/IF]';
$rendered_with_val = Rejaton_Catalog_Template_Renderer::parse_content( $cond_template, $data, false );
assert_test( strpos( $rendered_with_val, 'Rekomendasi Solar:' ) !== false && strpos( $rendered_with_val, 'kWp' ) !== false, 'Conditional [IF] tampil saat field berisi nilai' );

$data_without_power = $data;
unset( $data_without_power['daya_panel_surya'] );
$rendered_without_val = Rejaton_Catalog_Template_Renderer::parse_content( $cond_template, $data_without_power, false );
assert_test( strpos( $rendered_without_val, 'Rekomendasi Solar' ) === false, 'Conditional [IF] disembunyikan jika field kosong' );

echo "\nHasil: $tests_passed / $tests_total test berhasil dijalankan.\n";

if ( $tests_passed === $tests_total ) {
	echo "SEMUA TEST BERHASIL LULUS! [OK]\n";
	exit( 0 );
} else {
	echo "BEBERAPA TEST GAGAL! [ERROR]\n";
	exit( 1 );
}

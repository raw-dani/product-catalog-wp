<?php
/**
 * Template Validator for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Template_Validator {

	/**
	 * Extract all tokens in bracket format like [Token Name] or [IF:field] from content
	 *
	 * @param string $content
	 * @return array
	 */
	public static function extract_tokens( $content ) {
		$tokens = array();
		// Match standard tokens: [Something]
		if ( preg_match_all( '/\[([A-Za-z0-9_\x{00C0}-\x{00FF}\s\.\,\*\-\/\+\:\×]+)\]/u', $content, $matches ) ) {
			$tokens = array_unique( $matches[0] );
		}
		return $tokens;
	}

	/**
	 * Validate tokens in a template content
	 *
	 * @param string $content
	 * @return array array( 'valid' => array(), 'invalid' => array(), 'conditionals' => array() )
	 */
	public static function validate_content( $content ) {
		$extracted = self::extract_tokens( $content );
		$token_map = Rejaton_Catalog_Field_Registry::get_token_map();

		$valid        = array();
		$invalid      = array();
		$conditionals = array();

		foreach ( $extracted as $token_str ) {
			// Check if conditional tag [IF:xxx] or [/IF]
			if ( preg_match( '/^\[IF:([a-z0-9_]+)\]$/i', $token_str, $cm ) ) {
				$conditionals[] = $cm[1];
				continue;
			}
			if ( '[/IF]' === strtoupper( $token_str ) ) {
				continue;
			}

			// Check known tokens
			if ( isset( $token_map[ $token_str ] ) ) {
				$valid[] = $token_str;
			} else {
				$invalid[] = $token_str;
			}
		}

		return array(
			'valid'        => array_unique( $valid ),
			'invalid'      => array_unique( $invalid ),
			'conditionals' => array_unique( $conditionals ),
		);
	}

	/**
	 * Check missing required fields for a product given a template
	 *
	 * @param int $product_id
	 * @param string $content
	 * @return array
	 */
	public static function check_product_missing_fields( $product_id, $content ) {
		$validation = self::validate_content( $content );
		$token_map  = Rejaton_Catalog_Field_Registry::get_token_map();
		$values     = Rejaton_Catalog_Product_Meta::get_resolved_values( $product_id );

		$missing = array();

		foreach ( $validation['valid'] as $token_str ) {
			if ( ! isset( $token_map[ $token_str ] ) ) {
				continue;
			}
			$field_info = $token_map[ $token_str ];
			$key        = $field_info['key'];
			$val        = isset( $values[ $key ] ) ? $values[ $key ] : '';

			if ( '' === $val || null === $val ) {
				$missing[] = array(
					'token' => $token_str,
					'label' => $field_info['label'],
					'key'   => $key,
				);
			}
		}

		return $missing;
	}
}

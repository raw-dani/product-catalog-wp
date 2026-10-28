<?php
/**
 * Safe Template Renderer for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Template_Renderer {

	/**
	 * Render template for a given product ID
	 *
	 * @param int $product_id
	 * @param int|null $template_id Optional specific template ID
	 * @param bool $is_preview Whether this is for admin preview
	 * @return string Rendered safe HTML
	 */
	public static function render( $product_id, $template_id = null, $is_preview = false ) {
		if ( ! $template_id ) {
			$template_id = Rejaton_Catalog_Template_Repository::get_product_template_id( $product_id );
		}

		$template_post = Rejaton_Catalog_Template_Repository::get_template( $template_id );
		if ( ! $template_post ) {
			// Fallback to default content
			$raw_content = Rejaton_Catalog_Template_Repository::get_initial_template_content();
		} else {
			$raw_content = $template_post->post_content;
		}

		$resolved_values = Rejaton_Catalog_Product_Meta::get_resolved_values( $product_id );
		return self::parse_content( $raw_content, $resolved_values, $is_preview );
	}

	/**
	 * Parse content with resolved values and replace tokens securely
	 *
	 * @param string $content
	 * @param array $values
	 * @param bool $is_preview
	 * @return string
	 */
	public static function parse_content( $content, array $values, $is_preview = false ) {
		if ( empty( $content ) ) {
			return '';
		}

		$empty_policy = rejaton_get_option( 'empty_token_policy', 'blank' ); // 'blank' or 'dash'
		$token_map    = Rejaton_Catalog_Field_Registry::get_token_map();

		// 0. Auto-clean legacy debit text patterns from older templates (e.g., "[Debit] liter/detik atau [Debit × 3,6] m³/jam")
		$content = preg_replace(
			'/\[Debit\]\s*(?:liter\/detik|l\/detik|lps)\s*(?:atau|\/)\s*\[Debit\s*(?:×|\*|x)\s*3\,?6\]\s*m[³3]\/jam/iu',
			'[Debit] m³/jam',
			$content
		);
		$content = preg_replace(
			'/\[Debit\]\s*(?:liter\/detik|l\/detik|lps)/iu',
			'[Debit] m³/jam',
			$content
		);

		// 1. Process conditional blocks: [IF:field_key]...[/IF]
		$content = preg_replace_callback( '/\[IF:([a-z0-9_]+)\]([\s\S]*?)\[\/IF\]/i', function( $matches ) use ( $values ) {
			$field_key = $matches[1];
			$inner_content = $matches[2];

			$has_value = ( isset( $values[ $field_key ] ) && '' !== $values[ $field_key ] && null !== $values[ $field_key ] );

			return $has_value ? $inner_content : '';
		}, $content );

		// 2. Process recognized tokens
		foreach ( $token_map as $token => $info ) {
			$field_key = $info['key'];
			$raw_val   = isset( $values[ $field_key ] ) ? $values[ $field_key ] : '';

			if ( '' !== $raw_val && null !== $raw_val ) {
				// Format numeric values
				if ( is_numeric( $raw_val ) ) {
					$display_val = rejaton_format_number_locale( $raw_val );
				} else {
					$display_val = esc_html( (string) $raw_val );
				}
			} else {
				$display_val = ( 'dash' === $empty_policy ) ? '—' : '';
			}

			$content = str_replace( $token, $display_val, $content );
		}

		// 3. Handle unknown or remaining tokens
		if ( $is_preview ) {
			// In preview mode: highlight unknown tokens with warning tag
			$content = preg_replace( '/(\[[A-Za-z0-9_\x{00C0}-\x{00FF}\s\.\,\*\-\/\+\:\×]+\])/u', '<span class="rj-unknown-token-badge" style="background:#fff1f0;color:#cf1322;padding:2px 6px;border-radius:4px;border:1px dashed #ffa39e;font-size:12px;" title="Token tidak dikenal">$1</span>', $content );
		} else {
			// In public frontend: clean up unknown tokens to prevent leaking raw brackets
			$content = preg_replace( '/(\[[A-Za-z0-9_\x{00C0}-\x{00FF}\s\.\,\*\-\/\+\:\×]+\])/u', '', $content );
		}

		// 4. Sanitize final HTML through WordPress allowed post tags
		return wp_kses_post( $content );
	}
}

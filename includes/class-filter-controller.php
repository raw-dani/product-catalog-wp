<?php
/**
 * Filter Controller and AJAX Handler for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Filter_Controller {

	public function init() {
		// Public AJAX actions for filtering
		add_action( 'wp_ajax_rejaton_filter_products', array( $this, 'ajax_filter_products' ) );
		add_action( 'wp_ajax_nopriv_rejaton_filter_products', array( $this, 'ajax_filter_products' ) );
	}

	/**
	 * Parse request GET/POST parameters safely
	 *
	 * @param array $request
	 * @return array
	 */
	public static function parse_filter_request( array $request ) {
		$params = array();

		if ( ! empty( $request['s_prod'] ) ) {
			$params['search'] = sanitize_text_field( wp_unslash( $request['s_prod'] ) );
		}

		if ( ! empty( $request['cat'] ) ) {
			if ( is_array( $request['cat'] ) ) {
				$params['category'] = array_map( 'sanitize_title', wp_unslash( $request['cat'] ) );
			} else {
				$params['category'] = array( sanitize_title( wp_unslash( $request['cat'] ) ) );
			}
		}

		if ( ! empty( $request['tipe_pompa'] ) ) {
			$params['tipe_pompa'] = sanitize_text_field( wp_unslash( $request['tipe_pompa'] ) );
		}

		if ( isset( $request['debit_min'] ) && '' !== $request['debit_min'] ) {
			$params['debit_min'] = floatval( $request['debit_min'] );
		}
		if ( isset( $request['debit_max'] ) && '' !== $request['debit_max'] ) {
			$params['debit_max'] = floatval( $request['debit_max'] );
		}

		if ( isset( $request['head_min'] ) && '' !== $request['head_min'] ) {
			$params['head_min'] = floatval( $request['head_min'] );
		}
		if ( isset( $request['head_max'] ) && '' !== $request['head_max'] ) {
			$params['head_max'] = floatval( $request['head_max'] );
		}

		if ( isset( $request['daya_min'] ) && '' !== $request['daya_min'] ) {
			$params['daya_min'] = floatval( $request['daya_min'] );
		}
		if ( isset( $request['daya_max'] ) && '' !== $request['daya_max'] ) {
			$params['daya_max'] = floatval( $request['daya_max'] );
		}

		if ( ! empty( $request['sort'] ) ) {
			$params['sorting'] = sanitize_text_field( wp_unslash( $request['sort'] ) );
		}

		if ( ! empty( $request['paged'] ) ) {
			$params['paged'] = max( 1, absint( $request['paged'] ) );
		}

		if ( ! empty( $request['per_page'] ) ) {
			$params['per_page'] = absint( $request['per_page'] );
		}

		return $params;
	}

	/**
	 * Handle AJAX filtering request
	 */
	public function ajax_filter_products() {
		check_ajax_referer( 'rejaton_catalog_public_nonce', 'nonce' );

		$params = self::parse_filter_request( $_REQUEST );
		$query  = Rejaton_Catalog_Product_Query::get_products( $params );

		ob_start();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				require REJATON_CATALOG_PATH . 'public/templates/parts/product-card.php';
			}
			wp_reset_postdata();
		} else {
			echo '<div class="rj-empty-state"><p>' . esc_html__( 'Tidak ada produk yang sesuai dengan filter atau kata kunci yang dipilih.', 'rejaton-product-catalog' ) . '</p></div>';
		}
		$grid_html = ob_get_clean();

		// Render pagination
		$total_pages = $query->max_num_pages;
		$current_paged = isset( $params['paged'] ) ? $params['paged'] : 1;
		$pagination_html = self::render_ajax_pagination( $current_paged, $total_pages );

		wp_send_json_success( array(
			'grid_html'       => $grid_html,
			'pagination_html' => $pagination_html,
			'total_found'     => $query->found_posts,
		) );
	}

	/**
	 * Helper for AJAX pagination buttons
	 */
	public static function render_ajax_pagination( $current, $total ) {
		if ( $total <= 1 ) {
			return '';
		}

		$output = '<div class="rj-pagination" role="navigation">';
		for ( $i = 1; $i <= $total; $i++ ) {
			$active_class = ( $i == $current ) ? ' active' : '';
			$output .= sprintf(
				'<button type="button" class="rj-page-btn%s" data-page="%d">%d</button>',
				$active_class,
				$i,
				$i
			);
		}
		$output .= '</div>';

		return $output;
	}
}

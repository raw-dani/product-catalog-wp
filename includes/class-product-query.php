<?php
/**
 * Product Query Engine for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Product_Query {

	/**
	 * Build WP_Query arguments based on filter and sorting parameters
	 *
	 * @param array $params
	 * @return WP_Query
	 */
	public static function get_products( array $params = array() ) {
		$paged = isset( $params['paged'] ) ? max( 1, absint( $params['paged'] ) ) : 1;
		$per_page = isset( $params['per_page'] ) ? absint( $params['per_page'] ) : (int) rejaton_get_option( 'products_per_page', 12 );
		if ( $per_page <= 0 ) {
			$per_page = 12;
		}

		$args = array(
			'post_type'      => 'rejaton_product',
			'post_status'    => 'publish',
			'paged'          => $paged,
			'posts_per_page' => $per_page,
		);

		// 1. Keyword search
		if ( ! empty( $params['search'] ) ) {
			$args['s'] = sanitize_text_field( $params['search'] );
		}

		// 2. Tax query (Categories & Tags)
		$tax_query = array();
		if ( ! empty( $params['category'] ) ) {
			$cats = is_array( $params['category'] ) ? array_map( 'sanitize_title', $params['category'] ) : array( sanitize_title( $params['category'] ) );
			$tax_query[] = array(
				'taxonomy' => 'rejaton_product_cat',
				'field'    => 'slug',
				'terms'    => $cats,
				'operator' => 'IN',
			);
		}

		if ( ! empty( $params['tag'] ) ) {
			$tags = is_array( $params['tag'] ) ? array_map( 'sanitize_title', $params['tag'] ) : array( sanitize_title( $params['tag'] ) );
			$tax_query[] = array(
				'taxonomy' => 'rejaton_product_tag',
				'field'    => 'slug',
				'terms'    => $tags,
				'operator' => 'IN',
			);
		}

		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		// 3. Meta query (Numeric range filters: debit, head, solar power, tipe_pompa)
		$meta_query = array();

		// Tipe Pompa text filter
		if ( ! empty( $params['tipe_pompa'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_tipe_pompa',
				'value'   => sanitize_text_field( $params['tipe_pompa'] ),
				'compare' => 'LIKE',
			);
		}

		// Debit range filter
		if ( isset( $params['debit_min'] ) && '' !== $params['debit_min'] && is_numeric( $params['debit_min'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_debit',
				'value'   => (float) $params['debit_min'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '>=',
			);
		}
		if ( isset( $params['debit_max'] ) && '' !== $params['debit_max'] && is_numeric( $params['debit_max'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_debit',
				'value'   => (float) $params['debit_max'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '<=',
			);
		}

		// Head range filter: matches if product min/max intersects filter range
		if ( isset( $params['head_min'] ) && '' !== $params['head_min'] && is_numeric( $params['head_min'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_head_max',
				'value'   => (float) $params['head_min'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '>=',
			);
		}
		if ( isset( $params['head_max'] ) && '' !== $params['head_max'] && is_numeric( $params['head_max'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_head_min',
				'value'   => (float) $params['head_max'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '<=',
			);
		}

		// Solar panel power (daya_panel_surya)
		if ( isset( $params['daya_min'] ) && '' !== $params['daya_min'] && is_numeric( $params['daya_min'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_daya_panel_surya',
				'value'   => (float) $params['daya_min'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '>=',
			);
		}
		if ( isset( $params['daya_max'] ) && '' !== $params['daya_max'] && is_numeric( $params['daya_max'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_daya_panel_surya',
				'value'   => (float) $params['daya_max'],
				'type'    => 'DECIMAL(10,2)',
				'compare' => '<=',
			);
		}

		// Featured product filter
		if ( ! empty( $params['featured'] ) ) {
			$meta_query[] = array(
				'key'     => '_rj_featured',
				'value'   => '1',
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$args['meta_query'] = $meta_query;
		}

		// 4. Sorting
		$sorting = isset( $params['sorting'] ) ? sanitize_text_field( $params['sorting'] ) : rejaton_get_option( 'default_sorting', 'date_desc' );
		switch ( $sorting ) {
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'debit_asc':
				$args['meta_query'] = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
				$args['meta_query']['debit_clause'] = array(
					'key'     => '_rj_debit',
					'type'    => 'DECIMAL(10,2)',
					'compare' => 'EXISTS',
				);
				$args['orderby'] = array(
					'debit_clause' => 'ASC',
					'date'         => 'DESC',
				);
				break;
			case 'debit_desc':
				$args['meta_query'] = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
				$args['meta_query']['debit_clause'] = array(
					'key'     => '_rj_debit',
					'type'    => 'DECIMAL(10,2)',
					'compare' => 'EXISTS',
				);
				$args['orderby'] = array(
					'debit_clause' => 'DESC',
					'date'         => 'DESC',
				);
				break;
			case 'date_asc':
				$args['orderby'] = 'date';
				$args['order']   = 'ASC';
				break;
			case 'date_desc':
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
		}

		return new WP_Query( $args );
	}
}

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

		// 1. Keyword search (Judul, Konten, Excerpt, Custom Fields & Template Deskripsi)
		if ( ! empty( $params['search'] ) ) {
			$search_keyword = sanitize_text_field( $params['search'] );
			$matching_ids = self::get_matching_product_ids( $search_keyword );

			if ( empty( $matching_ids ) ) {
				// Tidak ada produk yang cocok sama sekali
				$args['post__in'] = array( 0 );
			} else {
				$args['post__in'] = $matching_ids;
			}
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

		// Debit tier filter (1 s/d 10 Liter/detik)
		// Tiap tier N memiliki rentang: N * 3.6 <= _rj_debit < (N + 1) * 3.6
		// Gunakan query langsung ke postmeta untuk performa tinggi & reliabilitas multi-pilihan (OR).
		if ( ! empty( $params['debit_tier'] ) ) {
			$tiers = is_array( $params['debit_tier'] ) ? $params['debit_tier'] : array( $params['debit_tier'] );
			$tier_ids = self::get_product_ids_by_debit_tiers( $tiers );

			if ( empty( $tier_ids ) ) {
				$args['post__in'] = array( 0 );
			} elseif ( isset( $args['post__in'] ) ) {
				$intersect = array_intersect( $args['post__in'], $tier_ids );
				$args['post__in'] = ! empty( $intersect ) ? array_values( $intersect ) : array( 0 );
			} else {
				$args['post__in'] = $tier_ids;
			}
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

	/**
	 * Find matching product IDs across Title, Content, Excerpt, Custom Fields, and Dynamic Template Descriptions
	 *
	 * @param string $keyword
	 * @return array Array of matched post IDs
	 */
	public static function get_matching_product_ids( $keyword ) {
		global $wpdb;

		$keyword = trim( $keyword );
		if ( empty( $keyword ) ) {
			return array();
		}

		$like = '%' . $wpdb->esc_like( $keyword ) . '%';

		// 1. Search in posts table (post_title, post_content, post_excerpt) for rejaton_product
		$post_ids_sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'rejaton_product'
			AND post_status = 'publish'
			AND (post_title LIKE %s OR post_content LIKE %s OR post_excerpt LIKE %s)",
			$like,
			$like,
			$like
		);
		$post_matched_ids = $wpdb->get_col( $post_ids_sql );
		$matched_ids = array_map( 'absint', (array) $post_matched_ids );

		// 2. Search in postmeta table (_rj_* fields like tipe_pompa, custom fields, dll)
		$meta_ids_sql = $wpdb->prepare(
			"SELECT DISTINCT pm.post_id FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.post_type = 'rejaton_product'
			AND p.post_status = 'publish'
			AND pm.meta_key LIKE '_rj_%%'
			AND pm.meta_value LIKE %s",
			$like
		);
		$meta_matched_ids = $wpdb->get_col( $meta_ids_sql );
		if ( ! empty( $meta_matched_ids ) ) {
			$matched_ids = array_merge( $matched_ids, array_map( 'absint', $meta_matched_ids ) );
		}

		// 3. Search in dynamic template source (if template contains keywords)
		$template_ids_sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'rejaton_template'
			AND post_status = 'publish'
			AND post_content LIKE %s",
			$like
		);
		$matching_template_ids = $wpdb->get_col( $template_ids_sql );
		if ( ! empty( $matching_template_ids ) ) {
			// Find products using these templates or products using default template if default matched
			$default_template_id = (int) rejaton_get_option( 'default_template_id', 0 );
			$escaped_tpl_ids = implode( ',', array_map( 'absint', $matching_template_ids ) );

			$assigned_prod_ids = $wpdb->get_col(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_rj_template_id'
				AND meta_value IN ($escaped_tpl_ids)"
			);
			if ( ! empty( $assigned_prod_ids ) ) {
				$matched_ids = array_merge( $matched_ids, array_map( 'absint', $assigned_prod_ids ) );
			}

			if ( in_array( $default_template_id, array_map( 'absint', $matching_template_ids ), true ) ) {
				// Products without custom template inherit default template
				$default_prod_ids = $wpdb->get_col(
					"SELECT p.ID FROM {$wpdb->posts} p
					LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_rj_template_id')
					WHERE p.post_type = 'rejaton_product' AND p.post_status = 'publish'
					AND (pm.meta_value IS NULL OR pm.meta_value = '' OR pm.meta_value = '0')"
				);
				if ( ! empty( $default_prod_ids ) ) {
					$matched_ids = array_merge( $matched_ids, array_map( 'absint', $default_prod_ids ) );
				}
			}
		}

		return array_values( array_unique( $matched_ids ) );
	}

	/**
	 * Get product IDs matching one or more debit tiers (1 s/d 10 Liter/detik)
	 *
	 * @param array $tiers
	 * @return array Array of post IDs
	 */
	public static function get_product_ids_by_debit_tiers( array $tiers ) {
		global $wpdb;

		$tier_conditions = array();
		foreach ( $tiers as $tier ) {
			$tier = absint( $tier );
			if ( $tier < 1 || $tier > 10 ) {
				continue;
			}
			$min_m3 = round( $tier * 3.6, 2 );
			$max_m3 = round( ( $tier + 1 ) * 3.6, 2 );

			$tier_conditions[] = $wpdb->prepare(
				'(CAST(pm.meta_value AS DECIMAL(10,2)) >= %f AND CAST(pm.meta_value AS DECIMAL(10,2)) < %f)',
				$min_m3,
				$max_m3
			);
		}

		if ( empty( $tier_conditions ) ) {
			return array();
		}

		$where_tiers = implode( ' OR ', $tier_conditions );
		$sql = "SELECT DISTINCT pm.post_id FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.post_type = 'rejaton_product'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_rj_debit'
			AND ($where_tiers)";

		$ids = $wpdb->get_col( $sql );
		return ! empty( $ids ) ? array_map( 'absint', $ids ) : array();
	}
}

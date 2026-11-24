<?php
/**
 * Catalog Archive Template for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// In case this file is loaded directly via archive template (not via shortcode)
if ( empty( $is_shortcode ) ) {
	$params = Rejaton_Catalog_Filter_Controller::parse_filter_request( $_GET );
	$query  = Rejaton_Catalog_Product_Query::get_products( $params );
	$show_filters = true;
	$show_sorting = true;
	$desktop_cols = (int) rejaton_get_option( 'desktop_columns', 3 );
	get_header();
}

$all_cats = get_terms( array(
	'taxonomy'   => 'rejaton_product_cat',
	'hide_empty' => true,
) );

$selected_cats   = isset( $params['category'] ) ? (array) $params['category'] : array();
$selected_debit  = isset( $params['debit_tier'] ) ? (array) $params['debit_tier'] : array();
$grid_class = 'rj-product-grid rj-cols-' . esc_attr( isset( $desktop_cols ) ? $desktop_cols : 3 );

$debit_tiers = array(
	1  => '1 Liter/detik (3,6 m³/jam)',
	2  => '2 Liter/detik (7,2 m³/jam)',
	3  => '3 Liter/detik (10,8 m³/jam)',
	4  => '4 Liter/detik (14,4 m³/jam)',
	5  => '5 Liter/detik (18,0 m³/jam)',
	6  => '6 Liter/detik (21,6 m³/jam)',
	7  => '7 Liter/detik (25,2 m³/jam)',
	8  => '8 Liter/detik (28,8 m³/jam)',
	9  => '9 Liter/detik (32,4 m³/jam)',
	10 => '10 Liter/detik (36,0 m³/jam)',
);
?>

<div class="rj-catalog-wrapper" id="rj-catalog-root">
	<div class="rj-catalog-header">
		<h1 class="rj-catalog-title"><?php esc_html_e( 'Katalog Produk Pompa Surya Rejaton', 'rejaton-product-catalog' ); ?></h1>
		<p class="rj-catalog-subtitle"><?php esc_html_e( 'Solusi pengadaan pompa air tenaga surya resmi LORENTZ & sistem hybrid untuk irigasi, air bersih, dan proyek perpipaan.', 'rejaton-product-catalog' ); ?></p>
	</div>

	<!-- Mobile Filter Drawer Toggle Button & Status Bar -->
	<div class="rj-mobile-filter-bar">
		<button type="button" class="rj-btn rj-btn-mobile-filter" id="rj-open-mobile-filter" aria-label="<?php esc_attr_e( 'Buka Filter & Spesifikasi Produk', 'rejaton-product-catalog' ); ?>">
			<svg class="rj-btn-icon" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="4" y1="21" x2="4" y2="14"></line>
				<line x1="4" y1="10" x2="4" y2="3"></line>
				<line x1="12" y1="21" x2="12" y2="12"></line>
				<line x1="12" y1="8" x2="12" y2="3"></line>
				<line x1="20" y1="21" x2="20" y2="16"></line>
				<line x1="20" y1="12" x2="20" y2="3"></line>
				<line x1="1" y1="14" x2="7" y2="14"></line>
				<line x1="9" y1="8" x2="15" y2="8"></line>
				<line x1="17" y1="16" x2="23" y2="16"></line>
			</svg>
			<span class="rj-btn-text"><?php esc_html_e( 'Filter & Spesifikasi', 'rejaton-product-catalog' ); ?></span>
			<?php
			$active_filter_count = 0;
			if ( ! empty( $selected_debit ) ) {
				$active_filter_count += count( $selected_debit );
			}
			if ( ! empty( $selected_cats ) ) {
				$active_filter_count += count( $selected_cats );
			}
			if ( ! empty( $params['search'] ) ) {
				$active_filter_count += 1;
			}
			?>
			<span class="rj-filter-badge" id="rj-mobile-filter-badge" <?php echo ( $active_filter_count > 0 ) ? '' : 'style="display:none;"'; ?>><?php echo esc_html( $active_filter_count ); ?></span>
		</button>
		<div class="rj-mobile-result-count">
			<span class="rj-count-num"><?php echo esc_html( $query->found_posts ); ?></span> <?php esc_html_e( 'produk', 'rejaton-product-catalog' ); ?>
		</div>
	</div>

	<div class="rj-catalog-layout">
		<!-- Sidebar Filters (Left Side) -->
		<?php if ( ! empty( $show_filters ) ) : ?>
			<aside class="rj-catalog-sidebar" id="rj-catalog-sidebar">
				<div class="rj-sidebar-inner">
					<div class="rj-sidebar-header-mobile">
						<h3><?php esc_html_e( 'Filter Produk', 'rejaton-product-catalog' ); ?></h3>
						<button type="button" class="rj-close-mobile-filter" id="rj-close-mobile-filter">&times;</button>
					</div>

					<form id="rj-filter-form" class="rj-filter-form" method="get">
						<?php if ( ! empty( $params['per_page'] ) ) : ?>
							<input type="hidden" name="per_page" value="<?php echo esc_attr( $params['per_page'] ); ?>" />
						<?php endif; ?>
						<!-- Keyword Search -->
						<div class="rj-filter-group">
							<label for="rj_search_input" class="rj-filter-label"><?php esc_html_e( 'Pencarian Model / Seri', 'rejaton-product-catalog' ); ?></label>
							<div class="rj-search-input-wrap">
								<input type="text" name="s_prod" id="rj_search_input" value="<?php echo esc_attr( isset( $params['search'] ) ? $params['search'] : '' ); ?>" placeholder="Contoh: PS2, Submersible..." />
								<div class="rj-input-spinner" id="rj-search-spinner" style="display:none;" aria-hidden="true"></div>
							</div>
						</div>

						<!-- Debit Range / Kapasitas Debit Filter -->
						<div class="rj-filter-group">							
							<div class="rj-filter-checklist">
								<?php foreach ( $debit_tiers as $tier_val => $tier_label ) : ?>
									<label class="rj-checkbox-label">
										<input type="checkbox" name="debit_tier[]" value="<?php echo esc_attr( $tier_val ); ?>" <?php checked( in_array( $tier_val, $selected_debit ) ); ?> />
										<span><?php echo esc_html( $tier_label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>


						<div class="rj-filter-actions">
							<button type="submit" class="rj-btn rj-btn-primary rj-btn-block"><?php esc_html_e( 'Terapkan Filter', 'rejaton-product-catalog' ); ?></button>
							<button type="button" class="rj-btn rj-btn-link rj-btn-block" id="rj-reset-filter"><?php esc_html_e( 'Reset Semua Filter', 'rejaton-product-catalog' ); ?></button>
						</div>
					</form>
				</div>
			</aside>
		<?php endif; ?>

		<!-- Main Products Container (Right Side) -->
		<main class="rj-catalog-content">
			<!-- Sorting & Result Bar -->
			<div class="rj-toolbar">
				<div class="rj-toolbar-count">
					Menampilkan <strong id="rj-result-count"><?php echo esc_html( $query->found_posts ); ?></strong> produk
				</div>

				<?php if ( ! empty( $show_sorting ) ) : ?>
					<div class="rj-toolbar-sort">
						<label for="rj-sort-select"><?php esc_html_e( 'Urutkan:', 'rejaton-product-catalog' ); ?></label>
						<select id="rj-sort-select" name="sort">
							<option value="date_desc" <?php selected( isset( $params['sorting'] ) ? $params['sorting'] : '', 'date_desc' ); ?>><?php esc_html_e( 'Produk Terbaru', 'rejaton-product-catalog' ); ?></option>
							<option value="title_asc" <?php selected( isset( $params['sorting'] ) ? $params['sorting'] : '', 'title_asc' ); ?>><?php esc_html_e( 'Nama: A s/d Z', 'rejaton-product-catalog' ); ?></option>
							<option value="debit_desc" <?php selected( isset( $params['sorting'] ) ? $params['sorting'] : '', 'debit_desc' ); ?>><?php esc_html_e( 'Debit Terbesar', 'rejaton-product-catalog' ); ?></option>
							<option value="debit_asc" <?php selected( isset( $params['sorting'] ) ? $params['sorting'] : '', 'debit_asc' ); ?>><?php esc_html_e( 'Debit Terkecil', 'rejaton-product-catalog' ); ?></option>
						</select>
					</div>
				<?php endif; ?>
			</div>

			<!-- Loading Overlay -->
			<div class="rj-loading-overlay" id="rj-loading-spinner" style="display:none;">
				<div class="rj-spinner"></div>
				<div class="rj-loading-text"><?php esc_html_e( 'Memuat produk...', 'rejaton-product-catalog' ); ?></div>
			</div>

			<!-- Products Grid -->
			<div class="<?php echo esc_attr( $grid_class ); ?>" id="rj-products-grid">
				<?php if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post();
						require REJATON_CATALOG_PATH . 'public/templates/parts/product-card.php';
					endwhile;
					wp_reset_postdata();
				else : ?>
					<div class="rj-empty-state">
						<p><?php esc_html_e( 'Tidak ada produk yang sesuai dengan filter yang dipilih.', 'rejaton-product-catalog' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Pagination -->
			<div class="rj-pagination-wrapper" id="rj-pagination-wrapper" data-per-page="<?php echo esc_attr( isset( $params['per_page'] ) ? $params['per_page'] : '' ); ?>">
				<?php
				$total_pages = $query->max_num_pages;
				$current_paged = isset( $params['paged'] ) ? $params['paged'] : 1;
				echo Rejaton_Catalog_Filter_Controller::render_ajax_pagination( $current_paged, $total_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</main>
	</div>
</div>

<?php
if ( empty( $is_shortcode ) ) {
	get_footer();
}

<?php
/**
 * Catalog Archive Template for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// In case this file is loaded directly via archive template
if ( ! isset( $query ) ) {
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

$selected_cats = isset( $params['category'] ) ? (array) $params['category'] : array();
$grid_class = 'rj-product-grid rj-cols-' . esc_attr( isset( $desktop_cols ) ? $desktop_cols : 3 );
?>

<div class="rj-catalog-wrapper" id="rj-catalog-root">
	<div class="rj-catalog-header">
		<h1 class="rj-catalog-title"><?php esc_html_e( 'Katalog Produk Pompa Surya Rejaton', 'rejaton-product-catalog' ); ?></h1>
		<p class="rj-catalog-subtitle"><?php esc_html_e( 'Solusi pengadaan pompa air tenaga surya resmi LORENTZ & sistem hybrid untuk irigasi, air bersih, dan proyek perpipaan.', 'rejaton-product-catalog' ); ?></p>
	</div>

	<!-- Mobile Filter Drawer Toggle Button -->
	<div class="rj-mobile-filter-bar">
		<button type="button" class="rj-btn rj-btn-secondary" id="rj-open-mobile-filter">
			<span class="dashicons dashicons-filter"></span>
			<?php esc_html_e( 'Filter & Spesifikasi', 'rejaton-product-catalog' ); ?>
		</button>
		<div class="rj-mobile-result-count">
			<span class="rj-count-num"><?php echo esc_html( $query->found_posts ); ?></span> <?php esc_html_e( 'produk ditemukan', 'rejaton-product-catalog' ); ?>
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
						<!-- Keyword Search -->
						<div class="rj-filter-group">
							<label for="rj_search_input" class="rj-filter-label"><?php esc_html_e( 'Pencarian Model / Seri', 'rejaton-product-catalog' ); ?></label>
							<div class="rj-search-input-wrap">
								<input type="text" name="s_prod" id="rj_search_input" value="<?php echo esc_attr( isset( $params['search'] ) ? $params['search'] : '' ); ?>" placeholder="Contoh: PS2, Submersible..." />
							</div>
						</div>

						<!-- Categories (Hierarchical Checkboxes) -->
						<?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
							<div class="rj-filter-group">
								<div class="rj-filter-label"><?php esc_html_e( 'Kategori Produk', 'rejaton-product-catalog' ); ?></div>
								<div class="rj-filter-checklist">
									<?php foreach ( $all_cats as $cat ) : ?>
										<label class="rj-checkbox-label">
											<input type="checkbox" name="cat[]" value="<?php echo esc_attr( $cat->slug ); ?>" <?php checked( in_array( $cat->slug, $selected_cats ) ); ?> />
											<span><?php echo esc_html( $cat->name ); ?></span>
											<span class="rj-count">(<?php echo esc_html( $cat->count ); ?>)</span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>


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
			<div class="rj-pagination-wrapper" id="rj-pagination-wrapper">
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
if ( ! isset( $query ) ) {
	get_footer();
}

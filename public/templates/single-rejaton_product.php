<?php
/**
 * Single Product Detail Template for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$product_id = get_the_ID();
$tipe_pompa = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'tipe_pompa', '' );
$debit      = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'debit', '' );
$head_min   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_min', '' );
$head_max   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_max', '' );
$daya_panel = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'daya_panel_surya', '' );
$head_range = rejaton_format_head_range( $head_min, $head_max, 'meter' );

$gallery_ids = Rejaton_Catalog_Product_Meta::get_gallery_ids( $product_id );
$main_thumb  = get_post_thumbnail_id( $product_id );
if ( $main_thumb && ! in_array( $main_thumb, $gallery_ids ) ) {
	array_unshift( $gallery_ids, $main_thumb );
}

$cta_label = rejaton_get_option( 'cta_text', 'Konsultasikan via WhatsApp' );
$wa_url    = rejaton_get_whatsapp_url( $product_id );

// Rendered description from dynamic template
$rendered_desc = Rejaton_Catalog_Template_Renderer::render( $product_id );

// Get spec table fields configured in settings
$spec_keys = rejaton_get_option( 'spec_table_fields', array( 'tipe_pompa', 'debit', 'rentang_head', 'daya_panel_surya', 'debit_m3_jam' ) );
$resolved_values = Rejaton_Catalog_Product_Meta::get_resolved_values( $product_id );
$all_fields      = Rejaton_Catalog_Field_Registry::get_all_fields();
?>

<div class="rj-detail-wrapper">
	<!-- Breadcrumbs with Schema.org Microdata -->
	<nav class="rj-breadcrumbs" aria-label="Breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Beranda', 'rejaton-product-catalog' ); ?></span></a>
			<meta itemprop="position" content="1" />
		</span>
		<span class="rj-sep">&rsaquo;</span>
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'rejaton_product' ) ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Katalog Produk', 'rejaton-product-catalog' ); ?></span></a>
			<meta itemprop="position" content="2" />
		</span>
		<span class="rj-sep">&rsaquo;</span>
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span class="rj-current" itemprop="name"><?php the_title(); ?></span>
			<meta itemprop="position" content="3" />
		</span>
	</nav>

	<div class="rj-detail-main">
		<!-- Left: Media & Gallery -->
		<div class="rj-detail-gallery-col">
			<div class="rj-gallery-main-view">
				<?php if ( ! empty( $gallery_ids ) ) :
					$main_img_id = $gallery_ids[0];
					echo wp_get_attachment_image(
						$main_img_id,
						'large',
						false,
						array(
							'id'            => 'rj-main-photo',
							'alt'           => esc_attr( get_the_title() ),
							'decoding'      => 'async',
							'fetchpriority' => 'high',
						)
					);
				else : ?>
					<div class="rj-no-thumb-detail">
						<span class="dashicons dashicons-camera" style="font-size:48px; width:48px; height:48px;"></span>
						<p><?php esc_html_e( 'Foto Produk Rejaton', 'rejaton-product-catalog' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( count( $gallery_ids ) > 1 ) : ?>
				<div class="rj-gallery-thumbs">
					<?php foreach ( $gallery_ids as $idx => $gid ) :
						$large_src    = wp_get_attachment_image_url( $gid, 'large' );
						$large_srcset = wp_get_attachment_image_srcset( $gid, 'large' );
						if ( empty( $large_src ) ) {
							$large_src = wp_get_attachment_url( $gid );
						}
						?>
						<button type="button" class="rj-thumb-item<?php if ( 0 === $idx ) echo ' active'; ?>" data-large="<?php echo esc_url( $large_src ); ?>" data-srcset="<?php echo esc_attr( $large_srcset ? $large_srcset : '' ); ?>" aria-label="<?php printf( esc_attr__( 'Foto produk %d', 'rejaton-product-catalog' ), $idx + 1 ); ?>">
							<?php
							echo wp_get_attachment_image(
								$gid,
								'thumbnail',
								false,
								array(
									'alt'      => esc_attr( get_the_title() . ' thumbnail ' . ( $idx + 1 ) ),
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Right: Product Overview & CTA -->
		<div class="rj-detail-info-col">
			<h1 class="rj-product-title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<div class="rj-product-summary">
					<?php the_excerpt(); ?>
				</div>
			<?php endif; ?>

			<!-- Quick Spec Highlights Card -->
			<div class="rj-highlight-specs">
				<?php if ( ! empty( $tipe_pompa ) ) : ?>
					<div class="rj-highlight-item">
						<div class="rj-hl-label"><?php esc_html_e( 'Tipe Pompa', 'rejaton-product-catalog' ); ?></div>
						<div class="rj-hl-val"><?php echo esc_html( $tipe_pompa ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $debit && null !== $debit ) : ?>
					<div class="rj-highlight-item">
						<div class="rj-hl-label"><?php esc_html_e( 'Kapasitas Debit', 'rejaton-product-catalog' ); ?></div>
						<div class="rj-hl-val"><?php echo esc_html( rejaton_format_number_locale( $debit ) ); ?> <small>m³/jam</small></div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $head_range ) ) : ?>
					<div class="rj-highlight-item">
						<div class="rj-hl-label"><?php esc_html_e( 'Kemampuan Head', 'rejaton-product-catalog' ); ?></div>
						<div class="rj-hl-val"><?php echo esc_html( $head_range ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $daya_panel && null !== $daya_panel ) : ?>
					<div class="rj-highlight-item">
						<div class="rj-hl-label"><?php esc_html_e( 'Rekomendasi Panel', 'rejaton-product-catalog' ); ?></div>
						<div class="rj-hl-val"><?php echo esc_html( rejaton_format_number_locale( $daya_panel ) ); ?> <small>kWp</small></div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Consultation WhatsApp CTA Button -->
			<?php if ( ! empty( $wa_url ) ) : ?>
				<div class="rj-cta-action-area">
					<a href="<?php echo esc_url( $wa_url ); ?>" class="rj-btn rj-btn-whatsapp rj-btn-lg" target="_blank" rel="noopener noreferrer">
						<span class="dashicons dashicons-format-chat" style="vertical-align:text-bottom;"></span>
						<?php echo esc_html( $cta_label ); ?>
					</a>
					<p class="rj-cta-note"><?php esc_html_e( 'Dapatkan konsultasi teknis gratis mengenai penentuan tipe pompa, debit sumur, dan kebutuhan panel surya dari tim ahli Rejaton.', 'rejaton-product-catalog' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Bottom Section: Dynamic Rendered Description -->
	<div class="rj-detail-tabs-section">
		<div class="rj-section-block">
			<h2 class="rj-section-title"><?php esc_html_e( 'Deskripsi & Ulasan Sistem', 'rejaton-product-catalog' ); ?></h2>
			<div class="rj-rendered-description">
				<?php echo $rendered_desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();

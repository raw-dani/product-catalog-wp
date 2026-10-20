<?php
/**
 * Product Card Template Part
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = get_the_ID();
$tipe_pompa = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'tipe_pompa', '' );
$debit      = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'debit', '' );
$head_min   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_min', '' );
$head_max   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'head_max', '' );
$daya_panel = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'daya_panel_surya', '' );
$featured   = Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'featured', '0' );

$head_range = rejaton_format_head_range( $head_min, $head_max, 'm' );
$thumb_url  = get_the_post_thumbnail_url( $product_id, 'medium_large' );
$cats       = get_the_terms( $product_id, 'rejaton_product_cat' );
?>

<article class="rj-product-card" id="product-<?php echo esc_attr( $product_id ); ?>">
	<div class="rj-card-media">
		<?php if ( '1' === $featured ) : ?>
			<span class="rj-badge-featured"><?php esc_html_e( 'Unggulan', 'rejaton-product-catalog' ); ?></span>
		<?php endif; ?>

		<a href="<?php the_permalink(); ?>" class="rj-card-img-link">
			<?php if ( $thumb_url ) : ?>
				<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
			<?php else : ?>
				<div class="rj-no-thumb">
					<span class="dashicons dashicons-camera"></span>
					<span><?php esc_html_e( 'Foto Pompa Rejaton', 'rejaton-product-catalog' ); ?></span>
				</div>
			<?php endif; ?>
		</a>
	</div>

	<div class="rj-card-body">
		<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
			<div class="rj-card-category">
				<a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
			</div>
		<?php endif; ?>

		<h3 class="rj-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<div class="rj-card-excerpt">
				<?php echo esc_html( wp_trim_words( get_the_excerpt(), 14 ) ); ?>
			</div>
		<?php endif; ?>

		<!-- Technical specs quick chips -->
		<div class="rj-card-specs">
			<?php if ( ! empty( $tipe_pompa ) ) : ?>
				<div class="rj-spec-chip" title="Tipe Pompa">
					<span class="rj-chip-icon dashicons dashicons-admin-generic"></span>
					<span class="rj-chip-text"><?php echo esc_html( $tipe_pompa ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $debit && null !== $debit ) : ?>
				<div class="rj-spec-chip" title="Debit Air">
					<span class="rj-chip-icon dashicons dashicons-admin-site-alt3"></span>
					<span class="rj-chip-text"><?php echo esc_html( rejaton_format_number_locale( $debit ) ); ?> L/dtk</span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $head_range ) ) : ?>
				<div class="rj-spec-chip" title="Rentang Head">
					<span class="rj-chip-icon dashicons dashicons-arrow-up-alt"></span>
					<span class="rj-chip-text">Head <?php echo esc_html( $head_range ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $daya_panel && null !== $daya_panel ) : ?>
				<div class="rj-spec-chip" title="Daya Panel Surya">
					<span class="rj-chip-icon dashicons dashicons-lightbulb"></span>
					<span class="rj-chip-text"><?php echo esc_html( rejaton_format_number_locale( $daya_panel ) ); ?> kWp</span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="rj-card-footer">
		<a href="<?php the_permalink(); ?>" class="rj-btn rj-btn-outline rj-btn-block">
			<?php esc_html_e( 'Lihat Spesifikasi Detail', 'rejaton-product-catalog' ); ?> &rarr;
		</a>
	</div>
</article>

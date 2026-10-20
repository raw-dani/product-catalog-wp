<?php
/**
 * Admin Notices for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Admin_Notices {

	public function init() {
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
	}

	public function display_notices() {
		$screen = get_current_screen();
		if ( ! $screen || 'rejaton_product' !== $screen->post_type ) {
			return;
		}

		// Check if WhatsApp CS number is configured
		$whatsapp = rejaton_get_option( 'whatsapp_number', '' );
		if ( empty( $whatsapp ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Rejaton Product Catalog:', 'rejaton-product-catalog' ); ?></strong>
					<?php esc_html_e( 'Nomor WhatsApp CS konsultasi belum diatur. Silakan atur di ', 'rejaton-product-catalog' ); ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rejaton_product&page=rejaton-catalog-settings' ) ); ?>">
						<?php esc_html_e( 'Pengaturan Katalog', 'rejaton-product-catalog' ); ?>
					</a>
					<?php esc_html_e( ' agar tombol CTA di halaman produk dapat langsung mengarah ke WhatsApp CS Rejaton.', 'rejaton-product-catalog' ); ?>
				</p>
			</div>
			<?php
		}
	}
}

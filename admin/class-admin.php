<?php
/**
 * Admin Controller for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function register_admin_menus() {
		// Submenu: Atribut & Custom Field
		add_submenu_page(
			'edit.php?post_type=rejaton_product',
			__( 'Atribut & Custom Field', 'rejaton-product-catalog' ),
			__( 'Atribut & Custom Field', 'rejaton-product-catalog' ),
			'manage_options',
			'rejaton-custom-fields',
			array( $this, 'render_custom_fields_page' )
		);

		// Submenu: Template Deskripsi
		add_submenu_page(
			'edit.php?post_type=rejaton_product',
			__( 'Template Deskripsi', 'rejaton-product-catalog' ),
			__( 'Template Deskripsi', 'rejaton-product-catalog' ),
			'manage_options',
			'edit.php?post_type=rejaton_product_template'
		);

		// Submenu: Pengaturan Katalog
		add_submenu_page(
			'edit.php?post_type=rejaton_product',
			__( 'Pengaturan Katalog', 'rejaton-product-catalog' ),
			__( 'Pengaturan Katalog', 'rejaton-product-catalog' ),
			'manage_options',
			'rejaton-catalog-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function enqueue_admin_assets( $hook ) {
		global $post_type;

		if ( 'rejaton_product' === $post_type || 'rejaton_product_template' === $post_type || ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'rejaton' ) !== false ) ) {
			wp_enqueue_media();
			wp_enqueue_style( 'rejaton-admin-css', REJATON_CATALOG_URL . 'admin/css/admin.css', array(), REJATON_CATALOG_VERSION );
			wp_enqueue_script( 'rejaton-admin-js', REJATON_CATALOG_URL . 'admin/js/admin.js', array( 'jquery' ), REJATON_CATALOG_VERSION, true );

			wp_localize_script( 'rejaton-admin-js', 'rejaton_admin_vars', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'rejaton_admin_nonce' ),
				'confirm_delete' => __( 'Apakah Anda yakin ingin menghapus custom field ini?', 'rejaton-product-catalog' ),
			) );
		}
	}

	public function render_custom_fields_page() {
		require_once REJATON_CATALOG_PATH . 'admin/class-field-manager.php';
		Rejaton_Catalog_Field_Manager::render_page();
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'rejaton-product-catalog' ) );
		}

		$options = get_option( 'rejaton_catalog_settings', array() );
		$card_fields = isset( $options['card_fields'] ) ? (array) $options['card_fields'] : array();
		$spec_fields = isset( $options['spec_table_fields'] ) ? (array) $options['spec_table_fields'] : array();
		$all_fields  = Rejaton_Catalog_Field_Registry::get_all_fields();
		?>
		<div class="wrap rj-admin-wrap">
			<h1><?php esc_html_e( 'Pengaturan Rejaton Product Catalog', 'rejaton-product-catalog' ); ?></h1>

			<?php settings_errors( 'rejaton_catalog_settings_group' ); ?>

			<form method="post" action="options.php" class="rj-settings-form">
				<?php settings_fields( 'rejaton_catalog_settings_group' ); ?>

				<div class="rj-settings-card">
					<h2><?php esc_html_e( 'Tampilan & Layout Katalog', 'rejaton-product-catalog' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="products_per_page"><?php esc_html_e( 'Produk per Halaman', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<input type="number" name="rejaton_catalog_settings[products_per_page]" id="products_per_page" value="<?php echo esc_attr( isset( $options['products_per_page'] ) ? $options['products_per_page'] : 12 ); ?>" min="1" max="100" class="small-text" />
								<p class="description"><?php esc_html_e( 'Jumlah produk yang ditampilkan per halaman pada arsip katalog.', 'rejaton-product-catalog' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="desktop_columns"><?php esc_html_e( 'Kolom Desktop', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<select name="rejaton_catalog_settings[desktop_columns]" id="desktop_columns">
									<option value="2" <?php selected( isset( $options['desktop_columns'] ) ? $options['desktop_columns'] : 3, 2 ); ?>>2 Kolom</option>
									<option value="3" <?php selected( isset( $options['desktop_columns'] ) ? $options['desktop_columns'] : 3, 3 ); ?>>3 Kolom (Standar)</option>
									<option value="4" <?php selected( isset( $options['desktop_columns'] ) ? $options['desktop_columns'] : 3, 4 ); ?>>4 Kolom</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Field Ditampilkan pada Kartu', 'rejaton-product-catalog' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $all_fields as $fkey => $fdef ) : ?>
										<label style="display:inline-block; margin-right:15px; margin-bottom:6px;">
											<input type="checkbox" name="rejaton_catalog_settings[card_fields][]" value="<?php echo esc_attr( $fkey ); ?>" <?php checked( in_array( $fkey, $card_fields ) ); ?> />
											<?php echo esc_html( $fdef['label'] ); ?>
										</label>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Field Ditampilkan di Spesifikasi', 'rejaton-product-catalog' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $all_fields as $fkey => $fdef ) : ?>
										<label style="display:inline-block; margin-right:15px; margin-bottom:6px;">
											<input type="checkbox" name="rejaton_catalog_settings[spec_table_fields][]" value="<?php echo esc_attr( $fkey ); ?>" <?php checked( in_array( $fkey, $spec_fields ) ); ?> />
											<?php echo esc_html( $fdef['label'] ); ?>
										</label>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="empty_token_policy"><?php esc_html_e( 'Kebijakan Token Kosong', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<select name="rejaton_catalog_settings[empty_token_policy]" id="empty_token_policy">
									<option value="blank" <?php selected( isset( $options['empty_token_policy'] ) ? $options['empty_token_policy'] : 'blank', 'blank' ); ?>><?php esc_html_e( 'Kosongkan (Hapus token)', 'rejaton-product-catalog' ); ?></option>
									<option value="dash" <?php selected( isset( $options['empty_token_policy'] ) ? $options['empty_token_policy'] : 'blank', 'dash' ); ?>><?php esc_html_e( 'Tampilkan Tanda Strip (—)', 'rejaton-product-catalog' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<div class="rj-settings-card">
					<h2><?php esc_html_e( 'Integrasi Kontak & CTA WhatsApp', 'rejaton-product-catalog' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="whatsapp_number"><?php esc_html_e( 'Nomor WhatsApp CS', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<input type="text" name="rejaton_catalog_settings[whatsapp_number]" id="whatsapp_number" value="<?php echo esc_attr( isset( $options['whatsapp_number'] ) ? $options['whatsapp_number'] : '' ); ?>" class="regular-text" placeholder="Contoh: 08123456789 atau 628123456789" />
								<p class="description"><?php esc_html_e( 'Nomor WhatsApp resmi Rejaton untuk menerima konsultasi teknis dari pengunjung.', 'rejaton-product-catalog' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cta_text"><?php esc_html_e( 'Label Tombol CTA', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<input type="text" name="rejaton_catalog_settings[cta_text]" id="cta_text" value="<?php echo esc_attr( isset( $options['cta_text'] ) ? $options['cta_text'] : 'Konsultasikan via WhatsApp' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="whatsapp_message_template"><?php esc_html_e( 'Template Pesan WhatsApp', 'rejaton-product-catalog' ); ?></label></th>
							<td>
								<textarea name="rejaton_catalog_settings[whatsapp_message_template]" id="whatsapp_message_template" rows="3" class="large-text"><?php echo esc_textarea( isset( $options['whatsapp_message_template'] ) ? $options['whatsapp_message_template'] : 'Halo CS Rejaton, saya ingin berkonsultasi mengenai produk {product_name} ({product_url}).' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Dapat memuat variabel {product_name} dan {product_url}.', 'rejaton-product-catalog' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div class="rj-settings-card">
					<h2><?php esc_html_e( 'Shortcode Tersedia', 'rejaton-product-catalog' ); ?></h2>
					<p><?php esc_html_e( 'Gunakan shortcode berikut pada halaman Classic Editor atau widget Shortcode Elementor:', 'rejaton-product-catalog' ); ?></p>
					<code>[rejaton_product_catalog]</code>
					<p class="description"><?php esc_html_e( 'Atribut yang didukung: columns="3", per_page="12", category="slug-kategori", show_filters="true", show_sorting="true"', 'rejaton-product-catalog' ); ?></p>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

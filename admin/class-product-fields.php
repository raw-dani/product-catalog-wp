<?php
/**
 * Product Meta Boxes and Fields Handler for Classic Editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Product_Fields {

	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_rejaton_product', array( $this, 'save_product_fields' ), 10, 2 );
	}

	public function register_meta_boxes() {
		// Meta Box: Data Teknis & Atribut Produk
		add_meta_box(
			'rejaton_technical_fields',
			__( 'Spesifikasi & Data Teknis Pompa Air', 'rejaton-product-catalog' ),
			array( $this, 'render_technical_box' ),
			'rejaton_product',
			'normal',
			'high'
		);

		// Meta Box: Galeri Foto Produk
		add_meta_box(
			'rejaton_product_gallery',
			__( 'Galeri Foto Produk', 'rejaton-product-catalog' ),
			array( $this, 'render_gallery_box' ),
			'rejaton_product',
			'normal',
			'default'
		);

		// Meta Box: Template Deskripsi & Preview
		add_meta_box(
			'rejaton_product_template_box',
			__( 'Template Deskripsi & Live Preview', 'rejaton-product-catalog' ),
			array( $this, 'render_template_box' ),
			'rejaton_product',
			'normal',
			'default'
		);

		// Meta Box: Opsi Tambahan (Featured, CTA)
		add_meta_box(
			'rejaton_product_options_box',
			__( 'Opsi Produk', 'rejaton-product-catalog' ),
			array( $this, 'render_options_sidebox' ),
			'rejaton_product',
			'side',
			'default'
		);
	}

	public function render_technical_box( $post ) {
		wp_nonce_field( 'rejaton_save_product_meta', 'rejaton_product_meta_nonce' );

		$tipe_pompa       = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'tipe_pompa', '' );
		$debit            = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'debit', '' );
		$head_min         = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'head_min', '' );
		$head_max         = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'head_max', '' );
		$daya_panel_surya = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'daya_panel_surya', '' );
		$custom_defs      = Rejaton_Catalog_Field_Registry::get_custom_fields();
		?>
		<div class="rj-meta-grid">
			<div class="rj-meta-row">
				<label for="rj_tipe_pompa"><strong><?php esc_html_e( 'Tipe Pompa', 'rejaton-product-catalog' ); ?>:</strong></label>
				<input type="text" name="rj_tipe_pompa" id="rj_tipe_pompa" value="<?php echo esc_attr( $tipe_pompa ); ?>" class="regular-text" placeholder="Contoh: PS2-1800 CS-F" />
				<span class="description"><?php esc_html_e( 'Digunakan pada token [Tipe Pompa]', 'rejaton-product-catalog' ); ?></span>
			</div>

			<div class="rj-meta-row">
				<label for="rj_debit"><strong><?php esc_html_e( 'Debit (liter/detik)', 'rejaton-product-catalog' ); ?>:</strong></label>
				<input type="number" step="any" min="0" name="rj_debit" id="rj_debit" value="<?php echo esc_attr( $debit ); ?>" class="small-text" placeholder="2.5" />
				<span class="description"><?php esc_html_e( 'Otomatis dihitung menjadi [Debit × 3,6] m³/jam', 'rejaton-product-catalog' ); ?></span>
			</div>

			<div class="rj-meta-row">
				<label><strong><?php esc_html_e( 'Rentang Head (meter)', 'rejaton-product-catalog' ); ?>:</strong></label>
				<div style="display:inline-flex; align-items:center; gap:8px;">
					<input type="number" step="any" min="0" name="rj_head_min" id="rj_head_min" value="<?php echo esc_attr( $head_min ); ?>" class="small-text" placeholder="Min" />
					<span>s/d</span>
					<input type="number" step="any" min="0" name="rj_head_max" id="rj_head_max" value="<?php echo esc_attr( $head_max ); ?>" class="small-text" placeholder="Max" />
					<span>meter</span>
				</div>
				<span class="description"><?php esc_html_e( 'Digunakan pada token [Rentang Head] dan filter numerik', 'rejaton-product-catalog' ); ?></span>
			</div>

			<div class="rj-meta-row">
				<label for="rj_daya_panel_surya"><strong><?php esc_html_e( 'Daya Panel Surya (kWp)', 'rejaton-product-catalog' ); ?>:</strong></label>
				<input type="number" step="any" min="0" name="rj_daya_panel_surya" id="rj_daya_panel_surya" value="<?php echo esc_attr( $daya_panel_surya ); ?>" class="small-text" placeholder="1.8" />
				<span class="description"><?php esc_html_e( 'Digunakan pada token [Daya Panel Surya]', 'rejaton-product-catalog' ); ?></span>
			</div>

			<?php if ( ! empty( $custom_defs ) ) : ?>
				<hr style="margin:16px 0; border:0; border-top:1px solid #ddd;" />
				<h4 style="margin:10px 0;"><?php esc_html_e( 'Atribut Tambahan (Custom Fields)', 'rejaton-product-catalog' ); ?></h4>
				<?php foreach ( $custom_defs as $ckey => $cdef ) :
					$val = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'custom_' . $ckey, '' );
					?>
					<div class="rj-meta-row">
						<label for="rj_custom_<?php echo esc_attr( $ckey ); ?>"><strong><?php echo esc_html( $cdef['label'] ); ?>:</strong></label>
						<?php if ( 'textarea' === $cdef['type'] ) : ?>
							<textarea name="rj_custom[<?php echo esc_attr( $ckey ); ?>]" id="rj_custom_<?php echo esc_attr( $ckey ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $val ); ?></textarea>
						<?php elseif ( 'select' === $cdef['type'] ) :
							$options = isset( $cdef['options'] ) ? (array) $cdef['options'] : array();
							?>
							<select name="rj_custom[<?php echo esc_attr( $ckey ); ?>]" id="rj_custom_<?php echo esc_attr( $ckey ); ?>">
								<option value="">-- <?php esc_html_e( 'Pilih', 'rejaton-product-catalog' ); ?> --</option>
								<?php foreach ( $options as $opt ) : ?>
									<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $val, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( 'number' === $cdef['type'] ) : ?>
							<input type="number" step="any" name="rj_custom[<?php echo esc_attr( $ckey ); ?>]" id="rj_custom_<?php echo esc_attr( $ckey ); ?>" value="<?php echo esc_attr( $val ); ?>" class="small-text" />
							<?php if ( ! empty( $cdef['unit'] ) ) echo ' <span>' . esc_html( $cdef['unit'] ) . '</span>'; ?>
						<?php else : ?>
							<input type="text" name="rj_custom[<?php echo esc_attr( $ckey ); ?>]" id="rj_custom_<?php echo esc_attr( $ckey ); ?>" value="<?php echo esc_attr( $val ); ?>" class="regular-text" />
						<?php endif; ?>
						<?php if ( ! empty( $cdef['token'] ) ) : ?>
							<span class="description"><?php printf( esc_html__( 'Token: %s', 'rejaton-product-catalog' ), esc_html( $cdef['token'] ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render_gallery_box( $post ) {
		$gallery_ids = Rejaton_Catalog_Product_Meta::get_gallery_ids( $post->ID );
		?>
		<div class="rj-gallery-wrapper">
			<input type="hidden" name="rj_product_gallery" id="rj_product_gallery_input" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>" />
			<div id="rj_gallery_container" class="rj-gallery-grid">
				<?php foreach ( $gallery_ids as $img_id ) :
					$thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
					if ( $thumb ) : ?>
						<div class="rj-gallery-item" data-id="<?php echo esc_attr( $img_id ); ?>">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
							<button type="button" class="rj-gallery-remove">&times;</button>
						</div>
					<?php endif;
				endforeach; ?>
			</div>
			<p>
				<button type="button" class="button rj-add-gallery-images" id="rj_add_gallery_btn">
					<span class="dashicons dashicons-format-gallery" style="vertical-align:text-top;"></span>
					<?php esc_html_e( 'Kelola / Tambah Foto Galeri', 'rejaton-product-catalog' ); ?>
				</button>
			</p>
			<span class="description"><?php esc_html_e( 'Pilih beberapa foto dari Media Library untuk ditampilkan sebagai galeri di halaman detail produk.', 'rejaton-product-catalog' ); ?></span>
		</div>
		<?php
	}

	public function render_template_box( $post ) {
		$all_templates = Rejaton_Catalog_Template_Repository::get_all_templates();
		$chosen_template_id = (int) Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'template_id', 0 );
		$default_id = (int) get_option( Rejaton_Catalog_Template_Repository::DEFAULT_TEMPLATE_OPTION, 0 );

		// Render current preview
		$preview_html = Rejaton_Catalog_Template_Renderer::render( $post->ID, $chosen_template_id ? $chosen_template_id : $default_id, true );
		?>
		<div class="rj-template-selector-bar">
			<label for="rj_template_id"><strong><?php esc_html_e( 'Pilih Template Deskripsi:', 'rejaton-product-catalog' ); ?></strong></label>
			<select name="rj_template_id" id="rj_template_id">
				<option value="0" <?php selected( $chosen_template_id, 0 ); ?>>
					<?php esc_html_e( 'Gunakan Template Default Global', 'rejaton-product-catalog' ); ?>
				</option>
				<?php foreach ( $all_templates as $tpl ) : ?>
					<option value="<?php echo esc_attr( $tpl->ID ); ?>" <?php selected( $chosen_template_id, $tpl->ID ); ?>>
						<?php echo esc_html( $tpl->post_title ); ?> <?php if ( $tpl->ID === $default_id ) echo ' (Default)'; ?>
					</option>
				<?php endforeach; ?>
			</select>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=rejaton_product_template' ) ); ?>" class="button button-primary" target="_blank" style="margin-left:8px;">
				<span class="dashicons dashicons-plus-alt2" style="vertical-align:text-bottom; margin-right:2px;"></span>
				<?php esc_html_e( 'Buat Template Baru', 'rejaton-product-catalog' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rejaton_product_template' ) ); ?>" class="button button-secondary" target="_blank" style="margin-left:4px;">
				<?php esc_html_e( 'Semua Template', 'rejaton-product-catalog' ); ?>
			</a>
		</div>

		<div class="rj-preview-box">
			<h4 style="margin:12px 0 6px 0;"><?php esc_html_e( 'Live Preview Hasil Render Template:', 'rejaton-product-catalog' ); ?></h4>
			<div class="rj-preview-content" style="background:#fff; border:1px solid #ccd0d4; padding:16px 20px; border-radius:4px; max-height:350px; overflow-y:auto;">
				<?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<p class="description"><?php esc_html_e( 'Deskripsi ini otomatis dirender dari template yang dipilih dan data teknis produk di atas. Anda tidak perlu mengetik ulang spesifikasi.', 'rejaton-product-catalog' ); ?></p>
		</div>
		<?php
	}

	public function render_options_sidebox( $post ) {
		$featured = Rejaton_Catalog_Product_Meta::get_meta( $post->ID, 'featured', '0' );
		?>
		<div class="rj-sidebox-row">
			<label>
				<input type="checkbox" name="rj_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<strong><?php esc_html_e( 'Produk Unggulan (Featured)', 'rejaton-product-catalog' ); ?></strong>
			</label>
			<p class="description"><?php esc_html_e( 'Tampilkan badge unggulan pada kartu dan izinkan filter khusus.', 'rejaton-product-catalog' ); ?></p>
		</div>
		<?php
	}

	public function save_product_fields( $post_id, $post ) {
		// Nonce check
		if ( ! isset( $_POST['rejaton_product_meta_nonce'] ) || ! wp_verify_nonce( $_POST['rejaton_product_meta_nonce'], 'rejaton_save_product_meta' ) ) {
			return;
		}

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save technical fields
		if ( isset( $_POST['rj_tipe_pompa'] ) ) {
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'tipe_pompa', sanitize_text_field( wp_unslash( $_POST['rj_tipe_pompa'] ) ) );
		}

		if ( isset( $_POST['rj_debit'] ) ) {
			$debit = ( '' !== $_POST['rj_debit'] ) ? floatval( $_POST['rj_debit'] ) : '';
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'debit', $debit );
		}

		if ( isset( $_POST['rj_head_min'] ) ) {
			$head_min = ( '' !== $_POST['rj_head_min'] ) ? floatval( $_POST['rj_head_min'] ) : '';
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'head_min', $head_min );
		}

		if ( isset( $_POST['rj_head_max'] ) ) {
			$head_max = ( '' !== $_POST['rj_head_max'] ) ? floatval( $_POST['rj_head_max'] ) : '';
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'head_max', $head_max );
		}

		if ( isset( $_POST['rj_daya_panel_surya'] ) ) {
			$daya = ( '' !== $_POST['rj_daya_panel_surya'] ) ? floatval( $_POST['rj_daya_panel_surya'] ) : '';
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'daya_panel_surya', $daya );
		}

		// Save gallery
		if ( isset( $_POST['rj_product_gallery'] ) ) {
			$raw_gallery = sanitize_text_field( wp_unslash( $_POST['rj_product_gallery'] ) );
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'product_gallery', $raw_gallery );
		}

		// Save template selection
		if ( isset( $_POST['rj_template_id'] ) ) {
			Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'template_id', absint( $_POST['rj_template_id'] ) );
		}

		// Save featured
		$featured = ! empty( $_POST['rj_featured'] ) ? '1' : '0';
		Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'featured', $featured );

		// Save dynamic custom fields
		if ( isset( $_POST['rj_custom'] ) && is_array( $_POST['rj_custom'] ) ) {
			$custom_defs = Rejaton_Catalog_Field_Registry::get_custom_fields();
			foreach ( $_POST['rj_custom'] as $ckey => $cval ) {
				if ( isset( $custom_defs[ $ckey ] ) ) {
					$clean_val = sanitize_textarea_field( wp_unslash( $cval ) );
					Rejaton_Catalog_Product_Meta::set_meta( $post_id, 'custom_' . $ckey, $clean_val );
				}
			}
		}
	}
}

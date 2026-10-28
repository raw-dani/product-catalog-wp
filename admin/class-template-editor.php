<?php
/**
 * Template Editor and Token Bar for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Template_Editor {

	public function init() {
		add_action( 'add_meta_boxes_rejaton_template', array( $this, 'register_template_meta_boxes' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_token_bar_above_editor' ) );
		add_action( 'save_post_rejaton_template', array( $this, 'validate_and_save_template' ), 10, 2 );
		add_action( 'wp_ajax_rejaton_preview_template', array( $this, 'ajax_preview_template' ) );
	}

	public function register_template_meta_boxes() {
		add_meta_box(
			'rejaton_template_options_box',
			__( 'Pengaturan Template', 'rejaton-product-catalog' ),
			array( $this, 'render_options_meta_box' ),
			'rejaton_template',
			'side',
			'default'
		);

		add_meta_box(
			'rejaton_template_preview_box',
			__( 'Live Preview & Validasi Token Template', 'rejaton-product-catalog' ),
			array( $this, 'render_preview_meta_box' ),
			'rejaton_template',
			'normal',
			'high'
		);
	}

	public function render_options_meta_box( $post ) {
		$default_id = (int) get_option( Rejaton_Catalog_Template_Repository::DEFAULT_TEMPLATE_OPTION, 0 );
		$is_default = ( $post->ID === $default_id );
		wp_nonce_field( 'rejaton_save_template_nonce', 'rejaton_template_nonce' );
		?>
		<p>
			<label>
				<input type="checkbox" name="rj_is_default_template" value="1" <?php checked( $is_default ); ?> />
				<strong><?php esc_html_e( 'Jadikan Template Default Global', 'rejaton-product-catalog' ); ?></strong>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( 'Template default digunakan secara otomatis untuk semua produk yang tidak memiliki pilihan template khusus.', 'rejaton-product-catalog' ); ?>
		</p>
		<?php
	}

	/**
	 * Render token toolbar right above Classic / WYSIWYG Editor
	 */
	public function render_token_bar_above_editor( $post ) {
		if ( ! is_object( $post ) || 'rejaton_template' !== $post->post_type ) {
			return;
		}

		$token_map = Rejaton_Catalog_Field_Registry::get_token_map();
		?>
		<div class="rj-token-bar-container" style="background:#f0f6fc; border:1px solid #c8d9ea; border-radius:4px; padding:12px 16px; margin:16px 0;">
			<div style="font-weight:600; margin-bottom:8px; color:#0c3260;">
				<span class="dashicons dashicons-tag" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Klik token di bawah ini untuk menyisipkan ke dalam template deskripsi di posisi kursor:', 'rejaton-product-catalog' ); ?>
			</div>
			<div class="rj-token-buttons" style="display:flex; flex-wrap:wrap; gap:8px;">
				<?php foreach ( $token_map as $token => $tinfo ) : ?>
					<button type="button" class="button rj-token-insert-btn" data-token="<?php echo esc_attr( $token ); ?>" style="font-family:monospace; background:#fff;">
						+ <?php echo esc_html( $token ); ?>
						<span style="font-size:11px; color:#666;">(<?php echo esc_html( $tinfo['label'] ); ?>)</span>
					</button>
				<?php endforeach; ?>
				<button type="button" class="button rj-token-insert-btn" data-token="[IF:daya_panel_surya]Daya: [Daya Panel Surya] kWp[/IF]" style="font-family:monospace; background:#fff; border-color:#2271b1; color:#2271b1;">
					+ <?php esc_html_e( '[IF Conditional]', 'rejaton-product-catalog' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	public function render_preview_meta_box( $post ) {
		$content = ( is_object( $post ) && isset( $post->post_content ) ) ? $post->post_content : '';
		$validation = Rejaton_Catalog_Template_Validator::validate_content( $content );

		// Sample products for preview
		$sample_products = get_posts( array(
			'post_type'      => 'rejaton_product',
			'posts_per_page' => 10,
			'post_status'    => 'any',
		) );
		?>
		<div class="rj-template-validation-report" style="margin-bottom:16px;">
			<?php if ( ! empty( $validation['invalid'] ) ) : ?>
				<div class="notice notice-warning inline" style="margin:0 0 12px 0;">
					<p><strong><?php esc_html_e( 'Peringatan Token Tidak Dikenal:', 'rejaton-product-catalog' ); ?></strong>
					<?php echo esc_html( implode( ', ', $validation['invalid'] ) ); ?>
					(<?php esc_html_e( 'Token ini belum terdaftar di Atribut & Custom Field', 'rejaton-product-catalog' ); ?>)</p>
				</div>
			<?php else : ?>
				<div class="notice notice-success inline" style="margin:0 0 12px 0;">
					<p><?php esc_html_e( 'Semua token dalam template ini valid dan terdaftar.', 'rejaton-product-catalog' ); ?></p>
				</div>
			<?php endif; ?>

			<div style="margin-bottom:12px; display:flex; align-items:center; gap:10px;">
				<label for="rj_preview_product_select"><strong><?php esc_html_e( 'Uji Preview dengan Data Produk:', 'rejaton-product-catalog' ); ?></strong></label>
				<select id="rj_preview_product_select">
					<option value="0">-- <?php esc_html_e( 'Data Contoh Simulasi Rejaton', 'rejaton-product-catalog' ); ?> --</option>
					<?php foreach ( $sample_products as $sp ) : ?>
						<option value="<?php echo esc_attr( $sp->ID ); ?>"><?php echo esc_html( $sp->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div id="rj_template_preview_output" style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:4px; min-height:120px;">
				<?php
				// Sample dummy values for initial preview
				$sample_values = array(
					'tipe_pompa'       => 'PS2-1800 CS-F',
					'debit'            => 2.5,
					'debit_m3_jam'     => 9,
					'rentang_head'     => '40–80 meter',
					'daya_panel_surya' => 1.8,
				);
				echo Rejaton_Catalog_Template_Renderer::parse_content( $content, $sample_values, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</div>
		<?php
	}

	public function validate_and_save_template( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['rejaton_template_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rejaton_template_nonce'] ) ), 'rejaton_save_template_nonce' ) ) {
			if ( ! empty( $_POST['rj_is_default_template'] ) ) {
				update_option( Rejaton_Catalog_Template_Repository::DEFAULT_TEMPLATE_OPTION, $post_id );
			} else {
				$current_default = (int) get_option( Rejaton_Catalog_Template_Repository::DEFAULT_TEMPLATE_OPTION, 0 );
				if ( $current_default === (int) $post_id ) {
					// Don't uncheck if it's the only default unless another one exists
				}
			}
		}

		// Invalidate cached renders when template is saved
		$cache = new Rejaton_Catalog_Cache();
		$cache->flush_template_cache( $post_id );
	}

	/**
	 * AJAX endpoint to render live preview for template editor
	 */
	public function ajax_preview_template() {
		check_ajax_referer( 'rejaton_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Akses ditolak.', 'rejaton-product-catalog' ) ) );
		}

		$content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

		if ( $product_id > 0 ) {
			$raw_meta = Rejaton_Catalog_Product_Meta::get_all_meta( $product_id );
			$values = array();
			foreach ( $raw_meta as $mk => $mv ) {
				$clean_key = str_replace( Rejaton_Catalog_Product_Meta::META_PREFIX, '', $mk );
				$values[ $clean_key ] = $mv;
			}
			$rendered = Rejaton_Catalog_Template_Renderer::parse_content( $content, $values, true );
		} else {
			$sample_values = array(
				'tipe_pompa'       => 'PS2-1800 CS-F',
				'debit'            => 2.5,
				'debit_m3_jam'     => 9,
				'rentang_head'     => '40–80 meter',
				'daya_panel_surya' => 1.8,
			);
			$rendered = Rejaton_Catalog_Template_Renderer::parse_content( $content, $sample_values, true );
		}

		wp_send_json_success( array( 'html' => $rendered ) );
	}
}

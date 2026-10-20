<?php
/**
 * Custom Field Manager Interface for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Field_Manager {

	public function init() {
		add_action( 'admin_init', array( $this, 'handle_form_actions' ) );
	}

	public function handle_form_actions() {
		if ( ! isset( $_POST['rj_field_action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Akses ditolak.', 'rejaton-product-catalog' ) );
		}

		check_admin_referer( 'rejaton_custom_field_action', 'rj_field_nonce' );

		$action = sanitize_key( $_POST['rj_field_action'] );
		$fields = Rejaton_Catalog_Field_Registry::get_custom_fields();

		if ( 'add' === $action || 'edit' === $action ) {
			$label = sanitize_text_field( wp_unslash( $_POST['field_label'] ) );
			$key   = sanitize_key( wp_unslash( $_POST['field_key'] ) );
			$type  = sanitize_key( wp_unslash( $_POST['field_type'] ) );
			$unit  = sanitize_text_field( wp_unslash( $_POST['field_unit'] ) );
			$token = sanitize_text_field( wp_unslash( $_POST['field_token'] ) );

			if ( empty( $label ) || empty( $key ) ) {
				add_settings_error( 'rj_fields', 'missing_data', __( 'Label dan Key field wajib diisi.', 'rejaton-product-catalog' ), 'error' );
				return;
			}

			// Format token if provided and doesn't have brackets
			if ( ! empty( $token ) && substr( $token, 0, 1 ) !== '[' ) {
				$token = '[' . trim( $token ) . ']';
			}

			// Options for select/multiselect
			$raw_options = isset( $_POST['field_options'] ) ? sanitize_textarea_field( wp_unslash( $_POST['field_options'] ) ) : '';
			$options_arr = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", '', $raw_options ) ) ) );

			$fields[ $key ] = array(
				'key'         => $key,
				'label'       => $label,
				'type'        => $type,
				'unit'        => $unit,
				'token'       => $token,
				'options'     => $options_arr,
				'in_card'     => ! empty( $_POST['in_card'] ),
				'in_filter'   => ! empty( $_POST['in_filter'] ),
				'in_spec'     => ! empty( $_POST['in_spec'] ),
			);

			Rejaton_Catalog_Field_Registry::save_custom_fields( $fields );
			add_settings_error( 'rj_fields', 'field_saved', __( 'Custom field berhasil disimpan.', 'rejaton-product-catalog' ), 'updated' );
		} elseif ( 'delete' === $action ) {
			$key = sanitize_key( wp_unslash( $_POST['field_key'] ) );
			if ( isset( $fields[ $key ] ) ) {
				unset( $fields[ $key ] );
				Rejaton_Catalog_Field_Registry::save_custom_fields( $fields );
				add_settings_error( 'rj_fields', 'field_deleted', __( 'Custom field berhasil dihapus.', 'rejaton-product-catalog' ), 'updated' );
			}
		}
	}

	public static function render_page() {
		$system_fields = Rejaton_Catalog_Field_Registry::get_initial_fields();
		$custom_fields = Rejaton_Catalog_Field_Registry::get_custom_fields();
		?>
		<div class="wrap rj-admin-wrap">
			<h1><?php esc_html_e( 'Atribut Teknis & Custom Field Produk', 'rejaton-product-catalog' ); ?></h1>
			<p><?php esc_html_e( 'Kelola spesifikasi dan custom field yang dapat diinput pada setiap produk serta disisipkan sebagai variabel token ke template deskripsi.', 'rejaton-product-catalog' ); ?></p>

			<?php settings_errors( 'rj_fields' ); ?>

			<div class="rj-two-col-layout" style="display:flex; gap:24px; align-items:flex-start; margin-top:20px;">
				<!-- Left: Form Tambah Field Baru -->
				<div class="rj-col-form" style="flex:1; background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
					<h2><?php esc_html_e( 'Tambah Custom Field Baru', 'rejaton-product-catalog' ); ?></h2>
					<form method="post" action="">
						<?php wp_nonce_field( 'rejaton_custom_field_action', 'rj_field_nonce' ); ?>
						<input type="hidden" name="rj_field_action" value="add" />

						<table class="form-table">
							<tr>
								<th><label for="field_label"><?php esc_html_e( 'Label Field', 'rejaton-product-catalog' ); ?> *</label></th>
								<td><input type="text" name="field_label" id="field_label" class="regular-text" required placeholder="Contoh: Voltase Maksimal" /></td>
							</tr>
							<tr>
								<th><label for="field_key"><?php esc_html_e( 'Field Key (Internal)', 'rejaton-product-catalog' ); ?> *</label></th>
								<td>
									<input type="text" name="field_key" id="field_key" class="regular-text" required placeholder="voltase_maksimal" pattern="[a-z0-9_]+" />
									<p class="description"><?php esc_html_e( 'Huruf kecil, angka, garis bawah (_). Tidak boleh berubah setelah dibuat.', 'rejaton-product-catalog' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="field_type"><?php esc_html_e( 'Tipe Data', 'rejaton-product-catalog' ); ?></label></th>
								<td>
									<select name="field_type" id="field_type">
										<option value="text">Text Singkat</option>
										<option value="number">Number (Angka)</option>
										<option value="textarea">Textarea (Paragraf)</option>
										<option value="select">Dropdown Select</option>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="field_unit"><?php esc_html_e( 'Satuan (Opsional)', 'rejaton-product-catalog' ); ?></label></th>
								<td><input type="text" name="field_unit" id="field_unit" class="small-text" placeholder="Volt, Bar, mm" /></td>
							</tr>
							<tr>
								<th><label for="field_token"><?php esc_html_e( 'Token Template', 'rejaton-product-catalog' ); ?></label></th>
								<td>
									<input type="text" name="field_token" id="field_token" class="regular-text" placeholder="[Voltase Maksimal]" />
									<p class="description"><?php esc_html_e( 'Variabel yang bisa disisipkan ke template deskripsi produk.', 'rejaton-product-catalog' ); ?></p>
								</td>
							</tr>
							<tr id="row_field_options" style="display:none;">
								<th><label for="field_options"><?php esc_html_e( 'Pilihan Dropdown', 'rejaton-product-catalog' ); ?></label></th>
								<td>
									<textarea name="field_options" id="field_options" rows="3" class="regular-text" placeholder="Pilihan 1&#10;Pilihan 2"></textarea>
									<p class="description"><?php esc_html_e( 'Satu pilihan per baris.', 'rejaton-product-catalog' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Visibilitas', 'rejaton-product-catalog' ); ?></th>
								<td>
									<label><input type="checkbox" name="in_card" value="1" /> <?php esc_html_e( 'Tampilkan di Kartu Katalog', 'rejaton-product-catalog' ); ?></label><br />
									<label><input type="checkbox" name="in_spec" value="1" checked /> <?php esc_html_e( 'Tampilkan di Tabel Spesifikasi', 'rejaton-product-catalog' ); ?></label><br />
									<label><input type="checkbox" name="in_filter" value="1" /> <?php esc_html_e( 'Gunakan Sebagai Filter Sidebar', 'rejaton-product-catalog' ); ?></label>
								</td>
							</tr>
						</table>

						<?php submit_button( __( 'Simpan Custom Field', 'rejaton-product-catalog' ), 'primary', 'submit_add_field' ); ?>
					</form>
				</div>

				<!-- Right: Daftar Field yang Terdaftar -->
				<div class="rj-col-list" style="flex:1.4;">
					<h2><?php esc_html_e( 'Daftar Field & Token Aktif', 'rejaton-product-catalog' ); ?></h2>

					<h3><?php esc_html_e( '1. Atribut Bawaan Standar Pompa Rejaton', 'rejaton-product-catalog' ); ?></h3>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Label', 'rejaton-product-catalog' ); ?></th>
								<th><?php esc_html_e( 'Key', 'rejaton-product-catalog' ); ?></th>
								<th><?php esc_html_e( 'Tipe', 'rejaton-product-catalog' ); ?></th>
								<th><?php esc_html_e( 'Token Template', 'rejaton-product-catalog' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $system_fields as $sf ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $sf['label'] ); ?></strong></td>
									<td><code><?php echo esc_html( $sf['key'] ); ?></code></td>
									<td><?php echo esc_html( $sf['type'] . ( ! empty( $sf['unit'] ) ? ' (' . $sf['unit'] . ')' : '' ) ); ?></td>
									<td><code><?php echo esc_html( ! empty( $sf['token'] ) ? $sf['token'] : '—' ); ?></code></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<h3 style="margin-top:24px;"><?php esc_html_e( '2. Custom Field Tambahan', 'rejaton-product-catalog' ); ?></h3>
					<?php if ( empty( $custom_fields ) ) : ?>
						<p><em><?php esc_html_e( 'Belum ada custom field tambahan yang dibuat.', 'rejaton-product-catalog' ); ?></em></p>
					<?php else : ?>
						<table class="widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Label', 'rejaton-product-catalog' ); ?></th>
									<th><?php esc_html_e( 'Key', 'rejaton-product-catalog' ); ?></th>
									<th><?php esc_html_e( 'Token', 'rejaton-product-catalog' ); ?></th>
									<th><?php esc_html_e( 'Aksi', 'rejaton-product-catalog' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $custom_fields as $ck => $cf ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $cf['label'] ); ?></strong></td>
										<td><code><?php echo esc_html( $ck ); ?></code></td>
										<td><code><?php echo esc_html( ! empty( $cf['token'] ) ? $cf['token'] : '—' ); ?></code></td>
										<td>
											<form method="post" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus field ini?');" style="display:inline;">
												<?php wp_nonce_field( 'rejaton_custom_field_action', 'rj_field_nonce' ); ?>
												<input type="hidden" name="rj_field_action" value="delete" />
												<input type="hidden" name="field_key" value="<?php echo esc_attr( $ck ); ?>" />
												<button type="submit" class="button button-link-delete" style="color:#a00;"><?php esc_html_e( 'Hapus', 'rejaton-product-catalog' ); ?></button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<script>
		document.getElementById('field_type').addEventListener('change', function() {
			var optRow = document.getElementById('row_field_options');
			if (this.value === 'select') {
				optRow.style.display = 'table-row';
			} else {
				optRow.style.display = 'none';
			}
		});
		</script>
		<?php
	}
}

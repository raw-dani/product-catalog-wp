<?php
/**
 * Duplicate Products and Templates for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Duplicate {

	public function init() {
		// Row actions on post lists
		add_filter( 'post_row_actions', array( $this, 'add_duplicate_row_action' ), 10, 2 );

		// Button on edit screen submitbox
		add_action( 'post_submitbox_start', array( $this, 'add_duplicate_submitbox_button' ) );

		// Handle duplicate request in admin_action hook
		add_action( 'admin_action_rejaton_duplicate_post', array( $this, 'handle_duplicate_action' ) );

		// Admin notice after duplication
		add_action( 'admin_notices', array( $this, 'display_duplicate_notice' ) );
	}

	/**
	 * Add "Duplikat" link on row actions in edit.php
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 * @return array
	 */
	public function add_duplicate_row_action( $actions, $post ) {
		if ( ! is_object( $post ) || ! in_array( $post->post_type, array( 'rejaton_product', 'rejaton_template' ), true ) ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$duplicate_url = wp_nonce_url(
			admin_url( 'admin.php?action=rejaton_duplicate_post&post=' . $post->ID ),
			'rejaton_duplicate_post_' . $post->ID
		);

		$label = ( 'rejaton_product' === $post->post_type )
			? __( 'Duplikat Produk', 'rejaton-product-catalog' )
			: __( 'Duplikat Template', 'rejaton-product-catalog' );

		$actions['rejaton_duplicate'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( $duplicate_url ),
			esc_attr__( 'Gandakan item ini sebagai draf baru', 'rejaton-product-catalog' ),
			esc_html( $label )
		);

		return $actions;
	}

	/**
	 * Add "Duplikat" button in Publish metabox
	 */
	public function add_duplicate_submitbox_button() {
		global $post;

		if ( ! is_object( $post ) || ! in_array( $post->post_type, array( 'rejaton_product', 'rejaton_template' ), true ) ) {
			return;
		}

		// Only show for already saved posts
		if ( 'auto-draft' === $post->post_status || empty( $post->ID ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$duplicate_url = wp_nonce_url(
			admin_url( 'admin.php?action=rejaton_duplicate_post&post=' . $post->ID ),
			'rejaton_duplicate_post_' . $post->ID
		);

		$label = ( 'rejaton_product' === $post->post_type )
			? __( 'Duplikat Produk Ini', 'rejaton-product-catalog' )
			: __( 'Duplikat Template Ini', 'rejaton-product-catalog' );
		?>
		<div id="rj-duplicate-action" style="padding: 10px 12px; border-bottom: 1px solid #dcdcde; background: #f6f7f7;">
			<a class="button button-small" href="<?php echo esc_url( $duplicate_url ); ?>" style="width: 100%; text-align: center;">
				<span class="dashicons dashicons-admin-page" style="vertical-align: text-bottom; margin-right: 2px;"></span>
				<?php echo esc_html( $label ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Handle duplicate action
	 */
	public function handle_duplicate_action() {
		if ( empty( $_GET['post'] ) ) {
			wp_die( esc_html__( 'Tidak ada post yang dipilih untuk diduplikat.', 'rejaton-product-catalog' ) );
		}

		$original_id = absint( $_GET['post'] );
		check_admin_referer( 'rejaton_duplicate_post_' . $original_id );

		$original_post = get_post( $original_id );
		if ( ! $original_post || ! in_array( $original_post->post_type, array( 'rejaton_product', 'rejaton_template' ), true ) ) {
			wp_die( esc_html__( 'Post tidak ditemukan atau tipe post tidak didukung.', 'rejaton-product-catalog' ) );
		}

		if ( ! current_user_can( 'edit_post', $original_id ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk menduplikat post ini.', 'rejaton-product-catalog' ) );
		}

		$current_user = wp_get_current_user();
		$new_author_id = $current_user ? $current_user->ID : $original_post->post_author;

		// Post data
		$new_title = $original_post->post_title . ' ' . __( '(Salinan)', 'rejaton-product-catalog' );

		$new_post_args = array(
			'post_title'     => $new_title,
			'post_content'   => $original_post->post_content,
			'post_excerpt'   => $original_post->post_excerpt,
			'post_status'    => 'draft',
			'post_type'      => $original_post->post_type,
			'post_author'    => $new_author_id,
			'post_parent'    => $original_post->post_parent,
			'menu_order'     => $original_post->menu_order,
			'comment_status' => $original_post->comment_status,
			'ping_status'    => $original_post->ping_status,
		);

		$new_id = wp_insert_post( $new_post_args );

		if ( is_wp_error( $new_id ) || 0 === $new_id ) {
			wp_die( esc_html__( 'Gagal menduplikat item.', 'rejaton-product-catalog' ) );
		}

		// 1. Copy all custom taxonomies (category, tag)
		$taxonomies = get_object_taxonomies( $original_post->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $original_id, $taxonomy, array( 'fields' => 'slugs' ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}

		// 2. Copy all post meta (specifications, gallery, options)
		$post_metas = get_post_meta( $original_id );
		if ( ! empty( $post_metas ) ) {
			foreach ( $post_metas as $meta_key => $meta_values ) {
				// Skip internal WordPress lock/edit meta
				if ( in_array( $meta_key, array( '_edit_lock', '_edit_last' ), true ) ) {
					continue;
				}

				foreach ( $meta_values as $meta_val ) {
					// Unserialize data if it was stored serialized
					$val = maybe_unserialize( $meta_val );
					add_post_meta( $new_id, $meta_key, $val );
				}
			}
		}

		// Redirect directly to edit screen of the cloned item
		$redirect_url = add_query_arg(
			array(
				'action'            => 'edit',
				'post'              => $new_id,
				'rejaton_duplicated' => 1,
			),
			admin_url( 'post.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Display success notice when redirected to new post
	 */
	public function display_duplicate_notice() {
		if ( ! empty( $_GET['rejaton_duplicated'] ) ) {
			$screen = get_current_screen();
			$item_label = ( $screen && 'rejaton_template' === $screen->post_type )
				? __( 'Template deskripsi', 'rejaton-product-catalog' )
				: __( 'Produk', 'rejaton-product-catalog' );
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Berhasil diduplikat!', 'rejaton-product-catalog' ); ?></strong>
					<?php printf( esc_html__( '%s berhasil disalin sebagai draf baru. Silakan sesuaikan judul, isi, dan spesifikasi sebelum mempublikasikannya.', 'rejaton-product-catalog' ), esc_html( $item_label ) ); ?>
				</p>
			</div>
			<?php
		}
	}
}

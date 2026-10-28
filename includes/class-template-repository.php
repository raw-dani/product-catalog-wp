<?php
/**
 * Template Repository for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Template_Repository {

	const DEFAULT_TEMPLATE_OPTION = 'rejaton_default_template_id';

	/**
	 * Ensure the default Rejaton marketing & spec template exists in CPT rejaton_product_template
	 *
	 * @return int Template ID
	 */
	public static function ensure_default_template() {
		$default_id = get_option( self::DEFAULT_TEMPLATE_OPTION, 0 );

		if ( $default_id ) {
			$post = get_post( $default_id );
			if ( $post && in_array( $post->post_type, array( 'rejaton_template', 'rejaton_product_template' ), true ) && 'trash' !== $post->post_status ) {
				// Normalize post type to rejaton_template if it was legacy
				if ( 'rejaton_product_template' === $post->post_type ) {
					wp_update_post( array( 'ID' => $post->ID, 'post_type' => 'rejaton_template' ) );
				}
				return $default_id;
			}
		}

		// Check if any template exists (check both rejaton_template and legacy)
		$existing = get_posts( array(
			'post_type'      => array( 'rejaton_template', 'rejaton_product_template' ),
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'draft' ),
		) );

		if ( ! empty( $existing ) ) {
			if ( 'rejaton_product_template' === $existing[0]->post_type ) {
				wp_update_post( array( 'ID' => $existing[0]->ID, 'post_type' => 'rejaton_template' ) );
			}
			update_option( self::DEFAULT_TEMPLATE_OPTION, $existing[0]->ID );
			return $existing[0]->ID;
		}

		// Create the official initial default Rejaton template
		$content = self::get_initial_template_content();

		$template_id = wp_insert_post( array(
			'post_title'   => 'Template Default Pompa Air Rejaton',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'rejaton_template',
		) );

		if ( ! is_wp_error( $template_id ) ) {
			update_option( self::DEFAULT_TEMPLATE_OPTION, $template_id );
			return $template_id;
		}

		return 0;
	}

	/**
	 * Get initial HTML content for default template
	 *
	 * @return string
	 */
	public static function get_initial_template_content() {
		return <<<HTML
<h3>Pompa Air [Tipe Pompa]</h3>
<p>Pompa air tenaga surya LORENTZ [Tipe Pompa] merupakan solusi pompa air untuk kebutuhan air bersih, dengan kapasitas debit [Debit] liter/detik dan kemampuan head [Rentang Head] meter. Sistem ini dapat dipadukan dengan panel surya berkapasitas [Daya Panel Surya] kWp, berdasarkan konfigurasi teknis yang sesuai dengan spesifikasi pompa dan kebutuhan instalasi.</p>
<p>Dengan teknologi pompa air tenaga surya LORENTZ, sistem ini dapat membantu memenuhi kebutuhan air untuk irigasi pertanian, penyediaan air bersih, sumur bor, maupun aplikasi lainnya sesuai karakteristik model pompa.</p>

<h3>Spesifikasi dan Rekomendasi Sistem</h3>
<ol>
	<li>Tipe pompa: [Tipe Pompa]</li>
	<li>Kapasitas debit: [Debit] liter/detik atau [Debit × 3,6] m³/jam</li>
	<li>Rentang head: [Rentang Head] meter</li>
	<li>Rekomendasi kapasitas panel surya: [Daya Panel Surya] kWp</li>
</ol>

<h3>Mengapa Memilih Rejaton?</h3>
<p>PT Reja Aton Energi (Rejaton) merupakan distributor resmi pompa air tenaga surya dan pompa hybrid yang melayani kebutuhan proyek air bersih, irigasi pertanian, dan pengadaan pompa tenaga surya di berbagai wilayah Indonesia.</p>
<p>Rejaton memosisikan diri sebagai distributor pompa air tenaga surya No. 1 di Indonesia, dengan pengalaman sejak 2016 dan dukungan layanan untuk berbagai kebutuhan proyek.</p>
<p>Keunggulan layanan Rejaton meliputi:</p>
<ul>
	<li>Konsultasi teknis gratis untuk membantu menentukan tipe pompa dan konfigurasi sistem sesuai kebutuhan proyek.</li>
	<li>Rekomendasi sistem berdasarkan kebutuhan lapangan, termasuk debit, head, sumber air, dan kebutuhan energi.</li>
	<li>Dukungan instalasi proyek melalui tim teknis sesuai cakupan layanan dan kebutuhan lokasi.</li>
	<li>Dukungan dokumen pengadaan, seperti surat dukungan dan dokumen teknis sesuai ketersediaan.</li>
	<li>Pengalaman menangani proyek air bersih dan irigasi di berbagai wilayah Indonesia.</li>
</ul>
<p>Rejaton telah menyediakan kebutuhan pompa dan berkontribusi dalam berbagai proyek CSR, proyek Lembaga Non Profit (NGO), proyek Balai Wilayah Sungai, serta proyek kebutuhan air bersih dinas, termasuk JIAT, PAMSIMAS, dan SPAM.</p>

<h3>Konsultasikan Kebutuhan Pompa Anda</h3>
<p><strong>PT Reja Aton Energi (Rejaton)</strong></p>
HTML;
	}

	/**
	 * Get all active templates
	 *
	 * @return array
	 */
	public static function get_all_templates() {
		return get_posts( array(
			'post_type'      => array( 'rejaton_template', 'rejaton_product_template' ),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
	}

	/**
	 * Get template by ID
	 *
	 * @param int $template_id
	 * @return WP_Post|null
	 */
	public static function get_template( $template_id ) {
		$template = get_post( $template_id );
		if ( $template && in_array( $template->post_type, array( 'rejaton_template', 'rejaton_product_template' ), true ) ) {
			return $template;
		}
		return null;
	}

	/**
	 * Get effective template ID for a product
	 *
	 * @param int $product_id
	 * @return int
	 */
	public static function get_product_template_id( $product_id ) {
		$custom_template_id = (int) Rejaton_Catalog_Product_Meta::get_meta( $product_id, 'template_id', 0 );
		if ( $custom_template_id > 0 ) {
			$tpl = self::get_template( $custom_template_id );
			if ( $tpl && 'publish' === $tpl->post_status ) {
				return $custom_template_id;
			}
		}

		return (int) get_option( self::DEFAULT_TEMPLATE_OPTION, 0 );
	}
}

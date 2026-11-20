<?php
/**
 * Template Repository for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Template_Repository {

	const DEFAULT_TEMPLATE_OPTION = 'rejaton_default_template_id';
	const PDF_TEMPLATE_OPTION     = 'rejaton_pdf_template_id';

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
				$updates = array( 'ID' => $post->ID );
				$need_update = false;

				// Normalize post type to rejaton_template if it was legacy
				if ( 'rejaton_product_template' === $post->post_type ) {
					$updates['post_type'] = 'rejaton_template';
					$need_update = true;
				}

				// Update template to new debit format if it still uses legacy format
				if ( false !== strpos( $post->post_content, 'Kapasitas debit: [Debit] m³/jam' ) || false !== strpos( $post->post_content, 'Kapasitas debit: [Debit] m3/jam' ) ) {
					$updates['post_content'] = str_replace(
						array(
							'<li>Kapasitas debit: [Debit] m³/jam</li>',
							'<li>Kapasitas debit: [Debit] m3/jam</li>',
							'Kapasitas debit: [Debit] m³/jam',
							'Kapasitas debit: [Debit] m3/jam',
						),
						array(
							'<li>Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam</li>',
							'<li>Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam</li>',
							'Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam',
							'Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam',
						),
						$post->post_content
					);
					$need_update = true;
				}

				if ( $need_update ) {
					wp_update_post( $updates );
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
	 * Ensure the new PDF-based Marketing & Recommendation Template exists without overwriting existing templates
	 *
	 * @return int Template ID
	 */
	public static function ensure_pdf_template() {
		$pdf_template_id = (int) get_option( self::PDF_TEMPLATE_OPTION, 0 );

		if ( $pdf_template_id ) {
			$post = get_post( $pdf_template_id );
			if ( $post && in_array( $post->post_type, array( 'rejaton_template', 'rejaton_product_template' ), true ) && 'trash' !== $post->post_status ) {
				return $pdf_template_id;
			}
		}

		// Check if a template with the title already exists
		$existing = get_posts( array(
			'post_type'      => array( 'rejaton_template', 'rejaton_product_template' ),
			'title'          => 'Template Katalog & Rekomendasi Pompa Lorentz',
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'draft' ),
		) );

		if ( ! empty( $existing ) ) {
			update_option( self::PDF_TEMPLATE_OPTION, $existing[0]->ID );
			return $existing[0]->ID;
		}

		// Insert new template without overwriting existing templates
		$content = self::get_pdf_template_content();

		$new_template_id = wp_insert_post( array(
			'post_title'   => 'Template Katalog & Rekomendasi Pompa Lorentz',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'rejaton_template',
		) );

		if ( ! is_wp_error( $new_template_id ) ) {
			update_option( self::PDF_TEMPLATE_OPTION, $new_template_id );
			return $new_template_id;
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
<p>Pompa air tenaga surya LORENTZ [Tipe Pompa] merupakan solusi pompa air untuk kebutuhan air bersih, dengan kapasitas debit [Debit] m³/jam dan kemampuan head [Rentang Head] meter. Sistem ini dapat dipadukan dengan panel surya berkapasitas [Daya Panel Surya] kWp, berdasarkan konfigurasi teknis yang sesuai dengan spesifikasi pompa dan kebutuhan instalasi.</p>
<p>Dengan teknologi pompa air tenaga surya LORENTZ, sistem ini dapat membantu memenuhi kebutuhan air untuk irigasi pertanian, penyediaan air bersih, sumur bor, maupun aplikasi lainnya sesuai karakteristik model pompa.</p>

<h3>Spesifikasi dan Rekomendasi Sistem</h3>
<ol>
	<li>Tipe pompa: [Tipe Pompa]</li>
	<li>Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam</li>
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
	 * Get HTML content for the PDF-based Landing / Catalog Template
	 *
	 * @return string
	 */
	public static function get_pdf_template_content() {
		return <<<HTML
<h3>Pompa Air [Tipe Pompa]</h3>
<p>Pompa air tenaga surya LORENTZ [Tipe Pompa] merupakan solusi pompa air untuk kebutuhan air bersih, dengan kapasitas debit [Debit] m³/jam dan kemampuan head [Rentang Head] meter. Sistem ini dapat dipadukan dengan panel surya berkapasitas [Daya Panel Surya] kWp, berdasarkan konfigurasi teknis yang sesuai dengan spesifikasi pompa dan kebutuhan instalasi.</p>
<p>Dengan teknologi pompa air tenaga surya LORENTZ, sistem ini dapat membantu memenuhi kebutuhan air untuk irigasi pertanian, penyediaan air bersih, sumur bor, maupun aplikasi lainnya sesuai karakteristik model pompa.</p>

<h3>Tabel Spesifikasi Teknis</h3>
<table class="rj-spec-table" style="width:100%; border-collapse:collapse; margin-bottom:24px;">
	<thead>
		<tr style="background:#f8fafc;">
			<th style="padding:12px 16px; border:1px solid #e2e8f0; text-align:left; width:40%;">Parameter</th>
			<th style="padding:12px 16px; border:1px solid #e2e8f0; text-align:left;">Spesifikasi</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="padding:12px 16px; border:1px solid #e2e8f0; font-weight:600;">Tipe pompa</td>
			<td style="padding:12px 16px; border:1px solid #e2e8f0;">[Tipe Pompa]</td>
		</tr>
		<tr>
			<td style="padding:12px 16px; border:1px solid #e2e8f0; font-weight:600;">Debit Pompa Air</td>
			<td style="padding:12px 16px; border:1px solid #e2e8f0;">[Debit per detik] liter/detik</td>
		</tr>
		<tr>
			<td style="padding:12px 16px; border:1px solid #e2e8f0; font-weight:600;">Debit m³/jam</td>
			<td style="padding:12px 16px; border:1px solid #e2e8f0;">[Debit] m³/jam</td>
		</tr>
		<tr>
			<td style="padding:12px 16px; border:1px solid #e2e8f0; font-weight:600;">Rentang Head</td>
			<td style="padding:12px 16px; border:1px solid #e2e8f0;">[Rentang Head] meter</td>
		</tr>
		<tr>
			<td style="padding:12px 16px; border:1px solid #e2e8f0; font-weight:600;">Daya Panel Surya</td>
			<td style="padding:12px 16px; border:1px solid #e2e8f0;">[Daya Panel Surya] kWp</td>
		</tr>
	</tbody>
</table>

<h3>Jual Lorentz [Tipe Pompa] untuk Berbagai Kebutuhan Proyek</h3>
<p>Lorentz [Tipe Pompa] merupakan solusi pompa air yang dapat mendukung berbagai kebutuhan pengadaan dan operasional, mulai dari proyek penyediaan air bersih, irigasi pertanian, hingga infrastruktur dan fasilitas umum. Bagi Anda yang mencari tempat jual Lorentz [Tipe Pompa], Rejaton menyediakan pilihan produk dari merek terpercaya dengan dukungan konsultasi teknis, rekomendasi produk, serta pendampingan kebutuhan instalasi sesuai cakupan layanan.</p>
<p>Berpengalaman sejak 2016, Rejaton turut mendukung berbagai kebutuhan proyek CSR, NGO, Balai Wilayah Sungai, JIAT, PAMSIMAS, dan SPAM di Indonesia.</p>

<h3>Harga dan Distributor Lorentz [Tipe Pompa] di Rejaton</h3>
<p>Sedang mencari harga [Tipe Pompa] terbaru [Bulan] [Tahun] untuk kebutuhan proyek atau instalasi? Rejaton menyediakan [Tipe Pompa] untuk memenuhi kebutuhan pengadaan pompa air di berbagai wilayah Indonesia. Sebagai distributor Lorentz, Rejaton siap membantu pelanggan yang ingin membeli [Tipe Pompa] melalui konsultasi teknis, rekomendasi produk berdasarkan kebutuhan proyek, dukungan instalasi, serta dokumen pengadaan sesuai ketersediaan. Hubungi tim Rejaton untuk mendapatkan informasi produk, konsultasi, dan penawaran harga terbaik.</p>

<h3>Rekomendasi Pompa Air Lainnya</h3>
[rejaton_recommended_products count="3" orderby="rand"]

<h3>Konsultasikan Kebutuhan Pompa Anda</h3>
<p><strong>PT Reja Aton Energi (Rejaton)</strong></p>
<p>
	Kontak WA : <a href="https://wa.me/6281290362622" target="_blank" rel="noopener noreferrer">CS Online</a><br>
	Instagram: <a href="https://www.instagram.com/rejaton_pompasurya/" target="_blank" rel="noopener noreferrer">Rejaton Pompa Surya</a><br>
	Project: <a href="https://www.instagram.com/rejatonprojects/" target="_blank" rel="noopener noreferrer">Rejaton Projects</a><br>
	Facebook: <a href="https://www.facebook.com/rejatonpompasurya/" target="_blank" rel="noopener noreferrer">Rejaton Pompa Surya</a><br>
	Youtube: <a href="https://www.youtube.com/@rejatonpompasurya" target="_blank" rel="noopener noreferrer">@rejatonpompasurya</a><br>
	Tiktok : <a href="https://www.tiktok.com/@rejatonpompasurya" target="_blank" rel="noopener noreferrer">@rejatonpompasurya</a>
</p>

<h3>Jual Pompa Air Berkualitas untuk Berbagai Kebutuhan</h3>
<p>Rejaton menyediakan berbagai pilihan pompa air Lorentz untuk mendukung kebutuhan penyediaan air bersih, irigasi pertanian, proyek infrastruktur, dan aplikasi lainnya. Tersedia beragam tipe pompa dengan spesifikasi dan teknologi yang dapat disesuaikan dengan kondisi sumber air serta kebutuhan operasional. Rejaton membantu pelanggan menentukan solusi pemompaan yang tepat melalui konsultasi teknis dan layanan pengiriman ke berbagai wilayah Indonesia.</p>

<h3>Pilihan Produk Pompa Air untuk Kebutuhan Proyek</h3>
<p>Temukan berbagai pilihan pompa air dengan teknologi dan karakteristik yang dirancang untuk menunjang kebutuhan pemompaan di berbagai sektor. Produk dapat digunakan untuk aplikasi sumur bor, distribusi air bersih, pengairan lahan pertanian, hingga proyek pemerintah dan sosial. Setiap tipe memiliki spesifikasi tersendiri sehingga pemilihan produk perlu mempertimbangkan kondisi lapangan, sumber energi, kebutuhan debit, serta ketinggian pemompaan.</p>

<h3>Harga Pompa Air [Bulan] [Tahun] Terbaru di Rejaton</h3>
<p>Cari informasi harga pompa air [Bulan] [Tahun] terbaru melalui Rejaton untuk mendapatkan pilihan produk yang sesuai dengan kebutuhan dan anggaran proyek. Tersedia informasi mengenai tipe, spesifikasi teknis, dan karakteristik setiap produk sebagai bahan pertimbangan sebelum pembelian. Hubungi tim Rejaton untuk memperoleh konsultasi pemilihan pompa, informasi ketersediaan produk, serta penawaran harga dengan dukungan pengiriman ke seluruh Indonesia.</p>
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

	/**
	 * Migrate all existing templates in database to clean legacy debit string
	 */
	public static function migrate_legacy_templates() {
		// Only run if not already migrated or once per day/option
		$migrated_ver = get_option( 'rejaton_templates_debit_migrated', '' );
		if ( '1.0.7' === $migrated_ver ) {
			return;
		}

		$templates = get_posts( array(
			'post_type'      => array( 'rejaton_template', 'rejaton_product_template' ),
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		foreach ( $templates as $t ) {
			$needs_update = false;
			$new_content  = $t->post_content;

			if ( false !== strpos( $new_content, 'Kapasitas debit: [Debit]' ) ) {
				$new_content = str_replace(
					array(
						'<li>Kapasitas debit: [Debit] m³/jam</li>',
						'<li>Kapasitas debit: [Debit] m3/jam</li>',
						'Kapasitas debit: [Debit] m³/jam',
						'Kapasitas debit: [Debit] m3/jam',
					),
					array(
						'<li>Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam</li>',
						'<li>Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam</li>',
						'Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam',
						'Debit pompa air: [Debit per detik] liter/detik atau [Debit] m³/jam',
					),
					$new_content
				);
				if ( $new_content !== $t->post_content ) {
					$needs_update = true;
				}
			}

			$post_type = ( 'rejaton_product_template' === $t->post_type ) ? 'rejaton_template' : $t->post_type;
			if ( $post_type !== $t->post_type ) {
				$needs_update = true;
			}

			if ( $needs_update ) {
				wp_update_post( array(
					'ID'           => $t->ID,
					'post_content' => $new_content,
					'post_type'    => $post_type,
				) );
			}
		}

		update_option( 'rejaton_templates_debit_migrated', '1.0.7' );

		// Flush all caches so frontends see updated content immediately
		if ( class_exists( 'Rejaton_Catalog_Cache' ) ) {
			$cache = new Rejaton_Catalog_Cache();
			$cache->flush_all_cache();
		}
	}
}

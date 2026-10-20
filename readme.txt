=== Rejaton Product Catalog ===
Contributors: rejaton
Tags: product catalog, solar pump, pompa surya, custom fields, lorentz
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Katalog produk pompa air tenaga surya mandiri tanpa ketergantungan WooCommerce, dilengkapi custom fields, dynamic template engine, filter frontend, dan integrasi WhatsApp CTA.

== Description ==

Rejaton Product Catalog adalah plugin WordPress khusus yang dirancang untuk mengelola katalog produk pompa air tenaga surya LORENTZ dan sistem hybrid tanpa memerlukan plugin WooCommerce atau Elementor Pro. Plugin ini bekerja optimal dengan Classic Editor dan menyediakan shortcode fleksibel yang dapat disematkan di mana saja (termasuk Elementor free & Gutenberg).

= Fitur Utama =

* **Custom Post Type & Taksonomi**: Manajemen produk, kategori hierarkis, dan tag produk yang rapi.
* **Dynamic Description Template Engine**: Tulis template satu kali dengan variabel token seperti `[Tipe Pompa]`, `[Debit]`, `[Rentang Head]`, `[Daya Panel Surya]`, dan rumus turunan `[Debit × 3,6]`. Deskripsi semua produk akan terbarui secara otomatis ketika template diedit.
* **Perhitungan Otomatis Debit**: Mengonversi debit liter/detik menjadi m³/jam secara akurat (contoh: 2,5 liter/detik menjadi 9 m³/jam).
* **Atribut & Custom Field Manager**: Tambahkan custom field baru melalui dashboard admin tanpa menyentuh source code.
* **Frontend Katalog 3-Kolom Responsif**: Grid 3-kolom di desktop, 2-kolom di tablet, dan 1-kolom di ponsel dengan filter drawer yang nyaman.
* **Filter Numerik & Atribut**: Filter pencarian, kategori, rentang debit, rentang head, dan daya panel surya dengan AJAX instan tanpa reload penuh.
* **Halaman Detail Produk & Galeri**: Dilengkapi galeri foto interaktif, tabel spesifikasi teknis, breadcrumb, dan tombol CTA konsultasi WhatsApp langsung ke CS.

== Installation ==

1. Upload folder `rejaton-product-catalog` ke direktori `/wp-content/plugins/` atau upload file ZIP melalui menu **Plugins > Add New > Upload Plugin**.
2. Aktifkan plugin melalui menu **Plugins** di WordPress.
3. Buka menu **Katalog Rejaton > Pengaturan Katalog** untuk mengisi nomor WhatsApp CS dan mengatur opsi tampilan.
4. Mulai tambahkan produk melalui menu **Katalog Rejaton > Tambah Produk**.
5. Sematkan katalog pada halaman dengan shortcode `[rejaton_product_catalog]`.

== Frequently Asked Questions ==

= Apakah plugin ini memerlukan WooCommerce? =
Tidak. Plugin ini 100% mandiri dan tidak memerlukan WooCommerce.

= Apakah kompatibel dengan Elementor dan Astra? =
Ya, sangat kompatibel. Gunakan widget Shortcode di Elementor dengan kode `[rejaton_product_catalog]`. Styling CSS telah di-namespace dengan prefix `.rj-` agar tidak bentrok dengan tema Astra maupun tema lainnya.

== Changelog ==

= 1.0.0 =
* Rilis perdana Rejaton Product Catalog dengan CPT produk, custom field manager, template engine dinamis, filter frontend AJAX, dan integrasi CTA WhatsApp.

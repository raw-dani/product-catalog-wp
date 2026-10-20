# Rejaton Product Catalog

Plugin WordPress mandiri untuk manajemen katalog produk pompa air tenaga surya tanpa ketergantungan WooCommerce, Elementor Pro, atau plugin pihak ketiga lainnya. Plugin ini dirancang untuk alur kerja yang mudah menggunakan Classic Editor serta menyediakan shortcode untuk Elementor, Gutenberg, dan tema Astra.

## Fitur Utama

1. **Manajemen Produk Mandiri (CPT & Taksonomi)**
   - Custom Post Type: `rejaton_product`.
   - Taksonomi kategori hierarkis (`rejaton_product_cat`) dan tag (`rejaton_product_tag`).
   - Foto utama (Featured Image) dan Galeri Foto interaktif dari Media Library WordPress.
   - Status produk unggulan (*featured*).

2. **Mesin Template Deskripsi Dinamis (Dynamic Template Engine)**
   - Buat dan kelola template deskripsi secara terpusat melalui CPT `rejaton_product_template`.
   - Sisipkan token dinamis seperti `[Tipe Pompa]`, `[Debit]`, `[Rentang Head]`, `[Daya Panel Surya]`, dan `[Debit × 3,6]`.
   - Perhitungan otomatis debit: `m³/jam = debit (liter/detik) × 3.6` (contoh: 2,5 L/detik menghasilkan 9 m³/jam).
   - Mendukung blok kondisional aman `[IF:field_key]...[/IF]`.
   - Saat template diubah, seluruh produk yang menggunakan template tersebut langsung terbarui tanpa perlu diedit satu per satu.
   - Dilengkapi validasi token dan live preview dengan data produk aktual.

3. **Manajer Custom Field Tanpa Koding**
   - Tambahkan atribut teknis baru langsung dari dashboard WordPress (**Katalog Rejaton > Atribut & Custom Field**).
   - Mendukung tipe data Text, Number, Textarea, dan Dropdown Select.
   - Tentukan apakah field muncul pada kartu katalog, tabel spesifikasi, atau sidebar filter.

4. **Frontend Katalog Responsif & Filter AJAX**
   - Tampilan grid desktop 3 kolom, tablet 2 kolom, dan smartphone 1 kolom.
   - Sidebar filter di sebelah kiri: pencarian model/tipe, kategori hierarkis, rentang debit, rentang head, dan daya panel surya.
   - Drawer filter mobile yang dapat dibuka dan ditutup dengan mulus.
   - Pembaruan hasil produk via AJAX instan dengan pagination dan reset filter.

5. **Halaman Detail Produk & CTA Konsultasi WhatsApp**
   - Galeri foto interaktif dengan thumbnail switcher.
   - Blok spesifikasi ringkas (quick highlight chips).
   - Deskripsi hasil render template dinamis.
   - Tabel spesifikasi teknis lengkap.
   - Tombol CTA konsultasi WhatsApp dengan nomor resmi Rejaton dan template pesan otomatis yang ter-encode rapi.

6. **Keamanan, SEO & Kompatibilitas Tema**
   - Pemeriksaan hak akses (`current_user_can`) dan verifikasi `nonce` pada semua form dan endpoint AJAX.
   - Sanitasi input dan escaping output kontekstual (`wp_kses_post`, `esc_html`, `esc_attr`, `esc_url`).
   - Penanganan canonical, OpenGraph, dan proteksi `noindex,follow` untuk query URL filter.
   - Namespace CSS khusus `.rj-` untuk mencegah tabrakan style dengan tema Astra maupun tema WordPress lainnya.
   - Terintegrasi dengan WordPress Object Cache dan LiteSpeed Cache purging hooks.

## Persyaratan Sistem

- **WordPress**: Versi 5.8 atau lebih baru.
- **PHP**: Versi 7.4 hingga 8.2+.
- **Tema Rekomendasi**: Astra, Hello Elementor, GeneratePress, atau tema WordPress standar.

## Penggunaan Shortcode

Sematkan shortcode berikut di halaman Classic Editor atau widget Shortcode Elementor:

```text
[rejaton_product_catalog]
```

Atribut yang didukung:
- `columns="3"` : Jumlah kolom produk pada desktop (pilihan: 2, 3, atau 4).
- `per_page="12"` : Jumlah produk yang ditampilkan per halaman.
- `category="pompa-submersible"` : Menampilkan kategori tertentu sejak awal.
- `show_filters="true"` : Tampilkan/sembunyikan sidebar filter (`true`/`false`).
- `show_sorting="true"` : Tampilkan/sembunyikan dropdown pengurutan (`true`/`false`).

## Dokumentasi Tambahan

- [Panduan Instalasi & Update](docs/INSTALLATION.md)
- [Panduan Administrator & Input Produk](docs/ADMIN-GUIDE.md)
- [Panduan Pembuatan Template & Variabel Token](docs/TEMPLATE-GUIDE.md)

## Lisensi

Plugin ini berlisensi [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

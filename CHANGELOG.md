# Changelog - Rejaton Product Catalog

Semua perubahan penting pada plugin Rejaton Product Catalog didokumentasikan di file ini.

## [1.0.0] - 2026-10-20

### Added
- **Bootstrap Plugin**: File utama, loader modular, aktivator, dan deactivator.
- **Custom Post Type & Taksonomi**:
  - CPT `rejaton_product` untuk produk.
  - CPT privat `rejaton_product_template` untuk template deskripsi dinamis.
  - Taksonomi kategori hierarkis `rejaton_product_cat` dan tag `rejaton_product_tag`.
- **Manajemen Data Teknis & Media**:
  - Meta box spesifikasi: Tipe Pompa, Debit (L/s), Head Min & Max (meter), Daya Panel Surya (kWp).
  - Galeri foto produk via WordPress Media Library dengan multi-image selector.
  - Opsi produk unggulan (Featured).
- **Dynamic Template Engine**:
  - Parser regex aman tanpa `eval()`.
  - Dukungan token standar: `[Tipe Pompa]`, `[Debit]`, `[Rentang Head]`, `[Daya Panel Surya]`, `[Debit × 3,6]`.
  - Dukungan conditional block: `[IF:field_key]...[/IF]`.
  - Seeding otomatis template default resmi Rejaton saat aktivasi.
  - Live preview dan validasi token di admin template editor.
- **Atribut & Custom Field Manager**:
  - Halaman dashboard untuk menambah custom field baru tanpa mengubah source code.
  - Dukungan tipe Text, Number, Textarea, dan Select.
- **Frontend & Filter**:
  - Template arsip katalog dengan grid 3-kolom (desktop), 2-kolom (tablet), dan 1-kolom (ponsel).
  - Sidebar filter kiri dengan filter teks, kategori hierarkis, rentang debit, rentang head, dan daya panel.
  - Filter drawer responsif untuk perangkat mobile.
  - Filter AJAX real-time dengan pagination dan pengurutan (terbaru, abjad, debit).
  - Template detail produk dengan galeri thumbnail interaktif, quick specs chips, deskripsi dinamis, tabel spesifikasi, dan tombol CTA WhatsApp ter-encode.
- **Integrasi Tema & Plugin**:
  - Shortcode `[rejaton_product_catalog]` untuk Classic Editor dan Elementor.
  - Namespace CSS `.rj-` untuk perlindungan terhadap tema Astra.
  - Hook invalidasi cache untuk WordPress Object Cache dan LiteSpeed Cache.
  - Integrasi SEO dasar dengan perlindungan canonical dan noindex query filter.
- **Testing & QA**:
  - Unit test suite mandiri untuk kalkulasi debit, rentang head, validator token, dan parsing conditional block.

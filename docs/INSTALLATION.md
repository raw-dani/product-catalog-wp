# Panduan Instalasi Rejaton Product Catalog

## 1. Persyaratan Lingkungan
- WordPress versi 5.8 atau lebih baru.
- PHP versi 7.4, 8.0, 8.1, atau 8.2+.
- MySQL 5.7+ atau MariaDB 10.3+.

## 2. Cara Pemasangan Melalui Dashboard WordPress
1. Unduh arsip file `rejaton-product-catalog.zip`.
2. Masuk ke halaman admin WordPress (`/wp-admin`).
3. Buka menu **Plugins > Add New** (Tambah Baru).
4. Klik tombol **Upload Plugin** (Unggah Plugin) di bagian atas layar.
5. Pilih file `rejaton-product-catalog.zip` dari komputer Anda, lalu klik **Install Now**.
6. Setelah instalasi selesai, klik **Activate Plugin**.

## 3. Cara Pemasangan Manual via FTP / File Manager
1. Ekstrak file `rejaton-product-catalog.zip`.
2. Unggah folder `rejaton-product-catalog` ke direktori server: `/wp-content/plugins/`.
3. Buka menu **Plugins** di admin WordPress dan klik **Activate** pada plugin Rejaton Product Catalog.

## 4. Konfigurasi Awal Pasca Aktivasi
1. Buka menu **Katalog Rejaton > Pengaturan Katalog**.
2. Masukkan nomor WhatsApp CS resmi Rejaton (contoh: `08123456789`).
3. Periksa pengaturan kolom katalog desktop (standar: 3 kolom) dan produk per halaman (standar: 12).
4. Klik **Simpan Perubahan**.

## 5. Menampilkan Katalog di Website
- **Melalui Classic Editor**: Buat halaman baru (misal: "Katalog Pompa"), ketikkan shortcode `[rejaton_product_catalog]` pada editor, lalu publikasikan.
- **Melalui Elementor**: Tambahkan widget "Shortcode" ke area halaman Anda, masukkan `[rejaton_product_catalog]`, lalu klik Update.

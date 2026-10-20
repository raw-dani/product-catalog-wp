# Panduan Pengguna & Administrator Rejaton Product Catalog

## 1. Menambahkan Produk Baru
1. Masuk ke **Katalog Rejaton > Tambah Produk**.
2. Masukkan **Nama Produk** pada kolom judul (contoh: *Pompa Air Tenaga Surya LORENTZ PS2-1800 CS-F*).
3. Isi ringkasan singkat pada kotak **Excerpt / Kutipan** jika diperlukan.
4. Pada kolom kanan, pilih atau unggah **Foto Utama Produk** (*Featured Image*).
5. Pada meta box **Spesifikasi & Data Teknis Pompa Air**, isi data:
   - **Tipe Pompa**: Model pompa (misal: `PS2-1800 CS-F`).
   - **Debit**: Nilai debit dalam satuan liter/detik (misal: `2.5`). Sistem otomatis menghitung nilai m³/jam (contoh: 9 m³/jam).
   - **Rentang Head**: Masukkan batas `Min` dan `Max` dalam meter (misal: `40` s/d `80`).
   - **Daya Panel Surya**: Nilai daya panel surya dalam kWp (misal: `1.8`).
6. Pada meta box **Galeri Foto Produk**, klik tombol **Kelola / Tambah Foto Galeri** untuk memilih beberapa foto tambahan dari Media Library.
7. Pilih **Kategori Produk** dan **Tag Produk** di sisi kanan.
8. Centang kotak **Produk Unggulan** jika ingin memberi badge khusus "Unggulan" pada produk ini.
9. Klik tombol **Terbitkan** (*Publish*).

## 2. Mengatur Template Deskripsi Produk
- Secara default, setiap produk baru akan menggunakan **Template Default Global Rejaton**.
- Anda dapat melihat hasil akhir teks deskripsi pada bagian **Live Preview Hasil Render Template** di halaman edit produk.
- Jika produk tertentu memerlukan format deskripsi yang berbeda, pilih template lain pada dropdown **Pilih Template Deskripsi**.

## 3. Menambahkan Atribut / Custom Field Baru
Jika ada spesifikasi teknis baru yang ingin ditambahkan (misalnya *Voltase Maksimal* atau *Diameter Pipa Output*):
1. Buka menu **Katalog Rejaton > Atribut & Custom Field**.
2. Pada formulir sebelah kiri:
   - Masukkan **Label Field** (misal: `Diameter Pipa Output`).
   - Masukkan **Field Key** berupa huruf kecil tanpa spasi (misal: `diameter_pipa`).
   - Pilih **Tipe Data** (`text`, `number`, `textarea`, atau `select`).
   - Masukkan satuan jika ada (misal: `dim` atau `inch`).
   - Masukkan nama token jika ingin disisipkan ke template (misal: `[Diameter Pipa]`).
   - Centang visibilitas (Kartu Katalog, Tabel Spesifikasi, Filter Sidebar).
3. Klik **Simpan Custom Field**.
4. Field ini akan langsung muncul di halaman edit semua produk.

## 4. Konfigurasi Nomor WhatsApp CS
1. Buka menu **Katalog Rejaton > Pengaturan Katalog**.
2. Pada kolom **Nomor WhatsApp CS**, masukkan nomor telepon aktif CS Rejaton (gunakan format Indonesia seperti `08123456789` atau `628123456789`).
3. Anda dapat menyesuaikan template pesan otomatis jika diperlukan.
4. Klik **Simpan Perubahan**.

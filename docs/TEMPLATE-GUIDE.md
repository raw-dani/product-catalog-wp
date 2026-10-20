# Panduan Template & Token Rejaton Product Catalog

## 1. Konsep Dynamic Template
Rejaton Product Catalog menggunakan pendekatan template dinamis terpusat. Anda cukup merancang format deskripsi dan penawaran teknis satu kali, lalu menyisipkan variabel (token) spesifikasi ke dalamnya. 

Ketika ada penyesuaian materi promosi, nomor legalitas, atau format spesifikasi, Anda cukup mengedit template tersebut. Seluruh produk yang terhubung akan langsung menampilkan deskripsi baru tanpa perlu membuka dan mengedit ratusan produk secara manual.

## 2. Daftar Token Standar yang Didukung

| Token | Sumber Data | Keterangan Format / Contoh Hasil |
|---|---|---|
| `[Tipe Pompa]` | Field Tipe Pompa | Contoh: `PS2-1800 CS-F` |
| `[Debit]` | Field Debit (liter/detik) | Diformat dengan pemisah koma Indonesia. Contoh: `2,5` |
| `[Rentang Head]` | Field Head Min & Max | Otomatis diformat: `40–80 meter` atau `Mulai dari 40 meter` |
| `[Daya Panel Surya]` | Field Daya Panel (kWp) | Contoh: `1,8` |
| `[Debit × 3,6]` | Kalkulasi otomatis | `debit * 3.6`. Contoh: 2,5 L/s menghasilkan `9` m³/jam |

Jika Anda telah menambahkan custom field baru dengan token (misalnya `[Voltase Maksimal]`), token tersebut juga akan otomatis terdaftar dan dapat disisipkan.

## 3. Menyisipkan Token ke Editor
Saat membuka menu **Katalog Rejaton > Template Deskripsi > Tambah/Edit Template**, akan muncul bilah tombol token di atas area editor teks:
- Klik tombol token (misalnya `+ [Tipe Pompa]`) untuk langsung menyisipkan token tersebut pada posisi kursor di editor teks atau Visual Editor.

## 4. Penggunaan Blok Kondisional ([IF])
Jika ada bagian teks yang hanya ingin ditampilkan apabila produk memiliki spesifikasi tertentu, gunakan blok `[IF:field_key]`:

```html
[IF:daya_panel_surya]
<p>Rekomendasi kapasitas panel surya untuk model ini adalah <strong>[Daya Panel Surya] kWp</strong>.</p>
[/IF]
```

Jika produk tidak mengisi daya panel surya, paragraf di dalam blok `[IF]` tidak akan dirender sehingga tampilan tetap rapi.

## 5. Live Preview & Validasi
Di bawah area penulisan template, terdapat panel **Live Preview & Validasi Token Template**:
- Sistem akan memeriksa apakah ada token yang salah ketik (token tidak dikenal).
- Token tidak dikenal akan diberi peringatan warna kuning/merah agar admin dapat memperbaikinya sebelum dipublikasikan.
- Anda dapat memilih produk nyata pada dropdown preview untuk mensimulasikan bagaimana template tersebut tampil pada produk tertentu.

# DOKUMENTASI FITUR EXPORT DATA RIWAYAT

## Deskripsi
Fitur ini memungkinkan user untuk mengeksport data riwayat aktivitas dalam berbagai format:
- **CSV** - Format teks terpisah koma (kompatibel dengan Excel, Google Sheets, dll)
- **Excel** - Format .xlsx (Microsoft Excel)
- **PDF** - Format dokumen portabel

## Instalasi Library (Opsional)

### Untuk Export Excel (.xlsx)
Export CSV akan selalu bekerja tanpa library tambahan. Namun, untuk Excel dengan formatting yang lebih baik, install PhpSpreadsheet:

```bash
composer require phpoffice/phpspreadsheet
```

### Untuk Export PDF
Untuk export PDF dengan styling yang lebih baik, install Dompdf:

```bash
composer require barryvdh/laravel-dompdf
```

Atau install Dompdf langsung:
```bash
composer require dompdf/dompdf
```

## Cara Penggunaan

1. **Klik tombol "Ekspor"** pada halaman Riwayat Aktivitas
2. **Pilih format** yang diinginkan:
   - Excel (.xlsx) - Rekomendasi untuk data banyak
   - PDF (.pdf) - Untuk laporan profesional
   - CSV (.csv) - Kompatibel universal
3. **File akan otomatis diunduh** dengan format: `Riwayat_Aktivitas_[TANGGAL_WAKTU].[format]`

## Filter yang Diterapkan Saat Export

Export akan menerapkan filter yang sama dengan yang sedang aktif di halaman:
- **Search** - Pencarian pengguna, aktivitas, atau IP
- **Type Filter** - Filter jenis aktivitas (Sirine)

## Struktur Data yang Diekspor

Setiap baris export berisi:
| Kolom | Deskripsi |
|-------|-----------|
| No | Nomor urut data |
| Waktu | Tanggal dan jam aktivitas (format: dd-mm-yyyy hh:mm:ss) |
| Pengguna | Nama pengguna yang melakukan aktivitas |
| Jenis Aktivitas | Tipe aktivitas (ALARM_ON, ALARM_OFF, AUTO_OFF) |
| IP Address | Alamat IP dari perangkat |
| Deskripsi | Deskripsi lengkap aktivitas |

## Fallback Behavior

- Jika library PhpOffice/PhpSpreadsheet tidak tersedia, Excel export akan fallback ke CSV
- Jika library Dompdf tidak tersedia, PDF export akan fallback ke CSV
- CSV export selalu tersedia tanpa dependency eksternal

## File yang Dimodifikasi

1. **app/Http/Controllers/Admin/RiwayatController.php** - Menambah method `export()` dan helper methods
2. **resources/views/admin/riwayat.blade.php** - Menambah modal export dan JavaScript functions
3. **resources/views/exports/riwayat-pdf.blade.php** - Template PDF (NEW)
4. **routes/web.php** - Menambah route `/admin/riwayat/export`

## Troubleshooting

### File tidak terunduh
- Pastikan pop-up blocker tidak menutup download
- Cek browser console untuk error

### Export PDF tidak bekerja
- Install Dompdf: `composer require dompdf/dompdf`
- Export akan fallback ke CSV jika library tidak ada

### Karakter khusus tidak tampil dengan benar di Excel
- Pastikan encoding UTF-8 digunakan
- Buka file dengan Excel dan set encoding ke UTF-8

## Keamanan

- User hanya bisa export data yang bisa dilihat (tergantung role/filter)
- Export dengan server-side validation
- File di-download langsung dari server, bukan disimpan

## Performance Notes

- Export hingga 10,000+ data biasanya tidak ada masalah
- Untuk dataset sangat besar, pertimbangkan batching atau async export
- CSV paling cepat, PDF paling lambat


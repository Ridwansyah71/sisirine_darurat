# Dokumentasi Fitur Export & Import User

## Deskripsi
Fitur export dan import memungkinkan admin untuk:
1. **Export**: Mengunduh semua data user dalam format CSV
2. **Import**: Upload file CSV untuk menambahkan multiple users sekaligus

## Fitur Export

### Cara Menggunakan
1. Buka halaman **Manajemen Pengguna** (Admin → Pengguna)
2. Klik tombol **Export** (warna ungu)
3. File CSV akan otomatis terunduh

### Format File Export
```
ID;Nama;Email;No. Telepon;Role;Status;Tanggal Dibuat
1;Admin Utama;admin@example.com;081234567;ADMIN;Aktif;2026-01-23 10:30:00
2;User Pertama;user@example.com;082345678;USER;Aktif;2026-01-22 15:45:00
```

### Kolom dalam Export
- **ID**: ID user (auto increment)
- **Nama**: Nama lengkap user
- **Email**: Email address
- **No. Telepon**: Nomor telepon
- **Role**: ADMIN atau USER
- **Status**: Aktif atau Tidak Aktif
- **Tanggal Dibuat**: Timestamp pembuatan akun

## Fitur Import

### Cara Menggunakan
1. Buka halaman **Manajemen Pengguna** (Admin → Pengguna)
2. Klik tombol **Import** (warna hijau)
3. Download template CSV (opsional, tersedia di modal)
4. Isi data user di Excel/Notepad dengan format CSV
5. Upload file CSV
6. Sistem akan validate dan import otomatis

### Format File Import

#### Template CSV
```
ID;Nama;Email;No. Telepon;Role;Status
1;John Doe;john@example.com;081234567890;ADMIN;Aktif
2;Jane Smith;jane@example.com;082345678901;USER;Aktif
3;Bob Johnson;bob@example.com;083456789012;USER;Tidak Aktif
```

#### Penjelasan Kolom
| Kolom | Tipe | Required | Keterangan |
|-------|------|----------|-----------|
| ID | Integer | No | Bisa dikosongkan, hanya untuk reference |
| Nama | String | **Yes** | Nama lengkap user (max 255 karakter) |
| Email | Email | **Yes** | Email harus unik, tidak boleh duplikat |
| No. Telepon | String | No | Nomor telepon (bisa dikosongkan) |
| Role | String | **Yes** | ADMIN atau USER (case insensitive) |
| Status | String | No | aktif/active/1/yes = aktif, selain itu = tidak aktif |

### Validasi Import

Sistem akan melakukan validasi berikut:
- ✅ Email harus unik (tidak boleh sudah terdaftar)
- ✅ Email harus format valid
- ✅ Nama tidak boleh kosong
- ✅ Role harus ADMIN atau USER
- ✅ Role case insensitive (admin, Admin, ADMIN semuanya valid)
- ✅ File harus CSV atau TXT
- ✅ Max file size: 2MB

### Default Password untuk Import
Ketika import user, password default yang diberikan adalah:
```
Password123!
```

User harus mengganti password saat login pertama kali (jika ada fitur force change password).

### Hasil Import
Setelah upload, sistem akan:
1. Validasi setiap baris data
2. Report jumlah user yang berhasil ditambahkan
3. Report jumlah error (jika ada)
4. Tampilkan error detail untuk setiap baris yang gagal

### Contoh Error Messages
```
Baris 2: Email john@example.com sudah terdaftar
Baris 3: Nama, Email, dan Role harus diisi
Baris 5: Role MANAGER tidak valid (gunakan ADMIN atau USER)
```

## Membuat File CSV dengan Excel

### Langkah-langkah
1. Buka Excel
2. Buat kolom dengan header: `ID`, `Nama`, `Email`, `No. Telepon`, `Role`, `Status`
3. Isi data user
4. **Penting**: Set delimiter menjadi semicolon (;)
   - File → Options → Advanced
   - Cari: Separators for delimiter
   - Ubah menjadi semicolon
5. Save as → Format CSV UTF-8 (.csv)

### Atau menggunakan Notepad
1. Buka Notepad
2. Ketik data dengan format:
```
ID;Nama;Email;No. Telepon;Role;Status
;John Doe;john@example.com;081234567;ADMIN;Aktif
;Jane Smith;jane@example.com;082345678;USER;Aktif
```
3. Save as → Encoding: UTF-8 → Extension: .csv

## Route & Endpoints

### Export
- **Route**: `GET /admin/pengguna/export`
- **Name**: `admin.pengguna.export`
- **Method**: GET
- **Response**: CSV file download

### Import
- **Route**: `POST /admin/pengguna/import`
- **Name**: `admin.pengguna.import`
- **Method**: POST (multipart/form-data)
- **Parameter**: `file` (CSV file)
- **Response**: Redirect dengan session messages

### Template Download
- **Route**: `GET /admin/template/users`
- **Name**: `admin.template.users`
- **Method**: GET
- **Response**: CSV template download

## Controller Methods

### UserController@export
```php
public function export()
```
- Generate CSV dari semua users
- Set UTF-8 BOM untuk kompatibilitas Excel
- Return StreamedResponse

### UserController@import
```php
public function import(Request $request)
```
- Validate file upload (CSV/TXT, max 2MB)
- Parse CSV dengan delimiter semicolon
- Validate setiap row
- Create user jika valid
- Return redirect dengan messages

## Best Practices

1. **Backup Before Import**
   - Selalu backup database sebelum import besar-besaran

2. **Validate Data**
   - Check data di Excel sebelum export
   - Gunakan template yang disediakan

3. **Email Validation**
   - Pastikan email unik sebelum import
   - Format email harus valid

4. **Role Consistency**
   - Gunakan ADMIN atau USER (konsisten)
   - Jangan gunakan role lain

5. **Status Format**
   - Gunakan: aktif, Aktif, AKTIF, 1, yes (untuk aktif)
   - Sisanya dianggap tidak aktif

## Troubleshooting

### "File tidak diterima"
- Pastikan file format CSV atau TXT
- Pastikan file size < 2MB
- Pastikan delimiter adalah semicolon (;), bukan comma (,)

### "Email sudah terdaftar"
- Check apakah email sudah ada di database
- Gunakan email yang berbeda

### "Role tidak valid"
- Hanya gunakan ADMIN atau USER
- Gunakan huruf besar (ADMIN, USER)

### "Data tidak terimport sama sekali"
- Check format header CSV (harus: ID;Nama;Email;No. Telepon;Role;Status)
- Check encoding file (harus UTF-8)
- Lihat error messages untuk detail

## File Structure

```
app/Http/Controllers/Admin/
├── UserController.php (export & import methods)
└── TemplateController.php (template download)

routes/
└── web.php (routes untuk export, import, template)

resources/views/admin/
└── pengguna.blade.php (UI dengan modal import/export)
```

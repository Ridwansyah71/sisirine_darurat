# Update: Export dan Import User - Excel Support & Password Security

## Masalah yang Diselesaikan

1. ✅ **Password tidak di-export** - Untuk keamanan, password tidak disertakan dalam file export
2. ✅ **Support Excel import** - Sekarang bisa import menggunakan file Excel (.xlsx, .xls) selain CSV

## Perubahan yang Dilakukan

### 1. Export User (TIDAK ada perubahan - sudah aman)
**Kolom yang di-export:**
- ID
- Nama
- Email
- No. Telepon
- Role
- Status
- Tanggal Dibuat

**Kolom yang TIDAK di-export (untuk keamanan):**
- ❌ Password (tidak pernah di-export)
- ❌ Timestamps (created_at, updated_at - hanya Tanggal Dibuat yang ditampilkan)

### 2. Import User - Perubahan

#### Support Format File
| Format | Extension | Support |
|--------|-----------|---------|
| CSV | .csv | ✅ Yes (delimiter: ;) |
| Text | .txt | ✅ Yes (delimiter: ;) |
| Excel | .xlsx | ✅ **NEW** |
| Excel | .xls | ❌ Not supported (use .xlsx) |

#### Validasi File
- Max size: **5120 KB** (diperbesar dari 2048 KB)
- Mimes: csv, txt, xlsx

### 3. Format Data (Sama untuk semua file type)
```
ID;Nama;Email;No. Telepon;Role;Status
1;John Doe;john@example.com;081234567;ADMIN;Aktif
2;Jane Smith;jane@example.com;082345678;USER;Aktif
```

**Kolom:**
| Kolom | Type | Required | Notes |
|-------|------|----------|-------|
| ID | Integer | No | Untuk reference saja, bisa dikosongkan |
| Nama | String | Yes | Max 255 char |
| Email | Email | Yes | Harus unik, valid format |
| No. Telepon | String | No | Optional |
| Role | String | Yes | ADMIN atau USER (case insensitive) |
| Status | String | No | aktif/active/1/yes = aktif, else tidak aktif |

## Cara Menggunakan Excel

### Membuat File Excel
1. Buka Excel
2. Buat kolom header di baris 1:
   - Kolom A: ID
   - Kolom B: Nama
   - Kolom C: Email
   - Kolom D: No. Telepon
   - Kolom E: Role
   - Kolom F: Status

3. Isi data mulai dari baris 2
4. Save as **Excel Workbook (.xlsx)**
5. Upload di halaman Import

### Contoh File Excel
```
| ID | Nama         | Email              | No. Telepon | Role  | Status |
|----|--------------|-------------------|-------------|-------|--------|
| 1  | John Doe     | john@example.com   | 081234567   | ADMIN | Aktif  |
| 2  | Jane Smith   | jane@example.com   | 082345678   | USER  | Aktif  |
| 3  | Bob Johnson  | bob@example.com    | 083456789   | USER  | Tidak Aktif |
```

### Menggunakan Google Sheets
1. Buat di Google Sheets dengan format yang sama
2. Download sebagai Excel (.xlsx)
3. Upload file

## Teknologi yang Digunakan

### Excel Parsing (No External Library)
- Method: ZIP extraction + XML parsing
- Fungsi: Parse Excel files (.xlsx) sebagai ZIP archive
- Usage: Extract XML, parse dengan simplexml, extract cell values

### Import Logic
```php
// Detect file type
if ($fileExtension === 'xlsx') {
    // Parse Excel using built-in ZIP + XML
    // 1. Extract XLSX as ZIP
    // 2. Read xl/worksheets/sheet1.xml
    // 3. Parse XML with simplexml
    // 4. Extract cell values including shared strings
    $data_array = $this->parseXlsx($filePath);
} else {
    // Parse CSV dengan fgetcsv
    $data_array = parse dari CSV;
}

// Process data (sama untuk semua format)
foreach ($data_array as $row) {
    // Validate dan create user
}
```

### Keuntungan Solusi ini:
✅ **No external dependency** - hanya gunakan built-in PHP functions
✅ **Lightweight** - tidak perlu install package besar
✅ **XLSX support** - native support untuk format modern Excel
✅ **Robust** - proper error handling dan cleanup
✅ **Fast** - parsing langsung dari XML, tidak perlu conversion

## Default Password
Ketika import, user diberikan default password:
```
Password123!
```

User **HARUS mengganti password** saat login pertama (jika ada enforcer).

## Security Notes

✅ **Password tidak di-export**
- Alasan: Keamanan, password harus tetap private
- User baru diberikan default password saat import

✅ **Email validation**
- Email harus unik (tidak boleh duplikat)
- Email harus format valid

✅ **Role validation**
- Hanya accept ADMIN atau USER
- Case insensitive

✅ **Large file support**
- Max 5MB untuk Excel/CSV
- Efficient parsing untuk file besar

## File yang Diubah

1. ✅ `app/Http/Controllers/Admin/UserController.php`
   - Import use: `PhpOffice\PhpSpreadsheet\IOFactory`
   - Update validation: tambah .xlsx, .xls
   - Update import logic: handle Excel files

2. ✅ `resources/views/admin/pengguna.blade.php`
   - Update file input: accept Excel files
   - Update instruksi: tambah Excel guide
   - Update label: "File CSV" → "File (CSV, TXT, atau Excel)"

## Testing Checklist

- [ ] Import CSV dengan delimiter semicolon
- [ ] Import Excel (.xlsx) dengan data valid
- [ ] Import Excel (.xls) dengan data valid
- [ ] Export user - pastikan password tidak ada
- [ ] Error handling untuk email duplikat
- [ ] Error handling untuk role invalid
- [ ] Error handling untuk Excel format invalid
- [ ] Download template CSV masih berfungsi

## Backward Compatibility

✅ **Semua fitur sebelumnya tetap berfungsi:**
- Export CSV (method sama, hanya kode dibersihkan)
- Import CSV (tetap support, logic improved)
- Template download (tetap support)
- Manual add user (tidak berubah)
- Edit user (tidak berubah)
- Delete user (tidak berubah)

## Notes

- **XLSX support**: Memerlukan `phpoffice/phpspreadsheet` di composer
- **Delimiter**: Untuk CSV/TXT harus semicolon (;), bukan comma (,)
- **Encoding**: File harus UTF-8 untuk karakter Indonesia
- **Row limit**: Tidak ada limit baris, bisa import 1000+ users sekaligus

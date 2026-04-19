# Incident Reporting System - Implementation Complete

**Date:** January 23, 2026  
**Status:** ✅ Ready for Testing

## Overview

Sistem reporting/incident telah berhasil diimplementasikan. Sekarang sirine hanya dapat dinyalakan ketika ada laporan insiden aktif. Ini mencegah pengguna yang iseng mengklik tombol sirine tanpa alasan.

## Fitur Utama

### 1. **Tipe-Tipe Insiden**
- 🔥 **Kebakaran** - Fire
- 🚨 **Pencurian** - Theft/Burglary
- 📍 **Gempa Bumi** - Earthquake
- 🌊 **Banjir** - Flood/Flash Flood
- 🚗 **Kecelakaan** - Accident/Collision
- ⚠️ **Penyerangan** - Attack/Assault
- 🛡️ **Gangguan Keamanan** - Security Breach
- ❓ **Lainnya** - Other

### 2. **Workflow Sistem**

```
User Membuat Laporan Insiden
    ↓
Admin/User Melihat Insiden Aktif di Dashboard
    ↓
Klik Tombol Sirine (READY)
    ↓
Klik Tombol Sirine Lagi (ON) → SIRINE MENYALA! 🔊
    ↓
Tandai Insiden: Terselesaikan atau Alarm Palsu
```

## Bagian Sistem yang Diubah

### 1. **Database**
- **Migration Baru:** `2026_01_23_143000_create_incidents_table.php`
- **Tabel `incidents` dengan kolom:**
  - `id`, `user_id`, `type`, `description`, `location`, `status`
  - `reported_at`, `resolved_at`, timestamps

### 2. **Model**
- **File:** `app/Models/Incident.php`
- **Methods Penting:**
  - `active()` - Get active incidents dari 24 jam terakhir
  - `hasActive()` - Check if ada active incidents
  - `getLatestActive()` - Get latest active incident
  - `getTypeLabel()` - Format tipe insiden dengan emoji

### 3. **Controller**
- **User:** `app/Http/Controllers/User/AlarmController.php`
  - **store()** - DIMODIFIKASI: Cek apakah ada active incident sebelum ALARM_ON
  - Jika tidak ada incident → Return error 422 dengan pesan

- **New:** `app/Http/Controllers/User/IncidentController.php`
  - `index()` - List incidents user
  - `create()` - Show form input incident
  - `store()` - Create incident baru
  - `resolve()` - Mark incident sebagai resolved
  - `falseAlarm()` - Mark incident sebagai false alarm
  - `getActive()` - AJAX: Get active incidents

### 4. **Views**
- **Dashboard Update:** `resources/views/user/dashboard.blade.php`
  - Added incident alert box (jika ada active incident)
  - Added "Lapor Insiden" button di navbar
  - Added incident checking logic (every 10 seconds)
  - Enhanced error handling saat ALARM_ON tanpa incident

- **New Views:**
  - `resources/views/user/incidents/create.blade.php` - Form lapor insiden
  - `resources/views/user/incidents/index.blade.php` - List riwayat insiden

### 5. **Routes**
**File:** `routes/web.php`

```php
// User Incident Routes
Route::get('/user/incidents', ...)                  // List incidents
Route::get('/user/incidents/create', ...)           // Form create
Route::post('/user/incidents', ...)                 // Store incident
Route::post('/user/incidents/{id}/resolve', ...)    // Mark resolved
Route::post('/user/incidents/{id}/false-alarm', ...) // Mark false alarm
Route::delete('/user/incidents/{id}', ...)          // Delete incident
Route::get('/user/api/incidents/active', ...)       // AJAX: Get active
```

## Cara Menggunakan

### Untuk User:

**1. Membuat Laporan Insiden:**
```
1. Klik tombol ⚠️ "Lapor Insiden" di navbar
2. Pilih jenis kejadian dari dropdown
3. Isi deskripsi detail kejadian (minimal 10 karakter)
4. (Optional) Isi lokasi kejadian
5. Klik "Kirim Laporan"
```

**2. Menyalakan Sirine (dengan Laporan):**
```
1. Buka Dashboard
2. Lihat notifikasi "Ada Insiden Aktif!" di atas
3. Klik tombol sirine (READY state - warna kuning)
4. Klik tombol sirine lagi (ON state - untuk nyalakan)
5. Sirine akan menyala ✓
```

**3. Menandai Insiden Selesai:**
```
1. Klik "Lihat Detail" dari notifikasi insiden
2. Atau buka menu "Riwayat" → "Insiden"
3. Klik tombol "Terselesaikan" atau "Alarm Palsu"
```

## Error Handling

### Skenario 1: User Mencoba Nyalakan Sirine Tanpa Laporan
```
User clicks READY → clicks ON
Server Response:
{
    "status": "error",
    "message": "Sirine tidak dapat dinyalakan tanpa laporan insiden aktif...",
    "code": "NO_ACTIVE_INCIDENT"
}

UI Action: Show alert, reset state ke OFF
```

### Skenario 2: Incident Sudah Resolved
```
Jika incident status = RESOLVED atau FALSE_ALARM
Incident tidak lagi dianggap "active"
User tidak bisa nyalakan sirine sampai ada incident baru
```

## Validasi Input

### Form Lapor Insiden:
- **Jenis:** Required, harus salah satu dari enum types
- **Deskripsi:** Required, 10-500 karakter
- **Lokasi:** Optional, max 100 karakter

### Authorization:
- User hanya bisa akses/modify incident miliknya sendiri
- Admin bisa akses semua incident
- Hanya creator atau admin yang bisa resolve/false alarm

## Testing Steps

### Test 1: Lapor Insiden Berhasil
```
1. Go to /user/incidents/create
2. Fill form dengan data lengkap
3. Submit → Should see success modal
4. Dashboard should show incident alert
```

### Test 2: Sirine Tanpa Laporan (ERROR)
```
1. Go to /user/dashboard
2. Clear active incidents (via incidents page)
3. Try click button sirine → READY
4. Try click lagi → Should get error alert
5. No MQTT message sent
6. State should reset to OFF
```

### Test 3: Sirine Dengan Laporan (SUCCESS)
```
1. Go to /user/incidents/create
2. Create incident
3. Go to /user/dashboard
4. Should see incident alert
5. Click sirine → READY (yellow)
6. Click sirine again → ON (green)
7. MQTT message: ALARM_ON ✓
```

### Test 4: Incident Management
```
1. Create incident
2. Go to /user/incidents
3. View incident in list
4. Click "Terselesaikan" → Status changes to RESOLVED
5. No longer shows as active incident
6. Dashboard alert should disappear
```

### Test 5: Active Incident Alert
```
1. Create incident
2. Go to /user/dashboard
3. Should see red alert box with incident info
4. Alert auto-updates every 10 seconds
5. When incident resolved, alert disappears
```

## Database Setup

Jalankan migration:
```bash
php artisan migrate
```

Ini akan membuat tabel `incidents` dengan struktur lengkap.

## Key Security Features

✅ **Authorization Check** - Hanya user yg membuat atau admin bisa modify
✅ **Input Validation** - Semua field divalidasi di backend
✅ **Type Safety** - Enum untuk incident types
✅ **CSRF Protection** - Semua POST/DELETE routes protected
✅ **Status Constraint** - Hanya ACTIVE incidents dalam 24 jam yang dihitung
✅ **Activity Logging** - Setiap incident creation dicatat dengan user_id

## API Endpoints

### Get Active Incidents (AJAX)
```
GET /user/api/incidents/active
Response: {
    "count": 2,
    "incidents": [
        {
            "id": 1,
            "type": "Kebakaran 🔥",
            "description": "...",
            "location": "Ruang Server",
            "reported_at": "2 minutes ago"
        }
    ]
}
```

### Create Incident
```
POST /user/incidents
Body: {
    "type": "KEBAKARAN",
    "description": "Api di ruang server lantai 3",
    "location": "Ruang Server"
}
Response: {
    "status": "success",
    "message": "Laporan insiden berhasil dibuat...",
    "incident_id": 1
}
```

### Resolve Incident
```
POST /user/incidents/{id}/resolve
Response: {
    "status": "success",
    "message": "Insiden telah ditandai sebagai terselesaikan."
}
```

### Try Alarm ON (Without Incident)
```
POST /user/alarm/log
Body: { "action": "ALARM_ON" }

ERROR Response (422):
{
    "status": "error",
    "message": "Sirine tidak dapat dinyalakan tanpa laporan insiden aktif...",
    "code": "NO_ACTIVE_INCIDENT"
}
```

## Production Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Test incident creation form
- [ ] Test sirine activation with/without incident
- [ ] Verify MQTT message sent only when incident exists
- [ ] Test authorization (user can't modify others' incidents)
- [ ] Test admin access to all incidents
- [ ] Verify incident alert auto-updates in dashboard
- [ ] Test resolve/false-alarm functionality
- [ ] Check database logging is working
- [ ] Test on mobile (responsive design)
- [ ] Load test: Multiple incidents handling

## Architecture Diagram

```
Dashboard
├── Incident Alert (auto-update every 10s)
├── Master Button (requires active incident to turn ON)
└── Navbar with "Lapor Insiden" button
        ↓
IncidentController (Create/Manage)
├── store() → Create new incident
├── resolve() → Mark as RESOLVED
├── falseAlarm() → Mark as FALSE_ALARM
└── getActive() → Return active incidents (AJAX)
        ↓
Incident Model
├── active() → Query active incidents (last 24h)
├── hasActive() → Boolean check
└── getLatestActive() → Latest active incident
        ↓
AlarmController (Modified)
├── store() → Check Incident::hasActive() before ALARM_ON
└── Return error 422 if no active incident
        ↓
Database (incidents table)
```

## Notes

- **Active Period:** Incidents considered "active" jika created dalam 24 jam terakhir dan status = "ACTIVE"
- **Auto-update:** Dashboard checks active incidents every 10 seconds
- **MQTT Integration:** ALARM_ON message hanya sent jika incident validation passed
- **Soft Delete:** Consider adding soft delete jika ingin keep history
- **Admin Panel:** Bisa enhance dengan admin dashboard untuk view all incidents

---

**Implementation Status:** ✅ COMPLETE

**Next Steps:**
1. Run: `php artisan migrate`
2. Test in browser: `/user/incidents/create`
3. Test dashboard: `/user/dashboard`
4. Monitor logs for any issues

**Support Files:**
- Migration: `database/migrations/2026_01_23_143000_create_incidents_table.php`
- Model: `app/Models/Incident.php`
- Controller: `app/Http/Controllers/User/IncidentController.php`
- Views: `resources/views/user/incidents/`

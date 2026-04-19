# Dokumentasi Fitur Auto-Off Sirine

## Deskripsi
Fitur auto-off sirine telah diperbaiki dengan menambahkan multiple layers untuk memastikan sirine benar-benar mati setelah durasi yang ditentukan.

## Alur Kerja Auto-Off

### 1. **Frontend Check (Real-time)**
- Frontend melakukan pengecekan setiap 1 detik
- Jika waktu saat ini >= `auto_off_at`, sirine otomatis mati
- Mengirim MQTT command `ALARM_OFF` ke device
- Update UI untuk menampilkan status OFF

**File:** `resources/views/admin/sirine.blade.php`
- Fungsi: `checkAutoOff()`
- Interval: Setiap 1 detik

### 2. **Middleware Check (Request-based)**
- Middleware `CheckAutoOff` berjalan pada setiap request
- Dijalankan maksimal 1 kali per menit (dengan cache)
- Otomatis trigger auto-off jika waktu sudah tercapai
- Publish MQTT command OFF ke device
- Catat di log sebagai `AUTO_OFF`

**File:** `app/Http/Middleware/CheckAutoOff.php`
- Applied ke semua user & admin routes
- Prevents excessive database updates dengan caching

### 3. **Scheduler (Cron Job)**
- Berjalan setiap menit (bisa diubah)
- Menjalankan command: `php artisan alarm:check-auto-off`
- Independent check tanpa perlu request dari user
- Cocok untuk sistem yang berjalan 24/7

**File:** `app/Console/Commands/CheckAutoOffAlarm.php`
**Scheduler:** `app/Console/Kernel.php`

### 4. **API Endpoint (Manual Trigger)**
- Admin dapat manually trigger auto-off check
- Endpoint: `POST /admin/alarm/check-auto-off`
- Response: Status dan time remaining

**Controller:** `app/Http/Controllers/Admin/AlarmController@checkAutoOff`

## Database Schema

Tabel `alarm_states`:
```
id                INTEGER PRIMARY KEY
is_on            BOOLEAN (default: false)
activated_at     TIMESTAMP (nullable)
auto_off_at      TIMESTAMP (nullable) - Waktu kapan alarm harus mati
auto_off_duration INTEGER (default: 60 detik)
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

## Cara Kerja

### Ketika Sirine Dinyalakan:
1. Admin/User click tombol "NYALAKAN"
2. Backend update: `auto_off_at = now() + auto_off_duration` (misal: +10 detik)
3. MQTT publish command `ALARM_ON` ke device
4. Frontend capture `auto_off_at` dari response

### Saat Auto-Off Terpicu:
1. **Frontend** detect waktu sudah lewat `auto_off_at` → publish MQTT OFF
2. **Middleware** detect saat next request → update database & publish MQTT OFF
3. **Scheduler** detect saat cron job running → update database & publish MQTT OFF
4. Backend send `AUTO_OFF` log entry

## Configurasi

### Mengubah Durasi Auto-Off
- UI: Admin Dashboard → Pengaturan Durasi Auto-OFF (slider 5-60 detik)
- API: `POST /admin/alarm/duration` dengan parameter `duration`
- Database: Query `UPDATE alarm_states SET auto_off_duration = 10`

### Mengubah Interval Scheduler
Edit file `app/Console/Kernel.php`:
```php
$schedule->command('alarm:check-auto-off')
    ->everyMinute()  // Ubah ke everyTwoMinutes(), everyFiveMinutes(), dll
    ->withoutOverlapping();
```

## Testing

### Via Terminal (Manual Command):
```bash
php artisan alarm:check-auto-off
```

### Via API:
```bash
curl -X POST http://localhost:8000/admin/alarm/check-auto-off \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json"
```

### Via Frontend:
1. Buka Admin Dashboard → Manajemen Sirine
2. Click "NYALAKAN"
3. Wait untuk durasi yang ditentukan
4. Sirine otomatis mati setelah waktu tercapai

## Logging

Semua aktivitas auto-off dicatat di:
- **Database:** Table `alarm_logs` dengan action = `AUTO_OFF`
- **Application Log:** `storage/logs/laravel.log`
- **Scheduler Log:** `storage/logs/schedule.log`

## Troubleshooting

### Sirine Tidak Mati Otomatis
1. Cek apakah `auto_off_at` sudah set di database
   ```bash
   php artisan tinker
   >>> \App\Models\AlarmState::first();
   ```

2. Cek apakah MQTT service berjalan
3. Cek log di `storage/logs/laravel.log`
4. Manual trigger via API endpoint

### Scheduler Tidak Berjalan
Pastikan laravel scheduler sudah di-setup di cron:
```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### Middleware Conflict
Jika ada issue, disable middleware temporary:
- Edit `routes/web.php`
- Hapus `'check-auto-off'` dari middleware group
- Manual rely pada frontend check

## Performance Notes

- Middleware cache: 60 detik (prevent database thrashing)
- Frontend check: 1000ms interval (responsive real-time)
- Scheduler: Every minute (configurable)
- MQTT publish: QoS 1 (at least once delivery)

## Future Improvements

- [ ] Add UI countdown timer untuk visual feedback
- [ ] Add webhook notification saat auto-off
- [ ] Add email notification
- [ ] Add analytics untuk tracking auto-off events
- [ ] Add mobile push notification

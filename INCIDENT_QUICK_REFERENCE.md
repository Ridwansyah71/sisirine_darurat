# QUICK REFERENCE - Incident Reporting System

## 🚀 Deployment Checklist

✅ **Completed:**
- Migration created & executed
- Model `Incident` created
- Controller `IncidentController` created
- AlarmController modified with validation
- Routes configured
- Views created (create & index)
- Dashboard enhanced
- Cache cleared

## 📱 User Interface

### New Page: Lapor Insiden
**URL:** `/user/incidents/create`
- Form untuk report insiden dengan validasi
- Dropdown jenis insiden (8 tipe)
- Deskripsi & lokasi (optional)
- Success/Error modal

### Enhanced: Dashboard
**URL:** `/user/dashboard`
- ✨ NEW: Incident alert box (merah, auto-update)
- ✨ NEW: "Lapor Insiden" button di navbar (simbol ⚠️)
- Enhanced: Master button sekarang requires incident aktif
- Enhanced: Error handling jika coba ON tanpa incident

### New Page: Riwayat Insiden
**URL:** `/user/incidents`
- List semua incident user
- Filter status: ACTIVE / RESOLVED / FALSE_ALARM
- Action buttons: Terselesaikan / Alarm Palsu
- Pagination 15 per page

## 🔌 API Endpoints

```
CREATE INCIDENT
POST /user/incidents
Body: { type, description, location }

GET ACTIVE INCIDENTS (AJAX)
GET /user/api/incidents/active

RESOLVE INCIDENT
POST /user/incidents/{id}/resolve

MARK FALSE ALARM
POST /user/incidents/{id}/false-alarm

DELETE INCIDENT
DELETE /user/incidents/{id}

GET ALARM STATUS (MODIFIED)
GET /alarm/status
Response now includes: has_active_incident

TURN ON ALARM (MODIFIED)
POST /user/alarm/log
Action: "ALARM_ON" - NOW REQUIRES ACTIVE INCIDENT
```

## 🔐 Security

- ✅ Authorization: User dapat akses hanya incident miliknya
- ✅ Validation: Semua input di-validate di backend
- ✅ CSRF: Semua POST/DELETE protected
- ✅ Enum: Jenis incident di-restrict ke enum list
- ✅ Status Constraint: Hanya incident 24 jam terakhir yang "active"

## 🧪 Testing Flow

### Test 1: Create Incident
```
1. Go to /user/incidents/create
2. Select: "Kebakaran"
3. Describe: "Api di ruang server"
4. Location: "Lantai 3"
5. Submit → Success modal
6. Auto redirect to dashboard
```

### Test 2: Dashboard Alert
```
1. Dashboard shows incident alert
2. Button in alert: "Lihat Detail"
3. Red box with emoji type & time
4. Auto-updates every 10 seconds
```

### Test 3: Activate Alarm (Success)
```
1. With incident active in dashboard
2. Click sirine button → READY (yellow)
3. Click again → ON (green)
4. Sirine should turn on ✓
```

### Test 4: Activate Alarm (Fail)
```
1. Resolve all incidents
2. Dashboard alert disappears
3. Try click sirine → READY (yellow)
4. Click again → Alert: "Tidak ada laporan aktif"
5. State reset to OFF
```

### Test 5: Manage Incident
```
1. Go to /user/incidents
2. See incident with status ACTIVE
3. Click "Terselesaikan" → Status RESOLVED
4. Dashboard alert disappears
```

## 📊 Database Schema

```sql
CREATE TABLE incidents (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    type ENUM('KEBAKARAN', 'PENCURIAN', ...),
    description TEXT,
    location VARCHAR(100),
    status ENUM('ACTIVE', 'RESOLVED', 'FALSE_ALARM'),
    reported_at TIMESTAMP,
    resolved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX(user_id),
    INDEX(status),
    INDEX(reported_at)
);
```

## 🎯 Workflow

```
Normal User Flow:
1. User mendengar ada kebakaran
2. Go to /user/incidents/create
3. Select "KEBAKARAN", isi deskripsi, submit
4. Dashboard shows incident alert
5. Click sirine (READY) → Click lagi (ON)
6. Sirine menyala 🔊
7. When resolved, mark as "Terselesaikan"

Safety Flow:
- User clicks sirine without incident
- System: "No active incident - rejected"
- MQTT: No message sent
- Sirine: Stays OFF
- User: Alerted to create incident first
```

## 🐛 Troubleshooting

### Issue: "Sirine tidak dapat dinyalakan"
**Solution:** Create incident first at `/user/incidents/create`

### Issue: Incident alert not showing
**Solution:** Check browser console for errors, refresh page

### Issue: Incident stays active forever
**Solution:** Only incidents < 24 hours old are "active", mark as resolved

### Issue: MQTT message sent when shouldn't
**Solution:** Check AlarmController validation - should reject if no incident

## 📝 Files Modified/Created

### New Files:
- `database/migrations/2026_01_23_143000_create_incidents_table.php`
- `app/Models/Incident.php`
- `app/Http/Controllers/User/IncidentController.php`
- `resources/views/user/incidents/create.blade.php`
- `resources/views/user/incidents/index.blade.php`
- `INCIDENT_REPORTING_SYSTEM.md` (documentation)

### Modified Files:
- `app/Http/Controllers/User/AlarmController.php`
- `routes/web.php`
- `resources/views/user/dashboard.blade.php`

## 🚦 Status Indicators

| Status | Color | Meaning | Active? |
|--------|-------|---------|---------|
| ACTIVE | Red | Incident sedang berlangsung | ✅ YES |
| RESOLVED | Green | Incident sudah selesai | ❌ NO |
| FALSE_ALARM | Yellow | Alarm palsu/tidak ada insiden | ❌ NO |

## 💡 Key Features

✨ **Required Incident to Activate:**
- Prevents false alarms / misuse
- Only real incidents trigger alarm

✨ **Real-time Alert:**
- Dashboard updates every 10 seconds
- Instant notification when incident active

✨ **Complete Tracking:**
- All incidents logged with user & timestamp
- Can resolve with status: Resolved or False Alarm

✨ **Type Classification:**
- 8 pre-defined incident types
- Helps categorize emergencies

✨ **Optional Location:**
- Can specify where incident occurs
- Helps emergency responders

## 📞 Support

**Emergency Incident Types:**
1. 🔥 **Kebakaran** - Fire
2. 🚨 **Pencurian** - Theft/Burglary
3. 📍 **Gempa Bumi** - Earthquake
4. 🌊 **Banjir** - Flood
5. 🚗 **Kecelakaan** - Accident
6. ⚠️ **Penyerangan** - Attack
7. 🛡️ **Gangguan Keamanan** - Security Issue
8. ❓ **Lainnya** - Other

---

**Version:** 1.0 - January 23, 2026
**Status:** ✅ Production Ready

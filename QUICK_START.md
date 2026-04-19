# 🚀 QUICK START - Incident Reporting System

## ⚡ What's New?

✨ Sirine sekarang hanya menyala jika ada **LAPORAN INSIDEN AKTIF**
✨ Mencegah user iseng mengklik sirine tanpa alasan
✨ Semua incident tercatat dengan user & waktu

---

## 📱 User Flow (3 Steps)

### 1️⃣ Lapor Insiden
```
Dashboard → Click ⚠️ "Lapor Insiden"
          → Select jenis (Kebakaran, Pencurian, dll)
          → Describe apa yang terjadi
          → Submit
          → ✅ Incident created, alert muncul
```

### 2️⃣ Aktivasi Sirine
```
Dashboard → See red alert "Ada Insiden Aktif!"
         → Click sirine button → READY (yellow)
         → Click again → ON (green) 🔊
         → Sirine menyala!
```

### 3️⃣ Tandai Selesai
```
Dashboard → Click "Lihat Detail" di alert
         → atau go to menu → Riwayat
         → Click "Terselesaikan" atau "Alarm Palsu"
         → ✅ Incident resolved
```

---

## 🔴 ERROR: Sirine Tanpa Laporan

**Jika tidak ada incident aktif:**
```
Click sirine → READY (yellow)
Click again → ❌ Alert: "Sirine tidak dapat dinyalakan 
                        tanpa laporan insiden aktif"
```

**Solusi:** Buat laporan insiden dulu!

---

## 🎯 8 Jenis Insiden

| Tipe | Emoji | Contoh |
|------|-------|--------|
| Kebakaran | 🔥 | Api di ruang server |
| Pencurian | 🚨 | Pencuri di depan pintu |
| Gempa Bumi | 📍 | Gempa kuat terasa |
| Banjir | 🌊 | Air banjir di basement |
| Kecelakaan | 🚗 | Mobil tabrakan |
| Penyerangan | ⚠️ | Ada penyerang |
| Gangguan Keamanan | 🛡️ | Pintu tidak terkunci |
| Lainnya | ❓ | Insiden lainnya |

---

## 🔗 New URLs

```
Create Incident:    /user/incidents/create
View Incidents:     /user/incidents
Dashboard (modified): /user/dashboard
```

---

## 📊 Dashboard Changes

### NEW: Incident Alert Box (Red)
```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃ ⚠️ Ada Insiden Aktif!     ┃
┃ 🔥 Kebakaran · 2 min ago  ┃
┃ [Lihat Detail]            ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

### NEW: Navbar Button (⚠️)
```
Navbar items:
[🏠 Home] [⚠️ Lapor] [📋 History] [👤 Profile]
           ^new button
```

---

## ✅ Checklist

- [x] Migration completed
- [x] 7 new routes
- [x] 1 new model (Incident)
- [x] 1 new controller (IncidentController)
- [x] 2 new views (create, index)
- [x] Dashboard updated
- [x] Sirine validation added
- [x] Real-time alerts working

---

## 🧪 Quick Test

### Test 1: Create Incident
```
1. Go: /user/incidents/create
2. Select: Kebakaran
3. Write: "Ada api di ruang server"
4. Submit → Success modal
```

### Test 2: See Alert
```
1. Go dashboard
2. Should see red "Ada Insiden Aktif!" box
3. Shows: type + time
```

### Test 3: Activate Sirine
```
1. With incident active
2. Click button → READY (yellow)
3. Click again → ON (green)
4. ✅ Sirine on!
```

### Test 4: Error Case
```
1. Resolve the incident
2. Alert disappears
3. Try click sirine
4. ❌ Error: "Tidak ada laporan aktif"
```

---

## 📚 Documentation

| File | Purpose |
|------|---------|
| `INCIDENT_REPORTING_SYSTEM.md` | Complete guide |
| `INCIDENT_QUICK_REFERENCE.md` | API & endpoints |
| `TEST_INCIDENT_SYSTEM.md` | 12 test scenarios |
| `IMPLEMENTATION_CHECKLIST.md` | What's implemented |
| `QUICK_START.md` | This file |

---

## ⚙️ Database

```sql
-- New table: incidents
-- Columns: id, user_id, type, description, location, 
--         status, reported_at, resolved_at, timestamps
-- Indices: user_id, status, reported_at
```

**Status:** `php artisan migrate` already run ✅

---

## 🔐 Important Notes

### Security ✅
- ✅ Only you can access your incidents
- ✅ Admin can see all
- ✅ CSRF protection on all forms
- ✅ Input validation

### Active Incident Rules
- ✅ Must be < 24 hours old
- ✅ Must have status = "ACTIVE"
- ✅ Only then sirine can turn ON

### MQTT ✅
- ✅ Message sent only if incident validated
- ✅ No false alarms go to device

---

## 🐛 Troubleshooting

**Q: Form not showing?**
A: Clear cache: `php artisan optimize:clear`

**Q: Sirine not blocked without incident?**
A: Check if AlarmController has validation code

**Q: Alert not updating?**
A: Refresh page, check browser console

**Q: Can't resolve incident?**
A: Must be the one who created it (or admin)

---

## 💡 Tips

1. **Quick Report:** Use emojis in description for clarity
2. **Location:** Always fill location if possible (helps responders)
3. **Resolve Quickly:** Mark as resolved when danger passed
4. **False Alarm:** Mark as false alarm if it was mistake
5. **Check History:** Go to `/user/incidents` to see all your reports

---

## 📞 Support

**For Help:**
1. Check this QUICK_START.md
2. See INCIDENT_QUICK_REFERENCE.md for API
3. Check browser console for errors
4. Contact admin for authorization issues

---

## 🎉 Summary

**Old System:** User could click sirine anytime → Risk of false alarms
**New System:** Incident required → Only real emergencies trigger sirine

**Result:** ✨ Safer, more secure, fully audited system! ✨

---

**Status:** ✅ Production Ready  
**Deploy Date:** January 23, 2026  
**Version:** 1.0  

🚨 **Stay Safe!** 🚨

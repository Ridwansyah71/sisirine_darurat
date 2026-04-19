# TEST INCIDENT REPORTING SYSTEM

**Date:** January 23, 2026
**Status:** Ready for Testing

## Pre-Test Setup

```bash
# 1. Database migrated ✓
php artisan migrate

# 2. Cache cleared ✓
php artisan optimize:clear

# 3. Application running
php artisan serve
# atau gunakan existing server
```

## Test Suite

### TEST 1: Create Incident Form
**Purpose:** Verify incident creation form works

**Steps:**
1. Open browser → Navigate to `http://localhost:8000/user/incidents/create`
2. Verify form displays with:
   - Dropdown "Jenis Kejadian" (8 options)
   - Textarea "Deskripsi Kejadian"
   - Input "Lokasi (Opsional)"
   - Submit button "Kirim Laporan"
   - Cancel button "Batal"
3. Try submit with empty form → Should show validation errors
4. Fill with valid data:
   - Jenis: "Kebakaran"
   - Deskripsi: "Ada api di ruang server lantai 3"
   - Lokasi: "Ruang Server"
5. Click submit → Should see success modal
6. Modal should auto-redirect to dashboard after 3 seconds

**Expected Result:** ✅ Success modal appears, database records incident

---

### TEST 2: Dashboard Incident Alert
**Purpose:** Verify incident alert shows on dashboard

**Steps:**
1. After TEST 1 success, you should be on dashboard
2. Look for red alert box above the master button
3. Alert should contain:
   - Icon ⚠️
   - Text: "Ada Insiden Aktif!"
   - Incident type: "Kebakaran 🔥"
   - Time: "2 minutes ago" (or similar)
   - Button: "Lihat Detail"
4. Refresh page → Alert should still appear (persisted)
5. Wait and verify alert updates periodically (every 10 seconds)
6. Click "Lihat Detail" → Should go to `/user/incidents`

**Expected Result:** ✅ Red alert box visible with incident info

---

### TEST 3: Activate Alarm WITH Active Incident
**Purpose:** Verify sirine can turn ON with incident

**Steps:**
1. Ensure incident is active (from TEST 1)
2. On dashboard, click master button (red circle)
3. Should show modal: "Hidupkan Sirine?"
4. Click "Ya" → Button changes to YELLOW (READY state)
5. Button should show "READY" and status "Siap Dihidupkan"
6. Click button again → Should turn GREEN (ON state)
7. Button should show "ON" and status "ALARM AKTIF"
8. Check MQTT: Should have received ALARM_ON message
9. Wait 60 seconds → Should auto-off (yellow → red)

**Expected Result:** ✅ Sirine turns ON, MQTT message sent, auto-off works

---

### TEST 4: Try Activate Alarm WITHOUT Active Incident
**Purpose:** Verify sirine is protected from unauthorized activation

**Steps:**
1. First, resolve all incidents:
   - Go to `/user/incidents`
   - Click "Terselesaikan" on active incident
   - Refresh dashboard → Alert should disappear
2. Try click master button → Modal "Hidupkan Sirine?"
3. Click "Ya" → Button turns YELLOW (READY)
4. Click button again (try to turn ON) → Should see alert:
   ```
   ⚠️ Sirine tidak dapat dinyalakan tanpa laporan insiden aktif. 
   Silakan buat laporan terlebih dahulu.
   ```
5. Button should reset to RED (OFF)
6. Check MQTT: Should NOT have ALARM_ON message

**Expected Result:** ✅ Sirine blocked, error alert shown, no MQTT message

---

### TEST 5: Incident List View
**Purpose:** Verify incident history page works

**Steps:**
1. Create 3 different incidents:
   - Kebakaran di Ruang Server
   - Pencurian di Pintu Utama
   - Banjir di Basement
2. Go to `/user/incidents`
3. Should see list with:
   - Status indicator (red for active)
   - Type with emoji
   - Description
   - Location
   - Timestamp
   - User name
   - Action buttons (Terselesaikan, Alarm Palsu)
4. Scroll down → Verify pagination (15 per page)
5. For one incident, click "Terselesaikan" → Refresh → Status changed to green
6. For another, click "Alarm Palsu" → Refresh → Status changed to yellow

**Expected Result:** ✅ All incidents display correctly, status changes work

---

### TEST 6: Input Validation
**Purpose:** Verify form validation works

**Steps:**
1. Go to `/user/incidents/create`
2. Test: Submit without selecting type → Error shown
3. Test: Submit with description < 10 chars → Error shown
4. Test: Submit with description > 500 chars → Error/truncated
5. Test: Submit with valid type, empty description → Error shown
6. Test: Leave location empty → Should allow (optional)
7. Check character counter → Should update real-time (0/500)
8. Type 50 chars → Should show "50/500"
9. Try submit with all valid data → Success

**Expected Result:** ✅ All validation rules enforced

---

### TEST 7: Real-time Alert Update
**Purpose:** Verify dashboard auto-updates

**Steps:**
1. User A: Open dashboard, create incident
2. User B: Open dashboard in another tab/browser
3. Wait 10 seconds → User B's dashboard should show alert
4. User A: Resolve the incident
5. Wait 10 seconds → User B's alert should disappear
6. Verify both dashboards in sync

**Expected Result:** ✅ Alerts update across browsers without refresh

---

### TEST 8: Alarm Controller Integration
**Purpose:** Verify backend properly checks incidents

**Steps:**
1. Use Developer Tools → Network tab
2. Create incident
3. Try activate alarm → Monitor POST to `/user/alarm/log`
4. Verify request body: `{ "action": "ALARM_ON" }`
5. With incident active: Response should be `{ "status": "ok", "is_on": true }`
6. Resolve incident
7. Try activate alarm → Monitor request
8. Response should be error: `{ "status": "error", "code": "NO_ACTIVE_INCIDENT" }`

**Expected Result:** ✅ Controller returns correct status/error

---

### TEST 9: MQTT Integration
**Purpose:** Verify MQTT messages only sent with incident

**Steps:**
1. Setup MQTT broker monitoring (or check logs)
2. Dashboard with incident → Click sirine → Should see ALARM_ON on topic
3. Activate AUTO_OFF → Should see AUTO_OFF message
4. Click OFF → Should see ALARM_OFF message
5. Dashboard without incident → Try click sirine → NO ALARM_ON sent to MQTT

**Expected Result:** ✅ MQTT messages only sent when incident valid

---

### TEST 10: Multi-User Scenario
**Purpose:** Verify multi-user incident handling

**Setup:**
- User A: Has user role
- User B: Has user role
- Admin: Has admin role

**Steps:**
1. User A creates incident
2. User B navigates to dashboard → Should see User A's incident
3. User B tries resolve User A's incident → Should get error (auth failure)
4. User A marks incident resolved
5. User B tries activate alarm → Should fail (no active incident)
6. Admin views `/user/incidents` page (if admin version exists) → Should see all incidents

**Expected Result:** ✅ Authorization properly enforced, users can't modify others' incidents

---

### TEST 11: Mobile Responsiveness
**Purpose:** Verify form works on mobile

**Steps:**
1. Open `/user/incidents/create` on mobile or use DevTools mobile view
2. Verify layout looks good:
   - Header readable
   - Form inputs full-width
   - Buttons accessible
   - Textarea resizable
3. Fill and submit form on mobile → Should work properly
4. Check `/user/dashboard` on mobile:
   - Alert box displays well
   - Master button centered
   - Navbar at bottom accessible

**Expected Result:** ✅ All pages work on mobile

---

### TEST 12: Edge Cases
**Purpose:** Test unusual scenarios

**Steps:**
1. **Multiple Active Incidents:**
   - Create 3 incidents simultaneously
   - Dashboard should show only latest in alert
   - But `/user/incidents` should list all 3
   
2. **Spam Prevention:**
   - Try create same incident twice rapidly
   - Should both be created (no spam filter yet)
   - Note: Can add rate-limiting in future
   
3. **Location Input:**
   - Try with special chars: `<script>, '; DROP TABLE`
   - Should not cause SQL injection
   - Should be properly escaped
   
4. **Old Incidents:**
   - Create incident > 24 hours ago in DB directly
   - Should NOT show as "active"
   - Dashboard should not show alert for old incidents
   
5. **Deleted Incident:**
   - Create incident
   - Delete via DELETE endpoint
   - Should no longer appear in list
   - Should no longer block sirine

**Expected Result:** ✅ All edge cases handled gracefully

---

## Database Verification

### Check Incident Table Structure
```sql
DESCRIBE incidents;
```
Should show:
- id, user_id, type, description, location, status
- reported_at, resolved_at, created_at, updated_at

### Check Sample Data
```sql
SELECT * FROM incidents ORDER BY created_at DESC LIMIT 5;
```

### Check Indices
```sql
SHOW INDEXES FROM incidents;
```
Should have indices on: user_id, status, reported_at

---

## Performance Testing

### Load Test: Create Many Incidents
```php
// Run in Tinker
for ($i = 0; $i < 100; $i++) {
    \App\Models\Incident::create([
        'user_id' => 1,
        'type' => 'KEBAKARAN',
        'description' => 'Test incident ' . $i,
        'location' => 'Test location',
        'status' => 'ACTIVE',
        'reported_at' => now(),
    ]);
}
```

Then test:
1. Dashboard loads quickly (should still be fast)
2. Active incidents query takes < 100ms
3. Pagination works smoothly
4. Alert updates still fast

---

## Regression Tests

### Ensure Original Features Still Work

- [ ] Login/Logout functionality
- [ ] User roles (user vs admin)
- [ ] Dashboard displays (no incidents)
- [ ] Alarm history/riwayat
- [ ] User profile page
- [ ] Admin panel (if exists)
- [ ] MQTT connection
- [ ] Auto-off functionality

---

## Test Results Summary

| Test # | Name | Status | Notes |
|--------|------|--------|-------|
| 1 | Create Incident Form | ⬜ | Pending |
| 2 | Dashboard Alert | ⬜ | Pending |
| 3 | Alarm ON with Incident | ⬜ | Pending |
| 4 | Alarm ON without Incident | ⬜ | Pending |
| 5 | Incident List | ⬜ | Pending |
| 6 | Input Validation | ⬜ | Pending |
| 7 | Real-time Update | ⬜ | Pending |
| 8 | Controller Integration | ⬜ | Pending |
| 9 | MQTT Integration | ⬜ | Pending |
| 10 | Multi-User | ⬜ | Pending |
| 11 | Mobile | ⬜ | Pending |
| 12 | Edge Cases | ⬜ | Pending |

**Mark ⬜ as ✅ when test passes, ❌ when fails**

---

## Known Limitations

- [ ] No spam prevention / rate limiting (can add)
- [ ] No email notification (can add)
- [ ] Character counter doesn't work offline
- [ ] Incident alert doesn't auto-remove when resolved (user must refresh)

---

## Deployment Verification

After deployment, verify:

✅ Migration ran successfully
✅ Routes registered (check: `php artisan route:list | grep incidents`)
✅ Models loaded properly
✅ Views compile without errors
✅ Database table exists with correct structure
✅ Can access `/user/incidents/create`
✅ Can create incident and see in database
✅ Dashboard shows alert correctly
✅ Sirine activation properly validated

---

**Test Date:** ___________  
**Tested By:** ___________  
**Status:** ⬜ Pending / ✅ Passed / ❌ Failed

**Notes:**

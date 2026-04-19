# Filter Functionality Testing Guide

## Overview
The filter system has been updated to properly support server-side filtering with pagination. The search and type filters are now fully functional.

## How It Works

### Frontend (View)
**File:** `resources/views/admin/riwayat.blade.php` (lines 626-647)

The filter form contains:
- **Search Input** (name="search"): Search by username, action, or IP address
- **Type Select** (name="type"): Filter by alarm action type
  - `ALARM_ON` - Nyala Sirine (Alarm On)
  - `ALARM_OFF` - Matikan Sirine (Alarm Off)
  - `AUTO_OFF` - Auto Off

The form submits via GET to `route('admin.riwayat')`, preserving filter values in the URL query parameters.

### Backend (Controller)
**File:** `app/Http/Controllers/Admin/RiwayatController.php`

The `index()` method applies filters:

```php
// Filter by search (action, IP, user name)
if ($request->has('search') && !empty($request->search)) {
    $search = $request->search;
    $query->where(function ($q) use ($search) {
        $q->where('action', 'LIKE', "%{$search}%")
          ->orWhere('ip_address', 'LIKE', "%{$search}%")
          ->orWhereHas('user', function ($u) use ($search) {
              $u->where('name', 'LIKE', "%{$search}%");
          });
    });
}

// Filter by type/action
if ($request->has('type') && !empty($request->type)) {
    $type = $request->type;
    $query->where('action', 'LIKE', "%{$type}%");
}
```

The `export()` method also applies the same filters, so exported data respects the selected filters.

## Testing Scenarios

### Test 1: Search by Username
1. Go to Admin → Riwayat (Activity History)
2. Type a username in the search box (e.g., "admin")
3. Press Enter or click Filter
4. Verify only logs from that user are shown
5. Go to page 2 - filters should still apply

**Expected:** All displayed results contain the searched username

### Test 2: Search by IP Address
1. Type an IP address in the search box (e.g., "127.0.0.1" or "192.168")
2. Press Enter or click Filter
3. Verify only logs with matching IP are shown

**Expected:** All displayed results match the IP address

### Test 3: Search by Action
1. Type an action in the search box (e.g., "ALARM" or "OFF")
2. Press Enter or click Filter
3. Verify logs contain the action text

**Expected:** All displayed results contain the searched action

### Test 4: Filter by Type
1. Select a type from the dropdown (e.g., "Nyala Sirine")
2. Observe logs automatically filter (auto-submit on change)
3. Switch to another type
4. Verify correct logs are shown

**Expected:** Only logs with selected action type are shown

### Test 5: Combined Filters
1. Type a username in search (e.g., "admin")
2. Select a type (e.g., "ALARM_ON")
3. Click Filter
4. Verify only logs matching BOTH criteria are shown

**Expected:** Only logs from the user AND with the selected action type

### Test 6: Reset Filters
1. Apply any filters
2. Click the "Reset" button
3. Verify all filters are cleared and all logs are shown

**Expected:** URL returns to clean state: `/admin/riwayat`

### Test 7: Pagination with Filters
1. Apply a filter (e.g., select "ALARM_ON")
2. Navigate to page 2, 3, etc.
3. Verify filters are still applied on other pages

**Expected:** Filter query parameters persist in URL (e.g., `?type=ALARM_ON`) across pages

### Test 8: Export with Filters
1. Apply filters (e.g., search for a user + select type)
2. Click Export → Choose format (CSV, Excel, or PDF)
3. Verify exported file only contains filtered data

**Expected:** Exported data respects applied filters

### Test 9: Browser History
1. Apply filters
2. Click browser back button
3. Verify previous filter state is restored

**Expected:** Browser back/forward buttons work correctly with filter state

## URL Examples

**No filters:**
```
/admin/riwayat
```

**Search only:**
```
/admin/riwayat?search=admin
```

**Type filter only:**
```
/admin/riwayat?type=ALARM_ON
```

**Combined filters:**
```
/admin/riwayat?search=admin&type=ALARM_ON
```

## Filter Options Available

### Action Types (type parameter)
- `ALARM_ON` - Matches logs with "ALARM_ON" in action
- `ALARM_OFF` - Matches logs with "ALARM_OFF" in action  
- `AUTO_OFF` - Matches logs with "AUTO_OFF" in action
- Empty/blank - Shows all types (no type filter applied)

### Search Fields
- Username (from `users.name` via relationship)
- Action field (exact action performed)
- IP Address (client IP)

## Key Features

✅ **Server-side filtering** - Works with pagination
✅ **Browser history support** - Back/forward buttons preserve filter state
✅ **Value persistence** - Form shows currently applied filters
✅ **Auto-submit on type** - Dropdown changes automatically submit
✅ **Enter key support** - Pressing Enter in search box submits
✅ **Export respects filters** - Downloaded data is filtered
✅ **Reset functionality** - Easy way to clear all filters
✅ **URL shareable** - Filter state in URL can be shared

## Troubleshooting

### Filters Not Working
1. Verify form is submitting (check Network tab in browser DevTools)
2. Check URL contains query parameters (e.g., `?search=...`)
3. Ensure RiwayatController is receiving the request
4. Check browser console for JavaScript errors

### All Results Shown (No Filter Applied)
1. Check if query parameters are in the URL
2. Verify the search/type values are not empty
3. Check database has records matching the filter criteria

### Pagination Not Working with Filters
1. Verify URL has filter parameters on page 2, 3, etc.
2. Check for JavaScript errors preventing form submission
3. Ensure routes are correctly defined in `routes/api.php` or `routes/web.php`

## Code Changes Made

### Controller Updates (RiwayatController.php)
- Added type filter to `index()` method (lines 30-34)
- Type filter already present in `export()` method (lines 81-84)

### View Updates (riwayat.blade.php)
- Filter form structure with GET method (line 626)
- Type select dropdown with ALARM_ON/ALARM_OFF/AUTO_OFF options (lines 634-638)
- JavaScript filter function calls form.submit() (line 902)
- Event listeners for Enter key and auto-submit (lines 924-928)

## Performance Considerations

- Filters use LIKE queries for flexibility (substring matching)
- Search on user relationship uses `orWhereHas()` for efficiency
- Results paginated at 20 per page
- Type filter uses exact LIKE match for action field
- All filtering done server-side (more efficient than client-side)

## Future Enhancements

- Add date range picker in UI (controller already supports start_date/end_date)
- Add status filter (success/failure if implemented)
- Add sorting by column
- Add custom page size selector
- Add advanced filter options

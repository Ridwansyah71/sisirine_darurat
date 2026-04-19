# Excel Import Architecture Diagram

## Import Flow

```
┌─────────────────────────────────────────────────────────────┐
│                  User Clicks Import Button                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────┐
        │  Select File (.csv|.txt|.xlsx)
        │  Max 5MB, Required             │
        └──────────────┬─────────────────┘
                       │
                       ▼
        ┌────────────────────────────────┐
        │  File Upload to /admin/pengguna│
        │  /import (POST)                │
        └──────────────┬─────────────────┘
                       │
                       ▼
        ┌────────────────────────────────┐
        │  Laravel Request Validation    │
        │  - mimes: csv,txt,xlsx         │
        │  - max: 5120KB                 │
        └──────────────┬─────────────────┘
                       │
        ┌──────────────┴──────────────┐
        │                             │
        ▼                             ▼
   ┌─────────────┐            ┌───────────────┐
   │ CSV/TXT     │            │ XLSX          │
   │ File        │            │ (ZIP + XML)   │
   └──────┬──────┘            └────────┬──────┘
          │                           │
          ▼                           ▼
     ┌─────────────┐        ┌──────────────────┐
     │ fgetcsv()   │        │ ZipArchive::open │
     │ delimiter:; │        │ Extract to /tmp  │
     └──────┬──────┘        └────────┬─────────┘
            │                        │
            ▼                        ▼
     ┌────────────────┐      ┌──────────────────────┐
     │ Array of rows  │      │ Read sheet1.xml      │
     │ [             │      │ Read sharedStrings   │
     │ ['1','John',..] │      │ simplexml_load_str() │
     │ ['2','Jane',..] │      └────────┬─────────────┘
     │ ]              │              │
     └────────┬───────┘              ▼
              │              ┌─────────────────┐
              │              │ Extract rows    │
              │              │ & cells         │
              │              │ [              │
              │              │ ['1','John',...] │
              │              │ ['2','Jane',...] │
              │              │ ]              │
              │              └────────┬────────┘
              │                       │
              └───────────┬───────────┘
                          │
                          ▼
          ┌───────────────────────────────────┐
          │ Process Each Row                  │
          │ ┌─────────────────────────────────┤
          │ │ 1. Skip header (row 0)          │
          │ │ 2. Extract fields:              │
          │ │    - Nama (required)            │
          │ │    - Email (required)           │
          │ │    - Role (required)            │
          │ │    - Status (optional)          │
          │ │ 3. Validate:                    │
          │ │    - Email format & unique      │
          │ │    - Role in [ADMIN, USER]      │
          │ │    - Required fields not empty  │
          │ │ 4. Create User:                 │
          │ │    - Hash default password      │
          │ │    - Insert into users table    │
          │ │ 5. Track errors per row         │
          └─────────────────────────────────┘
                          │
                   ┌──────┴─────────┐
                   │                │
              Success          Error
                   │                │
         ┌─────────▼────────┐  │
         │ Add to success   │  │
         │ count            │  └─────────┬─────────┐
         └──────────────────┘           │         │
                                   Email Dup.  Role Invalid
                                   │         │
                                   ▼         ▼
                            ┌──────────────────────┐
                            │ Add to errors array  │
                            │ with row number &    │
                            │ error message        │
                            └──────────────────────┘
                                   │
                   ┌───────────────┴───────────────┐
                   ▼                               ▼
            ┌─────────────────┐          ┌────────────────────┐
            │ Cleanup temp    │          │ Cleanup temp       │
            │ directory       │          │ directory          │
            │ Delete /tmp/    │          │ Delete /tmp/       │
            │ xlsx_xxxxx/     │          │ xlsx_xxxxx/        │
            └────────┬────────┘          └─────────┬──────────┘
                     │                            │
                     └──────────────┬─────────────┘
                                    │
                                    ▼
                    ┌────────────────────────────────┐
                    │ Return Redirect Response       │
                    │ ┌──────────────────────────────┤
                    │ │ Success: "X users imported"  │
                    │ │ Errors: [                    │
                    │ │   "Row 2: Email dup",        │
                    │ │   "Row 3: Role invalid"      │
                    │ │ ]                            │
                    └────────────────────────────────┘
                                    │
                                    ▼
                    ┌────────────────────────────────┐
                    │ Redirect to /admin/pengguna    │
                    │ Display messages to user       │
                    └────────────────────────────────┘
```

---

## File Structure: XLSX

```
Excel File (users.xlsx)
│
└─→ (Extract as ZIP)
    │
    ├── mimetype
    ├── _rels/
    │   └── .rels
    ├── xl/
    │   ├── worksheets/
    │   │   └── sheet1.xml          ◄── Data rows & cells
    │   ├── sharedStrings.xml       ◄── Text values
    │   ├── styles.xml
    │   ├── workbook.xml
    │   └── _rels/
    ├── docProps/
    │   ├── app.xml
    │   └── core.xml
    └── [Content_Types].xml

XML Example (sheet1.xml):
┌─────────────────────────────────────────────────────────┐
│ <worksheet>                                             │
│   <sheetData>                                           │
│     <row r="1">                                         │
│       <c r="A1" t="s"><v>0</v></c>   ← Type=s (string) │
│       <c r="B1" t="s"><v>1</v></c>                      │
│     </row>                                              │
│     <row r="2">                                         │
│       <c r="A2"><v>1</v></c>         ← Type=n (number) │
│       <c r="B2" t="s"><v>2</v></c>                      │
│     </row>                                              │
│   </sheetData>                                          │
│ </worksheet>                                            │
└─────────────────────────────────────────────────────────┘

sharedStrings.xml:
┌─────────────────────────────────────────────────────────┐
│ <sst>                                                   │
│   <si><t>ID</t></si>           ← Index 0              │
│   <si><t>Nama</t></si>         ← Index 1              │
│   <si><t>John</t></si>         ← Index 2              │
│ </sst>                                                  │
└─────────────────────────────────────────────────────────┘
```

---

## Data Transformation Pipeline

```
┌────────────────────────────────────────────────────────────────┐
│ RAW XLSX DATA                                                  │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────┐ │
│ │ Cell[0][0]=0 │ │ Cell[0][1]=1 │ │ Cell[0][2]=2 │ │  ...   │ │
│ │ (string ref) │ │ (string ref) │ │ (string ref) │ │        │ │
│ └──────────────┘ └──────────────┘ └──────────────┘ └────────┘ │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────┐ │
│ │ Cell[1][0]=1 │ │ Cell[1][1]=2 │ │ Cell[1][2]=3 │ │  ...   │ │
│ │ (number)     │ │ (string ref) │ │ (string ref) │ │        │ │
│ └──────────────┘ └──────────────┘ └──────────────┘ └────────┘ │
└────────────────────────────────────────────────────────────────┘
                            ▼
                    ┌─────────────────────┐
                    │ Resolve Shared      │
                    │ String References   │
                    └────────────┬────────┘
                                 │
┌────────────────────────────────────────────────────────────────┐
│ PARSED ARRAY                                                   │
│ [                                                              │
│   ["ID", "Nama", "Email", "No HP", "Role", "Status"],         │
│   ["1", "John", "john@example.com", "081...", "ADMIN", "..."],│
│   ["2", "Jane", "jane@example.com", "082...", "USER", "..."], │
│ ]                                                              │
└────────────────────────────────────────────────────────────────┘
                            ▼
                    ┌─────────────────────┐
                    │ Skip Header Row     │
                    │ Loop Data Rows      │
                    └────────────┬────────┘
                                 │
┌────────────────────────────────────────────────────────────────┐
│ VALIDATION & TRANSFORMATION PER ROW                            │
│ ├─ Extract: $name, $email, $phone, $role, $is_active         │
│ ├─ Validate Email Format                                      │
│ ├─ Check Email Uniqueness                                     │
│ ├─ Validate Role (ADMIN|USER)                                 │
│ ├─ Map Status to is_active (1|0)                              │
│ ├─ Generate Default Password Hash                             │
│ └─ Prepare Insert Array                                       │
└────────────────────────────────────────────────────────────────┘
                            ▼
                ┌────────────────────────┐
                │ User::create($data)    │
                │ Insert to Database     │
                └────────────────────────┘
                            ▼
┌────────────────────────────────────────────────────────────────┐
│ DATABASE                                                       │
│ ┌─────┬──────┬────────────┬───────┬──────┬────────┐           │
│ │ id  │ name │ email      │ phone │ role │ pwd... │           │
│ ├─────┼──────┼────────────┼───────┼──────┼────────┤           │
│ │ ... │ John │ john@...   │ 081.. │ ADMIN│ hash.. │           │
│ │ ... │ Jane │ jane@...   │ 082.. │ USER │ hash.. │           │
│ └─────┴──────┴────────────┴───────┴──────┴────────┘           │
└────────────────────────────────────────────────────────────────┘
```

---

## Error Handling Tree

```
                    ┌─ Import Request
                    │
        ┌───────────┴────────────┐
        │                        │
     ✅ VALID              ❌ INVALID
        │                        │
        ▼                        ▼
    ┌────────────┐          ┌─────────────────────┐
    │ Process    │          │ Return Error        │
    │ File       │          │ - File required     │
    │            │          │ - Wrong MIME type   │
    │ ├─ ZIP     │          │ - File too large    │
    │ │ Error    │          └─────────────────────┘
    │ ├─ XML     │
    │ │ Error    │
    │ └─ Continue
    │    if OK   │
    │            │
    └──────┬─────┘
           │
    ┌──────▼────────┐
    │ For each Row  │
    └──────┬────────┘
           │
    ┌──────┴─────────────┬─────────────────┬────────────────┐
    │                    │                 │                │
    ▼                    ▼                 ▼                ▼
 ┌────────────┐   ┌────────────┐   ┌────────────┐   ┌────────────┐
 │ Required   │   │ Email      │   │ Role       │   │ Email      │
 │ Fields     │   │ Format     │   │ Validate   │   │ Unique     │
 │ Empty      │   │ Invalid    │   │ Invalid    │   │ Exists     │
 └─────┬──────┘   └─────┬──────┘   └─────┬──────┘   └─────┬──────┘
       │                │                │                │
       └────────────────┴────────────────┴────────────────┘
                        │
                  ┌─────▼──────┐
                  │ Add Error  │
                  │ for Row N  │
                  └────────────┘
                        │
         ┌──────────────┘
         │
      ┌──▼──┐
      │ YES │ Continue next row
      └─────┘
         │
    ┌────▼──────────────────────────────┐
    │ All rows processed                 │
    │ Summary:                           │
    │ - Success: N users created         │
    │ - Errors: M rows had issues        │
    │ - Details: Show error per row      │
    └────────────────────────────────────┘
```

---

## Technology Stack Layers

```
┌─────────────────────────────────────────────────────────┐
│ PRESENTATION LAYER                                      │
│ ├── HTML Form (pengguna.blade.php)                      │
│ ├── File Input (.csv, .txt, .xlsx)                      │
│ └── Result Messages (Success/Error)                     │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│ ROUTE LAYER (routes/web.php)                            │
│ ├── POST /admin/pengguna/import                         │
│ ├── GET /admin/pengguna/export                          │
│ └── GET /admin/template/users                           │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│ CONTROLLER LAYER (UserController.php)                   │
│ ├── import() - Route handler                            │
│ ├── export() - CSV generation                           │
│ ├── parseXlsx() - ZIP + XML parsing                     │
│ └── deleteDirectory() - Cleanup                         │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│ FILE PROCESSING LAYER                                   │
│ ├── Validation (Laravel's validator)                    │
│ ├── CSV: fgetcsv() with ; delimiter                     │
│ ├── ZIP: ZipArchive extension                           │
│ ├── XML: simplexml_load_string()                        │
│ └── File: file_get_contents(), fopen(), etc.            │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│ DATA LAYER (Models/Database)                            │
│ ├── User Model (Eloquent)                               │
│ ├── users table                                         │
│ ├── Hash::make() - Password hashing                     │
│ └── User::create() - Insert records                     │
└─────────────────────────────────────────────────────────┘
```

---

## Performance Characteristics

```
File Size → Processing Time → Memory

1000 rows CSV
├─ File: ~50 KB
├─ Time: ~100 ms
└─ Memory: ~1 MB

1000 rows XLSX
├─ File: ~50 KB
├─ Time: ~300 ms (includes ZIP extraction)
└─ Memory: ~5 MB

10000 rows CSV
├─ File: ~500 KB
├─ Time: ~1 sec
└─ Memory: ~10 MB

Max 5MB file
├─ CSV: ~100K rows (depending on data width)
├─ XLSX: ~50K rows
└─ Memory: Max ~50 MB (system dependent)
```

---

## Security Model

```
┌──────────────┐
│ User Uploads │
│ Excel File   │
└──────┬───────┘
       │
       ▼
┌────────────────────────┐
│ VALIDATION LAYER       │
├────────────────────────┤
│ ✅ MIME type check     │
│    mimes:csv,txt,xlsx  │
│                        │
│ ✅ File size check     │
│    max: 5120 KB        │
│                        │
│ ✅ Extension check     │
│    Only .csv,.txt,.xlsx│
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ PARSING LAYER          │
├────────────────────────┤
│ ✅ Temp dir creation   │
│    sys_get_temp_dir()  │
│                        │
│ ✅ ZIP extraction      │
│    Validate structure  │
│                        │
│ ✅ XML parsing         │
│    Safe string parse   │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ DATA VALIDATION LAYER  │
├────────────────────────┤
│ ✅ Email format        │
│    Validate::email()   │
│                        │
│ ✅ Email uniqueness    │
│    User::where(email)  │
│                        │
│ ✅ Role whitelist      │
│    [ADMIN, USER]       │
│                        │
│ ✅ Required fields     │
│    Null checks         │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ DATABASE LAYER         │
├────────────────────────┤
│ ✅ Password hashing    │
│    Hash::make()        │
│                        │
│ ✅ Parameterized query │
│    Eloquent ORM        │
│                        │
│ ✅ Transaction safety  │
│    Auto rollback       │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ CLEANUP LAYER          │
├────────────────────────┤
│ ✅ Temp files deleted  │
│    deleteDirectory()   │
│                        │
│ ✅ On success          │
│    Clean extracted ZIP │
│                        │
│ ✅ On error            │
│    Clean extracted ZIP │
└────────────────────────┘
```


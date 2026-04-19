# Entity Relationship Diagram (ERD) - Sisirine App

## Daftar Tabel Database

| No | Nama Tabel | Deskripsi | Relasi |
|----|-----------|----------|--------|
| 1 | users | Tabel pengguna sistem | - |
| 2 | alarm_logs | Log aktivitas alarm | FK: users |
| 3 | alarm_states | Status alarm keamanan | - |
| 4 | incidents | Laporan insiden keamanan | FK: users |
| 5 | settings | Pengaturan sistem | - |

---

## 1. TABEL: users

**Deskripsi**: Menyimpan data pengguna sistem Sisirine

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | BIGINT UNSIGNED | PK, AUTO INCREMENT | ID pengguna (primary key) |
| name | VARCHAR(255) | NOT NULL | Nama lengkap pengguna |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Email pengguna |
| password | VARCHAR(255) | NOT NULL | Password terenkripsi |
| phone | VARCHAR(255) | NULLABLE | Nomor telepon |
| role | ENUM | NOT NULL, DEFAULT: 'murid' | Role: satpam, wakasek, sapras, murid |
| is_active | BOOLEAN | NOT NULL, DEFAULT: 1 | Status aktif pengguna |
| remember_token | VARCHAR(100) | NULLABLE | Token remember me |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan data |
| updated_at | TIMESTAMP | NULLABLE | Waktu update terakhir |

**Index**:
- PRIMARY KEY: id
- UNIQUE: email

---

## 2. TABEL: alarm_logs

**Deskripsi**: Menyimpan log setiap aktivitas alarm (ON/OFF)

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | BIGINT UNSIGNED | PK, AUTO INCREMENT | ID log alarm |
| user_id | BIGINT UNSIGNED | FK, NOT NULL | Referensi ke users.id |
| action | VARCHAR(255) | NOT NULL | Aksi: ON atau OFF |
| latitude | DECIMAL(10,7) | NULLABLE | Koordinat latitude |
| longitude | DECIMAL(10,7) | NULLABLE | Koordinat longitude |
| ip_address | VARCHAR(255) | NULLABLE | Alamat IP perangkat |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan log |
| updated_at | TIMESTAMP | NULLABLE | Waktu update terakhir |

**Relasi**: 
- `user_id` → FK ke `users.id` (CASCADE DELETE)

**Index**:
- PRIMARY KEY: id
- FOREIGN KEY: user_id
- INDEX: user_id

---

## 3. TABEL: alarm_states

**Deskripsi**: Menyimpan status current alarm sistem keamanan

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | BIGINT UNSIGNED | PK, AUTO INCREMENT | ID state alarm |
| is_on | BOOLEAN | NOT NULL, DEFAULT: 0 | Status alarm (ON/OFF) |
| activated_at | TIMESTAMP | NULLABLE | Waktu alarm diaktifkan |
| auto_off_at | TIMESTAMP | NULLABLE | Waktu alarm auto-off |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan |
| updated_at | TIMESTAMP | NULLABLE | Waktu update terakhir |

**Index**:
- PRIMARY KEY: id

---

## 4. TABEL: incidents ⭐ (TABEL BARU)

**Deskripsi**: Menyimpan laporan insiden keamanan yang dilaporkan oleh pengguna

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | BIGINT UNSIGNED | PK, AUTO INCREMENT | ID insiden |
| user_id | BIGINT UNSIGNED | FK, NOT NULL | Referensi ke users.id (pelapor) |
| type | ENUM | NOT NULL | Tipe insiden: KEBAKARAN, PENCURIAN, GEMPA_BUMI, BANJIR, KECELAKAAN, PENYERANGAN, GANGGUAN_KEAMANAN, LAINNYA |
| description | TEXT | NOT NULL | Deskripsi detail insiden |
| location | VARCHAR(255) | NULLABLE | Lokasi kejadian insiden |
| status | ENUM | NOT NULL, DEFAULT: 'ACTIVE' | Status: ACTIVE, RESOLVED, FALSE_ALARM |
| reported_at | TIMESTAMP | NOT NULL | Waktu insiden dilaporkan |
| resolved_at | TIMESTAMP | NULLABLE | Waktu insiden selesai ditangani |
| created_at | TIMESTAMP | NULLABLE | Waktu record dibuat |
| updated_at | TIMESTAMP | NULLABLE | Waktu update terakhir |

**Relasi**:
- `user_id` → FK ke `users.id` (CASCADE DELETE)

**Index**:
- PRIMARY KEY: id
- FOREIGN KEY: user_id
- INDEX: user_id
- INDEX: status
- INDEX: reported_at

---

## 5. TABEL: settings

**Deskripsi**: Menyimpan pengaturan konfigurasi sistem

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | BIGINT UNSIGNED | PK, AUTO INCREMENT | ID setting |
| setting_name | VARCHAR(255) | NOT NULL, UNIQUE | Nama setting |
| setting_value | TEXT | NULLABLE | Nilai setting |
| description | VARCHAR(255) | NULLABLE | Deskripsi setting |
| type | VARCHAR(255) | NOT NULL, DEFAULT: 'string' | Tipe: string, boolean, integer, json |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan |
| updated_at | TIMESTAMP | NULLABLE | Waktu update terakhir |

**Index**:
- PRIMARY KEY: id
- UNIQUE: setting_name

---

## 📊 DIAGRAM RELASI ANTAR TABEL

```
┌─────────────────────────────────────────────────────────────────┐
│                      SISIRINE DATABASE                          │
└─────────────────────────────────────────────────────────────────┘

                            ┌──────────────┐
                            │   users      │
                            ├──────────────┤
                            │ id (PK)      │
                            │ name         │
                            │ email        │
                            │ password     │
                            │ phone        │
                            │ role         │
                            │ is_active    │
                            └──────────────┘
                                  ▲
                    ┌─────────────┼─────────────┐
                    │             │             │
                    │ FK          │ FK          │ (no FK)
                    │ user_id     │ user_id     │
                    │             │             │
        ┌───────────▼─────────┐   │   ┌────────▼──────────┐
        │  alarm_logs         │   │   │   incidents       │
        ├─────────────────────┤   │   ├──────────────────┤
        │ id (PK)             │   │   │ id (PK)          │
        │ user_id (FK)        │   │   │ user_id (FK)     │
        │ action              │   │   │ type             │
        │ latitude            │   │   │ description      │
        │ longitude           │   │   │ location         │
        │ ip_address          │   │   │ status           │
        │ created_at          │   │   │ reported_at      │
        │ updated_at          │   │   │ resolved_at      │
        └─────────────────────┘   │   └──────────────────┘
                                  │
                                  │ (no FK)
                                  │
        ┌─────────────────────────▼─┐
        │   alarm_states            │
        ├──────────────────────────┤
        │ id (PK)                  │
        │ is_on                    │
        │ activated_at             │
        │ auto_off_at              │
        │ created_at               │
        │ updated_at               │
        └──────────────────────────┘


        ┌──────────────────────┐
        │   settings           │
        ├──────────────────────┤
        │ id (PK)              │
        │ setting_name (UNIQUE)│
        │ setting_value        │
        │ description          │
        │ type                 │
        │ created_at           │
        │ updated_at           │
        └──────────────────────┘
```

---

## 📋 RELASI TABEL (Relationships)

### users ↔ alarm_logs
- **Tipe**: One-to-Many (1:M)
- **Foreign Key**: alarm_logs.user_id → users.id
- **Constraint**: CASCADE DELETE
- **Arti**: Satu pengguna dapat memiliki banyak log alarm

### users ↔ incidents ⭐
- **Tipe**: One-to-Many (1:M)
- **Foreign Key**: incidents.user_id → users.id
- **Constraint**: CASCADE DELETE
- **Arti**: Satu pengguna dapat melaporkan banyak insiden

### alarm_states
- **Tipe**: No Direct Relationship
- **Arti**: Standalone table untuk state global alarm

### settings
- **Tipe**: No Direct Relationship
- **Arti**: Standalone table untuk konfigurasi sistem

---

## 🔄 ENUM VALUES

### users.role
```
- 'satpam'    - Petugas satuan pengaman
- 'wakasek'   - Wakil kepala sekolah
- 'sapras'    - Kepala sarpras (sarana prasarana)
- 'murid'     - Murid/siswa (default)
```

### incidents.type ⭐
```
- 'KEBAKARAN'           - Insiden kebakaran
- 'PENCURIAN'           - Insiden pencurian
- 'GEMPA_BUMI'          - Insiden gempa bumi
- 'BANJIR'              - Insiden banjir
- 'KECELAKAAN'          - Insiden kecelakaan
- 'PENYERANGAN'         - Insiden penyerangan
- 'GANGGUAN_KEAMANAN'   - Gangguan keamanan lainnya
- 'LAINNYA'             - Tipe insiden lainnya
```

### incidents.status ⭐
```
- 'ACTIVE'      - Insiden masih aktif/belum diselesaikan
- 'RESOLVED'    - Insiden sudah diselesaikan
- 'FALSE_ALARM' - Insiden merupakan alarm palsu
```

### settings.type
```
- 'string'    - Nilai berupa string
- 'boolean'   - Nilai berupa boolean (true/false)
- 'integer'   - Nilai berupa integer
- 'json'      - Nilai berupa JSON object
```

---

## 📊 CONTOH DATA

### users
| id | name | email | role | is_active |
|----|------|-------|------|-----------|
| 1 | Budi Santoso | budi@sekolah.sch.id | satpam | true |
| 2 | Siti Nurhaliza | siti@sekolah.sch.id | wakasek | true |
| 3 | Ahmad Wijaya | ahmad@sekolah.sch.id | murid | true |

### alarm_logs
| id | user_id | action | ip_address | created_at |
|----|---------|--------|------------|-----------|
| 1 | 1 | ON | 192.168.1.100 | 2026-01-27 10:30:00 |
| 2 | 1 | OFF | 192.168.1.100 | 2026-01-27 14:45:00 |

### incidents ⭐
| id | user_id | type | description | status | reported_at |
|----|---------|------|-------------|--------|-----------|
| 1 | 1 | PENCURIAN | Pencurian di ruang gudang barang | ACTIVE | 2026-01-27 09:15:00 |
| 2 | 2 | KECELAKAAN | Kecelakaan di area parkir motor | RESOLVED | 2026-01-26 14:30:00 |
| 3 | 3 | KEBAKARAN | Kebakaran kecil di lab komputer | FALSE_ALARM | 2026-01-25 11:00:00 |

### alarm_states
| id | is_on | activated_at | auto_off_at |
|----|-------|-------------|-----------|
| 1 | true | 2026-01-27 10:30:00 | 2026-01-27 22:30:00 |

### settings
| id | setting_name | setting_value | type |
|----|--------------|---------------|------|
| 1 | auto_off_duration | 12 | integer |
| 2 | system_name | Sisirine App | string |
| 3 | enable_notifications | true | boolean |

---

## 🔐 INTEGRITAS DATA

### Foreign Key Constraints
- **alarm_logs.user_id** → users.id (CASCADE DELETE)
  - Jika user dihapus, semua log alarm user akan otomatis dihapus
  
- **incidents.user_id** → users.id (CASCADE DELETE)
  - Jika user dihapus, semua insiden yang dilaporkan user akan otomatis dihapus

### Unique Constraints
- **users.email** - Email harus unik di sistem
- **settings.setting_name** - Nama setting harus unik

### Index Performance
- **alarm_logs**: user_id, created_at (sorting/filtering)
- **incidents**: user_id, status, reported_at (query optimization)
- **users**: email (login query)

---

## 📝 MIGRATIONS

File migrasi yang digunakan:
1. `2025_12_31_093730_create_users_table.php` - Tabel users
2. `2025_12_31_100204_create_alarm_logs_table.php` - Tabel alarm_logs
3. `2026_01_06_111658_create_alarm_states_table.php` - Tabel alarm_states
4. `2026_01_14_150000_create_settings_table.php` - Tabel settings
5. `2026_01_23_143000_create_incidents_table.php` - Tabel incidents ⭐

---

**Terakhir diperbarui**: 27 Januari 2026
**Status**: Complete dengan tabel incidents
**Total Tabel**: 5 tabel

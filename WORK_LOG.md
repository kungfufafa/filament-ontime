# WORK LOG - Master Development & Feature Progress

Dokumen ini mencatat seluruh rekam jejak perkembangan pengerjaan fitur, arsitektur sistem, skema database, API endpoints, serta riwayat commit pada proyek **Filament OnTime** dari awal inisialisasi hingga saat ini.

---

## 🏗️ FASE 1: Inisialisasi Proyek & Arsitektur Dasar (Foundational Setup)

### 1. Framework & Core Stack
- **Framework**: Laravel 13, Filament v5, Livewire v4, TailwindCSS v4, PHP 8.4.
- **Keamanan & Role Permission**: Spatie Laravel-Permission (Role: `Superadmin`, `BOD`, `Approver`, `Employee`).
- **Autentikasi API**: Laravel Sanctum API Tokens (`personal_access_tokens`).

### 2. Master Data & Schema Dasar
- **Perusahaan (Company)**:
  - Migration: `2026_07_23_000001_create_companies_table.php` & `2026_07_23_035611_add_lat_long_to_companies_table.php`.
  - Model `Company.php`.
  - Resource `CompanyResource.php`: Manajemen Badan Usaha, pengaturan koordinat Latitude/Longitude lokasi kantor, serta batas radius presensi (geofencing meter).
- **Divisi (Division)**:
  - Migration: `2026_07_23_000002_create_divisions_table.php`.
  - Model `Division.php`.
  - Resource `DivisionResource.php`: Manajemen Divisi per Perusahaan.
- **Jabatan (Job Level & Job Title)**:
  - Migrations: `2026_07_23_000003_create_job_levels_table.php` & `2026_07_23_000004_create_job_titles_table.php`.
  - Resource `JobLevelResource.php` & `JobTitleResource.php`.
- **Manajemen Karyawan (Employee)**:
  - Migration: `2026_07_23_000005_create_employees_table.php`.
  - Model `Employee.php`.
  - Resource `EmployeeResource.php`: Manajemen data pokok karyawan (NIP, Nama Lengkap, Perusahaan, Divisi, Level Jabatan, Posisi, Tanggal Bergabung, dan penautan Akun User).
- **Manajemen Pengguna (User)**:
  - Migration: `0001_01_01_000000_create_users_table.php`.
  - Model `User.php`.
  - Resource `UserResource.php`: Manajemen akun pengguna, kredensial password, penugasan role akses, dan penautan profil Karyawan.

---

## 📸 FASE 2: Presensi, Geofencing, UI Kamera & Laporan

### 1. Engine Presensi & Geofencing
- **Tabel Absensi**:
  - Migration: `2026_07_23_000009_create_attendances_table.php`, `2026_07_23_035620_add_is_corrected_and_attachment_to_attendances.php`, `2026_07_23_040502_update_attendances_status_column.php`.
  - Model `Attendance.php` (status: `on_time`, `late`, `present`, `absent`, `leave`, `is_corrected`).
- **Geofencing Service**:
  - Service `GeofenceService.php`: Kalkulasi jarak Haversine (meter) antara posisi GPS HP/perangkat pengguna dengan titik koordinat perusahaan.

### 2. Antarmuka UI Kamera & Laporan
- **Halaman `AbsenHariIni.php`**:
  - Antarmuka Check-In / Check-Out mandiri bagi Karyawan dengan integrasi Kamera WebRTC interaktif, upload foto selfie, validasi radius Geofence, dan petunjuk peta.
  - Custom UI Kamera (`update ui kamera`) untuk pengalaman presensi seluler yang responsif.
- **Halaman `LaporanAbsensi.php` & Export**:
  - Rekapitulasi laporan absensi harian/bulanan dengan filter tanggal, divisi, dan perusahaan.
  - Class `AttendanceReportExport.php`: Ekspor laporan absensi lengkap ke format spreadsheet Excel.

---

## 🔄 FASE 3: Engine Approval & Modul Pengajuan (Workforce Requests)

### 1. Engine Approval Bertingkat (Approval Flow Engine)
- **Database Approval**:
  - Migrations: `2026_07_23_000006_create_approvers_table.php`, `2026_07_23_000008_create_approval_flows_table.php`, `2026_07_23_040028_create_approval_request_steps_table.php`.
  - Models: `ApprovalFlow.php`, `Approver.php`, `ApprovalRequestStep.php`.
- **Service `ApprovalFlowService.php`**:
  - Engine penanganan alur approval berurutan (Manager Divisi -> BOD -> Superadmin).
  - Parallel Approver rule & penugasan role/user spesifik.
  - Fitur In-App Database Notifications untuk memberitahukan pengajuan baru ke approver yang berhak.
- **Resource Pendukung**:
  - `ApprovalFlowResource.php`: Konfigurasi alur approval per Badan Usaha.
  - `ApproverResource.php`: Mapping penugasan Approver per Perusahaan & Divisi.

### 2. Modul Pengajuan (Requests)
- **Cuti & Izin (`LeaveRequestResource.php`)**:
  - Migration: `2026_07_23_000010_create_leave_requests_table.php`.
  - Pengajuan cuti tahunan, izin tidak masuk, dan sakit (potong kuota vs tanpa potong, unggah surat dokter, lacak status approval).
- **Lembur (`OvertimeRequestResource.php`)**:
  - Migration: `2026_07_23_000011_create_overtime_requests_table.php` & `2026_07_23_040333_add_actual_duration_to_overtime_requests_table.php`.
  - Pengajuan jam lembur dengan kalkulasi durasi otomatis.
- **Koreksi Absensi (`AttendanceCorrectionResource.php`)**:
  - Migration: `2026_07_23_000012_create_attendance_corrections_table.php`.
  - Pengajuan perbaikan jam presensi yang lupa/salah check-in/out.

### 3. Dashboard, Widgets & RESTful API V1
- **Halaman Approval Mandiri**: `ApprovalSaya.php` (Pusat persetujuan bagi Approver).
- **Widgets Panel**: `UserProfileWidget`, `EmployeeStatsWidget`, `SuperadminOverviewWidget`, `ApproverPendingWidget`.
- **API V1 Endpoints (`app/Http/Controllers/Api/V1`)**:
  - `AuthController`: Login & Logout token Sanctum.
  - `AttendanceApiController`: Endpoint presensi check-in/check-out via mobile.
  - `LeaveRequestApiController`, `OvertimeRequestApiController`, `AttendanceCorrectionApiController`, `ApprovalApiController`.

---

## 🎓 FASE 4: Modul Magang (Internship)

- **Database & Migration**:
  - `2026_07_27_000001_create_interns_table.php`: Tabel `interns` (`id`, `user_id`, `company_id`, `division_id`, `mentor_id`, `nis` [unique], `full_name`, `institution`, `email`, `phone`, `start_date`, `end_date`, `status`, `timestamps`).
- **Model & Relasi**:
  - Model `Intern.php`. Relasi di `User.php` (`intern()`) dan `Employee.php` (`mentoredInterns()`).
- **Filament Admin Resource**:
  - `InternResource.php`: Menu **Magang** di *Master Data* (`heroicon-o-academic-cap`).
  - Relasi Mentor langsung terhubung ke data Karyawan (`employees`).
  - **Otomatisasi Reaktif**: Memilih Mentor Karyawan otomatis mengisi (*auto-fill*) Badan Usaha & Divisi.
  - **Aksi 1-Click "Buat Akun"**: Pembuatan akun User otomatis (Role: `Employee`, Password default: `password123`).
- **Pengujian**: `tests/Feature/InternResourceTest.php` (LULUS 100%).

---

## 💼 FASE 5: Modul Freelance (Freelancer)

- **Database & Migration**:
  - `2026_07_27_000002_create_freelancers_table.php`: Tabel `freelancers` (`id`, `user_id`, `company_id`, `division_id`, `supervisor_id`, `freelancer_number` [unique], `full_name`, `institution`, `email`, `phone`, `start_date`, `end_date`, `status`, `timestamps`).
- **Model & Relasi**:
  - Model `Freelancer.php`. Relasi di `User.php` (`freelancer()`) dan `Employee.php` (`supervisedFreelancers()`).
- **Filament Admin Resource**:
  - `FreelanceResource.php`: Menu **Freelance** di *Master Data* (`heroicon-o-briefcase`).
  - Relasi Supervisor/PIC langsung terhubung ke data Karyawan.
  - **Otomatisasi Reaktif**: Memilih Supervisor Karyawan otomatis mengisi (*auto-fill*) Badan Usaha & Divisi.
  - **Aksi 1-Click "Buat Akun"**: Pembuatan akun User otomatis bagi pekerja freelance.
- **Pengujian**: `tests/Feature/FreelanceResourceTest.php` (LULUS 100%).

---

## 🚪 FASE 6: Modul Pengunduran Diri Karyawan & Pembatasan Status

- **Database & Migration**:
  - `2026_07_27_000003_add_is_active_to_users_table.php`: Menambahkan kolom `is_active` (boolean, default `true`) pada tabel `users`.
  - `2026_07_27_000004_create_resignations_table.php`: Tabel `resignations` (`id`, `employee_id`, `resignation_date`, `last_working_day`, `reason`, `handover_notes`, `status`, `current_step`, `rejection_reason`, `timestamps`).
- **Pembatasan Status Karyawan**:
  - Opsi status di `EmployeeResource.php` dibatasi hanya 2 pilihan: **Aktif (`active`)** dan **Non-Aktif (`inactive`)**.
- **Model & Handler Otomatis**:
  - Model `Resignation.php` dengan method `applyResignation()`.
  - Handler otomatis mengubah status `Employee` menjadi `inactive` dan `User.is_active` menjadi `false` (Non-Aktif) saat disetujui di tahap approval akhir.
  - Integrasi dengan `ApprovalFlowService.php`.
- **Filament Resource & Approval**:
  - `ResignationResource.php`: Menu **Pengunduran Diri Karyawan** di *Akses & Approval* (`heroicon-o-arrow-right-on-rectangle`).
  - `ApprovalFlowResource.php`: Tab **Pengunduran Diri** (`resignation_steps`) untuk mengatur alur approval resign per perusahaan.
- **Pengujian**: `tests/Feature/ResignationTest.php` (LULUS 100%).

---

## 🎨 FASE 7: Optimasi UX Navigasi & Relasi Posisi (Job Title) ke Divisi

- **Restrukturisasi Navigasi Sidebar (5 Kelompok Baku)**:
  - 👥 **Manajemen SDM**: `Data Karyawan`, `Data Magang`, `Data Freelance`.
  - 🏢 **Struktur Organisasi**: `Badan Usaha`, `Divisi`, `Level Jabatan`, `Nama Posisi (Job Title)`.
  - ⏱️ **Presensi & Pengajuan**: `Presensi Mandiri`, `Pengajuan Cuti & Izin`, `Pengajuan Lembur`, `Koreksi Absensi`, `Pengunduran Diri`, `Laporan Absensi`.
  - 📥 **Persetujuan (Inbox)**: `Persetujuan Masuk`, `Alur Persetujuan Perusahaan`, `Penugasan Approver`.
  - ⚙️ **Pengaturan Sistem**: `Manajemen User`.
- **Penjelas Visual & Modal User Creation Transparan**:
  - `InternResource` & `FreelanceResource`: Menambahkan *helperText* penjelas reaktivitas pada mentor/supervisor, perusahaan, dan divisi.
  - Aksi "Buat Akun" diubah menjadi modal form pre-filled (Email & Password default `password123`) untuk peninjauan admin.
- **Relasi Posisi (Job Title) ke Divisi**:
  - Migration: `2026_07_27_000005_add_division_id_to_job_titles_table.php` (Foreign Key `division_id` ke `divisions`).
  - Model `JobTitle.php` (`division()`) & `Division.php` (`jobTitles()`).
  - `JobTitleResource.php`: Pengaturan divisi spesifik per posisi.
  - `EmployeeResource.php`: Reaktivitas pilihan *Nama Posisi* otomatis ter-filter sesuai Divisi yang dipilih.
- **Status Offboarding Karyawan**:
  - Indikator badge `Pengunduran Diri` pada tabel Karyawan (Pending / Approved Resign).

---

## 🚀 FASE 8: Interactive Dashboard Widgets, Printable Letters, & Team Leave Calendar

- **Interactive Dashboard Widgets (Point 3)**:
  - `SuperadminOverviewWidget.php`, `ApproverPendingWidget.php`, & `EmployeeStatsWidget.php`: Seluruh statistik (Perusahaan Aktif, Hadir Hari Ini, Antrean Approval, Kehadiran, Kuota Cuti, Lembur) terhubung dengan link URL interaktif ke halaman/filter terkait.
- **Generasi & Cetak Dokumen Resmi (Point 4)**:
  - Aksi `cetakSurat` ditambahkan pada `ResignationResource.php`, `InternResource.php`, dan `FreelanceResource.php`.
  - Dibuat 3 template blade printable view resmi: [print-resignation.blade.php](file:///c:/Users/AHTAR/filament-ontime/resources/views/filament/modals/print-resignation.blade.php), [print-intern.blade.php](file:///c:/Users/AHTAR/filament-ontime/resources/views/filament/modals/print-intern.blade.php), dan [print-freelance.blade.php](file:///c:/Users/AHTAR/filament-ontime/resources/views/filament/modals/print-freelance.blade.php) yang dilengkapi tombol cetak / print dokumen.
- **Halaman Kalender Cuti Tim (Point 5)**:
  - Dibuat Halaman [KalenderCuti.php](file:///c:/Users/AHTAR/filament-ontime/app/Filament/Pages/KalenderCuti.php) di bawah kelompok *Presensi & Pengajuan* (`navigationSort = 7`).
  - Dilengkapi filter bulan, tahun, dan perusahaan, serta tampilan visual jadwal cuti karyawan yang disetujui / pending.
  - Feature Test [KalenderCutiTest.php](file:///c:/Users/AHTAR/filament-ontime/tests/Feature/KalenderCutiTest.php).

---

## 🎲 FASE 9: Generasi Database Demo & Seeding

- **Database Demo Seeder**:
  - Dijalankan `php artisan db:seed --class=DemoSeeder` (memanggil `DatabaseSeeder`, `ProductionSeeder`, `RoleSeeder`, `CompanyLocationSeeder`, dan `AttendanceSeeder`).
  - Mengisi data perusahaan, lokasi geofence kantor, alur approval, akun Superadmin, BOD, Approver, Karyawan, Anak Magang (SMK Wikrama Bogor), Freelancer, serta sampel data absensi untuk pengujian chart & tabel.
- **Perbaikan Bug Laporan Absensi (`LaporanAbsensi.php`)**:
  - Memperbaiki `TypeError: Argument #1 ($state) must be of type string, App\Enums\AttendanceStatus given`.
  - Menyesuaikan penanganan typehint pada kolom `status` agar mendukung `AttendanceStatus` enum object maupun `string`.

---

## 📊 Status Pengujian & Verifikasi Aplikasi
- **Automated Tests**: 46 passed (165 assertions)
- **Browser Subagent Visual Verification**: Verified 100% (Navigasi Sidebar 5 Kelompok, Helper Text Reaktif, Modal Akun User, Kolom Offboarding, & Filter Posisi per Divisi)
- **Code Formatting**: Pint Formatter Clean (`vendor/bin/pint --dirty --format agent`)

---

## 📜 Riwayat Commit Git (Complete Log)
1. `d984952`: `feat: scaffold new Laravel project with Filament and developer best-practice rule sets`
2. `d984952` & `040d286`: `feat: implement overtime, leave, and attendance correction resources with automated approval flows and user management widgets`
3. `501f053`: `update ui kamera`
4. `1818be6`: `feat: tambah modul dan skema data Magang (Internship)`
5. `cfd3936`: `feat: tambah modul dan skema data Freelance (Freelancer)`
6. `eb8e1a8`: `refactor: ubah penamaan modul menjadi Pengunduran Diri Karyawan`
7. `d536abb`: `feat: modul Pengunduran Diri Karyawan dan pembatasan status karyawan Aktif/Non-Aktif`
8. `7aa8456`: `feat: tambah optimasi navigasi UX, relasi posisi ke divisi, cetak surat resmi, dashboard interaktif, dan kalender cuti tim`
9. `4c6dde5`: `feat: support intern & freelancer in absensi, laporan, dan dashboard profil`

---
*Catatan: Dokumen ini disimpan secara lokal dan diperbarui secara berkala pada setiap penyelesaian tugas/prompt.*

---

## 🗺️ FITUR: Map Picker Interaktif untuk Lokasi & Cabang

**Tanggal**: 2026-07-29

### Deskripsi
Menambahkan komponen peta interaktif **OpenStreetMap (Leaflet)** ke form lokasi perusahaan. Tersedia dua metode pengambilan koordinat:
1. **Cari Alamat** — ketik nama tempat/kota → Nominatim geocoding API (gratis, tanpa API key) → marker bergerak ke lokasi
2. **GPS Saya** — tombol deteksi posisi perangkat saat ini via `navigator.geolocation` dengan akurasi tinggi (`enableHighAccuracy: true`)
3. **Klik/Drag Marker** — presisi manual langsung di peta
4. **Gunakan Lokasi Ini** — tekan tombol untuk mengisi field `latitude` & `longitude` di form Filament secara otomatis

### File Baru
- `app/Filament/Forms/Components/MapPickerField.php` — Custom Filament `Field`, `dehydrated(false)` (UI-only, tidak disimpan sendiri)
- `resources/views/filament/forms/components/map-picker-field.blade.php` — Blade view dengan Alpine.js + Leaflet CDN

### File Diubah
- `app/Filament/Resources/CompanyResource.php`:
  - Tambah `MapPickerField` di Section **"Koordinat GPS Utama / HQ"** (tab Informasi Utama)
  - Tambah `MapPickerField` di dalam `Repeater` "Lokasi & Cabang" (tab Multi-Geofence)
- `app/Filament/Resources/CompanyLocationResource.php`:
  - Tambah `MapPickerField` di form lokasi cabang

### Teknologi
- **Leaflet.js 1.9.4** via CDN — render peta OSM
- **Nominatim API** (api.openstreetmap.org) — geocoding gratis tanpa API key
- **Browser Geolocation API** — deteksi GPS perangkat
- **Alpine.js + `$wire.set()`** — push koordinat ke Filament form fields

### Verifikasi
- ✅ Map render di tab "Informasi Utama" (HQ section)
- ✅ Map render di dalam Repeater tab "Lokasi & Cabang"
- ✅ Map render di form CompanyLocationResource
- ✅ Search alamat berfungsi (Nominatim)
- ✅ Marker draggable & klik peta memperbarui koordinat
- ✅ "Gunakan Lokasi Ini" mengisi field latitude & longitude

---

## 📊 FITUR: Perbaikan Foto & Kolom Kondisional Lokasi GPS pada Laporan Absensi

**Tanggal**: 2026-07-29

### Deskripsi
1. **Perbaikan Tampilan Foto Check-In / Check-Out**:
   - Mengubah `disk('s3')` pada `LaporanAbsensi.php` menjadi `config('filesystems.default')` agar foto yang tersimpan di disk lokal/public maupun S3 dapat dirender dengan benar.
   - Mendukung format path relatif storage maupun URL langsung.
2. **Kolom Kondisional Lokasi GPS**:
   - Menambahkan metode `isGpsRequiredInPolicy()` untuk mengecek apakah kebijakan perusahaan (`CompanyPolicy`) mengaktifkan kewajiban GPS (`require_gps`).
   - Menambahkan kolom **Lokasi GPS (In / Out)** pada tabel Laporan Absensi yang otomatis muncul apabila aturan `require_gps` aktif.
   - Kolom menampilkan koordinat Check-In & Check-Out dan memiliki link interaktif yang ketika diklik akan langsung membuka lokasi di **OpenStreetMap** pada tab baru.
3. **Ekspor Excel (`AttendanceReportExport.php`)**:
   - Menambahkan kolom koordinat `GPS Check-In` dan `GPS Check-Out` pada hasil unduhan rekap Excel.
   - Memperbaiki penanganan disk gambar agar ekspor foto di Excel tidak error saat menggunakan disk lokal.

### File Diubah
- `app/Filament/Pages/LaporanAbsensi.php`
- `app/Exports/AttendanceReportExport.php`
- `WORK_LOG.md`

### Commit
- `52cac8c`: `feat: tambah map picker interaktif OpenStreetMap dan perbaikan laporan absensi (foto S3 & lokasi GPS)`




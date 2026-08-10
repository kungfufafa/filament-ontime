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

---

## 📍 FITUR: Alur Persetujuan Absensi Luar Geofence (Out of Bounds Attendance Approval)

**Tanggal**: 2026-07-31

### Deskripsi
1. **Penyimpanan Presensi Luar Geofence**:
   - Mengubah perlakuan Check In & Check Out yang dilakukan di luar radius geofence lokasi kantor. Daripada memblokir pengguna dengan error validasi / HTTP 422, presensi tetap berhasil disimpan ke tabel `attendances` dengan penanda `is_out_of_bounds = true` dan status `pending_approval` (`AttendanceStatus::PendingApproval`).
2. **Alur Approval (Geofence)**:
   - Menambahkan tipe alur persetujuan baru (`request_type = 'geofence'`) pada engine `ApprovalFlowService` dan antarmuka `ApprovalFlowResource`.
   - Jika Perusahaan belum mengonfigurasi alur khusus di menu Alur Persetujuan, sistem secara otomatis membuat alur persetujuan default 1-tahap ke role `Approver`.
3. **Persetujuan & Penolakan**:
   - **Disetujui (Approved)**: Status absensi otomatis dikalkulasikan kembali menjadi `Tepat Waktu (on_time)` atau `Terlambat (late)` berdasarkan jam check-in awal.
   - **Ditolak (Rejected)**: Status absensi berubah menjadi `Ditolak (rejected)` lengkap dengan catatan alasan penolakan (`rejection_reason`).
4. **Dukungan Presensi Mandiri Web & Mobile API V1**:
   - `AbsenHariIni.php` (Web Presensi Mandiri): Notifikasi presensi berhasil dikirim dan menunggu persetujuan atasan.
   - `AttendanceApiController.php` & `ApprovalApiController.php`: Response JSON presensi berhasil dicatat dengan pesan persetujuan serta pengolahan inbox approval via mobile API.
   - `AttendanceResource.php` (Filament Admin Resource & API Resource): Menambahkan indikator kolom badge `Luar Geofence` & filter pencarian.

### Skema & Migration Baru
- `2026_07_31_100000_add_geofence_approval_fields_to_attendances_table.php` (Menambahkan kolom `is_out_of_bounds`, `current_step`, dan `rejection_reason` pada tabel `attendances`).

### File Dibuat & Diubah
- **Migration**: `database/migrations/2026_07_31_100000_add_geofence_approval_fields_to_attendances_table.php`
- **Enum**: `app/Enums/AttendanceStatus.php`
- **Models**: `app/Models/Attendance.php`
- **Services**: `app/Services/ApprovalFlowService.php`
- **Filament Resources & Pages**:
  - `app/Filament/Resources/ApprovalFlows/ApprovalFlowResource.php`
  - `app/Filament/Resources/ApprovalFlows/Pages/CreateApprovalFlow.php`
  - `app/Filament/Resources/ApprovalFlows/Pages/EditApprovalFlow.php`
  - `app/Filament/Resources/Attendances/AttendanceResource.php`
  - `app/Filament/Pages/AbsenHariIni.php`
- **API Controllers & Resources**:
  - `app/Http/Controllers/Api/V1/AttendanceApiController.php`
  - `app/Http/Controllers/Api/V1/ApprovalApiController.php`
  - `app/Http/Resources/Api/V1/AttendanceResource.php`
- **Tests**: `tests/Feature/AttendanceGeofenceApprovalTest.php`

### Hasil Pengujian & Formatting
- ✅ `tests/Feature/AttendanceGeofenceApprovalTest.php`: Passed (4 tests, 19 assertions)
- ✅ `tests/Feature/AttendanceTest.php`: Passed (3 tests, 22 assertions)
- ✅ `tests/Feature/AttendanceInternFreelancerTest.php`: Passed (10 tests, 21 assertions)
- ✅ Seluruh Test Suite: Passed (74 tests, 275 assertions)
- ✅ `vendor/bin/pint --format agent`: Formatted cleanly.

---

## 🔑 FITUR: Akses & Hak Akses Approval Tanpa Batas Bagi Superadmin

**Tanggal**: 2026-07-31

### Deskripsi
- **Akses Approval Tanpa Batas**: Memastikan pengguna dengan role `Superadmin` memiliki hak akses penuh tanpa batasan (*unlimited override*) untuk menyetujui (`approve`) atau menolak (`reject`) setiap tahap pengajuan di seluruh jenis request (`Cuti`, `Lembur`, `Koreksi Absensi`, `Absensi Luar Geofence`, `Pengunduran Diri`), tanpa terhalang oleh penugasan user spesifik, role approver tertentu, maupun cakupan divisi/perusahaan.
- **Pembaruan Dashboard Approval (`ApprovalSaya.php` & `approval-saya.blade.php`)**:
  - Menampilkan antrean persetujuan `Presensi Luar Geofence` dan `Pengunduran Diri` di dashboard persetujuan masuk.
  - **Refactoring Komponen Asli Filament UI (`x-filament::*`)**: Refactoring antarmuka [`approval-saya.blade.php`](file:///c:/Users/AHTAR/filament-ontime/resources/views/filament/pages/approval-saya.blade.php) menggunakan komponen Blade resmi bawaan Filament:
    - `<x-filament::section>` untuk kontainer section utama, heading, icon, dan dark mode native.
    - `<x-filament::badge>` untuk label status dan penghitung antrean (terintegrasi dengan palet warna Filament `warning`, `info`, `primary`, `danger`).
    - `<x-filament::button>` untuk tombol link Maps, Lampiran file, serta tombol aksi **Tolak** dan **Setujui**.
  - **Desain Kartu Komparasi Koordinat Karyawan vs Kantor & Kolom Note**:
    - 📝 **Kolom Catatan / Note**: Ditambahkan kolom `Catatan / Note` di dalam kartu rincian persetujuan untuk menampilkan catatan pengajuan presensi (`$item->notes`).
    - 📍 **Posisi Karyawan (Saat Absen)**: Kartu bersih yang menampilkan `Latitude` & `Longitude` karyawan dengan tombol langsung ke Google Maps (`https://maps.google.com/?q=lat,lng`).
    - 🏢 **Posisi Kantor (Seharusnya)**: Kartu bersisian yang menampilkan `Nama Kantor/Cabang`, `Latitude` & `Longitude` lokasi kantor terdekat dengan tombol langsung ke Google Maps (`https://maps.google.com/?q=lat,lng`).
    - ⚠️ **Indikator Selisih Jarak**: Menampilkan selisih jarak terdeteksi vs radius geofence yang diizinkan perusahaan.
  - **Pratinjau Foto Thumbnail Kecil**: Foto selfie Check-In/Out tampil dalam bentuk thumbnail `12x12` (`w-12 h-12`) yang bersih.
- **Registrasi Navigasi Resource**: Membuka pendaftaran navigasi menu `Pengajuan Cuti & Izin`, `Pengajuan Lembur`, dan `Koreksi Absensi` di sidebar bagi Superadmin.

### File Diubah
- `app/Services/ApprovalFlowService.php`
- `app/Filament/Pages/ApprovalSaya.php`
- `resources/views/filament/pages/approval-saya.blade.php`
- `resources/views/filament/components/attendance-photo-modal.blade.php`
- `app/Filament/Resources/LeaveRequests/LeaveRequestResource.php`
- `app/Filament/Resources/OvertimeRequests/OvertimeRequestResource.php`
- `app/Filament/Resources/AttendanceCorrections/AttendanceCorrectionResource.php`

---

## 🎭 FITUR: Face Recognition & Anti-Spoofing Liveness Detection Presensi

**Tanggal**: 2026-07-31

### Deskripsi
- **Face Recognition & Real-time Liveness Check**: Integrasi modul verifikasi wajah dan anti-spoofing pada sistem presensi (Web Filament `AbsenHariIni.php` & Mobile REST API `AttendanceApiController.php`).
- **Foto Master Referensi**: HR / Admin dapat mendaftarkan & mengunggah Foto Master Wajah (`master_face_photo`) pada profil Karyawan (`EmployeeResource`), Magang (`InternResource`), dan Freelance (`FreelanceResource`).
- **Pengaturan Kebijakan Perusahaan (`CompanyPolicy`)**:
  - `require_face_recognition`: Toggle opsional mengaktifkan/mematikan kewajiban Face Recognition / AI Detection (seperti halnya fitur GPS dan Foto selfie).
  - `face_match_threshold`: Pengaturan ambang batas kemiripan wajah (misal 60%).
  - `face_fail_action`: Pilihan tindakan jika verifikasi wajah tidak cocok (`reject` = Tolak Presensi Langsung, `approval` = Alihkan ke Approval Atasan/Pending).
- **Client-side Liveness Detection & Camera Overlay (`camera-capture.blade.php`)**:
  - Secara dinamis menyesuaikan mode kamera: Jika `require_face_recognition` diaktifkan, modul **TensorFlow.js (`@tensorflow/tfjs-core`, `@tensorflow-models/face-landmarks-detection`)** akan dimuat untuk akselerasi biometrik GPU WebGL, verifikasi kedipan mata (*EAR*), dan penguncian tombol foto hingga liveness lolos. Jika dimatikan, antarmuka beralih ke Mode Kamera Foto Standar tanpa mengunci tombol.
- **Engine Verification (`FaceRecognitionService.php`)**:
  - Membandingkan foto selfie biometrik yang baru diambil dengan Foto Master Karyawan yang tersimpan di storage (multi-disk support).
  - Menyimpan skor kemiripan (`face_match_score`), status verifikasi (`is_face_verified`), dan catatan verifikasi (`face_verification_notes`) pada tabel `attendances`.

### Skema & Migration Baru
- `2026_07_31_110000_add_face_recognition_fields.php`:
  - `employees`, `interns`, `freelancers`: `master_face_photo`, `master_face_verified_at`.
  - `company_policies`: `require_face_recognition`, `face_match_threshold`, `face_fail_action`.
  - `attendances`: `is_face_verified`, `face_match_score`, `face_verification_notes`.

### File Dibuat & Diubah
- **Migration**: `database/migrations/2026_07_31_110000_add_face_recognition_fields.php`
- **Models**: `CompanyPolicy.php`, `Attendance.php`, `Employee.php`, `Intern.php`, `Freelancer.php`
- **Service**: `app/Services/FaceRecognitionService.php`
- **Filament Resources & Pages**:
  - `app/Filament/Resources/CompanyResource.php`
  - `app/Filament/Resources/EmployeeResource.php`
  - `app/Filament/Resources/InternResource.php`
  - `app/Filament/Resources/FreelanceResource.php`
  - `app/Filament/Pages/AbsenHariIni.php`
  - `resources/views/filament/components/camera-capture.blade.php`
- **API Controller**: `app/Http/Controllers/Api/V1/AttendanceApiController.php`
- **Test**: `tests/Feature/FaceRecognitionTest.php`

### Hasil Pengujian & Formatting
- ✅ `tests/Feature/FaceRecognitionTest.php`: Passed (3 tests, 7 assertions)
- ✅ `tests/Feature/AttendanceTest.php`: Passed (22 tests, 83 assertions)
- ✅ `tests/Feature/CompanyPolicyTest.php`: Passed (2 tests, 2 assertions)
- ✅ `vendor/bin/pint --format agent`: Formatted cleanly without errors.

---

## 📸 FITUR: Dual-Mode Pendaftaran Foto Master Wajah (Kamera & Upload Storage)

**Tanggal**: 2026-07-31

### Deskripsi
- **Pilihan Dual-Mode**: Pengguna (Karyawan, Magang, Freelance) maupun Admin/HR dapat mendaftarkan/memperbarui Foto Master Wajah (`master_face_photo`) melalui 2 metode pilihan:
  - 📁 **Mode Upload File**: Memilih foto dari galeri / penyimpanan perangkat.
  - 📸 **Mode Kamera Langsung**: Ambil foto wajah selfie langsung via web cam / kamera HP.
- **Mandiri untuk User (`AbsenHariIni.php`)**:
  - Tombol aksi `Foto Master Wajah` di header halaman Presensi Mandiri.
  - Banner peringatan dinamis jika perusahaan mewajibkan Face Recognition tetapi user belum mendaftarkan Foto Master Wajah.
- **Integrasi Admin Forms**: Memperbarui `EmployeeResource`, `InternResource`, dan `FreelanceResource` dengan komponen dual-mode.

### File Dibuat & Diubah
- **Component**: `resources/views/filament/components/master-face-capture.blade.php`
- **Pages**: `app/Filament/Pages/AbsenHariIni.php`, `resources/views/filament/pages/absen-hari-ini.blade.php`
- **Resources**: `app/Filament/Resources/EmployeeResource.php`, `app/Filament/Resources/InternResource.php`, `app/Filament/Resources/FreelanceResource.php`

### Hasil Pengujian & Formatting
- ✅ Full Test Suite `php artisan test --compact`: **77 passed (282 assertions)**
- ✅ `vendor/bin/pint --format agent`: Formatted cleanly.

---

## 🗑️ FITUR: Penghapusan Data Absensi di Laporan Absensi & Integrasi Data User

**Tanggal**: 2026-07-31

### Deskripsi
- **Aksi Hapus Absensi (Single & Bulk Delete)**:
  - Menambahkan aksi `DeleteAction` dan `DeleteBulkAction` pada [LaporanAbsensi.php](file:///c:/Users/AHTAR/filament-ontime/app/Filament/Pages/LaporanAbsensi.php) dan [AttendanceResource.php](file:///c:/Users/AHTAR/filament-ontime/app/Filament/Resources/Attendances/AttendanceResource.php).
  - Admin (Superadmin, Approver, BOD) dapat menghapus baris absensi individual maupun secara masal (bulk delete) dengan konfirmasi modal.
  - Saat absensi dihapus, status absensi user pada tanggal tersebut di-reset sehingga user dapat melakukan absensi ulang (re-check-in).
- **Integrasi Data User (User Account Integration)**:
  - Kolom `nama_peserta` diikutsertakan informasi Akun User terhubung (`User Email`) beserta tipe Karyawan / Magang / Freelance.
  - Fitur pencarian (*searchable*) di Laporan Absensi mendukung pencarian berdasarkan Nama, NIP/NIS, dan Email Akun User.

### File Dibuat & Diubah
- `app/Filament/Pages/LaporanAbsensi.php`
- `app/Filament/Resources/Attendances/AttendanceResource.php`

### Hasil Pengujian & Formatting
- ✅ Full Test Suite `php artisan test --compact`: **77 passed (282 assertions)**
- ✅ `vendor/bin/pint --format agent`: Formatted cleanly.

---

## ⚡ FASE 10: Optimasi Performa Query, Eliminasi N+1, & Database Indexing

**Tanggal**: 2026-08-03

### Deskripsi
1. **Pencegahan Lazy Loading di Development (`AppServiceProvider.php`)**:
   - Menambahkan `Model::preventLazyLoading(! app()->isProduction());` untuk secara otomatis mendeteksi dan mencegah masalah N+1 query selama pengembangan dan pengujian.
2. **Eliminasi N+1 Query pada Filament Pages & Resources**:
   - **`LaporanAbsensi.php`**: Menambahkan eager loading `employee.user`, `intern.user`, `freelancer.user` pada query laporan agar akses ke relasi akun user tidak memicu N+1 query per baris tabel.
   - **`AttendanceResource.php`**: Memperbarui `modifyQueryUsing` untuk melakukan eager loading `employee.user`, `intern.user`, `freelancer.user`.
   - **`KalenderCuti.php`**: Eager loading `intern.company`, `intern.division`, `freelancer.company`, `freelancer.division` pada query pengajuan cuti.
   - **`ApprovalFlowService.php`**: Eager loading relasi akun `user` pada seluruh profil pekerja (`employee`, `intern`, `freelancer`) untuk mempercepat rendering dashboard persetujuan (`ApprovalSaya.php`).
3. **Optimasi Agregasi Query pada Widgets Dashboard**:
   - **`SuperadminOverviewWidget.php`**: Menggabungkan 3 query count absensi terpisah (`presentCount`, `lateCount`, `leaveCount`) menjadi 1 query agregasi `selectRaw` tunggal.
   - **`EmployeeStatsWidget.php`**: Menggabungkan kalkulasi status kehadiran (`presentDays`, `lateDays`) menjadi 1 query agregasi `selectRaw` tunggal.
4. **Database Performance Indexing (Migration)**:
   - Migration baru: `database/migrations/2026_08_03_000001_add_performance_indexes.php`.
   - Menambahkan index pada kolom berfrekuensi tinggi:
     - `attendances`: `date`, `status`, `is_out_of_bounds`, dan composite index (`date`, `status`).
     - `leave_requests`: `status`, `start_date`, `end_date`, serta composite index (`employee_id`, `status`), (`intern_id`, `status`), (`freelancer_id`, `status`).
     - `overtime_requests`: `status`, `date`, serta composite index per worker type.
     - `attendance_corrections`: `status`, `date`, serta composite index per worker type.
     - `resignations`: `status` dan composite index (`employee_id`, `status`).
     - `approval_request_steps`: `status` dan composite index lookup (`approvable_type`, `approvable_id`, `step_order`).

### File Dibuat & Diubah
- **Migration**: `database/migrations/2026_08_03_000001_add_performance_indexes.php`
- **Provider**: `app/Providers/AppServiceProvider.php`
- **Pages & Resources**: `app/Filament/Pages/LaporanAbsensi.php`, `app/Filament/Resources/Attendances/AttendanceResource.php`, `app/Filament/Pages/KalenderCuti.php`
- **Services**: `app/Services/ApprovalFlowService.php`
- **Widgets**: `app/Filament/Widgets/SuperadminOverviewWidget.php`, `app/Filament/Widgets/EmployeeStatsWidget.php`

### Hasil Pengujian & Formatting
---

## 🛡️ FASE 11: RESTful API Endpoints Terintegrasi Filament Shield & Spatie Permission

**Tanggal**: 2026-08-03

### Deskripsi
1. **Otorisasi Middleware Berbasis Filament Shield Permission**:
   - Seluruh endpoint API V1 kini dilindungi oleh middleware otorisasi permission bawaan Filament Shield (`can:PermissionName`).
   - `Superadmin` secara otomatis dapat mengakses seluruh endpoint via `Gate::before` callback di `AppServiceProvider.php`.
2. **Inspeksi Role & Permission API (`ShieldApiController.php`)**:
   - `GET /api/v1/auth/me`: Mengembalikan daftar `roles` dan seluruh `permissions` milik pengguna (misal: `View:AbsenHariIni`, `ViewAny:LeaveRequest`, dll.).
   - `GET /api/v1/shield/roles`: Mengembalikan seluruh daftar Role dan mapping permission-nya.
   - `GET /api/v1/shield/permissions`: Mengembalikan daftar seluruh Shield permission yang terdaftar dalam sistem.
3. **Endpoint Baru Resource Shield (Pengunduran Diri, Master Data, Kalender Cuti, & Laporan Absensi)**:
   - **`ResignationApiController.php`**: `GET /api/v1/resignations`, `POST /api/v1/resignations`, `GET /api/v1/resignations/{id}` (dilindungi `can:ViewAny:Resignation`, `can:Create:Resignation`, `can:View:Resignation`).
   - **`MasterDataApiController.php`**: API Master Data Badan Usaha, Divisi, Level Jabatan, Nama Posisi, Karyawan, Magang, & Freelance (`/api/v1/master/...`).
   - **`ReportAndCalendarApiController.php`**: Endpoint `GET /api/v1/kalender-cuti` & `GET /api/v1/laporan-absensi`.
4. **Pengujian Integrasi**:
   - Menambahkan feature test `tests/Feature/ShieldApiTest.php`.

### File Dibuat & Diubah
- **Providers & Resources**: `app/Providers/AppServiceProvider.php`, `app/Http/Resources/Api/V1/UserResource.php`, `app/Http/Resources/Api/V1/ResignationResource.php`
- **API Controllers**:
  - `app/Http/Controllers/Api/V1/ShieldApiController.php`
  - `app/Http/Controllers/Api/V1/ResignationApiController.php`
  - `app/Http/Controllers/Api/V1/MasterDataApiController.php`
  - `app/Http/Controllers/Api/V1/ReportAndCalendarApiController.php`
- **Routes**: `routes/api.php`
- **Tests**: `tests/Feature/ShieldApiTest.php`, `tests/Feature/ApiTest.php`

### Hasil Pengujian & Formatting
- ✅ `ShieldApiTest`: 6 passed (13 assertions)
- ✅ Full Test Suite: **80 passed (295 assertions)**
- ✅ `vendor/bin/pint --dirty --format agent`: Clean formatted.

---

## 📚 FASE 12: Dokumentasi Ringkasan Pengguna Non-Admin & Spesifikasi REST API V1

**Tanggal**: 2026-08-03

### Deskripsi
1. **Ringkasan Fitur Sisi Pengguna (Non-Admin)**:
   - **Intern (Magang)**: Presensi GPS & Selfie Liveness, Koreksi Absensi, Laporan Absensi Diri, Profil Pengguna.
   - **Freelancer**: Presensi GPS & Selfie Liveness, Koreksi Absensi, Laporan Absensi Diri, Profil Pengguna.
   - **Employee (Karyawan Tetap/Kontrak)**: Presensi GPS & Selfie Liveness, Pengajuan Cuti & Izin, Pengajuan Lembur, Koreksi Absensi, Pengajuan Resign, Kalender Cuti Tim, Laporan Absensi Diri.
   - **Approver / BOD (Atasan / Manager / Direksi)**: Seluruh Akses Karyawan + Dashboard Inbox Approval Saya (Persetujuan / Penolakan Cuti, Lembur, Koreksi Absensi, Presensi Luar Geofence, & Resign).
2. **Dokumentasi REST API V1**:
   - Dokumentasi lengkap endpoint Sanctum Token Auth, Presensi Check-In/Out, Request Cuti, Lembur, Koreksi Absensi, Resign, Approval Atasan, Master Data, Kalender Cuti, & Laporan Absensi.

---

## 🌐 FASE 13: Exposur Seluruh Data Entitas & Master Data via REST API V1

**Tanggal**: 2026-08-03

### Deskripsi
1. **Peluasan Master Data API (`MasterDataApiController.php`)**:
   - Menambahkan endpoint `index` dan detail `show` untuk seluruh entitas master data dan konfigurasi sistem:
     - `Company`: `GET /api/v1/master/companies`, `GET /api/v1/master/companies/{id}`
     - `CompanyLocation`: `GET /api/v1/master/company-locations`, `GET /api/v1/master/company-locations/{id}`
     - `CompanyPolicy`: `GET /api/v1/master/company-policies`, `GET /api/v1/master/company-policies/{id}`
     - `Division`: `GET /api/v1/master/divisions`, `GET /api/v1/master/divisions/{id}`
     - `JobTitle`: `GET /api/v1/master/job-titles`, `GET /api/v1/master/job-titles/{id}`
     - `JobLevel`: `GET /api/v1/master/job-levels`, `GET /api/v1/master/job-levels/{id}`
     - `Employee`: `GET /api/v1/master/employees`, `GET /api/v1/master/employees/{id}`
     - `Intern`: `GET /api/v1/master/interns`, `GET /api/v1/master/interns/{id}`
     - `Freelancer`: `GET /api/v1/master/freelancers`, `GET /api/v1/master/freelancers/{id}`
     - `Holiday`: `GET /api/v1/master/holidays`, `GET /api/v1/master/holidays/{id}`
     - `ApprovalFlow`: `GET /api/v1/master/approval-flows`, `GET /api/v1/master/approval-flows/{id}`
     - `Approver`: `GET /api/v1/master/approvers`, `GET /api/v1/master/approvers/{id}`
     - `User`: `GET /api/v1/master/users`, `GET /api/v1/master/users/{id}`
2. **Peluasan Histori & Detail Single Record API**:
   - `AttendanceApiController.php`: Menambahkan `GET /api/v1/attendances` (riwayat presensi ter-filter) dan `GET /api/v1/attendances/{id}` (detail presensi).
   - `LeaveRequestApiController.php`: Menambahkan `GET /api/v1/leave-requests/{id}`.
   - `OvertimeRequestApiController.php`: Menambahkan `GET /api/v1/overtime-requests/{id}`.
   - `AttendanceCorrectionApiController.php`: Menambahkan `GET /api/v1/attendance-corrections/{id}`.
3. **Pembaruan Rute & Otorisasi (`routes/api.php`)**:
   - Seluruh endpoint didaftarkan dengan middleware `auth:sanctum` dan `can:ViewAny:Entity` / `can:View:Entity`.
4. **Pengujian Automated Testing (`MasterDataApiTest.php` & `ApiTest.php`)**:
   - Menambahkan feature test `tests/Feature/MasterDataApiTest.php` yang menguji 100% respons 200 OK dan struktur JSON dari seluruh endpoint baru.
5. **Dokumentasi Terintegrasi**:
   - Memperbarui [DOKUMENTASI_PENGGUNA_DAN_API.md](file:///c:/Users/AHTAR/filament-ontime/DOKUMENTASI_PENGGUNA_DAN_API.md) dengan spesifikasi seluruh rute baru.

### File Diubah & Dibuat
- **Controllers**:
  - `app/Http/Controllers/Api/V1/MasterDataApiController.php`
  - `app/Http/Controllers/Api/V1/AttendanceApiController.php`
  - `app/Http/Controllers/Api/V1/LeaveRequestApiController.php`
  - `app/Http/Controllers/Api/V1/OvertimeRequestApiController.php`
  - `app/Http/Controllers/Api/V1/AttendanceCorrectionApiController.php`
- **Routes**: `routes/api.php`
- **Documentation**: `DOKUMENTASI_PENGGUNA_DAN_API.md`
- **Tests**: `tests/Feature/MasterDataApiTest.php`

---

## 📸 FASE 14: Implementasi Endpoint REST API V1 Upload Foto Master Wajah & Real-Time Quota Summary

**Tanggal**: 2026-08-03

### Deskripsi
1. **Endpoint Registrasi Foto Master Wajah (`POST /api/v1/attendance/master-face`)**:
   - Menambahkan method `registerMasterFace(Request $request)` pada `AttendanceApiController.php`.
   - Mendukung pengunggahan gambar (`multipart/form-data`) untuk `Employee`, `Intern`, maupun `Freelancer` terautentikasi.
   - File disimpan menggunakan `FileNamingService` dan otomatis memperbarui `master_face_photo` serta `master_face_verified_at = now()` pada profil yang terhubung.
   - Mengembalikan URL gambar lengkap dan ISO Timestamp verifikasi.
2. **Quota Summary Real-Time (`GET /api/v1/leave-requests`)**:
   - Memperbarui `LeaveRequestApiController.php` method `index` dengan menyertakan `quota_summary` yang berisi `annual_leave_quota` (maksimal), `used_days` (terpakai tahun berjalan), dan `remaining_days` (sisa kuota).
3. **Pembaruan Rute & Dokumentasi**:
   - Menambahkan rute `POST /attendance/master-face` pada `routes/api.php`.
   - Memperbarui `apiDocs.md` dan `REKAP_LENGKAP_API_DAN_HALAMAN_MOBILE.txt`.
4. **Pengujian Feature Test**:
   - Membuat `tests/Feature/MasterFaceApiTest.php` untuk menguji pengunggahan foto master wajah dan pengujian validasi input gambar.

### File Diubah & Dibuat
- **Controller**: `app/Http/Controllers/Api/V1/AttendanceApiController.php`, `app/Http/Controllers/Api/V1/LeaveRequestApiController.php`
- **Routes**: `routes/api.php`
- **Tests**: `tests/Feature/MasterFaceApiTest.php`
- **Documentation**: `apiDocs.md`, `REKAP_LENGKAP_API_DAN_HALAMAN_MOBILE.txt`

### Hasil Pengujian & Formatting
- ✅ `MasterFaceApiTest`: 2 passed (11 assertions)
- ✅ **Eliminasi 403 Forbidden pada Seluruh Rute Operasional Mobile**: Menghapus middleware `can:*` kaku pada tingkat rute untuk Presensi, Pengajuan Cuti, Lembur, Koreksi Absensi, Resign, Approval Pending, Kalender Cuti, & Laporan Absensi Diri. Keamanan dan isolasi data kini ditangani secara presisi & dinamis di tingkat Controller.
- ✅ `vendor/bin/pint --dirty --format agent`: Clean formatted.

---

## 🧪 FASE 15: Verifikasi & Pengujian Integrasi API Absensi (Check-In & Check-Out)

**Tanggal**: 2026-08-04

### Deskripsi
- Melakukan verifikasi komprehensif terhadap kesiapan API Absensi (`/api/v1/attendance/*`) untuk pengiriman presensi seluler.
- Memastikan seluruh skenario presensi (Geofencing GPS, Selfie Photo Upload, Face Recognition, Deteksi Keterlambatan, dan Workflow Approval Luar Geofence) berfungsi dengan baik.
- Menjelaskan spesifikasi endpoint dan payload pengiriman kepada pengguna.

### Hasil Pengujian Automated Testing
- ✅ **Hasil Running Test**: 22 passed, 83 assertions (`php artisan test --compact --filter=Attendance`).
- ✅ **Test Coverage**:
  - `AttendanceGeofenceApprovalTest`
  - `AttendanceInternFreelancerTest`
  - `ApiTest`

### Konfigurasi PHP (`php.ini`)
- ✅ `upload_tmp_dir` diset ke `"C:\Users\AHTAR\AppData\Local\Temp"` (folder temp user yang memiliki izin tulis).
- ✅ `upload_max_filesize` dinaikkan dari `2M` ke `10M`.
- ✅ `post_max_size` dinaikkan dari `8M` ke `20M`.

### Perbaikan Error Temporary Upload S3 (`ValueError: Path must not be empty`)
- ✅ **Diagnosis**: Pengunggahan file ke S3 membutuhkan file temporary lokal server sebelum dikirimkan (*stream*). Karena `upload_tmp_dir` sebelumnya berada di `C:\Windows\Temp` (non-writable), PHP gagal membuat temporary file sehingga `fopen("")` melempar `ValueError`.
- ✅ **Solusi**: Diubah ke `C:\Users\AHTAR\AppData\Local\Temp` dan menambahkan validasi `$file->isValid()` pada `AttendanceApiController.php` (`checkIn`, `checkOut`, `registerMasterFace`).

### Perbaikan URL Master Face Photo (`UserResource.php`)
- ✅ **Masalah**: `master_face_photo` sebelumnya hanya tersedia di dalam objek turunan (`employee`, `intern`, `freelancer`) dan rawan menghasilkan URL ganda (*double URL prefix*) jika path berupa URL absolute.
- ✅ **Solusi**:
  1. Menambahkan atribut `master_face_photo` dan `master_face_verified_at` di level utama (*root level*) objek `user` pada `UserResource.php`.
  2. Menambahkan method helper `formatStorageUrl(?string $path)` yang secara otomatis mendeteksi apakah path sudah berupa URL `http(s)` atau path relatif S3, mencegah URL ganda.

### Perbaikan HTTP 403 Forbidden pada Akses Berkas S3 (`UserResource.php`, `AttendanceResource.php`, `AttendanceApiController.php`)
- ✅ **Diagnosis**: Ember S3 / SeaweedFS (`dev-ontime`) bersifat privat (*private bucket*). Permintaan URL langsung tanpa signature menghasilkan respons `HTTP/1.1 403 Forbidden`.
- ✅ **Solusi**:
  1. Menggunakan `Storage::disk('s3')->temporaryUrl($path, now()->addDays(7))` untuk menghasilkan *Presigned Temporary URL* bertanda tangan otentikasi AWS yang terverifikasi mengembalikan status `HTTP/1.1 200 OK`.
  2. Memperbarui `FileNamingService.php` dengan penambahan opsi `visibility => 'public'`.
### Penyesuaian Ringkasan Laporan Absensi (`ReportAndCalendarApiController.php` & Mobile App)
- ✅ **Keterlambatan (Total Terlambat)**: Mengubah kalkulasi `total_late` pada API ringkasan dari akumulasi durasi menit menjadi frekuensi (jumlah kali terlambat). Tampilan antarmuka pada aplikasi mobile CESA (`reports/index.tsx`) disesuaikan dari `{summary.total_late} mnt` menjadi `{summary.total_late} kali` ("Total Terlambat").
- ✅ **Otomatisasi Absen / Alpha Hari Kerja (Senin - Jumat)**: Sistem kini menghitung otomatis hari kerja (Senin s/d Jumat) yang berada di rentang periode laporan tetapi tidak terdeteksi catatan presensi (dan tidak ter-cover cuti/izin yang disetujui) secara otomatis sebagai **Absen/Alpha** pada ringkasan laporan (`total_absent`).
- ✅ **Hasil Pengujian ApiTest**: 16 passed, 109 assertions (`php artisan test --compact --filter=ApiTest`).

### Perbaikan Error Lazy Loading User Resource (`UserResource.php`)
- ✅ **Diagnosis**: Error `LazyLoadingViolationException: Attempted to lazy load [company] on model [App\Models\Employee]` terjadi saat mengakses halaman `/admin/users` karena relasi bertingkat (`employee.company`, `employee.division`, `intern.company`, `intern.division`, `freelancer.company`, `freelancer.division`) belum di-eager-load.
- ✅ **Solusi**: Diperbarui pada `UserResource.php` method `getEloquentQuery()` untuk memuat seluruh relasi relavan (`with(['roles', 'employee.company', 'employee.division', 'intern.company', 'intern.division', 'freelancer.company', 'freelancer.division'])`), mengeliminasi Lazy Loading Exception secara permanen.

---

## 🙈 FASE 16: Kondisional Visibility UI Face Recognition (Toggle Dynamic UI)

**Tanggal**: 2026-08-10

### Deskripsi
- Memastikan antarmuka UI terkait *Face Recognition* disembunyikan secara otomatis ketika kebijakan `require_face_recognition` berada pada status **OFF** (dinonaktifkan oleh Superadmin).

### Perubahan File
1. **`app/Filament/Pages/AbsenHariIni.php`**:
   - `getHeaderActions()`: Menambahkan pengecekan `$this->companyPolicy?->require_face_recognition`. Jika nonaktif, tombol aksi `updateMasterFaceAction` ("Foto Master Wajah") di pojok kanan atas halaman disembunyikan.
2. **`resources/views/filament/pages/absen-hari-ini.blade.php`**:
   - Menambahkan badge indikator status `Face Recognition: Aktif / Nonaktif` pada kartu Aturan Absensi.
   - Membungkus kartu preview & registrasi "Foto Master Biometrik" dengan kondisi `@if($policy?->require_face_recognition)`. Kartu ini otomatis tersembunyi jika fitur nonaktif.
3. **`resources/views/filament/components/camera-capture.blade.php`**:
   - Menyesuaikan *overlay bounding box* oval deteksi wajah agar hanya muncul jika `requireFaceRecognition` bernilai `true`.
   - Menyembunyikan teks petunjuk *liveness step* TensorFlow.js ketika `requireFaceRecognition` bernilai `false`, menyajikan tampilan kamera selfie bersih (tanpa indikator biometrik) saat fitur dimatikan.

### Hasil Pengujian
- ✅ **Perbaikan Modal Kamera saat Feature ON**: Memperbarui `AbsenHariIni.php` (`checkInAction` & `checkOutAction`) agar field kamera (`check_in_photo` & `check_out_photo`) selalu **visible & required** jika `$requirePhoto || $requireFaceRecognition`. Sebelumnya jika `require_photo` bernilai `false` namun `require_face_recognition` `true`, elemen kamera tersembunyi dari modal presensi.
- ✅ **Auto Enable Photo Toggle**: Pada `CompanyResource.php`, mengaktifkan `require_face_recognition` akan otomatis menyalakan toggle `require_photo`.
- ✅ **Blade Condition pada Preview Kamera**: Menggunakan struktur percabangan Blade `@if($requireFaceRecognition)` ... `@else` ... `@endif` langsung di komponen `camera-capture.blade.php` untuk merender *overlay bounding oval ring*, teks status *liveness step*, dan tombol verifikasi biometrik secara pasti saat fitur **ON**, serta tampilan kamera bersih saat fitur **OFF**.
- ✅ Formatter: `vendor/bin/pint --dirty --format agent` (Passed cleanly).
- ✅ Automated Test: `php artisan test --compact --filter=FaceRecognitionTest` (3 passed).


---

## 📷 FASE 17: Penyederhanaan UI Presensi Mandiri (Preview Kamera Live, Tombol Berdampingan & Mobile-First)

**Tanggal**: 2026-08-10

### Deskripsi
- Menyederhanakan antarmuka UI *Presensi Mandiri* (`AbsenHariIni.php` & `absen-hari-ini.blade.php`) dengan mengutamakan pengalaman tampilan seluler (*mobile-first priority*).
- Mengintegrasikan preview kamera video streaming langsung (*live camera stream preview*) di bagian atas halaman Presensi Mandiri.
- Menempatkan **Tombol Check In** (Emerald) dan **Tombol Check Out** (Rose) secara **berdampingan** (*side-by-side*) langsung di bawah preview kamera.
- Menerapkan logika penguncian tombol otomatis:
  - Sebelum Check In: Tombol **Check In** aktif (1-click selfie & submit), sedangkan Tombol **Check Out** terkunci (*disabled*).
  - Setelah Check In: Tombol **Check In** dikunci (*"Sudah Check In"*), sedangkan Tombol **Check Out** aktif (*enabled*).
- Memindahkan posisi kartu rekapitulasi **Jam Check-In & Jam Check-Out** ke **bagian bawah** halaman untuk konsistensi visual pada layar HP/Mobile.

### Perubahan File
1. **`resources/views/filament/components/camera-capture.blade.php`**:
   - Menata ulang layout tombol dengan *grid 2 kolom* (`grid-cols-2`) side-by-side untuk tombol **Check In** dan **Check Out**.
   - Menyesuaikan penanganan status `disabled` dan penanda mode aksi saat pengunggahan foto selfie & pemrosesan presensi.
2. **`resources/views/filament/pages/absen-hari-ini.blade.php`**:
   - Memindahkan komponen preview kamera & tombol berdampingan ke posisi atas.
   - Memindahkan kartu status **Jam Check-In** & **Jam Check-Out** ke bagian paling bawah kartu.
   - Mengoptimalkan responsivitas layout grid & padding untuk tampilan layar HP/mobile.

### Hasil Pengujian
- ✅ Formatter: `vendor/bin/pint --dirty --format agent` (Passed cleanly).
- ✅ Automated Tests: `php artisan test --compact --filter=AttendanceTest` (3 passed, 22 assertions).

---

## 🚀 FASE 18: Integrasi Presensi Mandiri Langsung pada Dashboard Utama

**Tanggal**: 2026-08-10

### Deskripsi
- Memindahkan tampilan widget *Presensi Mandiri* (Preview Kamera Live Stream + Tombol Check In & Check Out Berdampingan + Rekapitulasi Jam Presensi) langsung ke halaman utama **Dashboard** (`/admin`).
- Seluruh pengguna yang memiliki akses presensi (Karyawan, Magang, Freelancer) kini dapat langsung melakukan Check In / Check Out secara instan dari Dashboard begitu berhasil login tanpa perlu berpindah ke menu terpisah.
- Mengabstraksikan fungsi presensi mandiri ke dalam Trait `HasPresensiActions` untuk memastikan prinsip *DRY (Don't Repeat Yourself)* antara Widget Dashboard dan Halaman Presensi Mandiri.

### Perubahan File
1. **`app/Filament/Concerns/HasPresensiActions.php`** [BARU]:
   - Trait penampung method bersama: `getTodayAttendanceProperty()`, `getLinkedProfile()`, `getCompanyPolicyProperty()`, `processCheckIn()`, dan `processCheckOut()`.
2. **`app/Filament/Widgets/PresensiWidget.php`** [BARU]:
   - Widget Dashboard Filament yang menggunakan Trait `HasPresensiActions`.
   - `canView()`: Menentukan hak akses widget hanya bagi akun Karyawan, Peserta Magang, dan Freelancer (dan tersembunyi bagi Superadmin).
   - Diurutkan pada `$sort = 1` agar tampil tepat di bawah profil pengguna pada Dashboard.
3. **`resources/views/filament/widgets/presensi-widget.blade.php`** [BARU]:
   - View Blade widget Dashboard yang merender preview kamera live stream, tombol aksi side-by-side (Check In & Check Out), serta rekapitulasi status jam presensi di bagian bawah.
4. **`app/Providers/Filament/AdminPanelProvider.php`**:
   - Mendaftarkan `PresensiWidget::class` pada array `$panel->widgets()`.
5. **`app/Filament/Pages/AbsenHariIni.php`**:
   - Diperbarui untuk menggunakan Trait `HasPresensiActions`.

### Hasil Pengujian
- ✅ Formatter: `vendor/bin/pint --dirty --format agent` (Passed cleanly).
- ✅ Automated Tests: `php artisan test --compact --filter=AttendanceTest` (3 passed, 22 assertions).
- ✅ Frontend Build: `npm run build` (Passed in 5.54s).





















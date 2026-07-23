# Dokumen Spesifikasi Fitur & Otorisasi Berbasis Role (Role-Based Documentation)
## Sistem Presensi & Multi-Step Approval Multi-Company (OnTime)

**Tanggal:** 23 Juli 2026  
**Versi Sistem:** v1.0.0  
**Framework:** Laravel 13 & Filament v5  

---

## 📌 1. Pendahuluan & Arsitektur Keamanan

Sistem OnTime dirancang untuk mengelola presensi, kebijakan, dan alur persetujuan (approval) secara fleksibel pada lingkungan **Multi-Company (Multi-Tenant)**.

Pengamanan dan otorisasi sistem dibangun menggunakan dua lapis pertahanan:
1. **Role-Based Access Control (RBAC)**: Menggunakan paket Spatie Laravel Permission dengan 3 Role utama: `Superadmin`, `Approver`, dan `Employee`.
2. **Company & Division Data Scoping**: Pemfilteran di level backend Eloquent query untuk memastikan pengguna hanya dapat membaca dan meng-export data sesuai wewenangnya.

---

## 📊 2. Matriks Hak Akses & Fitur Lintas Role

| Modul / Fitur | Superadmin | Approver | Employee | Keterangan Scope |
| :--- | :---: | :---: | :---: | :--- |
| **Manajemen Badan Usaha (Company)** | ✅ Full | ❌ | ❌ | Lintas Perusahaan |
| **Kebijakan Perusahaan (Policy)** | ✅ Full | 👁️ Read | 👁️ Read | Per Perusahaan |
| **Hirarki Divisi & Jabatan** | ✅ Full | 👁️ Read | 👁️ Read | Per Perusahaan |
| **Alur Approval (ApprovalFlow)** | ✅ Full | 👁️ Read | ❌ | Per Perusahaan & Jenis Pengajuan |
| **Mapping Approver** | ✅ Full | 👁️ Read | ❌ | Scope Perusahaan / Divisi |
| **Presensi Harian (Check In/Out)** | ❌ | ❌ | ✅ Full | Mandiri per Karyawan |
| **Pengajuan Cuti / Izin** | 👁️ Read | 👁️ Read | ✅ Submit | Sesuai Kuota Tahun Kalender |
| **Pengajuan Lembur** | 👁️ Read | 👁️ Read | ✅ Submit | Dilengkapi Durasi Aktual |
| **Pengajuan Koreksi Absensi** | 👁️ Read | 👁️ Read | ✅ Submit | Koreksi Jam Absen |
| **Pusat Persetujuan (Approval Saya)**| 👁️ All | ✅ Active | ❌ | Ter-scope ke Step Aktif |
| **Widget Dashboard** | Superadmin Overview | Approver Pending | Employee Stats | Dynamic Widget per Role |
| **Laporan Absensi & Export Excel** | ✅ All PT | ✅ Scoped PT | ❌ | Approver terikat Scope Mapping |

---

## 🛠️ 3. Dokumentasi Fitur Lengkap Berdasarkan Role

---

### A. Role: Superadmin (Pengelola Sistem Global)

Superadmin memiliki wewenang penuh dalam mengonfigurasi struktur organisasi, kebijakan kerja, alur approval, dan memantau operasional seluruh perusahaan.

#### 1. Manajemen Badan Usaha (`CompanyResource`)
- **Fungsi**: Mengelola master data perusahaan terdaftar (Nama, Kode, Email, Alamat, Koordinat Lat/Long Kantor).
- **Fitur Utama**:
  - **Action "Kelola Kebijakan"**: Konfigurasi kebijakan 1-to-1 per perusahaan (`CompanyPolicy`), meliputi:
    - `late_tolerance_minutes`: Toleransi keterlambatan jam masuk (dalam menit).
    - `require_photo`: Kewajiban upload foto selfie saat check-in/out.
    - `require_gps` & `geofence_radius_meters`: Kewajiban GPS Geofence (menggunakan rumus Haversine dari titik koordinat kantor).
    - `annual_leave_quota`: Jatah kuota cuti tahunan karyawan.
    - `work_start_time` & `work_end_time`: Jam shift standar kerja.

#### 2. Manajemen Divisi (`DivisionResource`)
- **Fungsi**: Mengelola struktur divisi perusahaan bertingkat (Parent-Child).
- **Fitur Utama**:
  - **Visual Indentasi Tree**: Menampilkan nama divisi berpola hirarki (contoh: `IT & Software` ➔ `— Subdivisi QA`).
  - **Validasi Anti-Circular Reference**: Memiliki observer model yang menolak jika divisi dijadikan parent bagi dirinya sendiri atau dipilih dari sub-divisinya sendiri.

#### 3. Manajemen Jabatan & Karyawan (`JobLevel`, `JobTitle`, `EmployeeResource`)
- **Fungsi**: Mengelola tingkatan jabatan (`JobLevel`), nama jabatan (`JobTitle`), dan profil karyawan (`Employee`).
- **Fitur Utama**:
  - **Pemisahan Akun & Profil**: Karyawan dapat dibuat tanpa akun login (opsional). Akun login (`user_id`) ditautkan ketika karyawan membutuhkan akses ke portal/sistem.

#### 4. Konfigurator Alur Approval (`ApprovalFlowResource`)
- **Fungsi**: Membentuk urutan tahap approval per perusahaan untuk 3 jenis pengajuan (Cuti/Izin, Lembur, Koreksi Absensi).
- **Fitur Utama**:
  - **3 Tab Pengajuan**: Tab khusus per jenis pengajuan.
  - **Repeater Step Approval**: Menyusun urutan tahap approval (`step_order` 1, 2, 3...) beserta tipe penugasan (berdasarkan Role Approver atau Spesifik User). Memiliki validasi urutan berurutan tanpa lompatan.

#### 5. Otorisasi Mapping Approver (`ApproverResource`)
- **Fungsi**: Menugaskan pengguna ber-role `Approver` ke lingkup kerja tertentu.
- **Fitur Utama**:
  - **Dual Scope Level**: Approver dapat ditugaskan pada **Company Level** (menyetujui seluruh divisi di PT tsb) atau **Division Level** (khusus divisi tertentu).
  - **Composite Unique Index**: Mencegah duplikasi mapping untuk kombinasi `[user_id, company_id, division_id, level]`.

#### 6. Dashboard & Laporan Global (`SuperadminOverviewWidget` & `LaporanAbsensi`)
- **Fungsi**: Pemantauan kehadiran harian lintas perusahaan dan unduh rekap absensi.
- **Fitur Utama**:
  - Stat card statistik kehadiran lintas PT hari ini.
  - Export laporan rekap absensi ke format Excel (`.xlsx`) tanpa batasan scope.

---

### B. Role: Approver (Penyetuju / Atasan)

Approver bertugas mengevaluasi dan memberikan tindakan (setuju/tolak) terhadap pengajuan yang masuk pada tahap wewenangnya.

#### 1. In-App Database Notifications (FR-6.4)
- **Fungsi**: Memberikan notifikasi lonceng di kanan atas panel Filament saat ada pengajuan baru yang membutuhkan persetujuan.
- **Mekanisme**: Dipicu otomatis oleh `ApprovalFlowService` saat pengajuan baru dibuat atau saat pengajuan maju ke step berikutnya.

#### 2. Dashboard Widget Antrean Pending (`ApproverPendingWidget`)
- **Fungsi**: Menampilkan ringkasan jumlah pengajuan yang berada pada tahap aktif approver tsb langsung di dashboard utama.

#### 3. Pusat Persetujuan Terpusat (`ApprovalSaya`)
- **Fungsi**: Dashboard terpadu yang menggabungkan seluruh item pending dari pengajuan Cuti, Lembur, dan Koreksi Absensi.
- **Aturan Bisnis Utama**:
  - **Reusable Query (`getPendingRequestsForUser`)**: Pengambilan data terpusat di `ApprovalFlowService` sehingga logic otorisasi tidak tercecer.
  - **Semantik Approver Paralel (OR Semantics / First-to-Approve)**: Jika terdapat lebih dari 1 approver di step yang sama, persetujuan dari **salah satu approver** sudah cukup untuk memajukan pengajuan.
  - **Execution Timing**: Persetujuan pada step 1 atau 2 hanya memajukan `current_step`. Perubahan data absensi asli (`applyLeave()`, `applyCorrection()`) **hanya dieksekusi saat step terakhir (final step)** disetujui.
  - **Rejection**: Penolakan di step manapun langsung menghentikan alur pengajuan dan menandai status `rejected`.

#### 4. Laporan Rekap Absensi Ter-Scope (`LaporanAbsensi`)
- **Fungsi**: Melihat dan meng-export laporan rekap absensi karyawan.
- **Fitur Keamanan**: Query database secara ketat difilter berdasarkan mapping `approvers` milik pengguna. Approver PT A **tidak dapat melihat atau meng-export** data milik PT B.

---

### C. Role: Employee (Karyawan)

Employee bertugas melakukan pencatatan presensi harian serta mengajukan permohonan cuti, lembur, dan koreksi absensi.

#### 1. Pencatatan Presensi Harian (`AbsenHariIni`)
- **Fungsi**: Custom page untuk melakukan Check-In dan Check-Out harian.
- **Fitur Utama**:
  - **Dynamic Policy Enforcement**: Menyesuaikan tampilan berdasarkan policy PT karyawan yang login.
  - **Validasi Foto**: Menampilkan kamera/upload foto jika `require_photo = true`.
  - **Validasi Geofence GPS**: Mengambil titik koordinat browser dan menghitung jarak ke lokasi kantor menggunakan rumus spherical Haversine. Jika jarak `> geofence_radius_meters`, submit ditolak.
  - **Kalkulasi Terlambat**: Menghitung menit keterlambatan otomatis dengan membandingkan jam check-in terhadap `work_start_time + late_tolerance_minutes`.

#### 2. Pengajuan Cuti / Izin (`LeaveRequestResource`)
- **Fungsi**: Mengajukan cuti tahunan, izin, atau sakit.
- **Fitur Utama**:
  - **Validasi Kuota Tahun Kalender**: Pengajuan `annual_leave` menghitung sisa kuota (`CompanyPolicy.annual_leave_quota` dikurangi total cuti approved di tahun berjalan Jan-Des). Pengajuan melebihi sisa kuota ditolak otomatis.
  - **Auto-Generate Steps**: Submit pengajuan otomatis membentuk record `approval_request_steps` dan mendistribusikan notifikasi ke approver step 1.

#### 3. Pengajuan Lembur (`OvertimeRequestResource`)
- **Fungsi**: Mengajukan estimasi jam lembur dan tugas yang dikerjakan.
- **Fitur Utama**:
  - **Action "Isi Durasi Aktual"**: Setelah lembur disetujui, karyawan/approver dapat mencatat `actual_duration_minutes`.
  - **Warning Badge**: Jika durasi aktual melebihi estimasi awal, tabel menampilkan indikator peringatan (`+Xm dari estimasi`) berwarna kuning untuk audit payroll.

#### 4. Pengajuan Koreksi Absensi (`AttendanceCorrectionResource`)
- **Fungsi**: Mengajukan perbaikan data absensi (misal terlewat check-out) beserta alasan dan bukti lampiran dokumen.

#### 5. Dashboard Stat Card Pribadi (`EmployeeStatsWidget`)
- **Fungsi**: Menampilkan ringkasan pribadi bulan berjalan (total hari hadir, hari terlambat, sisa kuota cuti tahunan, dan total jam lembur disetujui).

---

## 📑 4. Ringkasan Alur Data Skenario Nyata

```text
[Employee Absen / Ajukan Request]
               │
               ▼
[Sistem Evaluasi Policy & Kuota] ──(Gagal)──► [Pesan Error Validasi]
               │ (Lulus)
               ▼
[ApprovalFlowService::generateSteps()] ──► [Kirim Database Notification]
               │
               ▼
[Approver Login & Buka 'Approval Saya']
               │
      ┌────────┴────────┐
   (Tolak)          (Setujui)
      │                 │
      ▼                 ▼
[Status Rejected]  [current_step == max_steps?]
(Flow Berhenti)        ├── (Tidak) ──► Increment Step & Notify Next Approver
                       └── (Ya)    ──► Status Approved & Eksekusi Callback
                                       (Update Attendances Record)
```

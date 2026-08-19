# Dokumen Pengujian UAT (User Acceptance Testing)
## Sistem Presensi & Approval Multi-Company (OnTime)

**Tanggal:** 23 Juli 2026  
**Versi Sistem:** v1.0.0  
**URL Aplikasi Lokal:** `http://127.0.0.1:8000/admin`  

---

### 🔑 Kredensial Login Pengujian

Gunakan akun-akun berikut untuk menguji perilaku sistem berdasarkan masing-masing role dan kebijakan perusahaan:

| Role / Jabatan | Email | Password | Scope & Kebijakan Perusahaan |
| :--- | :--- | :--- | :--- |
| **Superadmin** | `admin@ontime.com` | `password` | Akses Penuh seluruh Badan Usaha |
| **Approver PT MSI** | `approver.msi@ontime.com` | `password` | Approver PT Media System Indonesia |
| **Karyawan PT MSI** | `employee.msi@ontime.com` | `password` | Staff IT (Policy: Wajib Foto & Geofence GPS 100m Monas) |
| **Approver PT MSG** | `approver.msg@ontime.com` | `password` | Approver PT Media Solusi Global |
| **Karyawan PT MSG** | `employee.msg@ontime.com` | `password` | Staff Sales (Policy: Fleksibel, Foto/GPS false) |
| **Approver CV TOP** | `approver.top@ontime.com` | `password` | Approver CV Top Logistics |
| **Karyawan CV TOP** | `employee.top@ontime.com` | `password` | Staff Gudang (Policy: Wajib Foto, GPS false) |

---

### 📋 Checklist Skenario Pengujian UAT

#### Skenario 1: Manajemen Master Data Multi-Company (Role: Superadmin)
- [ ] **1.1 Lihat Daftar Perusahaan (`/admin/companies`)**: Memastikan 3 perusahaan demo (`PT MSI`, `PT MSG`, `CV TOP`) tampil dengan lokasi koordinat lat/long.
- [ ] **1.2 Kelola Kebijakan Perusahaan**: Klik action **Kelola Kebijakan** pada PT MSI. Ubah `toleransi_telat_menit` atau `radius_geofence_meter` dan pastikan tersimpan.  
- [ ] **1.3 Struktur Hirarki Divisi (`/admin/divisions`)**: Buka menu Divisi, pastikan tampilan nama divisi memiliki visual indentasi tree (`— Nama Subdivisi`).
- [ ] **1.4 Otorisasi Approver (`/admin/approvers`)**: Buka menu Approver, pastikan opsi user yang muncul hanya pengguna ber-role `Approver`.

---

#### Skenario 2: Konfigurasi Alur Approval Fleksibel (`/admin/approval-flows`)
- [ ] **2.1 Approval Multi-Step PT MSI**: Buka alur approval PT MSI. Memastikan pengajuan Cuti terkonfigurasi 2 Tahap (Step 1: Direct Manager, Step 2: HR Manager).
- [ ] **2.2 Validasi Urutan Step**: Coba tambah step approval baru dengan `step_order` yang melompati urutan (misal dari 1 langsung ke 3). Sistem harus menolak dengan pesan error validasi urutan.

---

#### Skenario 3: Absensi Harian (Role: Employee)
- [ ] **3.1 Absen Karyawan PT MSI (`employee.msi@ontime.com`)**:
  - Buka menu **Absen Hari Ini**.
  - Klik **Check In**. Pastikan sistem meminta izin Kamera & Geolocation GPS.
  - Jika posisi GPS di luar 100m dari titik Monas Jakarta, submit absensi ditolak oleh sistem.
- [ ] **3.2 Absen Karyawan PT MSG (`employee.msg@ontime.com`)**:
  - Buka menu **Absen Hari Ini**.
  - Klik **Check In**. Karena policy PT MSG tidak mewajibkan Foto & GPS, check-in langsung berhasil tanpa pemblokiran lokasi.

---

#### Skenario 4: Pengajuan Cuti & Validasi Kuota (Role: Employee)
- [ ] **4.1 Validasi Penolakan Kuota (`employee.msi@ontime.com`)**:
  - Buka menu **Leave Request** (`/admin/leave-requests`).
  - Ajukan Cuti Tahunan selama 15 hari (melebihi kuota 12 hari PT MSI). Pastikan pengajuan ditolak sistem dengan pesan sisa kuota tidak mencukupi.
- [ ] **4.2 Submission Cuti Valid**:
  - Ajukan Cuti Tahunan selama 2 hari. Pengajuan berhasil disimpan dengan status `Tahap 1 dari 2`.

---

#### Skenario 5: Pengajuan Lembur & Durasi Aktual
- [ ] **5.1 Pengajuan Lembur (`/admin/overtime-requests`)**:
  - Karyawan mengajukan lembur 120 menit untuk hari ini. Status menjadi `Menunggu Approval`.
- [ ] **5.2 Pengisian Durasi Aktual**:
  - Setelah lembur disetujui, amati tombol action **Isi Durasi Aktual**.
  - Jika durasi aktual diisi 150 menit (melebihi estimasi 120 menit), pastikan tabel menampilkan badge indikator kuning `150 m (+30m dari estimasi)`.

---

#### Skenario 6: Eksekusi Approval Terpusat (Role: Approver)
- [ ] **6.1 In-App Notification**:
  - Login sebagai `approver.msi@ontime.com`. Cek ikon lonceng notifikasi di kanan atas. Pastikan ada notifikasi pengajuan baru dari Andi.
- [ ] **6.2 Dashboard & Widget**:
  - Memastikan widget *Antrean Approval Saya* menampilkan jumlah item pending yang membutuhkan tindakan.
- [ ] **6.3 Halaman Approval Saya (`/admin/approval-saya`)**:
  - Buka halaman **Approval Saya**. Klik **Setujui** pada pengajuan cuti Andi.
  - Memastikan pengajuan berlanjut ke `Tahap 2` dan belum langsung mengubah data kehadiran sebelum tahap akhir disetujui.

---

#### Skenario 7: Laporan Rekap & Keamanan Scope Data (`/admin/laporan-absensi`)
- [ ] **7.1 Akses Laporan Approver (`approver.msi@ontime.com`)**:
  - Buka menu **Laporan Absensi**. Pastikan **HANYA** data karyawan PT MSI yang tampil. Data PT MSG & CV TOP tidak boleh bocor/tampil.
- [ ] **7.2 Export ke Excel**:
  - Klik tombol **Export ke Excel**. Pastikan file `.xlsx` berhasil diunduh dan datanya sesuai dengan filter scope pengguna.
- [ ] **7.3 Akses Laporan Superadmin (`admin@ontime.com`)**:
  - Buka menu **Laporan Absensi**. Memastikan data seluruh perusahaan (PT MSI, PT MSG, CV TOP) dapat difilter dan di-export secara lengkap.

---

### 📝 Lembar Persetujuan Pengujian (Sign-Off)

| Peran Tester / Stakeholder | Nama Tester | Status (Pass / Fail) | Catatan / Masukan | Tanggal Sign-Off |
| :--- | :--- | :--- | :--- | :--- |
| **Superadmin / HR Corp** | __________________ | `[  ] PASS` | _________________________________ | ____ / ____ / 2026 |
| **Manager PT MSI** | __________________ | `[  ] PASS` | _________________________________ | ____ / ____ / 2026 |
| **Manager PT MSG** | __________________ | `[  ] PASS` | _________________________________ | ____ / ____ / 2026 |
| **Manager CV TOP** | __________________ | `[  ] PASS` | _________________________________ | ____ / ____ / 2026 |

# Project Requirement Document (PRD)
## Sistem Absensi Multi-Perusahaan berbasis Laravel Filament

**Versi:** 1.0
**Tanggal:** 23 Juli 2026
**Status:** Draft

---

## 1. Latar Belakang

Perusahaan menaungi beberapa badan usaha (PT MSI, PT MSG, CV Top, dll) dengan struktur organisasi, jabatan, dan kebijakan operasional yang berbeda-beda. Dibutuhkan sistem absensi terpusat yang tetap fleksibel mengakomodasi perbedaan kebijakan tiap badan usaha, dengan pemisahan yang jelas antara **struktur organisasi/jabatan** dan **hak akses sistem**.

## 2. Tujuan

1. Mendigitalisasi proses absensi, izin/cuti, dan lembur karyawan.
2. Menyediakan struktur organisasi yang fleksibel (multi badan usaha, divisi berjenjang tak terbatas).
3. Memisahkan konsep jabatan struktural dengan hak akses/role sistem.
4. Mendukung kebijakan (policy) dan alur persetujuan (approval flow) yang berbeda-beda per badan usaha, dapat dikonfigurasi tanpa perlu ubah kode.
5. Menyediakan pelaporan kehadiran yang komprehensif per badan usaha/divisi/karyawan.

## 3. Ruang Lingkup (Scope)

**Termasuk dalam scope:**
- Manajemen master data (badan usaha, divisi, jabatan, posisi, karyawan)
- Manajemen role & hak akses (Superadmin, Approver, Employee)
- Absensi harian (check-in/check-out)
- Pengajuan izin/cuti, lembur, dan koreksi absensi
- Approval workflow dinamis & dapat dikonfigurasi per badan usaha
- Kebijakan (policy) absensi per badan usaha
- Dashboard & laporan

**Di luar scope (fase awal):**
- Payroll/penggajian (integrasi bisa dilakukan di fase berikutnya)
- Aplikasi mobile native (opsional, menyusul via API)

## 4. Definisi & Istilah

| Istilah | Penjelasan |
|---|---|
| Badan Usaha (Company) | Entitas hukum/perusahaan, mis. PT MSI, PT MSG, CV Top |
| Divisi/Unit | Unit kerja, bisa berjenjang (parent-child), mis. HR → Personalia, Recruitment |
| Jabatan (Job Level) | Level struktural karyawan: Staff, Manager, Coordinator, Leader, BOD — **informasi saja, bukan hak akses** |
| Posisi/Job Title | Nama pekerjaan aktual: IT Support, CS, Staff Gudang, dll |
| Role | Hak akses sistem: Superadmin, Approver, Employee — independen dari jabatan |
| Approver | User (role apapun jabatannya) yang ditugaskan menyetujui pengajuan untuk divisi/company tertentu |
| Company Policy | Aturan operasional spesifik per badan usaha (toleransi telat, wajib foto, dll) |
| Approval Flow | Urutan tahapan persetujuan, dapat berbeda per badan usaha & jenis pengajuan |

## 5. Aktor / Role Pengguna

| Role | Deskripsi |
|---|---|
| **Superadmin** | Akses penuh ke seluruh sistem: kelola master data, users, roles, company policy, approval flow, semua laporan lintas badan usaha |
| **Approver** | User yang di-assign untuk menyetujui pengajuan (izin/lembur/koreksi) pada divisi/company tertentu. Bisa siapapun jabatannya |
| **Employee** | Karyawan biasa: absen, ajukan izin/cuti/lembur, lihat riwayat & status pengajuan miliknya sendiri |

> Catatan: Jabatan (Staff/Manager/dst) **tidak otomatis** memberi hak approval. Hak approval hanya berlaku jika user diberi role **Approver** oleh Superadmin melalui tabel mapping `approvers`.

## 6. Struktur Data (High-Level)

### 6.1 Master Data
- `companies` — badan usaha
- `divisions` — divisi, self-referencing (`parent_id`), terikat ke `company_id`
- `job_levels` — jabatan struktural (master global)
- `job_titles` — posisi/job title
- `employees` — data karyawan, relasi ke company, division, job_level, job_title

### 6.2 Akses & Otorisasi
- `users` — akun login
- `roles` / `permissions` — via Filament Shield (Superadmin, Approver, Employee)
- `approvers` — mapping user → division/company yang bisa ia-approve, termasuk level tahapan approval

### 6.3 Konfigurasi per Badan Usaha
- `company_policies` — toleransi telat, wajib foto absen, wajib GPS, radius geofence, kuota cuti tahunan, dll
- `approval_flows` — urutan tahap approval per company per jenis pengajuan (izin, lembur, koreksi absen)

### 6.4 Transaksi
- `attendances` — data absensi harian
- `leave_requests` — pengajuan izin/cuti
- `overtime_requests` — pengajuan lembur
- `attendance_corrections` — pengajuan koreksi absen
- `holidays` — hari libur per company (opsional per company atau global)

## 7. Functional Requirements

### 7.1 Manajemen Master Data (Superadmin)
- FR-1.1: Superadmin dapat CRUD data badan usaha
- FR-1.2: Superadmin dapat CRUD divisi, termasuk menentukan parent divisi (struktur tree tak terbatas level)
- FR-1.3: Superadmin dapat CRUD jabatan (job level) dan menentukan urutan level (untuk referensi struktural)
- FR-1.4: Superadmin dapat CRUD posisi/job title
- FR-1.5: Superadmin dapat CRUD data karyawan dan meng-assign company, divisi, jabatan, posisi

### 7.2 Manajemen Role & Approver (Superadmin)
- FR-2.1: Superadmin dapat assign role (Superadmin/Approver/Employee) ke user manapun
- FR-2.2: Superadmin dapat menentukan mapping Approver → divisi/company + urutan tahap approval
- FR-2.3: Satu user dapat menjadi approver untuk lebih dari satu divisi/company
- FR-2.4: Satu divisi dapat memiliki lebih dari satu approver (paralel atau berjenjang)
- FR-2.5: **Tidak ada fitur registrasi mandiri (self-register) untuk user.** Halaman register bawaan Filament di-disable.
- FR-2.6: Akun user (login) hanya dapat dibuat oleh Superadmin (atau role dengan hak kelola user), dilakukan bersamaan atau setelah input data karyawan
- FR-2.7: Saat membuat akun, Superadmin langsung menentukan role sistemnya (Superadmin/Approver/Employee) dan mengaitkannya ke record `employee` yang sesuai
- FR-2.8: Superadmin dapat reset password / menonaktifkan akun user (mis. saat karyawan resign)

### 7.3 Konfigurasi Kebijakan (Superadmin)
- FR-3.1: Superadmin dapat mengatur `company_policy` per badan usaha (toleransi telat, wajib foto, wajib GPS & radius, kuota cuti, dll)
- FR-3.2: Superadmin dapat mengatur `approval_flow` per badan usaha per jenis pengajuan (jumlah tahap, siapa/role apa di tiap tahap)

### 7.4 Absensi (Employee)
- FR-4.1: Karyawan dapat melakukan check-in dan check-out
- FR-4.2: Sistem menentukan status kehadiran (tepat waktu/telat) berdasarkan shift & policy company terkait
- FR-4.3: Sistem dapat mewajibkan foto dan/atau validasi GPS saat absen, sesuai `company_policy`
- FR-4.4: Karyawan dapat mengajukan koreksi absensi jika lupa/gagal absen

### 7.5 Izin/Cuti & Lembur (Employee)
- FR-5.1: Karyawan dapat mengajukan izin/cuti dengan jenis, tanggal, dan alasan
- FR-5.2: Sistem menampilkan sisa kuota cuti sesuai `company_policy`
- FR-5.3: Karyawan dapat mengajukan lembur dengan estimasi durasi
- FR-5.4: Setiap pengajuan mengikuti `approval_flow` sesuai company & jenis pengajuan

### 7.6 Approval (Approver)
- FR-6.1: Approver hanya melihat pengajuan yang menjadi tanggung jawabnya (sesuai mapping `approvers`)
- FR-6.2: Approver dapat approve/reject disertai catatan
- FR-6.3: Jika approval flow memiliki tahap lanjutan, sistem otomatis meneruskan ke approver berikutnya setelah disetujui
- FR-6.4: Approver menerima notifikasi saat ada pengajuan baru untuk ditinjau

### 7.7 Dashboard & Laporan
- FR-7.1: Dashboard ringkasan kehadiran (harian/bulanan) sesuai scope akses user
- FR-7.2: Laporan rekap absensi per badan usaha, per divisi, per karyawan
- FR-7.3: Laporan keterlambatan, penggunaan cuti, dan lembur
- FR-7.4: Export laporan ke Excel/PDF

## 8. Non-Functional Requirements

| Kategori | Requirement |
|---|---|
| Platform | Laravel (versi terbaru) + Filament (versi terbaru) |
| Autentikasi | Filament default auth (login only, **tanpa halaman register publik**) + Filament Shield untuk role/permission. Akun dibuat manual oleh Superadmin |
| Skalabilitas | Mendukung multi-company dengan data terisolasi secara logis (bukan multi-tenant database terpisah, cukup scoping via company_id) |
| Konfigurabilitas | Policy & approval flow harus dapat diubah dari UI Filament tanpa deploy ulang kode |
| Keamanan | Role-based access control, audit log untuk aksi approval |
| Auditability | Setiap perubahan status approval tercatat (siapa, kapan, aksi apa) |
| Usability | UI approval sederhana, notifikasi jelas untuk approver |

## 9. Asumsi & Batasan

- Satu user hanya terdaftar sebagai satu employee record, namun bisa memiliki lebih dari satu role sistem.
- Approval flow bersifat sequential per tahap (tahap 2 baru berjalan setelah tahap 1 disetujui), kecuali ditentukan lain saat desain teknis.
- Modul payroll tidak termasuk fase ini; sistem hanya menyediakan data mentah (jam kerja, lembur, cuti) yang bisa diekspor/diintegrasikan kemudian.

## 10. Alur Kerja Utama (Ringkasan)

1. **Setup awal** — Superadmin membuat master data (company, divisi, jabatan, posisi), menentukan policy & approval flow per company, serta assign role Approver ke user terkait.
2. **Operasional harian** — Employee absen sesuai policy company-nya.
3. **Pengajuan** — Employee mengajukan izin/cuti/lembur/koreksi → masuk ke approval flow sesuai company.
4. **Approval** — Approver yang dipetakan meninjau dan memutuskan; jika ada tahap lanjut, otomatis diteruskan.
5. **Pelaporan** — Superadmin/Approver mengakses dashboard & laporan sesuai lingkup aksesnya.

## 11. Tahapan Pengembangan yang Disarankan

1. Migration & schema database
2. Filament Resource untuk seluruh master data
3. Modul role & permission (Filament Shield) + mapping approver
4. Modul company policy & approval flow (dinamis)
5. Modul absensi (check-in/out, koreksi)
6. Modul izin/cuti & lembur + integrasi approval flow
7. Dashboard & laporan
8. Testing & UAT per badan usaha (memastikan setiap company bisa punya kebijakan berbeda tanpa saling mengganggu)

---

*Dokumen ini adalah hasil brainstorming awal dan dapat direvisi seiring diskusi teknis lebih lanjut dengan tim.*
# 📑 DOKUMENTASI PENGGUNA & REST API V1 - FILAMENT ONTIME

Dokumen ini berisi **Ringkasan Website dari Sisi Pengguna (Non-Admin)** dan **Spesifikasi Lengkap RESTful API V1** untuk integrasi aplikasi mobile (Android/iOS) maupun sistem eksternal.

---

# 📌 BAGIAN 1: Ringkasan Website Sisi Pengguna (Non-Admin Roles)

| Peran (Role) | Ringkasan Fitur & Akses Pengguna |
| :--- | :--- |
| **🎓 Intern (Peserta Magang)** | • **Presensi Mandiri**: Check-In & Check-Out (GPS Geofencing, Selfie, Verifikasi Wajah/Liveness Detection).<br>• **Foto Master Wajah**: Pendaftaran & pembaruan foto master wajah biometrik (upload/kamera).<br>• **Koreksi Absensi**: Pengajuan perbaikan jam absensi jika lupa/kendala presensi.<br>• **Laporan Absensi Diri**: Melihat rekapitulasi riwayat presensi pribadi.<br>• **Profil**: Informasi akun magang, mentor terhubung, divisi, & perusahaan. |
| **💼 Freelancer (Pekerja Lepas)** | • **Presensi Mandiri**: Check-In & Check-Out (GPS Geofencing, Selfie, Verifikasi Wajah/Liveness Detection).<br>• **Foto Master Wajah**: Pendaftaran & pembaruan foto master wajah biometrik (upload/kamera).<br>• **Koreksi Absensi**: Pengajuan perbaikan jam absensi jika lupa/kendala presensi.<br>• **Laporan Absensi Diri**: Melihat rekapitulasi riwayat presensi pribadi.<br>• **Profil**: Informasi akun freelancer, supervisor/PIC terhubung, divisi, & perusahaan. |
| **👥 Employee (Karyawan Tetap / Kontrak)** | • **Presensi Mandiri**: Check-In & Check-Out (GPS/Geofencing, Selfie, Face Recognition). Absensi di luar geofence otomatis berstatus *Pending Approval*.<br>• **Foto Master Wajah**: Pendaftaran & pembaruan foto biometrik master.<br>• **Pengajuan Cuti & Izin**: Pengajuan cuti tahunan, sakit, atau izin (auto-check sisa kuota, unggah bukti lampiran).<br>• **Pengajuan Lembur**: Pengajuan jam lembur kerja (kalkulasi durasi & alasan lembur).<br>• **Koreksi Absensi**: Pengajuan perbaikan data jam check-in / check-out.<br>• **Pengajuan Resign**: Pengajuan pengunduran diri resmi (tanggal resign, *last working day*, alasan, & catatan serah terima).<br>• **Kalender Cuti Tim**: Tampilan kalender bersama jadwal cuti rekan kerja per bulan & perusahaan.<br>• **Laporan Absensi Diri**: Laporan riwayat presensi & keterlambatan pribadi. |
| **🛡️ Approver / BOD (Atasan / Manager / Direksi)** | • **Seluruh Fitur Karyawan**: (Presensi, Cuti, Lembur, Koreksi, Resign, Kalender Cuti, Laporan Absensi).<br>• **Inbox Persetujuan Masuk (Approval Saya)**: Pusat pemrosesan & pengambil keputusan (*Approve* / *Reject*) untuk pengajuan bawahan:<br>&nbsp;&nbsp;- Persetujuan Cuti & Izin<br>&nbsp;&nbsp;- Persetujuan Jam Lembur<br>&nbsp;&nbsp;- Persetujuan Koreksi Absensi<br>&nbsp;&nbsp;- Persetujuan Absensi Luar Geofence (dilengkapi kartu peta komparasi GPS Karyawan vs Kantor)<br>&nbsp;&nbsp;- Persetujuan Resign (menyetujui tahap akhir otomatis menonaktifkan akun karyawan). |

---

# 🌐 BAGIAN 2: Spesifikasi REST API V1

### Information Dasar API
* **Base URL**: `http://your-domain.com/api/v1`
* **Autentikasi**: Laravel Sanctum (`Personal Access Token`)
* **Headers Wajib**:
  ```http
  Authorization: Bearer <your_access_token>
  Accept: application/json
  ```

---

## 🔑 1. Autentikasi (`/auth`)

### 1.1 Login Pengguna
* **Method**: `POST`
* **URL**: `/api/v1/auth/login`
* **Public Route** (Tanpa Auth Header)
* **Request Body** (`application/json`):
  ```json
  {
    "email": "user@example.com",
    "password": "password123"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "message": "Login successful",
    "token_type": "Bearer",
    "token": "1|abcdef123456...",
    "user": {
      "id": 5,
      "name": "Budi Santoso",
      "email": "user@example.com",
      "roles": ["Employee"],
      "permissions": ["View:AbsenHariIni", "Create:LeaveRequest", "ViewAny:OvertimeRequest"]
    }
  }
  ```
* **Response 401 Unauthorized**:
  ```json
  {
    "message": "Credentials do not match our records."
  }
  ```

### 1.2 Informasi Profil Pengguna Saat Ini (`/me`)
* **Method**: `GET`
* **URL**: `/api/v1/auth/me`
* **Response 200 OK**:
  ```json
  {
    "user": {
      "id": 5,
      "name": "Budi Santoso",
      "email": "user@example.com",
      "roles": ["Employee"],
      "permissions": ["View:AbsenHariIni", "Create:LeaveRequest"]
    }
  }
  ```

### 1.3 Logout Akun
* **Method**: `POST`
* **URL**: `/api/v1/auth/logout`
* **Response 200 OK**:
  ```json
  {
    "message": "Logged out successfully"
  }
  ```

---

## ⏱️ 2. Presensi Absensi (`/attendance`)

### 2.1 Check-In Absensi
* **Method**: `POST`
* **URL**: `/api/v1/attendance/check-in`
* **Permission Needed**: `can:View:AbsenHariIni`
* **Request Body** (`multipart/form-data`):
  | Field | Type | Required | Description |
  | :--- | :--- | :--- | :--- |
  | `latitude` | `numeric` | Kondisional | Koordinat latitude posisi perangkat |
  | `longitude` | `numeric` | Kondisional | Koordinat longitude posisi perangkat |
  | `photo` | `file (image)` | Kondisional | File foto selfie biometrik presensi |

* **Response 200 OK**:
  ```json
  {
    "message": "Check in berhasil",
    "attendance": {
      "id": 102,
      "date": "2026-08-03",
      "check_in": "08:02:15",
      "check_out": null,
      "status": "on_time",
      "is_out_of_bounds": false,
      "is_face_verified": true,
      "face_match_score": 92.5
    }
  }
  ```

### 2.2 Check-Out Absensi
* **Method**: `POST`
* **URL**: `/api/v1/attendance/check-out`
* **Permission Needed**: `can:View:AbsenHariIni`
* **Request Body** (`multipart/form-data`): `latitude`, `longitude`, `photo` (opsional sesuai kebijakan perusahaan).
* **Response 200 OK**:
  ```json
  {
    "message": "Check out berhasil",
    "attendance": {
      "id": 102,
      "check_in": "08:02:15",
      "check_out": "17:05:00",
      "status": "on_time"
    }
  }
  ```

---

## 🏖️ 3. Pengajuan Cuti & Izin (`/leave-requests`)

### 3.1 Daftar Pengajuan Cuti Mandiri
* **Method**: `GET`
* **URL**: `/api/v1/leave-requests`
* **Permission Needed**: `can:ViewAny:LeaveRequest`
* **Response 200 OK**:
  ```json
  {
    "data": [
      {
        "id": 12,
        "leave_type": "annual",
        "start_date": "2026-08-10",
        "end_date": "2026-08-12",
        "days_count": 3,
        "reason": "Acara keluarga",
        "status": "pending",
        "attachment_url": null
      }
    ],
    "pagination": { "current_page": 1, "last_page": 1, "total": 1 }
  }
  ```

### 3.2 Buat Pengajuan Cuti Baru
* **Method**: `POST`
* **URL**: `/api/v1/leave-requests`
* **Permission Needed**: `can:Create:LeaveRequest`
* **Request Body** (`multipart/form-data`):
  | Field | Type | Required | Description |
  | :--- | :--- | :--- | :--- |
  | `leave_type` | `string` | Ya | Jenis cuti (`annual`, `sick`, `permission`, `maternity`, dll.) |
  | `start_date` | `date` | Ya | Tanggal mulai (`YYYY-MM-DD`) |
  | `end_date` | `date` | Ya | Tanggal selesai (`YYYY-MM-DD`) |
  | `reason` | `string` | Ya | Alasan pengajuan |
  | `attachment` | `file` | Opsional | Dokumen pendukung (pdf, jpg, png) |

### 3.3 Perbarui Pengajuan Cuti (Hanya Status Pending)
* **Method**: `PUT`
* **URL**: `/api/v1/leave-requests/{id}`
* **Permission Needed**: `can:Update:LeaveRequest`

---

## 🌙 4. Pengajuan Lembur (`/overtime-requests`)

### 4.1 Daftar Pengajuan Lembur
* **Method**: `GET` | **URL**: `/api/v1/overtime-requests` | **Permission**: `can:ViewAny:OvertimeRequest`

### 4.2 Buat Pengajuan Lembur Baru
* **Method**: `POST`
* **URL**: `/api/v1/overtime-requests`
* **Permission Needed**: `can:Create:OvertimeRequest`
* **Request Body** (`application/json`):
  ```json
  {
    "date": "2026-08-05",
    "start_time": "17:00",
    "end_time": "20:00",
    "reason": "Project deployment ke server staging"
  }
  ```

### 4.3 Perbarui Pengajuan Lembur
* **Method**: `PUT` | **URL**: `/api/v1/overtime-requests/{id}` | **Permission**: `can:Update:OvertimeRequest`

---

## ✏️ 5. Pengajuan Koreksi Absensi (`/attendance-corrections`)

### 5.1 Daftar Koreksi Absensi Mandiri
* **Method**: `GET` | **URL**: `/api/v1/attendance-corrections` | **Permission**: `can:ViewAny:AttendanceCorrection`

### 5.2 Buat Pengajuan Koreksi Absensi
* **Method**: `POST`
* **URL**: `/api/v1/attendance-corrections`
* **Permission Needed**: `can:Create:AttendanceCorrection`
* **Request Body** (`multipart/form-data` / `json`):
  ```json
  {
    "date": "2026-08-01",
    "corrected_check_in": "08:00:00",
    "corrected_check_out": "17:00:00",
    "reason": "Kendala jaringan pada perangkat saat melalukan check-in",
    "attachment": null
  }
  ```

---

## 🚪 6. Pengajuan Resign / Pengunduran Diri (`/resignations`)

### 6.1 Daftar Pengajuan Resign
* **Method**: `GET` | **URL**: `/api/v1/resignations` | **Permission**: `can:ViewAny:Resignation`

### 6.2 Buat Pengajuan Resign Baru
* **Method**: `POST`
* **URL**: `/api/v1/resignations`
* **Permission Needed**: `can:Create:Resignation`
* **Request Body** (`application/json`):
  ```json
  {
    "resignation_date": "2026-08-15",
    "last_working_day": "2026-09-15",
    "reason": "Mendapat penawaran karir baru",
    "handover_notes": "Dokumentasi kode dan kredensial server sudah diserahterimakan."
  }
  ```

### 6.3 Detail Pengajuan Resign
* **Method**: `GET` | **URL**: `/api/v1/resignations/{id}` | **Permission**: `can:View:Resignation`

---

## 🛡️ 7. Inbox Persetujuan Atasan (`/approvals`)

### 7.1 Daftar Pengajuan Menunggu Persetujuan (*Pending*)
* **Method**: `GET`
* **URL**: `/api/v1/approvals/pending`
* **Permission Needed**: `can:View:ApprovalSaya`
* **Response 200 OK**:
  ```json
  {
    "leave_requests": [...],
    "overtime_requests": [...],
    "attendance_corrections": [...],
    "geofence_attendances": [...]
  }
  ```

### 7.2 Proses Persetujuan / Penolakan
* **Method**: `PUT`
* **URL**: `/api/v1/approvals/{type}/{id}/process`
* **Path Parameters**:
  * `type`: Jenis pengajuan (`leave`, `overtime`, `correction`, `geofence`, `resignation`).
  * `id`: ID data pengajuan.
* **Permission Needed**: `can:View:ApprovalSaya`
* **Request Body** (`application/json`):
  ```json
  {
    "action": "approved",
    "rejection_note": null
  }
  ```
  *(Untuk menolak, set `"action": "rejected"` dan sertakan pesan di `"rejection_note"`)*.

* **Response 200 OK**:
  ```json
  {
    "message": "Pengajuan berhasil disetujui",
    "status": "approved",
    "current_step": 2
  }
  ```

---

## 📊 8. Master Data, Kalender & Laporan

### 8.1 Kalender Cuti Tim
* **Method**: `GET`
* **URL**: `/api/v1/kalender-cuti?month=8&year=2026&company_id=1`
* **Permission Needed**: `can:View:KalenderCuti`

### 8.2 Laporan Absensi Diri / Tim
* **Method**: `GET`
* **URL**: `/api/v1/laporan-absensi?start_date=2026-08-01&end_date=2026-08-31`
* **Permission Needed**: `can:View:LaporanAbsensi`

### 8.3 Data Master Badan Usaha / Perusahaan
* **Daftar**: `GET /api/v1/master/companies` | **Permission**: `can:ViewAny:Company`
* **Detail**: `GET /api/v1/master/companies/{id}` | **Permission**: `can:ViewAny:Company`

### 8.4 Lokasi Perusahaan & Geofence
* **Daftar**: `GET /api/v1/master/company-locations?company_id=1` | **Permission**: `can:ViewAny:CompanyLocation`
* **Detail**: `GET /api/v1/master/company-locations/{id}` | **Permission**: `can:ViewAny:CompanyLocation`

### 8.5 Kebijakan Perusahaan (Jam Kerja & Toleransi)
* **Daftar**: `GET /api/v1/master/company-policies?company_id=1` | **Permission**: `can:ViewAny:CompanyPolicy`
* **Detail**: `GET /api/v1/master/company-policies/{id}` | **Permission**: `can:ViewAny:CompanyPolicy`

### 8.6 Data Master Divisi
* **Daftar**: `GET /api/v1/master/divisions?company_id=1` | **Permission**: `can:ViewAny:Division`
* **Detail**: `GET /api/v1/master/divisions/{id}` | **Permission**: `can:ViewAny:Division`

### 8.7 Data Master Posisi / Job Title
* **Daftar**: `GET /api/v1/master/job-titles?division_id=2` | **Permission**: `can:ViewAny:JobTitle`
* **Detail**: `GET /api/v1/master/job-titles/{id}` | **Permission**: `can:ViewAny:JobTitle`

### 8.8 Data Master Level Jabatan / Job Level
* **Daftar**: `GET /api/v1/master/job-levels` | **Permission**: `can:ViewAny:JobLevel`
* **Detail**: `GET /api/v1/master/job-levels/{id}` | **Permission**: `can:ViewAny:JobLevel`

### 8.9 Data Karyawan (Employees)
* **Daftar**: `GET /api/v1/master/employees?company_id=1&status=permanent` | **Permission**: `can:ViewAny:Employee`
* **Detail**: `GET /api/v1/master/employees/{id}` | **Permission**: `can:ViewAny:Employee`

### 8.10 Data Peserta Magang (Interns)
* **Daftar**: `GET /api/v1/master/interns?company_id=1` | **Permission**: `can:ViewAny:Intern`
* **Detail**: `GET /api/v1/master/interns/{id}` | **Permission**: `can:ViewAny:Intern`

### 8.11 Data Pekerja Lepas (Freelancers)
* **Daftar**: `GET /api/v1/master/freelancers?company_id=1` | **Permission**: `can:ViewAny:Freelancer`
* **Detail**: `GET /api/v1/master/freelancers/{id}` | **Permission**: `can:ViewAny:Freelancer`

### 8.12 Data Hari Libur (Holidays)
* **Daftar**: `GET /api/v1/master/holidays?year=2026` | **Permission**: `can:ViewAny:Holiday`
* **Detail**: `GET /api/v1/master/holidays/{id}` | **Permission**: `can:ViewAny:Holiday`

### 8.13 Data Alur Persetujuan (Approval Flows)
* **Daftar**: `GET /api/v1/master/approval-flows?company_id=1&request_type=leave` | **Permission**: `can:ViewAny:ApprovalFlow`
* **Detail**: `GET /api/v1/master/approval-flows/{id}` | **Permission**: `can:ViewAny:ApprovalFlow`

### 8.14 Data Penyetuju (Approvers)
* **Daftar**: `GET /api/v1/master/approvers?company_id=1` | **Permission**: `can:ViewAny:Approver`
* **Detail**: `GET /api/v1/master/approvers/{id}` | **Permission**: `can:ViewAny:Approver`

### 8.15 Data Pengguna Sistem (Users)
* **Daftar**: `GET /api/v1/master/users?email=karyawan` | **Permission**: `can:ViewAny:User`
* **Detail**: `GET /api/v1/master/users/{id}` | **Permission**: `can:ViewAny:User`

---

## 📜 9. Riwayat Presensi & Detail Pengajuan

### 9.1 Riwayat Presensi Lengkap (`/attendances`)
* **Daftar**: `GET /api/v1/attendances?start_date=2026-08-01&end_date=2026-08-31` | **Permission**: `can:ViewAny:Attendance` (atau riwayat diri mandiri)
* **Detail**: `GET /api/v1/attendances/{id}` | **Permission**: `can:ViewAny:Attendance`

### 9.2 Detail Single Record Pengajuan
* **Detail Cuti**: `GET /api/v1/leave-requests/{id}` | **Permission**: `can:ViewAny:LeaveRequest`
* **Detail Lembur**: `GET /api/v1/overtime-requests/{id}` | **Permission**: `can:ViewAny:OvertimeRequest`
* **Detail Koreksi Absensi**: `GET /api/v1/attendance-corrections/{id}` | **Permission**: `can:ViewAny:AttendanceCorrection`


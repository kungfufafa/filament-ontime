# Dokumentasi RESTful API V1 - OnTime HR & Attendance System

Dokumentasi resmi Endpoint API V1 OnTime untuk keperluan integrasi Mobile App / Third-Party Application.

---

## 1. Authentication & Base URL

- **Base URL**: `http://127.0.0.1:8000/api/v1`
- **Authentication**: Laravel Sanctum Bearer Token
- **Headers Wajib**:
  - `Accept: application/json`
  - `Authorization: Bearer <TOKEN_SANCTUM>` (untuk endpoint terproteksi)

---

## 2. Ringkasan Endpoint POST & PUT

| Method | Endpoint | Fungsi | Authorization |
| :--- | :--- | :--- | :--- |
| **POST** | `/auth/login` | Login user & dapatkan Sanctum Bearer Token | Public |
| **POST** | `/auth/logout` | Logout user & hapus token | Bearer Token |
| **POST** | `/attendance/check-in` | Presensi Masuk (Check-In) + Foto/GPS | Bearer Token |
| **POST** | `/attendance/check-out` | Presensi Keluar (Check-Out) + Foto/GPS | Bearer Token |
| **POST** | `/leave-requests` | Buat Pengajuan Cuti / Izin Baru | Bearer Token |
| **PUT** | `/leave-requests/{id}` | Update Pengajuan Cuti (Status Pending) | Bearer Token |
| **POST** | `/overtime-requests` | Buat Pengajuan Lembur Baru | Bearer Token |
| **PUT** | `/overtime-requests/{id}` | Update Pengajuan Lembur (Status Pending) | Bearer Token |
| **POST** | `/attendance-corrections` | Buat Pengajuan Koreksi Absensi Baru | Bearer Token |
| **PUT** | `/attendance-corrections/{id}` | Update Pengajuan Koreksi Absensi | Bearer Token |
| **PUT** | `/approvals/{type}/{id}/process` | Proses Approval (Setujui / Tolak) | Bearer Token (Approver/BOD/Superadmin) |

---

## 3. Detail Specification & Payload Endpoint

### 3.1 Authentication

#### `POST /auth/login`
Memverifikasi kredensial pengguna dan mengembalikan Token Bearer.

- **Request Body (JSON / Form Data)**:
```json
{
  "email": "ranimaharani@ontime.com",
  "password": "ranimaharani"
}
```
- **Response Status**: `200 OK`
```json
{
  "message": "Login successful",
  "token_type": "Bearer",
  "token": "1|sanctum_token_string_here...",
  "user": {
    "id": 4,
    "name": "RANI MAHARANI",
    "email": "ranimaharani@ontime.com",
    "roles": ["Employee"],
    "employee": {
      "id": 4,
      "nip": "2025.06.11.01",
      "full_name": "RANI MAHARANI",
      "company": "PT OnTime Indonesia",
      "division": "E-Commerce ONLINE",
      "job_title": "ADMIN CS E-COMMERCE",
      "job_level": "Staff"
    }
  }
}
```

---

### 3.2 Presensi Harian (Check-In & Check-Out)

#### `POST /attendance/check-in`
Melakukan Presensi Masuk harian (Mendukung upload Foto Selfie & koordinat GPS).

- **Request Body (`multipart/form-data`)**:
  - `latitude` *(numeric, optional/required by policy)*: Contoh `-6.1753924`
  - `longitude` *(numeric, optional/required by policy)*: Contoh `106.8271528`
  - `photo` *(file image: jpeg, png, jpg, webp, max 5MB, optional/required by policy)*

- **Response Status**: `200 OK`
```json
{
  "message": "Check in berhasil",
  "attendance": {
    "id": 10,
    "employee_id": 4,
    "date": "2026-07-23",
    "check_in": "08:05:00",
    "check_out": null,
    "check_in_latitude": -6.1753924,
    "check_in_longitude": 106.8271528,
    "check_in_photo": "http://127.0.0.1:8000/storage/attendance/photos/xyz.jpg",
    "status": "present",
    "is_corrected": false,
    "late_minutes": 0
  }
}
```

#### `POST /attendance/check-out`
Melakukan Presensi Keluar harian.

- **Request Body (`multipart/form-data`)**:
  - `latitude` *(numeric)*
  - `longitude` *(numeric)*
  - `photo` *(file image)*

- **Response Status**: `200 OK`
```json
{
  "message": "Check out berhasil",
  "attendance": {
    "id": 10,
    "check_out": "17:02:15",
    "status": "present"
  }
}
```

---

### 3.3 Pengajuan Cuti / Izin (POST & PUT)

#### `POST /leave-requests`
Membuat pengajuan cuti baru dan otomatis mendaftarkan alur approval 3-stage.

- **Request Body (`multipart/form-data` / JSON)**:
```json
{
  "leave_type": "annual", // annual, sick, permitted, maternity, unpaid
  "start_date": "2026-08-01",
  "end_date": "2026-08-03",
  "reason": "Acara keluarga di kampung halaman",
  "attachment": null // file upload (optional)
}
```
- **Response Status**: `201 Created`
```json
{
  "message": "Pengajuan cuti berhasil dibuat",
  "data": {
    "id": 15,
    "employee_id": 4,
    "leave_type": "annual",
    "start_date": "2026-08-01",
    "end_date": "2026-08-03",
    "reason": "Acara keluarga di kampung halaman",
    "status": "pending",
    "current_step": 1,
    "total_steps": 3
  }
}
```

#### `PUT /leave-requests/{id}`
Memperbarui data pengajuan cuti yang masih berstatus `pending`.

- **Request Body (JSON / Form Data)**:
```json
{
  "reason": "Acara pernikahan keluarga besar (Revisi)"
}
```
- **Response Status**: `200 OK`
```json
{
  "message": "Pengajuan cuti berhasil diperbarui",
  "data": {
    "id": 15,
    "reason": "Acara pernikahan keluarga besar (Revisi)",
    "status": "pending"
  }
}
```

---

### 3.4 Pengajuan Lembur (POST & PUT)

#### `POST /overtime-requests`
Membuat pengajuan lembur baru.

- **Request Body (JSON)**:
```json
{
  "date": "2026-07-25",
  "start_time": "17:00",
  "end_time": "21:00",
  "reason": "Penyelesaian sprint fitur e-commerce"
}
```
- **Response Status**: `201 Created`

#### `PUT /overtime-requests/{id}`
Memperbarui data pengajuan lembur yang berstatus `pending`.

- **Request Body (JSON)**:
```json
{
  "end_time": "22:00",
  "reason": "Penyelesaian sprint fitur e-commerce & testing release"
}
```
- **Response Status**: `200 OK`

---

### 3.5 Pengajuan Koreksi Absensi (POST & PUT)

#### `POST /attendance-corrections`
Membuat pengajuan koreksi jam absensi.

- **Request Body (`multipart/form-data` / JSON)**:
```json
{
  "date": "2026-07-22",
  "corrected_check_in": "08:00",
  "corrected_check_out": "17:00",
  "reason": "Mesin presensi error/mati listrik",
  "attachment": null
}
```
- **Response Status**: `201 Created`

#### `PUT /attendance-corrections/{id}`
Memperbarui pengajuan koreksi absensi yang berstatus `pending`.

- **Request Body (JSON)**:
```json
{
  "reason": "Mesin presensi error dan mati listrik jaringan (Revisi)"
}
```
- **Response Status**: `200 OK`

---

### 3.6 Processing Approvals (PUT)

#### `PUT /approvals/{type}/{id}/process`
Memproses tahap persetujuan (Approve / Reject) untuk jenis pengajuan (`leave`, `overtime`, `correction`).

- **Path Parameters**:
  - `type`: `leave` / `overtime` / `correction`
  - `id`: ID Pengajuan
- **Request Body (JSON)**:
```json
{
  "action": "approved", // "approved" atau "rejected"
  "rejection_note": null // Wajib diisi jika action="rejected"
}
```
- **Response Status**: `200 OK`
```json
{
  "message": "Pengajuan berhasil disetujui",
  "status": "pending", // Berubah menjadi "approved" pada tahap 3 (Superadmin)
  "current_step": 2
}
```

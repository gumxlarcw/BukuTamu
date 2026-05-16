# API Reference — BukuTamu

> Referensi lengkap endpoint REST yang disediakan oleh `tamdes-web` (CodeIgniter 3 backend). Semua endpoint berada di bawah prefix `/api/`. Untuk arsitektur dan alur data, lihat [ARCHITECTURE.md](./ARCHITECTURE.md).

## 1. Konvensi

### 1.1 Base URL

| Environment | URL |
|---|---|
| Produksi | `https://bukutamu.bpsmalut.com/api` |
| Development (Vite proxy) | `http://localhost:5173/api` → diteruskan ke `http://localhost:8080/api` |

### 1.2 Format Response

Semua response berbentuk JSON dengan struktur konsisten:

```json
{
  "success": true,
  "data": { ... } | [...] | null,
  "message": "OK",
  "pagination": { "page": 1, "limit": 10, "total": 42, "totalPages": 5 }
}
```

| Field | Wajib | Keterangan |
|---|---|---|
| `success` | ✓ | `true` jika 2xx, `false` jika error |
| `data` | ✓ | payload — bisa object, array, atau null |
| `message` | ✓ | pesan untuk developer/user (Bahasa Indonesia) |
| `pagination` | opsional | hanya pada endpoint list dengan paging |

### 1.3 Autentikasi

- **Mekanisme:** JWT HS256 dalam cookie `jwt_token` (httpOnly, samesite=Strict, secure di HTTPS).
- **Expiry:** 4 jam.
- **Cara dapat token:** `POST /api/auth/login` → server set cookie otomatis.
- **Cara kirim token:** Browser kirim cookie otomatis (axios `withCredentials: true`).
- **Manual:** Bisa juga lewat header `Cookie: jwt_token=<token>`.

Endpoint dengan tanda **🔒** memerlukan autentikasi. Tanpa cookie/dengan token invalid → `401 Unauthorized`.

Endpoint dengan tanda **🔒🛡️** memerlukan role minimum tertentu. Role rendah → `403 Forbidden`.

Endpoint dengan tanda **🌐** publik (tanpa auth) — biasanya untuk kiosk fisik.

### 1.4 Role Hierarchy

```
operator (1) ≈ resepsionis (1) ≈ petugas_pst (1)  <  admin (2)  <  superadmin (3)
```

Tier 1 punya level numerik sama tapi **scope berbeda** (lihat `Api_base::require_layanan_role()`):
- `petugas_pst` → 4 layanan PST (Perpustakaan, Konsultasi Statistik, Rekomendasi, Penjualan)
- `resepsionis` → Lainnya, Keperluan Pimpinan
- `operator` (legacy) → bypass full access (backward compat)

`require_role('admin')` blokir semua tier 1, mengizinkan admin & superadmin.

### 1.5 Layanan-Based Authorization

Endpoint **finalisasi** (transisi ke `selesai` atau `menunggu_evaluasi`) memanggil `require_layanan_role()`:
- Cek role user vs layanan visit
- Mismatch → 403 dengan pesan: _"Layanan 'X' hanya bisa diselesaikan oleh Petugas PST"_ (atau Resepsionis)

Berlaku di:
- `PUT /api/visits/:id/status` (status target: selesai / menunggu_evaluasi)
- `PUT /api/consultations/:id` (status target: selesai / menunggu_evaluasi)
- `POST /api/consultations/:id/data` (selalu, karena auto-transition ke finalisasi)

Tidak berlaku (semua role boleh):
- Memanggil antrian (`POST /api/consultations/:id/call`)
- Transisi non-final (`dipanggil`, `diproses`)

### 1.6 Error Codes

| HTTP | Arti | Contoh |
|---|---|---|
| `200` | OK | Sukses GET/PUT |
| `201` | Created | Sukses POST yang membuat resource baru |
| `400` | Bad Request | Field wajib kosong, format salah |
| `401` | Unauthorized | Tidak ada token / token invalid / expired |
| `403` | Forbidden | Role tidak mencukupi |
| `404` | Not Found | Resource tidak ditemukan |
| `405` | Method Not Allowed | HTTP method salah |
| `429` | Too Many Requests | Rate limit login terlampaui |
| `500` | Server Error | Bug atau DB error |
| `502` | Bad Gateway | Layanan eksternal (printer/dashboard) tidak tersedia |

### 1.7 CORS

Origin yang diizinkan (dari `Api_base::__construct()`):
- `http://localhost:5173` (dev Vite)
- Nilai dari `FRONTEND_URL` di `.env` (produksi)

Permintaan dari origin lain akan kehilangan header CORS, browser memblokir.

---

## 2. 🔐 Auth — `/api/auth/*`

### `GET /api/auth/check` 🔒

Cek apakah token valid + ambil info user terkini dari DB.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "username": "petugas01",
    "nama": "Budi Santosa",
    "role": "operator"
  },
  "message": "Authenticated"
}
```

### `POST /api/auth/login` 🌐

Login dan terima cookie JWT.

**Request body:**
```json
{ "username": "admin", "password": "rahasia123" }
```

**Response 200 (set cookie `jwt_token`):**
```json
{
  "success": true,
  "data": { "id": 1, "username": "admin", "nama": "Administrator", "role": "superadmin" },
  "message": "Login berhasil"
}
```

**Error 401:** `Username atau password salah` (kalau sisa percobaan ≤ 2, ditambah `". Sisa N percobaan sebelum akun dikunci."`).
**Error 429:** `Terlalu banyak percobaan login. Coba lagi dalam 15 menit.` (setelah 5x gagal dari IP yang sama dalam 15 menit).

**Catatan:**
- Mencoba `admin_users` table dulu, fallback ke `.env` (`ADMIN_USERNAME` + `ADMIN_PASSWORD_HASH`).
- Setiap percobaan dicatat di `tamdes_login_attempts` (untuk rate limiting).
- Sukses → audit log `action=login`.

### `POST /api/auth/logout` 🔒

Hapus cookie JWT + log audit.

**Response 200:**
```json
{ "success": true, "data": null, "message": "Logout berhasil" }
```

---

## 3. 👤 Guests — `/api/guests/*`

### `GET /api/guests` 🔒

Daftar tamu (pagination + search).

**Query params:**
| Param | Default | Keterangan |
|---|---|---|
| `search` | — | LIKE on `nama`, `email`, `nama_instansi` |
| `page` | 1 | |
| `limit` | 10 | |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id_user": 8200042,
      "tgldatang": "2026-04-30",
      "nama": "Siti Aminah",
      "email": "siti@example.com",
      "notel": "08123456789",
      "jeniskelamin": "P",
      "umur": 28,
      "pendidikan": "S1",
      "pekerjaan": "PNS",
      "kategori_instansi": "Pemerintah",
      "nama_instansi": "Dinas Pertanian Halmahera",
      "pemanfaatan": "Penelitian",
      "registered_via": "kiosk"
    }
  ],
  "message": "OK",
  "pagination": { "page": 1, "limit": 10, "total": 152, "totalPages": 16 }
}
```

> ⚠️ Field `foto` (longblob) **tidak** dikembalikan — pakai `GET /api/guests/:id/photo`.

### `POST /api/guests` 🔒

Tambah tamu baru (jalur admin manual entry, bukan kiosk).

**Request body:** lihat field di response GET. Otomatis: `id_user = MAX(id_user)+1` (mulai 8200001), `tgldatang = today`, `registered_via = "admin:<username>"`.

**Response 201:** data tamu yang baru dibuat.

### `GET /api/guests/:id` 🔒

Detail satu tamu.

**Response 200:** object tamu (tanpa `foto`).
**Error 404:** `Tamu tidak ditemukan`

### `PUT /api/guests/:id` 🔒

Update field tamu. Hanya field whitelist yang di-update (cek `Guests::detail()` source).

**Request body (semua opsional):** `nama, email, notel, jeniskelamin, umur, disabilitas, jenis_disabilitas, pendidikan, pekerjaan, pekerjaan_lainnya, kategori_instansi, kategori_lainnya, nama_instansi, pemanfaatan, pemanfaatan_lainnya, pengaduan`

**Response 200:** object tamu yang ter-update.
**Audit:** otomatis menulis ke `tamdes_audit_log` dengan diff `from→to` per field yang berubah.

### `DELETE /api/guests/:id` 🔒🛡️ (admin+)

Hapus tamu. **Memerlukan role admin atau superadmin**.

**Audit:** action=`delete`, target_type=`guest`, target_id=`:id`.

### `GET /api/guests/:id/visits` 🔒

Riwayat kunjungan satu tamu.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id_kunjungan": 1234,
      "jenis_layanan": "Konsultasi Statistik",
      "date_visit": "2026-04-30 09:15:22",
      "status": "selesai",
      "nomor_antrian": "K005",
      "rating_pengunjung": 9
    }
  ],
  "message": "OK"
}
```

### `GET /api/guests/:id/photo` 🌐

Serve foto tamu sebagai image (Content-Type: `image/jpeg`, cache 1 jam).

**Response 200:** binary image data.
**Error 404:** tidak ada foto / tamu.

---

## 4. 📅 Visits — `/api/visits/*`

### `GET /api/visits` 🔒

Daftar kunjungan (pagination + filter).

**Query params:**
| Param | Keterangan |
|---|---|
| `q` | LIKE on `nama`, `nama_instansi`, `jenis_layanan`, `status` |
| `layanan` | LIKE on `jenis_layanan` |
| `status` | exact match (`antri`, `dipanggil`, `diproses`, `menunggu_evaluasi`, `selesai`) |
| `tahun` | filter `YEAR(date_visit)` |
| `bulan` | filter `MONTH(date_visit)` (1-12) |
| `page`, `limit` | default 1, 10 |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id_kunjungan": 1234,
      "id_user": 8200042,
      "jenis_layanan": "[\"Konsultasi Statistik\"]",
      "sarana": "[\"Tatap Muka\"]",
      "date_visit": "2026-04-30 09:15:22",
      "selesai_timestamp": "2026-04-30 09:48:11",
      "durasi_detik": 1969,
      "nomor_antrian": "K005",
      "status": "selesai",
      "rating_pengunjung": 9,
      "created_by": "kiosk",
      "nama": "Siti Aminah",
      "nama_instansi": "Dinas Pertanian Halmahera"
    }
  ],
  "pagination": { "page": 1, "limit": 10, "total": 285, "totalPages": 29 }
}
```

> ⚠️ `jenis_layanan` dan `sarana` disimpan sebagai JSON string. Frontend harus `JSON.parse()`.

### `POST /api/visits` 🔒

Buat kunjungan baru (admin manual entry).

**Request body:**
```json
{
  "id_user": 8200042,
  "jenis_layanan": ["Konsultasi Statistik"],
  "layanan_lainnya": null,
  "sarana": ["Tatap Muka"],
  "sarana_lainnya": null
}
```

**Response 201:** object visit baru, dengan `nomor_antrian` auto-generated.

### `GET /api/visits/:id` 🔒

Detail kunjungan + relasi.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "visit": { ... visit + biodata tamu (join tamdes_buku) },
    "consultation": [ ... rows konsultasi_pengunjung ... ],
    "evaluation":   [ ... rows tamdes_evaluasi_detail ... ]
  }
}
```

### `PUT /api/visits/:id/status` 🔒

Update status kunjungan.

**Request body:**
```json
{ "status": "selesai" }
```

Status valid: `antri`, `dipanggil`, `diproses`, `menunggu_evaluasi`, `selesai`.

Jika `status='selesai'` → otomatis isi `selesai_timestamp` dan hitung `durasi_detik = selesai - date_visit`.

**Audit:** action=`update_status`, detail `{from, to}`.

### `PUT /api/visits/:id/service` 🔒

Update jenis layanan & sarana.

**Request body:**
```json
{
  "jenis_layanan": ["Perpustakaan", "Konsultasi Statistik"],
  "layanan_lainnya": null,
  "sarana": ["Tatap Muka"],
  "sarana_lainnya": null
}
```

### `PUT /api/visits/:id/summary` 🔒

Simpan ringkasan/hasil konsultasi (1 baris di `konsultasi_pengunjung`).

**Request body:**
```json
{ "ringkasan": "Tamu meminta data IPM 2020-2024..." }
```
(field bisa juga bernama `hasil_konsultasi` — alias)

---

## 5. 💬 Consultations — `/api/consultations/*`

### `GET /api/consultations` 🔒

Antrian konsultasi **hari ini** (auto-filter `DATE(date_visit) = today`). Hanya layanan: `Perpustakaan`, `Konsultasi Statistik`, `Rekomendasi Kegiatan Statistik`, `Penjualan Produk Statistik`.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id_kunjungan": 1234,
      "id_user": 8200042,
      "jenis_layanan": "[\"Konsultasi Statistik\"]",
      "nomor_antrian": "K005",
      "status": "antri",
      "date_visit": "2026-04-30 09:15:22",
      "nama": "Siti Aminah",
      "nama_instansi": "Dinas Pertanian Halmahera",
      ...
    }
  ]
}
```

### `PUT /api/consultations/:id` 🔒

Update status konsultasi (alias dari `PUT /api/visits/:id/status`, untuk kemudahan FE).

### `POST /api/consultations/:id/call` 🔒

**Panggil tamu** — kirim nomor antrian ke layar TV (dashboard-pst.bpsmalut.com).

**Response 200:**
```json
{ "success": true, "message": "Antrian berhasil dipanggil", "nomor": "K005" }
```

**Error 502:** `cURL error: ...` jika dashboard tidak bisa dihubungi.

### `POST /api/consultations/:id/test-sound` 🔒

Test panggilan dengan nomor `"TES"` (untuk verifikasi audio TTS layar antrian berfungsi).

### `GET /api/consultations/:id/data` 🔒

Ambil rincian data konsultasi (`konsultasi_pengunjung`).

### `POST /api/consultations/:id/data` 🔒

Simpan rincian data konsultasi (replace strategy: hapus row lama, insert baru).

**Request body:**
```json
{
  "hasil_konsultasi": "Diberikan data IPM Halmahera 2020-2024 via email.",
  "kebutuhan_data": [
    {
      "rincian_data": "IPM Halmahera Utara",
      "wilayah_data": "Kabupaten Halmahera Utara",
      "tahun_awal": 2020,
      "tahun_akhir": 2024,
      "level_data": "Kabupaten",
      "periode_data": "Tahunan",
      "status_data": "Tersedia",
      "jenis_publikasi": "Berita Resmi Statistik",
      "judul_publikasi": "Statistik Daerah Halmahera Utara 2024",
      "tahun_publikasi": 2024,
      "digunakan_nasional": 0,
      "kualitas": "Baik"
    }
  ]
}
```

---

## 6. ⭐ Evaluations — `/api/evaluations/*`

### `GET /api/evaluations/pending` 🌐

Ambil 1 visit pertama (FIFO oleh `id_kunjungan ASC`) yang status-nya `menunggu_evaluasi`. Dipanggil oleh tablet evaluasi (polling).

**Response 200:**
```json
{
  "success": true,
  "data": { "id_kunjungan": 1234, "id_user": 8200042, "nomor_antrian": "K005", ... } | null
}
```

### `GET /api/evaluations/:id` 🌐

Daftar 17 indikator IKM + evaluasi yang sudah ada (untuk render form).

**Response 200:**
```json
{
  "success": true,
  "data": {
    "indikator": {
      "1": "Informasi pelayanan tersedia secara elektronik maupun non-elektronik.",
      "2": "Persyaratan pelayanan mudah dipenuhi.",
      "...": "...",
      "17": "Tidak ada praktik percaloan."
    },
    "evaluation": [ ... tamdes_evaluasi_detail rows kalau sudah pernah disubmit ... ]
  }
}
```

### `POST /api/evaluations/:id` 🌐

Submit evaluasi (Blok II Kepuasan PST). Sejak 2026-04-30: hanya kepuasan, 16 indikator, skala 1-10.

**Request body:**
```json
{
  "skor_keseluruhan": 9,
  "kepuasan": { "1": 8, "2": 9, "3": 7, ..., "16": 10 }
}
```

**Validasi:**
- `skor_keseluruhan` wajib 1-10.
- `kepuasan` wajib array/object.
- Tiap `kepuasan[i]` harus 1-10, di luar rentang akan di-skip.

**Side effects:**
- Hapus `tamdes_evaluasi_detail` lama (replace strategy).
- Insert per indikator dengan `kepentingan=NULL` (kolom deprecated, tetap untuk historical).
- Update `tamdes_kunjungan`: `status='selesai'`, `rating_pengunjung`, `selesai_timestamp`, `durasi_detik`.

**Migration note:** Kolom `kepentingan` di `tamdes_evaluasi_detail` sekarang nullable. Submission baru selalu NULL. Legacy data sebelum migrasi mungkin punya value 1-4 (skala lama).

### `GET /api/evaluations/:id/results` 🔒

Hasil evaluasi suatu kunjungan (untuk admin).

**Response 200:**
```json
{
  "success": true,
  "data": {
    "rating_pengunjung": 9,
    "status": "selesai",
    "durasi_detik": 1969,
    "details": [ { "indikator_id": 1, "kepentingan": 4, "kepuasan": 4 }, ... ],
    "indikator": { ... peta nama indikator ... }
  }
}
```

### `GET /api/evaluations/summary` 🔒

Ringkasan IKM seluruh kunjungan (untuk dashboard).

**Query params:**
- `tahun` (opsional, filter `YEAR(date_visit)`)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "visits": [ ... per-visit aggregate (avg kepentingan, avg kepuasan) ... ],
    "indicators": [
      { "indikator_id": 1, "avg_kepentingan": 3.8, "avg_kepuasan": 3.6, "responden": 45 },
      ...
    ],
    "overall": { "ikm_score": 3.62, "total_responden": 45 },
    "labels": { ... peta nama indikator ... }
  }
}
```

---

## 7. 🏪 Kiosk — `/api/kiosk/*` (publik)

Endpoint untuk perangkat kiosk fisik di front office. **Tanpa autentikasi** — kontrol akses melalui isolasi jaringan/device fingerprint.

### `GET /api/kiosk/face-data` 🌐

Daftar tamu dengan face descriptor (untuk pencocokan client-side oleh face-api.js).

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id_user": 8200042,
      "nama": "Siti Aminah",
      "face_descriptor": [ -0.092, 0.128, ..., 0.034 ]   // array 128 float
    }
  ]
}
```

### `GET /api/kiosk/guest-list` 🌐

Daftar nama tamu (untuk autocomplete pencarian manual).

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id_user": 8200042, "nama": "Siti Aminah", "nama_instansi": "Dinas Pertanian Halmahera" }
  ]
}
```

### `POST /api/kiosk/register` 🌐

Daftar tamu baru + buat kunjungan + dapat nomor antrian.

**Request body:**
```json
{
  "nama": "Budi Santosa",
  "email": "budi@example.com",
  "notel": "08123456789",
  "jeniskelamin": "L",
  "umur": 35,
  "disabilitas": 0,
  "pendidikan": "S2",
  "pekerjaan": "Dosen",
  "kategori_instansi": "Pendidikan",
  "nama_instansi": "Universitas Khairun",
  "pemanfaatan": "Penelitian",
  "pengaduan": "",
  "foto": "data:image/jpeg;base64,/9j/4AAQ...",
  "face_descriptor": [ ... 128 float ... ],
  "biometric_consent": true,
  "consent_timestamp": "2026-04-30T09:15:00Z",
  "jenis_layanan": ["Konsultasi Statistik"],
  "sarana": ["Tatap Muka"]
}
```

**Anti double-submit:** Jika kombinasi `nama`+`notel`+`tgldatang=today` sudah ada, return data existing (idempoten).

**Concurrency safety:** Pakai `LOCK TABLES` saat generate `id_user` untuk hindari race condition antara dua kiosk.

**Response 201:**
```json
{
  "success": true,
  "data": {
    "id_kunjungan": 1234,
    "id_user": 8200042,
    "nomor_antrian": "K005"
  },
  "message": "Pendaftaran berhasil"
}
```

### `POST /api/kiosk/visit` 🌐

Buat kunjungan untuk tamu yang **sudah pernah daftar** (id_user diketahui).

**Request body:**
```json
{
  "id_user": 8200042,
  "jenis_layanan": ["Konsultasi Statistik"],
  "sarana": ["Tatap Muka"]
}
```

**Response 201:** `{ id_kunjungan, nomor_antrian }`

### `GET /api/kiosk/ticket/:id` 🌐

Data tiket untuk render halaman tiket (atau cetak).

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id_kunjungan": 1234,
    "nomor_antrian": "K005",
    "jenis_layanan": "[\"Konsultasi Statistik\"]",
    "date_visit": "2026-04-30 09:15:22",
    "nama": "Budi Santosa"
  }
}
```

### `POST /api/kiosk/print` 🌐

Trigger cetak tiket via print server lokal (`http://localhost:5000/print`).

**Request body:** Object yang akan diteruskan apa adanya ke print server (typically `{ nomor_antrian, nama, jenis_layanan, ... }`).

**Response 200:** `{ success: true, message: "Tiket berhasil dicetak" }`
**Error 502:** Print server tidak respond.

### `GET /api/kiosk/profile-gaps/:id_user` 🌐

Cek field profil tamu yang kosong (untuk minta lengkapi data jika tamu lama dengan profil tidak lengkap).

**Response 200:**
```json
{
  "success": true,
  "data": { "gaps": ["umur", "pendidikan", "kategori_instansi"] }
}
```

Field yang dicek: `umur, disabilitas, jenis_disabilitas, pendidikan, pekerjaan, kategori_instansi, nama_instansi, pemanfaatan, email, notel`. Khusus `jenis_disabilitas`: hanya wajib jika `disabilitas=1`.

### `POST /api/kiosk/profile-update/:id_user` 🌐

Patch profil tamu (hanya field yang dikirim akan di-update).

**Request body:** subset dari field yang ada di gaps.

**Response 200:** `{ success: true, message: "Profil berhasil dilengkapi" }`

---

## 8. 📊 Dashboard — `/api/dashboard/*`

### `GET /api/dashboard/stats` 🔒

Statistik untuk halaman dashboard admin (jumlah tamu hari ini, antrian, dll).

**Response 200:** struktur tergantung implementasi `Dashboard::stats()` (cek source).

### `GET /api/dashboard/events` 🔒

Event terkini (untuk timeline / Server-Sent Events).

---

## 9. 👥 Users — `/api/users/*` (admin/superadmin)

### `GET /api/users` 🔒🛡️ (admin+)

Daftar user admin.

### `GET /api/users/:id` 🔒🛡️ (admin+)

Detail user.

### `POST /api/users/change-password` 🔒

User mengubah password sendiri.

**Request body:**
```json
{ "old_password": "...", "new_password": "..." }
```

---

## 10. 📋 Audit & Misc

### `GET /api/audit` 🔒🛡️ (admin+)

Daftar audit log dengan pagination & filter.

**Query params:** `page`, `limit`, `user`, `action`, `target_type`, `tanggal_awal`, `tanggal_akhir`.

### `GET /api/queue-stats` 🔒

Statistik antrian (rata-rata durasi tunggu, durasi pelayanan per layanan, dll).

### `GET /api/services` 🔒

Daftar jenis layanan PST yang tersedia (untuk dropdown).

**Response 200:**
```json
{
  "success": true,
  "data": [
    "Perpustakaan",
    "Konsultasi Statistik",
    "Rekomendasi Kegiatan Statistik",
    "Penjualan Produk Statistik",
    "Lainnya"
  ]
}
```

### `GET /api/responden` 🔒

Daftar responden tahunan (`tamdes_responden_tahunan`).

---

## 11. Contoh Pemanggilan

### cURL — Login

```bash
curl -i -X POST https://bukutamu.bpsmalut.com/api/auth/login \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"username":"admin","password":"rahasia"}'
```

### cURL — List Visits (pakai cookie)

```bash
curl -s https://bukutamu.bpsmalut.com/api/visits?page=1&limit=20 \
  -b cookies.txt | jq '.'
```

### Axios (frontend) — pola standar

```ts
// src/api/client.ts
import axios from 'axios'
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '',
  withCredentials: true,            // ⭐ auto-send cookie jwt_token
  headers: { 'Content-Type': 'application/json' },
})
```

```ts
// src/api/visits.ts
import apiClient from './client'

export const fetchVisits = (params: VisitFilters) =>
  apiClient.get('/api/visits', { params }).then(r => r.data.data)

export const callQueue = (id: number) =>
  apiClient.post(`/api/consultations/${id}/call`).then(r => r.data)
```

### TanStack Query — pola standar

```ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { fetchVisits, callQueue } from '@/api/visits'

const { data, isLoading } = useQuery({
  queryKey: ['visits', filters],
  queryFn: () => fetchVisits(filters),
})

const qc = useQueryClient()
const callMutation = useMutation({
  mutationFn: callQueue,
  onSuccess: () => qc.invalidateQueries({ queryKey: ['consultations'] }),
})
```

---

## 12. Catatan & Quirks

1. **JSON dalam string column:** `jenis_layanan` dan `sarana` di `tamdes_kunjungan` disimpan sebagai JSON string. Frontend wajib `JSON.parse()`. Belum ada migrasi ke kolom `JSON` MySQL native.

2. **`id_user` manual:** Tidak auto-increment. Mulai dari `8200001`. Kalau tabel kosong, baris pertama dapat `8200001`. Konvensi ini sengaja agar tidak konflik dengan ID lama dari sistem warisan (pre-2026).

3. **Replace strategy:** `Consultations::data()` dan `Evaluations::detail()` (POST) menghapus row lama lalu insert baru. Atomic dalam satu request, tapi kalau koneksi terputus di antara DELETE dan INSERT, data lama hilang. Tidak pakai transaction.

4. **CORS strict:** Hanya origin yang persis match (`http://localhost:5173` atau `FRONTEND_URL` di .env) yang dapat header CORS. Kalau testing dari Postman, OK karena Postman tidak enforce CORS.

5. **CSRF:** CodeIgniter 3 CSRF dimatikan untuk endpoint API (lihat `config.php`). Mitigasi via cookie `samesite=Strict` + `Origin` check di CORS.

6. **Rate limit hanya untuk login:** Endpoint lain belum punya rate limiting. Kalau ada serangan brute force ke endpoint kiosk publik, butuh fail2ban level OS atau Cloudflare di depan.

7. **`require_auth()` cuma di endpoint admin:** Endpoint `/api/kiosk/*` dan `/api/evaluations/(pending|:id)` sengaja publik. Mengubah jadi auth-required akan **break** kios fisik.

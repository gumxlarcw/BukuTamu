---
name: Spec — Dokumen Flow Pengunjung Bukutamu Digital PST
description: Rancangan dokumen cross-functional flowchart (swimlane) ISO 5807 untuk alur pengunjung dari datang hingga pulang — kiosk publik, backend API, MariaDB db_tamdes, petugas/sistem eksternal
status: draft (menunggu approval user)
penyusun: Claude (sesi 2026-05-18)
target_output: docs/FLOW_PENGUNJUNG.md (atau lokasi yang user pilih — lihat §8)
sumber_data: Eksplorasi langsung kode /var/www/html/bukutamu/frontend + /backend + dump SQL db_tamdes (2026-05-16)
---

# Spec: Dokumen Flow Pengunjung Bukutamu Digital PST

## 1. Tujuan & Cakupan

### 1.1 Tujuan
Menghasilkan **satu dokumen Markdown** berisi 5 cross-functional flowchart (swimlane) yang menggambarkan alur penuh seorang pengunjung di Aplikasi Buku Tamu Digital PST BPS Provinsi Maluku Utara — dari membuka kiosk sampai meninggalkan kantor — beserta efek setiap langkah ke backend (CodeIgniter API) dan basis data MariaDB (`db_tamdes`).

Dokumen ini dapat dipakai untuk:
- Bahan baku Bab IV.4 "Diagram Sekuens" di dokumen Rancangan Sistem (PerBPS 2/2021, kegiatan III.A.16).
- Referensi onboarding pengembang penerus.
- Diagram alur lampiran Petunjuk Operasional.

### 1.2 Cakupan
- **Tiga grup layanan**: SKD (4 layanan inti), DTSEN, Resepsionis (Lainnya / Keperluan Pimpinan).
- Alur **kunjungan baru** dan **kunjungan ulang**.
- **Pelayanan & finalisasi** termasuk panggilan antrian strict-mode dan 3-layer soft-correct gate.
- **Evaluasi tablet** untuk grup SKD.
- Interaksi dengan **print service kiosk-local** (`localhost:5300`) dan **dashboard PST eksternal** (port 5001).

### 1.3 Yang **tidak** masuk cakupan (sengaja)
- DELETE cascade admin (bukan alur pengunjung).
- Login admin + rate limiting (bukan alur pengunjung).
- Laporan & export CSV.
- Pengelolaan whitelist sarana (admin maintenance).

---

## 2. Standar yang Dipakai

| Aspek | Standar | Catatan |
|---|---|---|
| Simbol flowchart | **ISO 5807:1985** | Terminator (oval), Process (rectangle), Decision (diamond), Input/Output (parallelogram), Data Storage (cylinder), Connector (circle) |
| Swimlane / cross-functional | Praktik BPMN-style (ISO/IEC 19510:2013 informal) | ISO 5807 tidak mendefinisikan swimlane formal; kombinasi ini umum di dokumen rancangan BPS |
| Bahasa diagram | **Mermaid `flowchart`** dengan `subgraph` per swimlane | Render-able via Pandoc + mermaid-filter ke `.docx`/PDF |
| Aturan diagram | Setiap decision **wajib** punya cabang Ya **dan** Tidak yang ter-resolve (per arahan user) | Tidak ada cabang menggantung |
| Narasi | Setiap diagram diiringi narasi 1–2 paragraf | Konsisten dengan aturan di `rancangan-sistem-prompt.md` baris 35 |

### 2.1 Pemetaan Simbol ISO 5807 → Sintaks Mermaid

| Simbol ISO 5807 | Bentuk | Sintaks Mermaid | Pemakaian di dokumen |
|---|---|---|---|
| Terminator | Oval | `id([Mulai])` | Start/End tiap diagram |
| Proses | Persegi panjang | `id[Validasi form]` | Proses umum |
| Keputusan | Belah ketupat | `id{Apakah cocok?}` | Cabang Ya/Tidak |
| Input/Output | Jajar genjang | `id[/Tampil form/]` | Input dari user atau output ke user |
| Penyimpanan data | Silinder | `id[(tamdes_kunjungan)]` | Tabel DB |
| Koneksi | Lingkaran kecil | `id((A))` | Penyambung antar-diagram |
| Subroutine/predefined | Persegi panjang dengan garis ganda samping | `id[[Cetak tiket termal]]` | Proses ber-side-effect penting |

---

## 3. Aktor & Swimlane

Empat swimlane sesuai permintaan user:

| # | Swimlane | Aktor yang masuk | Warna saran (untuk render `.docx`) |
|---|---|---|---|
| 1 | **Konsumen / Kiosk Publik** | Browser kiosk publik (React SPA), tablet evaluasi, konsumen pengunjung | Biru muda |
| 2 | **Backend / API** | CodeIgniter modul `api` (port 60), endpoint `/api/*`, fungsi `require_layanan_role()`, `next_status_after_completion()`, `proxy_antrian()` | Hijau muda |
| 3 | **MariaDB / db_tamdes** | 10 tabel (lihat §5) | Kuning muda |
| 4 | **Petugas / Sistem Eksternal** | Resepsionis, Petugas PST (meja), Print service kiosk-local `localhost:5300`, Dashboard PST `port 5001`, TV antrian | Oranye muda |

**Aturan label node**: Karena swimlane 4 menggabungkan aktor heterogen, setiap node di kolom itu **wajib mencantumkan label aktor di awal**:
- `[Petugas PST: Klik Panggil]`
- `[Print Service localhost:5300: Cetak termal]`
- `[Dashboard PST :5001: Antrian terpanggil]`

Tujuan: pembaca tetap bisa membedakan tanpa kehilangan compactness diagram.

---

## 4. Lima Diagram yang Akan Dibuat

### 4.1 Diagram 1 — Master (Big Picture)

**Tujuan**: peta tingkat tinggi dari pintu masuk sampai pulang. Diagram ini **tidak** detail per endpoint; cukup cabang status + grup layanan.

**Cakupan urut sesuai kode aktual** (sudah diverifikasi langsung di `frontend/src/App.tsx` + halaman kiosk pada 2026-05-18):

```
[1] Pengunjung datang
 ↓
[2] /kiosk Welcome — tombol "Isi Buku Tamu"
 ↓
[3] /kiosk/service — pilih jenis layanan + sarana
     (cross-group block: SKD vs DTSEN vs Front-office mutually exclusive)
 ↓  [anotasi: grup ditentukan di sini, BUKAN sebagai cabang flow]
[4] /kiosk/status — Decision: "Sudah pernah daftar?"
     ├─ Tidak → [5a] /kiosk/form → /kiosk/capture
     │              ↓ POST /api/kiosk/register
     │              ↓
     └─ Ya   → [5b] /kiosk/recognize — Decision: "Wajah cocok?"
                    ├─ Ya → POST /api/kiosk/visit
                    └─ Tidak → modal "Cari nama" — Decision: "Pilih manual ketemu?"
                                ├─ Ya → POST /api/kiosk/visit
                                └─ Tidak → fallback ke /kiosk/form (handleNoMatch)
                                           ↓ jadi pengunjung baru (alur 5a)
[6] /kiosk/ticket/:id — cetak tiket termal ke localhost:5300
 ↓
[7] Tunggu di ruang tunggu
 ↓
[8] CABANG NYATA per grup di pelayanan:
     ├─ Grup SKD          → Petugas PST buka /admin/consultations
     │                       ↓ Panggil (strict-mode :5001)
     │                       ↓ Isi form konsultasi
     │                       ↓ Finalisasi → status = menunggu_evaluasi
     │                       ↓
     │                      [9] /kiosk/evaluasi tablet
     │                       ↓ submit → status = selesai
     │
     ├─ Grup DTSEN        → Petugas PST buka /admin/dtsen
     │                       ↓ Isi form DTSEN
     │                       ↓ Finalisasi → status = selesai (langsung)
     │
     └─ Grup Resepsionis  → Tidak masuk antrian PST
                             ↓ Langsung ke ruangan internal (sarana 33–36)
                             ↓ Petugas isi /admin/visits (manual entry)
                             ↓ Finalisasi → status = selesai (langsung)
[10] Pengunjung pulang
```

**Node penting** (perkiraan jumlah: ~24 node):
- Terminator: `[1] Pengunjung datang`, `[10] Pengunjung pulang`
- Decision: `Pilih layanan valid?` (di Service), `Sudah pernah daftar?` (di Status), `Wajah cocok?` (di Recognize), `Pilih manual ketemu?` (modal di Recognize), `Grup pelayanan?` (3-way di pelayanan, **bukan** di kiosk), `Status menunggu_evaluasi?` (decision menentukan apakah lewat tablet)
- Proses: `Lihat welcome`, `Pilih jenis layanan + sarana`, `Pilih status`, `Daftar baru (form+capture)`, `Scan wajah (recognize)`, `Cetak tiket`, `Tunggu panggilan`, `Pelayanan di meja PST/DTSEN`, `Isi evaluasi tablet`, `Petugas resepsionis catat manual`
- Cylinder DB: `tamdes_buku`, `tamdes_kunjungan`, `konsultasi_pengunjung`, `dtsen_konsultasi`, `tamdes_evaluasi_detail`
- External (swimlane 4): `Print Service localhost:5300: Cetak termal`, `Dashboard PST :5001: Panggilan TV antrian`

**Catatan kritis untuk akurasi diagram**:

1. **Cabang grup TIDAK terjadi di kiosk routing**. Welcome → Service → Status → Form/Recognize → Ticket — **identik** untuk semua 3 grup. Diagram Master harus menggambar 1 jalur tunggal di swimlane Konsumen/Kiosk sampai tiket dicetak.

2. **Cabang grup baru terlihat efek nyata di pelayanan** (setelah `[7] Tunggu`):
   - **SKD** masuk antrian PST (`/api/consultations` only menarik 4 layanan SKD).
   - **DTSEN** masuk antrian DTSEN (`/api/dtsen` terpisah).
   - **Resepsionis** (Lainnya / Keperluan Pimpinan) **tidak masuk antrian apapun** — pengunjung langsung ke ruangan internal, petugas catat manual di `/admin/visits`. Tidak ada panggilan TV.

3. **Fallback Recognize → Form**: kalau wajah tidak cocok dan pencarian manual juga gagal, halaman jatuh ke `/kiosk/form` membawa state layanan & sarana. Pengunjung yang awalnya ulang jadi pengunjung baru. Harus muncul sebagai panah balik di diagram.

4. **Status transition di node**: setiap node yang men-update kolom `status` di `tamdes_kunjungan` diberi anotasi `(status: antri → dipanggil → ...)`.

### 4.2 Diagram 2 — Pendaftaran Kunjungan Baru

**Tujuan**: detail langkah `/kiosk/service → /kiosk/status → /kiosk/form → /kiosk/capture → /kiosk/ticket/:id` untuk pengunjung baru.

**Highlight kritis**:
- Modal `PhotoDisclaimer` (biometric consent) — decision Ya/Tidak; Tidak → kembali ke form.
- Validasi 11 field di `VisitorForm` (nama, email, phone, umur, ...).
- Min 3 sample wajah untuk enable tombol "Ambil Foto" (target 5).
- `POST /api/kiosk/register` dengan LOCK TABLES — insert ke `tamdes_buku` + `tamdes_kunjungan` (+ upsert `tamdes_responden_tahunan`).
- `POST http://localhost:5300/print` dari TicketPage.

**Node count perkiraan**: ~28 node.

### 4.3 Diagram 3 — Pendaftaran Kunjungan Ulang (Face Recognition)

**Tujuan**: detail langkah `/kiosk/recognize` — warmup 600ms → sampling 5 descriptor → matching → cabang found/not-found → modal profile-gaps (opsional) → `POST /api/kiosk/visit`.

**Highlight kritis**:
- `GET /api/kiosk/face-data` di mount — fetch semua descriptor dari `tamdes_buku.face_descriptor`.
- Decision matching: `similarity > 0.55 AND margin > 0.08`? Cabang Ya/Tidak lengkap.
- Cabang Tidak → modal "Cari Nama Manual" → `GET /api/kiosk/guest-list` → user pilih → cek ulang.
- Cabang Tidak (manual juga tidak ketemu) → fallback ke `/kiosk/form` (jadi pengunjung baru).
- Setelah matched: `GET /api/kiosk/profile-gaps/{id_user}` mint token `profile-update` 5 menit.
- Modal gaps (decision): ada gap? Ya → modal isi profil + `POST /api/kiosk/profile-update/{id_user}` (header `X-Kiosk-Token`); Tidak → langsung lanjut.
- `POST /api/kiosk/visit` → insert ke `tamdes_kunjungan` (tanpa LOCK karena id_user sudah ada).
- Cetak tiket sama seperti Diagram 2.

**Node count perkiraan**: ~32 node.

### 4.4 Diagram 4 — Pelayanan & Finalisasi (3-layer Gate)

**Tujuan**: alur dari `status = antri` sampai `status = menunggu_evaluasi` (untuk SKD) atau `status = selesai` (untuk DTSEN/Resepsionis). Termasuk panggilan strict-mode + 3-layer soft-correct gate.

**Highlight kritis**:
- Petugas PST membuka antrian (`GET /api/consultations`).
- Klik "Panggil" → `POST /api/consultations/{id}/call`:
  - Backend `proxy_antrian()` POST ke `Dashboard PST :5001`.
  - Decision: dashboard respons OK? **Ya** → update `tamdes_kunjungan.status` = `dipanggil` + audit; **Tidak** → return 502, **JANGAN update DB**, audit `call_queue_failed`.
- Pengunjung dipanggil → mendekati meja PST.
- Petugas isi form konsultasi → `POST /api/consultations/data` (delete-insert `konsultasi_pengunjung`).
- Petugas klik "Selesai" → `PUT /api/consultations/{id}` (Layer 2 gate).
- **3-layer gate** sebagai cabang berturut-turut:
  - Layer 1: `PUT /api/visits/{id}/status` → `require_layanan_role()` + soft-correct
  - Layer 2: `PUT /api/consultations/{id}` → idem
  - Layer 3: `POST /api/evaluations/{id}` → cek `visit.status ∈ {menunggu_evaluasi, selesai}`
- Decision: Grup SKD?
  - **Ya**: status → `menunggu_evaluasi` (kecuali role bypass: admin/superadmin/operator → bisa langsung `selesai`)
  - **Tidak** (DTSEN/Resepsionis): status → `selesai` + isi `selesai_timestamp` + hitung `durasi_detik`
- DTSEN tambahan: `POST /api/dtsen/{id}/data` → insert ke `dtsen_konsultasi`.

**Node count perkiraan**: ~36 node (paling padat).

### 4.5 Diagram 5 — Evaluasi Tablet (SKD only)

**Tujuan**: alur dari `status = menunggu_evaluasi` sampai `status = selesai` melalui tablet evaluasi.

**Highlight kritis**:
- Tablet di `/kiosk/evaluasi` standby — polling `GET /api/evaluations/pending` setiap 5 detik.
- Decision polling: ada visit dengan `status = menunggu_evaluasi`?
  - **Tidak**: tetap standby (loop ke polling).
  - **Ya**: backend mint token `eval-submit` (TTL 10 menit) → return `{ id_kunjungan, kiosk_token }`.
- Navigate ke `/kiosk/evaluasi/:id` dengan token di route state.
- Decision: route state ada token? **Tidak** → bounce kembali ke standby.
- `GET /api/evaluations/{id}` header `X-Kiosk-Token` → fetch 16 indikator + (kalau SKD ada data konsultasi) skor kualitas per data.
- Pengunjung isi 16 nilai Likert 1–10 + skor keseluruhan.
- Decision: semua 16 indikator terisi? **Tidak** → tombol "Kirim" disabled.
- **Ya** → `POST /api/evaluations/{id}` header `X-Kiosk-Token`:
  - Layer 3 gate cek `visit.status ∈ {menunggu_evaluasi, selesai}`.
  - Delete existing `tamdes_evaluasi_detail` (idempotent untuk re-submit koreksi).
  - Loop insert 16 baris ke `tamdes_evaluasi_detail` (1 baris per indikator).
  - Update `konsultasi_pengunjung.kualitas` per item kalau ada.
  - Update `tamdes_kunjungan`: `status = selesai`, `rating_pengunjung`, `selesai_timestamp`, `durasi_detik`.
- "Terima Kasih" screen 4 detik → auto-navigate ke standby.

**Node count perkiraan**: ~26 node.

---

## 5. Daftar Tabel db_tamdes yang Akan Muncul di Diagram

| Tabel | Muncul di Diagram | Operasi yang ditampilkan |
|---|---|---|
| `tamdes_buku` | 2, 3 | INSERT (register), SELECT (face-data, profile-gaps), UPDATE (profile-update) |
| `tamdes_kunjungan` | 1, 2, 3, 4, 5 | INSERT (register/visit), UPDATE status (call/finalize/eval submit) |
| `konsultasi_pengunjung` | 4, 5 | DELETE-INSERT (consultations/data), UPDATE kualitas (eval submit) |
| `dtsen_konsultasi` | 4 | INSERT (dtsen/data) |
| `tamdes_evaluasi_detail` | 5 | DELETE-INSERT (eval submit) |
| `tamdes_responden_tahunan` | 2 | UPSERT (cohort SKD tahunan) |
| `tamdes_audit_log` | 4 | INSERT (call_queue_failed, update_status) |
| `tamdes_rate_limit` | 3 | INSERT (face-data, guest-list rate cap 30/min/IP) |
| `admin_users` | — | (tidak muncul — bukan alur pengunjung) |
| `tamdes_login_attempts` | — | (tidak muncul — bukan alur pengunjung) |
| `ci_sessions` | — | (tidak muncul — backend infra) |

---

## 6. Endpoint Backend yang Akan Muncul

| Endpoint | Diagram | Auth |
|---|---|---|
| `GET /api/services` | 1, 2, 3 | Public |
| `POST /api/kiosk/register` | 2 | Public (kiosk) |
| `GET /api/kiosk/face-data` | 3 | Public + rate limit 30/min |
| `GET /api/kiosk/guest-list` | 3 | Public + rate limit 30/min |
| `GET /api/kiosk/profile-gaps/{id_user}` | 3 | Public, mints `profile-update` token 5 menit |
| `POST /api/kiosk/profile-update/{id_user}` | 3 | Header `X-Kiosk-Token` |
| `POST /api/kiosk/visit` | 3 | Public (kiosk) |
| `GET /api/kiosk/ticket/{id}` | 2, 3 | Public (kiosk) |
| `POST http://localhost:5300/print` | 2, 3 | External (lokal kiosk PC) |
| `GET /api/consultations` | 4 | Auth (petugas_pst/admin) |
| `POST /api/consultations/{id}/call` | 4 | Auth, **proxy ke port 5001 strict-mode** |
| `POST /api/consultations/data` | 4 | Auth + `require_layanan_role` |
| `PUT /api/consultations/{id}` | 4 | Auth + Layer 2 gate |
| `POST /api/dtsen/{id}/data` | 4 | Auth |
| `PUT /api/visits/{id}/status` | 4 | Auth + Layer 1 gate |
| `GET /api/evaluations/pending` | 5 | Public, mints `eval-submit` token 10 menit |
| `GET /api/evaluations/{id}` | 5 | Header `X-Kiosk-Token` |
| `POST /api/evaluations/{id}` | 5 | Header `X-Kiosk-Token` + Layer 3 gate |

---

## 7. Findings / Inkonsistensi yang Akan Dicatat di Dokumen

Berdasarkan cross-check kode aktual vs dokumen yang sudah ada (`rancangan-sistem-prompt.md`, audit, prompts), berikut temuan yang akan saya cantumkan di **bagian "Catatan Inkonsistensi"** di akhir dokumen final supaya bisa ditindaklanjuti:

1. **Urutan halaman kiosk publik**: kode aktual `Welcome → Service → Status` (pilih layanan dulu), bukan `Welcome → Status → Service` seperti yang ditulis di `rancangan-sistem-prompt.md` §1.6.
2. **Role enum**: DB pakai `'superadmin','admin','operator','resepsionis','petugas_pst'` (5 role) — prompt menulis "viewer" yang **tidak ada** di DB. Bypass soft-correct: admin/superadmin/operator.
3. **Tabel `konsultasi_kualitas`**: disebut di prompt §1.8, **tidak ada** di DB aktual. Skor kualitas disimpan di kolom `konsultasi_pengunjung.kualitas` (varchar).
4. **`dtsen_konsultasi`**: **tidak punya FK constraint** ke `tamdes_kunjungan` di DB. Cascade delete dilakukan manual di `Visits.php` L153. Penting untuk diagram.
5. **Status ENUM punya 6 nilai**: `antri, dipanggil, proses, diproses, selesai, menunggu_evaluasi`. Nilai `proses` dan `diproses` ada dua (kemungkinan duplikat warisan).
6. **Skema evaluasi**: 1 **baris per indikator** (16 baris per kunjungan) di `tamdes_evaluasi_detail` — bukan 16 kolom. Sesuai pertanyaan terbuka di prompt §1.8.
7. **Min sample face capture**: konstanta kode = 3 (`MIN_SAMPLES_TO_CAPTURE`) untuk enable tombol; target 5 untuk averaging. Prompt menulis "5" saja.
8. **DTSEN tidak muncul di `/api/consultations/index`** — antrian DTSEN terpisah di `/api/dtsen`.

---

## 8. Output File Final

### 8.1 Lokasi yang saya usulkan

- **Pilihan A** (saya recommend): `docs/FLOW_PENGUNJUNG.md` — root `docs/`, mudah ditemukan oleh pengembang baru.
- **Pilihan B**: `docs/flows/2026-05-18-flow-pengunjung.md` — dengan tanggal & sub-folder.
- **Pilihan C**: `docs/diagrams/flow-pengunjung-bukutamu.md`.

### 8.2 Struktur dokumen final

```
# Flow Pengunjung Bukutamu Digital PST

## 1. Tentang Dokumen Ini
   (Tujuan, sumber data, tanggal, scope)

## 2. Ringkasan Aktor & Swimlane
   (4 swimlane + pemetaan aktor heterogen di kolom 4)

## 3. Diagram 1 — Master (Big Picture)
   ```mermaid
   flowchart LR
     subgraph KIOSK ["Konsumen / Kiosk Publik"]
       ...
     subgraph API ["Backend / API"]
       ...
   ```
   ### Narasi
   (1–2 paragraf)

## 4. Diagram 2 — Pendaftaran Kunjungan Baru
   (sama struktur)

## 5. Diagram 3 — Pendaftaran Kunjungan Ulang (Face Recognition)
   (sama)

## 6. Diagram 4 — Pelayanan & Finalisasi (3-Layer Gate)
   (sama)

## 7. Diagram 5 — Evaluasi Tablet (SKD only)
   (sama)

## 8. Tabel Ringkasan
   8.1 Status Transition `tamdes_kunjungan.status`
   8.2 Continuation Token HMAC (2 jenis)
   8.3 Endpoint × Tabel DB × Diagram

## 9. Catatan Inkonsistensi
   (8 finding dari §7 spec ini)
```

### 8.3 Perkiraan panjang

- ~450–600 baris Markdown.
- ~5 blok Mermaid (1 per diagram).
- 3 tabel ringkasan.

---

## 9. Hal yang Perlu Persetujuan User Sebelum Generate Dokumen Final

Sebelum saya tulis dokumen finalnya, mohon konfirmasi:

1. **Lokasi output** (§8.1): A, B, atau C — atau lokasi lain?
2. **Cakupan diagram master**: tetap 5 diagram seperti di §4, atau ada yang mau ditambah/dikurangi?
3. **Bagian "Catatan Inkonsistensi"** (§7 + §9 dokumen final): apakah disetujui untuk dicantumkan? Ini berguna untuk:
   - Memandu update dokumen `rancangan-sistem-prompt.md` (urutan halaman, role enum, dst).
   - Mencegah developer baru kesasar.
   - **Atau** Anda mau diagram saja tanpa catatan inkonsistensi (catatan disimpan terpisah)?
4. **Bahasa**: Bahasa Indonesia formal seperti dokumen rancangan (memakai "Anda", "petugas", "konsumen"), atau campuran ID + istilah teknis Inggris?
5. **Warna swimlane**: saya usulkan biru/hijau/kuning/oranye muda (§3). Setuju, atau ada preferensi lain?

Mohon balas dengan jawaban ke 5 pertanyaan ini. Setelah disetujui, saya akan generate `docs/FLOW_PENGUNJUNG.md` (atau lokasi yang Anda pilih) dalam satu kali tulis.

# Prompt untuk Claude Desktop — Generate Dokumen Rancangan Sistem Aplikasi Buku Tamu Digital PST

> **Cara pakai**:
> 1. Sebelum dipakai, isi tiga placeholder di bagian **"PARAMETER YANG ANDA TENTUKAN"** di bawah (target halaman, posisi atasan PPK, dan opsi forward-looking).
> 2. Copy seluruh isi blok yang diapit ```` ``` ```` (mulai dari baris `# Tugas` sampai akhir blok), paste ke Claude Desktop.
> 3. Claude Desktop akan menghasilkan dokumen Markdown lengkap yang siap di-convert ke `.docx` via Pandoc.
> 4. Untuk regenerate **diagram saja** (tanpa men-generate ulang seluruh dokumen), gunakan **Blok B** di bagian paling bawah file ini.

> **Catatan untuk pengaju angka kredit**: dokumen ini ditargetkan sebagai bukti fisik kegiatan **III.A.16 — Membuat Rancangan Sistem Informasi** (PerBPS 2/2021), pasangan dari Petunjuk Operasional (III.A.17). Pastikan dua dokumen tidak overlap isi: yang ini fokus ke *bagaimana sistem dirancang*, bukan ke *bagaimana memakainya*.

---

## PARAMETER YANG ANDA TENTUKAN

| # | Parameter | Pilihan | Isi |
|---|---|---|---|
| 1 | Target panjang | 30 / 45 / 60 halaman utama | **`[ISI: 45]`** |
| 2 | Nama atasan PPK langsung + NIP | Sesuai SK | **`[ISI: ___________________ / NIP __________]`** |
| 3 | Cakupan dokumen | `as-built` (mendokumentasikan v1.0 yang sudah jalan) **atau** `as-built + roadmap` (tambah bab pengembangan lanjut v1.1/v2.0) | **`[ISI: as-built + roadmap]`** |

> Ganti tiga isi di atas, lalu copy blok di bawah.

---

```
# Tugas

Susun **Dokumen Rancangan Sistem Aplikasi Buku Tamu Digital PST** lengkap, dalam format Markdown yang siap di-convert ke `.docx` via Pandoc. Dokumen ini adalah bukti fisik kegiatan **III.A.16 (Membuat Rancangan Sistem Informasi)** untuk pengajuan angka kredit Pranata Komputer Ahli Pertama BPS, mengacu PerBPS 2/2021.

Target panjang: **minimal 45 halaman utama** (Bab I–X) di luar pelengkap. Cakupan: dokumentasi rancangan versi 1.0 yang sudah operasional, dilengkapi bab pengembangan lanjut (v1.1 / v2.0).

ATURAN MAIN — PATUHI SEMUA:
- Jangan mengarang fitur, jangan menambahkan teknologi yang tidak disebut di Konteks. Kalau ragu, tulis "tidak diatur dalam rancangan ini" alih-alih menebak.
- Setiap diagram **wajib** menggunakan sintaks Mermaid (```mermaid``` fence) supaya bisa di-render Pandoc + mermaid-filter. Jangan pakai PlantUML atau ASCII art kecuali sebagai pelengkap.
- Setiap diagram **wajib** disertai narasi 1–2 paragraf yang menjelaskan komponen utama, arah data, dan alasan keputusan rancangan. Diagram tanpa narasi tidak dihitung halaman per juknis BPS.
- Setiap tabel **wajib** punya nomor + judul + sumber data (kalau berasal dari analisis sistem berjalan, sebutkan).
- Jangan menambahkan komentar meta ("Pada bab ini akan dibahas...") yang tidak memberi informasi. Langsung ke isi.

---

# 1. Konteks Aplikasi (Sumber Kebenaran — Pakai Persis, Jangan Modifikasi)

## 1.1 Identitas

| Atribut | Nilai |
|---|---|
| Nama aplikasi | Aplikasi Buku Tamu Digital PST |
| Instansi | BPS Provinsi Maluku Utara |
| URL produksi | https://bukutamu.bpsmalut.com |
| URL dashboard pimpinan (read-only) | https://malika.bpsmalut.com |
| Versi sistem yang dirancang | 1.0 |
| Tanggal pengesahan dokumen | 13 Mei 2026 |
| Penyusun | Wisnu Candra Gumelar |
| NIP penyusun | 199706092019121001 |
| Jabatan penyusun | Pranata Komputer Ahli Pertama |
| Atasan PPK langsung pengesah | [ISI: ___________________] (NIP: __________) |

## 1.2 Tumpukan Teknologi (Tech Stack)

| Lapisan | Teknologi | Catatan |
|---|---|---|
| Frontend (kiosk + admin) | Vite + React 18 + TypeScript, PWA | Single SPA, dua layout (KioskLayout, AdminLayout) |
| State / data fetching | React Router v6, fetch native, custom hooks | Tidak pakai Redux. State lokal per halaman. |
| Backend | CodeIgniter 3 (PHP), modul `api` (HMVC) | Library tambahan: `JWT_Helper.php` untuk continuation token |
| Basis data | MySQL/MariaDB, schema bernama `db_tamdes` (deliberate — nama warisan) | InnoDB, FK aktif |
| Print service | Node.js + escpos, berjalan **lokal di tiap kiosk PC** di `localhost:5300` | Bukan backend pusat — desain anti single-point-of-failure |
| Face recognition | face-api.js (browser-side), model SSD Mobilenet + landmark + recognition | Inferensi 100% di browser, hanya descriptor (128-dim float) yang dikirim |
| Reverse proxy | Apache vhost `bukutamu.bpsmalut.com` port 60 (HTTP) / 460 (HTTPS) | Frontend di-serve PM2 (`bukutamu-frontend`) |
| Audio cue (panggilan antrian) | Text-to-speech browser + dashboard PST eksternal di port 5001 (strict-mode call) | Update DB di-abort kalau dashboard tak respon |

## 1.3 Taksonomi Jenis Layanan (Tiga Grup — Mutually Exclusive)

| Grup | Jenis Layanan | Wajib SKD + Evaluasi? | Prefix Antrian | Whitelist Sarana (kode bitmask BPS) |
|---|---|---|---|---|
| **SKD** | Perpustakaan | Ya | **P** | 1, 2, 4, 9, 16, 32 |
| **SKD** | Konsultasi Statistik | Ya | **K** | 1, 2, 4, 9, 16, 32 |
| **SKD** | Rekomendasi Kegiatan Statistik | Ya | **R** | 1, 2, 4, 9, 16, 32 |
| **SKD** | Penjualan Produk Statistik | Ya | **J** | 1, 2, 4, 9, 16, 32 |
| **DTSEN** | Konsultasi DTSEN | Tidak (alur khusus) | **D** | 1 saja (datang langsung) |
| **Resepsionis** | Keperluan Pimpinan | Tidak | — | 33–36 (ruangan internal) |
| **Resepsionis** | Lainnya | Tidak | — | 33–36 |

Aturan: satu kunjungan tidak boleh mencampur Jenis Layanan dari grup berbeda. Validasi diterapkan di lapis frontend (komponen pemilihan layanan) **dan** backend (PUT `/api/visits/{id}/service`). Kode sarana 33–36 = Halmahera, Vicon, Gamalama, Pimpinan (ruang rapat internal).

## 1.4 Peran Pengguna (Role-Based Access Control)

| Peran | Cakupan | Akses Halaman Admin |
|---|---|---|
| **Konsumen** | Pengunjung PST. Tanpa login. Pakai kiosk publik untuk daftar, identifikasi (face recognition), dan mengisi evaluasi tablet. | — (kiosk only) |
| **Resepsionis** | Mencatat kunjungan baru, menerbitkan nomor antrian, scan wajah konsumen ulang, mengarahkan ke meja PST. | dashboard, guests, manual-entry, queue-stats |
| **Petugas PST** | Petugas meja PST. Mengisi rincian konsultasi/transaksi, menyelesaikan sesi layanan. | dashboard, consultations, dtsen, visits |
| **Admin** (Tim Diseminasi PST) | Mengelola whitelist sarana, hapus kunjungan (cascade + audit), tarik laporan. | semua kecuali users |
| **Superadmin** | Admin + manajemen user. | semua |
| **Viewer** (Pimpinan) | Read-only laporan dan dashboard. Sebagian disajikan via malika.bpsmalut.com. | dashboard, evaluations (read-only) |

## 1.5 Fitur Kunci Sistem (Wajib Dimasukkan ke Rancangan)

1. **Soft-correct gate tiga lapis (defense-in-depth)** — kunjungan grup SKD tidak boleh transisi ke status `selesai` tanpa evaluasi. Gate di:
   - `PUT /api/visits/{id}/status`
   - `PUT /api/consultations/{id}`
   - `POST /api/evaluations/{id}`
   Role admin/superadmin punya jalur bypass khusus untuk koreksi data.
2. **Continuation token HMAC** untuk endpoint kiosk tanpa login:
   - Token `profile-update` — TTL 5 menit, di-mint saat modal lengkapi profil dibuka.
   - Token `eval-submit` — TTL 10 menit, di-mint saat tablet evaluasi memuat kunjungan terlama yang antri.
   - Format: `{purpose}.{bound_id}.{exp_unix}.{base64url-hmac-sha256}`.
3. **Whitelist sarana per grup layanan** (lihat 1.3).
4. **DELETE cascade dengan audit log** — urutan: snapshot ke `tamdes_audit_log` → `konsultasi_pengunjung` → `dtsen_konsultasi` → `tamdes_evaluasi_detail` → `tamdes_kunjungan`.
5. **Face recognition pipeline** — warmup 600 ms → sampling 5 descriptor → averaging → matching dengan threshold 0,55 dan margin 0,08. Backup flow: pencarian nama manual.
6. **Prefix antrian otomatis** per Jenis Layanan (P/K/R/J/D).
7. **Pencetakan tiket termal lokal** — browser kiosk POST langsung ke `localhost:5300` (escpos), tidak via backend pusat.
8. **Strict-mode TV queue call** — panggilan antrian dibatalkan kalau dashboard PST (port 5001) tidak respon, supaya DB tidak ter-update tanpa suara panggilan benar-benar bunyi.
9. **Laporan**: SKD, IKM (16 indikator skala Likert 1–10), rekap kunjungan harian/bulanan/triwulanan/tahunan. Export CSV via halaman admin.
10. **Rate limiting login** — tabel `tamdes_rate_limit` + `tamdes_login_attempts` untuk anti brute-force.

## 1.6 Inventaris Halaman Frontend (untuk Rancangan Antarmuka)

**Kiosk publik** (tanpa login, KioskLayout):
- `/kiosk` Welcome
- `/kiosk/status` Pilih status (kunjungan baru vs ulang)
- `/kiosk/service` Pilih jenis layanan + sarana
- `/kiosk/form` Form data pengunjung
- `/kiosk/capture` Foto wajah (registrasi)
- `/kiosk/recognize` Scan wajah (identifikasi ulang)
- `/kiosk/ticket/:id` Tiket antrian + cetak termal

**Tablet evaluasi** (tanpa login, public route):
- `/kiosk/evaluasi` Standby — menunggu kunjungan SKD yang antri
- `/kiosk/evaluasi/:id` Form evaluasi 16 indikator + skor data per konsultasi

**Admin / petugas** (di balik login, AdminLayout):
- `/admin` Dashboard
- `/admin/guests`, `/admin/guests/add`, `/admin/guests/import`
- `/admin/consultations`, `/admin/consultations/:id/form`
- `/admin/dtsen`, `/admin/dtsen/:id/form`
- `/admin/visits`
- `/admin/manual-entry`
- `/admin/evaluations`
- `/admin/responden` (responden tahunan SKD)
- `/admin/audit` (role ≥ admin)
- `/admin/users` (role = superadmin)
- `/admin/queue-stats`
- `/admin/tentang`

**Auth & landing**:
- `/login`
- `/` (LandingPage publik)

## 1.7 Inventaris Endpoint API (untuk Rancangan Arsitektur & Sekuens)

Base path: `/api/` (CodeIgniter HMVC modul `api`).

| Resource | Endpoint utama |
|---|---|
| Auth | `POST /auth/login`, `POST /auth/logout`, `GET /auth/check` |
| Guests (buku tamu master) | `GET /guests`, `GET /guests/{id}`, `GET /guests/{id}/visits`, `GET /guests/{id}/photo` |
| Visits | `GET /visits`, `GET /visits/{id}`, `PUT /visits/{id}/status`, `PUT /visits/{id}/service`, `GET /visits/{id}/summary` |
| Consultations (SKD) | `GET /consultations`, `GET /consultations/{id}`, `PUT /consultations/{id}`, `POST /consultations/{id}/call`, `POST /consultations/{id}/test-sound`, `PUT /consultations/{id}/data` |
| DTSEN | `GET /dtsen`, `GET /dtsen/{id}`, `PUT /dtsen/{id}/data` |
| Evaluations | `GET /evaluations/pending`, `GET /evaluations/summary`, `GET /evaluations/{id}/results`, `POST /evaluations/{id}` |
| Responden tahunan | `GET /responden` |
| Users | `GET /users`, `POST /users/change-password` |
| Dashboard | `GET /dashboard`, `GET /dashboard/stats`, `GET /dashboard/events` |
| Audit | `GET /audit` |
| Kiosk (HMAC-protected, no-login) | `POST /kiosk/register`, `POST /kiosk/visit`, `GET /kiosk/ticket/{id}`, `GET /kiosk/face-data`, `GET /kiosk/guest-list`, `GET /kiosk/profile-gaps/{id_user}`, `POST /kiosk/profile-update/{id_user}` |
| Services | `GET /services` (whitelist sarana per grup) |
| Queue stats (TV antrian) | `GET /queue-stats` |

## 1.8 Inventaris Tabel (untuk Rancangan Basis Data)

Schema MySQL: **`db_tamdes`** (nama warisan, sengaja dipertahankan).

| Tabel | Peran |
|---|---|
| `tamdes_buku` | Master pengunjung (buku tamu) — identitas + face descriptor |
| `tamdes_kunjungan` | Tabel induk kunjungan — satu baris per kedatangan |
| `konsultasi_pengunjung` | Detail kunjungan grup SKD (Perpustakaan, Konsultasi, Rekomendasi, Penjualan) |
| `konsultasi_kualitas` | Skor kualitas per item data yang dihasilkan konsultasi SKD |
| `dtsen_konsultasi` | Detail kunjungan DTSEN (terpisah, tanpa evaluasi) |
| `tamdes_evaluasi_detail` | Jawaban 16 indikator IKM (1 baris per indikator atau 1 baris dengan 16 kolom — jelaskan keputusan rancangan) |
| `tamdes_audit_log` | Snapshot baris yang dihapus + metadata pelaku + waktu |
| `tamdes_login_attempts` | Riwayat upaya login (sukses + gagal) |
| `tamdes_rate_limit` | Sliding window untuk rate limiting |
| `tamdes_responden_tahunan` | Daftar responden tahunan SKD (cohort) |

User table: pakai konvensi `tamdes_users` (atau verifikasi nama dari model `Users.php`).

## 1.9 Daftar 16 Indikator IKM (Wajib Dilampirkan)

1. Informasi pelayanan pada unit layanan ini tersedia melalui media elektronik maupun non-elektronik.
2. Persyaratan pelayanan yang ditetapkan mudah dipenuhi/disiapkan oleh konsumen.
3. Prosedur/alur pelayanan yang ditetapkan mudah diikuti/dilakukan.
4. Jangka waktu penyelesaian pelayanan yang diterima sesuai dengan yang ditetapkan.
5. Biaya pelayanan yang dibayarkan sesuai dengan biaya yang ditetapkan.
6. Produk pelayanan yang diterima sesuai dengan yang dijanjikan.
7. Sarana dan prasarana pendukung pelayanan memberikan kenyamanan.
8. Data BPS mudah diakses melalui sarana utama yang digunakan.
9. Petugas pelayanan dan/atau aplikasi pelayanan online merespon dengan baik.
10. Petugas pelayanan dan/atau aplikasi pelayanan online mampu memberikan informasi yang jelas.
11. Fasilitas pengaduan PST mudah diakses.
12. Tidak ada diskriminasi dalam pelayanan.
13. Tidak ada pelayanan di luar prosedur/kecurangan pelayanan.
14. Tidak ada penerimaan gratifikasi.
15. Tidak ada pungutan liar (pungli) dalam pelayanan.
16. Tidak ada praktik percaloan dalam pelayanan.

Skala Likert 1–10. Skor keseluruhan terpisah, juga skala 1–10. Konsultasi yang menghasilkan data: tambahan skor kualitas per item data (tabel `konsultasi_kualitas`).

---

# 2. Ketentuan Format

- Kertas A4, spasi 1,5.
- Minimal **45 halaman utama** (Bab I–X) di luar pelengkap.
- Bahasa Indonesia formal: gunakan "Anda" / "petugas" / "konsumen" (bukan "kamu").
- Istilah bahasa Inggris di-*italic*.
- Awalan terikat **digabung** tanpa spasi: "antarwilayah", "pascaimplementasi", "antarindikator", "prabencana".
- Konsisten: "BPS Provinsi Maluku Utara" (bukan "BPS Malut"), "evaluasi kepuasan layanan" (bukan "evaluasi kepuasan").
- Hindari diksi over-engineered ("kontinuitas operasional", "sinergi", "strategis", "kapasitas diseminasi").
- Setiap diagram pakai Mermaid (```mermaid``` fence). Setiap diagram **wajib** disertai narasi.
- Setiap fitur diilustrasikan minimal sekali (diagram **atau** tabel **atau** snippet skema), tidak boleh deskriptif saja.

---

# 3. Struktur Dokumen yang Diinginkan

## [SAMPUL DEPAN]

- Judul: **DOKUMEN RANCANGAN SISTEM APLIKASI BUKU TAMU DIGITAL PST**
- Subjudul: **BPS Provinsi Maluku Utara**
- Versi: **1.0**
- Bulan/Tahun: **Mei 2026**
- Logo BPS (placeholder: `[GAMBAR — Logo BPS Provinsi Maluku Utara]`)
- Nama penyusun: Wisnu Candra Gumelar
- NIP: 199706092019121001

## [HALAMAN PENGESAHAN]

- Tabel pengesahan: disusun oleh penyusun, disahkan oleh **atasan PPK langsung**.
- Tanggal: 13 Mei 2026.
- Placeholder tanda tangan + nama + NIP atasan PPK langsung.

## [KATA PENGANTAR]

Satu halaman. Konteks: kebutuhan dokumentasi rancangan formal pasca-operasionalisasi.

## [METADATA]

- Nama aplikasi, versi, tanggal.
- Tujuan dokumen (bukti fisik III.A.16).
- Cakupan rancangan (v1.0).
- Pembaca yang dituju (asesor angka kredit, tim pengembang penerus, auditor sistem).
- Hubungan dengan dokumen lain (sebut Petunjuk Operasional sebagai dokumen pasangan, tanpa men-duplikasi isinya).

## [DAFTAR ISI] [DAFTAR GAMBAR] [DAFTAR TABEL]

---

## BAB I PENDAHULUAN

1.1 Latar Belakang — paper-based SKD + PHP legacy `bukutamu-legacy/` → kenapa perlu redesain.
1.2 Tujuan Rancangan.
1.3 Ruang Lingkup.
1.4 Manfaat yang Diharapkan (efisiensi pencatatan, akurasi data SKD, jejak audit).
1.5 Definisi dan Singkatan (SKD, IKM, IPP, PST, DTSEN, PAPI, HMAC, *defense-in-depth*, *whitelist*, *continuation token*, *prefix antrian*, *cascade delete*, *bitmask*, *threshold matching*).

## BAB II ANALISIS SISTEM BERJALAN

2.1 Sistem Berjalan Generasi 1 — paper-based (buku tamu fisik + kuesioner SKD kertas). Kelemahan: konsumen diminta isi biodata 2–3 kali, rekap manual, tidak ada audit trail.
2.2 Sistem Berjalan Generasi 2 — aplikasi PHP `bukutamu-legacy` (sudah digital, belum ada DTSEN, belum ada gate finalisasi, belum ada taksonomi 3-tier).
2.3 Analisis Gap antara Generasi 2 dengan kebutuhan PST per Mei 2026 (tabel ringkas gap → fitur baru).
2.4 Diagram alur sistem berjalan (Mermaid `flowchart LR`) untuk Generasi 1 dan Generasi 2.

## BAB III IDENTIFIKASI KEBUTUHAN

3.1 Kebutuhan Fungsional (per peran, bernomor FR-01, FR-02, ...). Minimal 25 FR.
3.2 Kebutuhan Non-Fungsional (bernomor NFR-01 dst.): kinerja, ketersediaan, keamanan, audit, *usability*, *maintainability*, *portability* untuk kiosk PC.
3.3 Asumsi dan Batasan Rancangan.
3.4 Stakeholder dan Matriks RACI ringkas.

## BAB IV RANCANGAN ARSITEKTUR

4.1 Diagram Konteks (Mermaid `flowchart`) — konsumen, resepsionis, petugas PST, admin, viewer pimpinan, sistem cetak termal, dashboard PST (port 5001), malika.bpsmalut.com.
4.2 Diagram Komponen (Mermaid `flowchart TB`) — frontend SPA (kiosk + admin), backend `api/`, MySQL `db_tamdes`, print service kiosk-local, dashboard PST, reverse proxy Apache, PM2.
4.3 Diagram Deployment / Topologi Jaringan (Mermaid `flowchart`) — server aplikasi, kiosk PC publik (browser + Node escpos lokal), tablet evaluasi (browser saja), TV antrian, jaringan internal BPS.
4.4 Diagram Sekuens (Mermaid `sequenceDiagram`) untuk **minimal lima skenario** kritis:
   - 4.4.a Kunjungan baru grup SKD: kiosk → register → service → ticket → cetak lokal.
   - 4.4.b Kunjungan ulang dengan face recognition.
   - 4.4.c Panggilan antrian strict-mode dengan abort kalau dashboard PST tak respon.
   - 4.4.d Submit evaluasi tablet (continuation token HMAC, 3-layer gate).
   - 4.4.e DELETE cascade dengan snapshot ke audit log.
4.5 Pola Arsitektur yang Dipakai (SPA + REST API + edge print service + browser-side ML inference). Jelaskan **alasan** memilih pola ini vs alternatif (mis. server-side face recognition ditolak karena bandwidth WAN).
4.6 Manajemen Konfigurasi (env var, secrets, rotation runbook — sebut bahwa rotasi rahasia dicatat di `docs/REMAINING_PHASE_B.md`).

## BAB V RANCANGAN BASIS DATA

5.1 ERD lengkap (Mermaid `erDiagram`) — semua tabel di §1.8 dengan kardinalitas yang benar.
5.2 Kamus Data per tabel — kolom, tipe, NOT NULL, default, FK, indeks. Format tabel.
5.3 Normalisasi — pernyataan tingkat normal (3NF / BCNF) dengan justifikasi per tabel.
5.4 Strategi Integritas Referensial — FK ON DELETE, urutan cascade manual di endpoint DELETE.
5.5 Strategi Indeks — minimal sebutkan indeks pada `tamdes_kunjungan.id_kunjungan`, `tamdes_kunjungan.id_buku`, `tamdes_kunjungan.status`, `tamdes_kunjungan.tgl_kunjungan`; indeks komposit untuk laporan harian/bulanan.
5.6 Catatan Penamaan Schema — jelaskan kenapa nama DB tetap `db_tamdes` walau aplikasi sudah berganti nama jadi `bukutamu` (rename live DB berisiko, asimetri ini deliberate).

## BAB VI RANCANGAN ANTARMUKA PENGGUNA

6.1 Inventaris Halaman per Peran (tabel referensi ke §1.6).
6.2 Diagram Alur Antarmuka (Mermaid `flowchart`) per peran — kiosk publik, resepsionis, petugas PST, admin, tablet evaluasi.
6.3 Wireframe Tekstual untuk **minimal delapan halaman kunci** (sketsa ASCII dalam blok kode + narasi):
   - `/kiosk` Welcome
   - `/kiosk/recognize` Face recognition
   - `/kiosk/ticket/:id` Tiket termal preview
   - `/kiosk/evaluasi/:id` Evaluasi 16 indikator
   - `/admin` Dashboard
   - `/admin/consultations/:id/form` Form konsultasi SKD
   - `/admin/dtsen/:id/form` Form DTSEN
   - `/admin/audit` Audit log viewer
6.4 Prinsip Desain UI yang Dipakai (kontras tinggi untuk kiosk, target sentuh ≥ 44 pt, tipografi besar, navigasi linier untuk konsumen, sidebar persisten untuk admin).

## BAB VII RANCANGAN KEAMANAN

7.1 Autentikasi dan Otorisasi (session-based untuk admin, role hierarchy resepsionis < petugas < admin < superadmin, viewer di jalur terpisah).
7.2 Continuation Token HMAC (format, claim, TTL, alasan dipilih dibanding JWT bearer untuk endpoint kiosk tanpa login).
7.3 Tiga Lapis *Soft-Correct Gate* — tabel matriks: lapis × kondisi yang diblok × jalur bypass.
7.4 Rate Limiting dan Anti Brute-Force (`tamdes_rate_limit`, `tamdes_login_attempts`).
7.5 Audit Log dan Penghapusan dengan Cascade (urutan transaksi, isi snapshot, retensi log).
7.6 Penanganan Data Pribadi Konsumen — tulis singkat, defensif, **tanpa** komitmen ke DPIA atau audit privasi penuh. Wajib memuat:
   - Daftar atribut pribadi yang dikumpulkan **persis**: nama, alamat surel (*email*), nama instansi/lembaga, foto wajah, face descriptor 128-dimensi. **Tidak ada** NIK, alamat tempat tinggal, atau nomor telepon yang dikumpulkan dalam rancangan ini — jangan menambahkan atribut yang tidak disebut.
   - Dasar pemrosesan: pelaksanaan tugas pelayanan publik PST sesuai mandat BPS, sejalan dengan UU 27/2022 tentang Pelindungan Data Pribadi (cukup disebut, jangan kutip pasal).
   - Prinsip yang diterapkan dalam rancangan: *data minimization* (hanya kumpulkan yang dipakai untuk identifikasi kunjungan ulang dan pelaporan SKD), *purpose limitation* (data tidak dipakai di luar layanan PST), keterbatasan akses (role-based, audit log untuk penghapusan).
   - Pernyataan eksplisit bahwa kebijakan privasi rinci, retensi terjadwal, dan prosedur permintaan akses/penghapusan oleh pemilik data **diatur di dokumen kebijakan tersendiri di luar lingkup rancangan ini**.
   - **Hindari** janji konkret tentang masa retensi (mis. "data dihapus setelah X tahun") kalau tidak ada implementasi penjadwalan di kode — cukup sebut "retensi diatur sesuai kebijakan unit".
7.7 Ancaman dan Mitigasi (tabel: SQL injection → prepared statement; XSS → sanitisasi React; CSRF — jelaskan posture; replay token → purpose claim; rusaknya kiosk lokal → degradasi anggun ke pencatatan manual).

## BAB VIII RANCANGAN PENGUJIAN

8.1 Strategi Pengujian (unit untuk gate logic, integrasi untuk endpoint kritis, manual UAT per peran).
8.2 Rencana Skenario Uji (tabel: ID, deskripsi, langkah, hasil yang diharapkan) — minimal 20 skenario, sekurangnya satu per peran + satu untuk tiap fitur kunci di §1.5.
8.3 Kriteria Penerimaan v1.0.

## BAB IX RANCANGAN PENGEMBANGAN LANJUT (Roadmap)

9.1 Pengembangan v1.1 (short-term, 1–3 bulan): perbaikan UX dari temuan UAT, pelaporan tambahan, observability.
9.2 Pengembangan v2.0 (medium-term, 6–12 bulan) — kandidat: integrasi single sign-on BPS, dashboard real-time WebSocket, offline-first kiosk (service worker + sync queue), ekspansi DTSEN ke fitur penuh, multi-bahasa.
9.3 Utang Teknis yang Diketahui (referensi ke `docs/AUDIT_2026-05-16.md` tanpa menyalin isinya).
9.4 Kriteria Sunset untuk v1.0.

## BAB X PENUTUP

10.1 Ringkasan keputusan rancangan utama (tabel: keputusan × alasan × konsekuensi yang diterima).
10.2 Acknowledgment kontributor.
10.3 Pintu masuk untuk pengembang penerus (file mana yang dibaca dulu).

## [DAFTAR PUSTAKA]

- PerBPS 2/2021 (juknis Pranata Komputer).
- Pedoman SKD BPS (versi yang dipakai PST).
- Dokumentasi resmi: React, Vite, CodeIgniter 3, MySQL, face-api.js, escpos.
- RFC 2104 (HMAC), RFC 7519 (JWT — sebagai referensi pembanding).

## [LAMPIRAN]

- Lampiran A: Kamus Data lengkap (per tabel — kalau Bab V sudah memuat penuh, sebut "Lihat Bab V" dan pakai lampiran ini untuk versi ringkas/glossary).
- Lampiran B: Daftar Endpoint API lengkap dalam format tabel (method, path, role, deskripsi).
- Lampiran C: Daftar 16 Indikator IKM (dari §1.9).
- Lampiran D: Daftar Kode Sarana Bitmask BPS + 33–36 internal.
- Lampiran E: Glosarium teknis.
- Lampiran F: Riwayat versi dokumen.

---

# 4. Output yang Diharapkan

Hasilkan **satu file Markdown utuh** yang siap di-pipe ke Pandoc. Gunakan heading level yang konsisten (`#` untuk bagian pelengkap, `##` untuk bab, `###` untuk subbab). Jangan singkat — tulis bab demi bab sampai semua selesai, walaupun panjang. Kalau benar-benar harus berhenti di tengah karena batas panjang, akhiri dengan komentar HTML `<!-- LANJUTKAN DARI: ... -->` supaya saya bisa minta sambungan tanpa kehilangan jejak.
```

---

## Blok B — Prompt Pendek (regenerate diagram saja)

> Pakai blok ini kalau dokumen utama sudah jadi tapi Anda mau iterasi satu/dua diagram saja tanpa regenerate semuanya.

```
Tugas: gambar ulang Diagram <NAMA_DIAGRAM> (mis. "Sequence Diagram Submit Evaluasi Tablet") untuk Dokumen Rancangan Sistem Buku Tamu Digital PST BPS Provinsi Maluku Utara.

Konteks ringkas:
- Frontend kiosk React + face-api.js, tablet evaluasi di route /kiosk/evaluasi/:id.
- Backend CodeIgniter modul api, DB MySQL db_tamdes.
- Endpoint terkait: GET /api/evaluations/pending, POST /api/evaluations/{id}.
- Token HMAC eval-submit TTL 10 menit, format {purpose}.{bound_id}.{exp_unix}.{base64url-hmac-sha256}.
- 3-layer soft-correct gate: PUT /api/visits/{id}/status, PUT /api/consultations/{id}, POST /api/evaluations/{id}.
- Tabel: tamdes_evaluasi_detail, tamdes_kunjungan, konsultasi_pengunjung.

Permintaan:
1. Hasilkan diagram dalam blok ```mermaid``` (pilih jenis paling cocok: sequenceDiagram / flowchart / erDiagram / stateDiagram-v2).
2. Sertakan narasi 1–2 paragraf di bawah diagram.
3. Pastikan komponen yang muncul cocok persis dengan konteks di atas — jangan menambah pihak ketiga atau library yang tidak disebut.

Output: hanya diagram + narasi, tidak perlu sampul atau heading bab.
```

---

## Tips Setelah Generate

1. **Cek halaman**: convert ke `.docx` dengan `pandoc rancangan.md -o rancangan.docx --filter mermaid-filter --reference-doc=ref.docx`, hitung halaman di Word. Kalau kurang, minta Claude Desktop "perpanjang Bab V dan Bab VII dengan tabel kamus data + matriks ancaman yang lebih lengkap" — jangan minta "tulis ulang lebih panjang" (hasilnya filler).
2. **Validasi nama tabel/endpoint**: cross-check dengan `grep -rn "tamdes_" backend/application/modules/api/` dan `backend/application/config/routes.php` sebelum sign-off — kalau Claude Desktop salah nama, koreksi manual.
3. **ERD**: render Mermaid `erDiagram` di https://mermaid.live/ dulu untuk verifikasi kardinalitas sebelum committing ke Word.
4. **Hindari overlap dengan Petunjuk Operasional**: kalau di dokumen ini sampai muncul "cara klik tombol X" atau "screenshot halaman Y" → itu ranah Petunjuk Operasional, hapus dari dokumen rancangan.
5. **Halaman pengesahan**: ganti placeholder atasan PPK langsung sebelum dikirim ke asesor.

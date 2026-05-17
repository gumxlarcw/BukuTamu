# Bukutamu — Project Overview & Perbandingan dengan Legacy

**Dokumen ini menjelaskan:**
1. Apa itu sistem Bukutamu (BPS Maluku Utara).
2. Struktur monorepo dan komponen-komponennya.
3. Perbandingan dengan **bukutamu-legacy** (frozen archive di `/var/www/html/bukutamu-legacy/`).
4. **Flow baru pencatatan pengunjung Survei Kebutuhan Data (SKD)** yang tidak ada di legacy.

> **Catatan**: Database `db_tamdes` sengaja TIDAK di-rename. Lihat `backend/application/config/database.php` & root `README.md`.

---

## 1. Ringkasan Proyek

Bukutamu adalah sistem **buku tamu + antrian PST** (Pelayanan Statistik Terpadu) BPS Maluku Utara. Sistem ini menangani:

- **Self-check-in tamu** lewat kiosk (face-recognition + QR / NIK lookup).
- **Cetak nomor antrian** (thermal printer lokal di kiosk).
- **Pemanggilan antrian** ke TV display (`dashboard-pst.bpsmalut.com`).
- **Form layanan PST** (Konsultasi Statistik, Perpustakaan, Rekomendasi, Penjualan, Konsultasi DTSEN).
- **Evaluasi Survei Kebutuhan Data (SKD)** via tablet di akhir kunjungan.
- **Pencatatan pengunjung resepsionis** (Lainnya, Keperluan Pimpinan) tanpa antrian.

### Domain & infrastruktur

| Komponen | Path | Stack | Port |
|---|---|---|---|
| Backend API | `/var/www/html/bukutamu/backend` | CodeIgniter 3 + HMVC (PHP) | :60 HTTP, :460 HTTPS (Apache vhost `bukutamu.bpsmalut.com`) |
| Frontend PWA | `/var/www/html/bukutamu/frontend` | Vite 8 + React 19 + TS, TanStack Query, Tailwind v4 | :3060 (PM2 `bukutamu-frontend`) |
| Print server | `/var/www/html/bukutamu/print` | Node + express + escpos | :5300 (jalan **lokal** di tiap kiosk PC) |
| Legacy (frozen) | `/var/www/html/bukutamu-legacy` | Same stack, snapshot pre-konsolidasi | :61 (`old-bukutamu.bpsmalut.com`), DB SELECT-only |

Detail konsolidasi 2026-05-16: `docs/AUDIT_2026-05-16.md`.

---

## 2. Komponen Backend (HMVC modules)

`backend/application/modules/`:

- `api/` — semua endpoint REST. Controllers extend `Api_base.php` (CORS, auth, role gate, sarana validation).
- `admin/` — view legacy untuk halaman lama (sebagian masih dipakai).
- `layanan/`, `recognize/`, `selamat_datang/` — halaman publik (kiosk view).

API controllers yang aktif di `application/modules/api/controllers/`:

```
Api_base.php       — kelas dasar (auth, role, sarana whitelist, status finalisasi)
Audit.php          — audit log query
Auth.php           — login JWT (cookie httpOnly)
Consultations.php  — antrian Konsultasi SKD inti + form rincian data
Dashboard.php      — agregat dashboard admin
Dtsen.php          — antrian + form Konsultasi DTSEN  ★ TIDAK ADA DI LEGACY
Evaluations.php    — SKD evaluation (16 indikator, skala 1–10)
Guests.php         — registrasi tamu (tamdes_buku)
Kiosk.php          — endpoint kiosk (face recognition, profile gap, ticket info)
Queue_stats.php    — statistik antrian per layanan
Responden.php      — daftar responden tahunan (untuk laporan BPS)
Services.php       — daftar 7 jenis layanan (hardcoded, bukan DB)
Users.php          — CRUD user admin
Visits.php         — CRUD kunjungan (DELETE cascade, soft-correct status)
```

---

## 3. Perbandingan Bukutamu (Baru) vs Legacy

### 3.1 Daftar layanan

**Legacy** (`bukutamu-legacy/application/modules/api/controllers/Services.php:12-19`) — **6 layanan**:

| id | name |
|---|---|
| perpustakaan | Perpustakaan |
| konsultasi | Konsultasi Statistik |
| rekomendasi | Rekomendasi Kegiatan Statistik |
| penjualan | Penjualan Produk Statistik |
| pimpinan | Keperluan Pimpinan |
| lainnya | Lainnya |

**Baru** (`backend/application/modules/api/controllers/Services.php:14-22`) — **7 layanan**:

Tambahan satu entri:

| id | name | Catatan |
|---|---|---|
| **konsultasi_dtsen** | **Konsultasi DTSEN** | **Baru** — di luar kuesioner SKD inti |

### 3.2 Taksonomi 3-tier (vs 2-tier di legacy)

**Legacy** hanya membedakan dua kategori:
- **PST**: 4 layanan (Perpustakaan, Konsultasi, Rekomendasi, Penjualan) → semuanya wajib evaluasi tablet.
- **Resepsionis**: Lainnya + Keperluan Pimpinan → langsung `selesai`.

**Baru** memisahkan 3 grup (mutually exclusive):

| Grup | Layanan | Role petugas | Status finalisasi | Antrian TV |
|---|---|---|---|---|
| **SKD** (Survei Kebutuhan Data) | 4 inti: Perpustakaan, Konsultasi Statistik, Rekomendasi, Penjualan | `petugas_pst` | `menunggu_evaluasi` → `selesai` setelah eval | Ya (prefix `K`/`P`/`R`/`J`) |
| **DTSEN** | Konsultasi DTSEN | `petugas_pst` | `selesai` langsung (skip eval) | Ya (prefix **`D`**) |
| **Resepsionis** | Lainnya, Keperluan Pimpinan | `resepsionis` | `selesai` langsung | Tidak (face-to-face) |

Implementasi: `backend/application/modules/api/controllers/Api_base.php:140-275` (lihat `require_layanan_role`, `validate_no_cross_layanan`, `next_status_after_completion`).

### 3.3 Tabel diff endpoint backend

| Controller | Legacy | Baru | Perbedaan utama |
|---|---|---|---|
| `Api_base.php` | role gate sederhana | + `validate_no_cross_layanan`, `validate_sarana_for_layanan`, `next_status_after_completion` 3-tier, `mint_kiosk_token` HMAC | Defense-in-depth baru |
| `Services.php` | 6 entri | 7 entri (+ DTSEN) | Hardcoded baru |
| `Consultations.php` | Tampilkan semua visit hari ini | **Filter ke 4 inti SKD saja** (DTSEN punya antrian sendiri) + soft-correct status SKD→`menunggu_evaluasi` + strict TV call (abort kalau dashboard timeout) | Antrian PST dipisah dari DTSEN |
| `Dtsen.php` | **tidak ada** | Baru: GET list, PUT status, GET/POST data DTSEN | Modul baru |
| `Evaluations.php` | Filter pending by 4 PST layanan | Sama + skip DTSEN (DTSEN tidak masuk pending eval) | Konsisten dengan 3-tier |
| `Visits.php` | CRUD basic | + DELETE cascade ke 3 child table (`konsultasi_pengunjung`, `dtsen_konsultasi`, `tamdes_evaluasi_detail`) + audit log sebelum delete | Operasi admin |
| `Kiosk.php` | kiosk view + face recognition | + `mint_kiosk_token` continuation token (profile-update 5min, eval-submit 10min) | Token short-lived |
| `Responden.php` | identik | identik (line-for-line) | — |

### 3.4 Skema DB yang sama vs baru

| Tabel | Legacy | Baru | Catatan |
|---|---|---|---|
| `tamdes_buku` | ✓ | ✓ | Identitas tamu (NIK, nama, instansi, dst.) |
| `tamdes_kunjungan` | ✓ | ✓ | Per-visit (jenis_layanan JSON, sarana JSON, status, rating, dst.) |
| `konsultasi_pengunjung` | ✓ | ✓ | Multi-row per visit (rincian data) — skema **sama persis** |
| `tamdes_evaluasi_detail` | ✓ | ✓ | 16 indikator SKD per visit, skala Likert 1–10 |
| `dtsen_konsultasi` | **tidak ada** | **baru** | 1 row per visit DTSEN, kolom: `jenis_konsultasi_dtsen`, `hasil`, `catatan`, `nik_dirujuk` |
| `audit_log` | ✓ | ✓ + lebih dipakai (capture sebelum DELETE, capture call-queue-failed) | |

---

## 4. Flow Baru Pencatatan Pengunjung SKD (Survei Kebutuhan Data)

> **Inti perbedaan**: di legacy, *setiap visit PST otomatis → `menunggu_evaluasi`* tanpa kemungkinan dibedakan. Di sistem baru, hanya **4 layanan inti SKD** yang trigger evaluasi tablet — sementara DTSEN (sama-sama PST role) **bypass** evaluasi.

### 4.1 Definisi SKD

**SKD = Survei Kebutuhan Data**. Kuesioner kepuasan BPS terhadap 4 layanan inti PST:

1. **Perpustakaan**
2. **Konsultasi Statistik**
3. **Rekomendasi Kegiatan Statistik**
4. **Penjualan Produk Statistik**

Diukur lewat **16 indikator IKM** (Indeks Kepuasan Masyarakat) skala 1–10, plus **rating keseluruhan** 1–10, plus **rating kualitas per data** untuk konsultasi yang menghasilkan data (`status_data ∈ {1, 2}`).

Indikator lengkap di `backend/application/modules/api/controllers/Evaluations.php:228-247`.

### 4.2 Flow end-to-end (baru)

```
┌─────────────────────────────────────────────────────────────────────┐
│  KIOSK (browser di PC tamu, face recognition warmup 600ms → match)  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
            Tamu pilih jenis layanan (grid 4 kolom).
            ── validate_no_cross_layanan ──
            FE blok kalau mix SKD + DTSEN + Resepsionis.
                              │
                              ▼
        Tamu pilih sarana (grid 3 kolom).
        ── validate_sarana_for_layanan ──
        SKD whitelist: [1, 2, 4, 9, 16, 32]
        DTSEN whitelist: [1]
        Resepsionis whitelist: [33, 34, 35, 36]
                              │
                              ▼
              POST /api/visits → tamdes_kunjungan
              status awal: 'menunggu' (atau 'antri')
              nomor_antrian di-generate sesuai prefix grup:
                K / P / R / J / D
                              │
                              ▼
              Cetak ticket via http://localhost:5300/print
              (escpos lib jalan lokal di kiosk PC)
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│  PETUGAS PST (browser admin)                                         │
│  GET /api/consultations  → HANYA 4 inti SKD (DTSEN tidak muncul)    │
│  GET /api/dtsen          → HANYA Konsultasi DTSEN                   │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
        Petugas klik "Panggil" → PUT /api/visits/{id}/call
        ── STRICT MODE ──
        Dashboard PST POST update-antrian.
        Kalau dashboard timeout / 5xx → 502 + audit_log, DB TIDAK update.
        (legacy: optimistic update, status bisa drift dari kondisi TV)
                              │
                              ▼
        Petugas isi form rincian_data per item:
          - rincian_data, wilayah_data, tahun_awal/akhir
          - level_data, periode_data
          - status_data (1=Ya sesuai, 2=Ya tidak sesuai, 3=Tidak ada)
          - jenis_publikasi, judul_publikasi, tahun_publikasi
          - digunakan_nasional
        (multi-row di konsultasi_pengunjung, sama dgn legacy)
                              │
                              ▼
        PUT /api/visits/{id} { status: 'selesai' }
        ── SOFT-CORRECT GATE ──
        Kalau visit SKD & role bukan bypass (admin/superadmin/operator):
          status di-paksa 'menunggu_evaluasi' (bukan 'selesai').
        Kalau DTSEN / Resepsionis: 'selesai' langsung.
        (legacy: tidak ada gate ini — depend on FE saja)
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│  TABLET EVALUASI (di front desk PST)                                 │
│  GET /api/evaluations/pending                                        │
│   → kembalikan visit terlama dgn status menunggu_evaluasi            │
│     dan layanan termasuk SKD-4 (DTSEN di-skip walau menunggu_eval)  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
        Tamu isi 16 indikator skala 1–10 + skor keseluruhan 1–10
        + kualitas per data (untuk konsultasi yg status_data ∈ {1,2}).
                              │
                              ▼
        POST /api/evaluations/{id}
        ── GATE LAYER 3: visit-must-be-eval-eligible ──
        Reject kalau visit bukan termasuk SKD-4.
        (defense in depth — kalau FE somehow submit eval untuk DTSEN/Resep)
                              │
                              ▼
        UPDATE tamdes_kunjungan SET
          rating_pengunjung, status='selesai',
          selesai_timestamp, durasi_detik
        INSERT tamdes_evaluasi_detail (16 rows kepuasan)
        UPDATE konsultasi_pengunjung.kualitas (per item)
```

### 4.3 Tiga gate finalisasi (defense-in-depth)

Yang membedakan flow baru dari legacy paling kuat: **3 lapis gate** yang mencegah SKD-eligible visit lolos ke `selesai` tanpa evaluasi.

| Lapis | Kode | Lokasi |
|---|---|---|
| **1. Visits status PUT soft-correct** | Kalau payload `status='selesai'` tapi visit termasuk SKD-4 → koreksi ke `menunggu_evaluasi` (kecuali role bypass) | `backend/application/modules/api/controllers/Visits.php` (PUT handler) |
| **2. Consultations status PUT soft-correct** | Sama, tapi di endpoint yang dipakai antrian PST | `backend/application/modules/api/controllers/Consultations.php` (PUT handler) |
| **3. Evaluations POST gate** | Reject submit eval untuk visit yang bukan SKD-4 (DTSEN/Resepsionis tidak bisa submit eval secara aksidental) | `backend/application/modules/api/controllers/Evaluations.php` (POST detail) |

**Legacy hanya punya filter di Evaluations.pending** (read-side), tidak ada write-side gate. Konsekuensi: di legacy, sebuah visit Resepsionis yang status-nya somehow ter-set `menunggu_evaluasi` (mis. dari edit manual di phpMyAdmin) **akan muncul di tablet evaluasi**. Di sistem baru, walau status ke-set demikian, evaluasi tetap tidak bisa submit.

### 4.4 Pemisahan antrian SKD vs DTSEN

| Aspek | Antrian SKD | Antrian DTSEN |
|---|---|---|
| Endpoint backend | `GET /api/consultations` (filter 4 inti SKD) | `GET /api/dtsen` (filter `Konsultasi DTSEN`) |
| Prefix nomor antrian | `K` Konsultasi, `P` Perpustakaan, `R` Rekomendasi, `J` Penjualan | `D` |
| Page frontend | `/admin/consultations`, `/admin/consultations/{id}/form` | `/admin/dtsen`, `/admin/dtsen/{id}/form` |
| Form data | Multi-row `konsultasi_pengunjung` (kolom rincian data, status_data, kualitas, dll.) | Single-row `dtsen_konsultasi` (jenis_konsultasi_dtsen, hasil, nik_dirujuk, catatan) |
| Status finalisasi | `menunggu_evaluasi` → eval tablet → `selesai` | `selesai` langsung |
| Muncul di tablet eval? | Ya | **Tidak** |
| Muncul di Responden Tahunan SKD-only filter? | Ya | **Tidak** (filter `eligible_services` exclude DTSEN) |

Per memory `dtsen_flow`: "Konsultasi DTSEN itu form khusus berbeda dengan 4 layanan PST" + "Antrian DTSEN terpisah" + "prefix queue D" — domain mandate dari user, bukan refactor.

### 4.5 Cross-group block (baru)

Legacy memungkinkan tamu pilih kombinasi apapun (mis. Perpustakaan + Keperluan Pimpinan dalam satu visit). Sistem baru **menolak** kombinasi cross-group:

```
SKD ⊕ DTSEN ⊕ Resepsionis  (mutually exclusive)
```

Implementasi: `Api_base::validate_no_cross_layanan()` (BE) + `isCrossLayanan` di `frontend/src/lib/role-access.ts` (FE).

Kenapa? Setiap grup punya **role petugas berbeda**, **status finalisasi berbeda**, dan **set sarana yang valid berbeda**. Kalau mix, akan ambigu siapa yang harus selesaikan visit.

### 4.6 Sarana whitelist per grup (baru)

| Grup | Kode sarana yang boleh |
|---|---|
| SKD | `1` (datang langsung), `2` (email), `4` (telepon), `9` (web), `16` (medsos), `32` (surat) |
| DTSEN | `1` (datang langsung saja) |
| Resepsionis | `33` (R. Halmahera), `34` (R. Vicon), `35` (R. Gamalama), `36` (R. Pimpinan) |

Kode 1-32 = bitmask BPS standar. Kode 33+ = sekuensial custom untuk ruangan internal. Legacy tidak punya validasi sisi grup — sarana code apapun bisa di-attach ke layanan apapun.

Implementasi: `Api_base::validate_sarana_for_layanan()` (BE) + `getAllowedSaranaCodes` di FE.

### 4.7 DELETE cascade & audit (baru)

`DELETE /api/visits/{id}` — admin/superadmin only:

1. `audit_log` capture state sebelum delete (snapshot full row).
2. DELETE `konsultasi_pengunjung WHERE id_kunjungan = ?`
3. DELETE `dtsen_konsultasi WHERE id_kunjungan = ?`
4. DELETE `tamdes_evaluasi_detail WHERE id_kunjungan = ?`
5. DELETE `tamdes_kunjungan WHERE id_kunjungan = ?`

**Implikasi maintenance**: kalau menambah child table baru yang referensi `id_kunjungan`, **wajib** update cascade chain ini. Lihat memory `admin_delete_visit`.

Legacy tidak punya endpoint DELETE — delete manual via phpMyAdmin akan meninggalkan orphan rows.

### 4.8 Kiosk continuation token (baru)

Endpoint kiosk yang tidak login (mis. update profile gap, submit evaluasi tablet) sekarang menggunakan **HMAC-signed short-lived token**:

```
{purpose}.{bound_id}.{exp_unix}.{base64url-hmac-sha256}
```

- `profile-update` token: 5 menit, di-mint oleh `Kiosk::profile_gaps`.
- `eval-submit` token: 10 menit, di-mint oleh `Evaluations::pending`.

Token dikirim via header `X-Kiosk-Token` atau body `kiosk_token`. `purpose` claim mencegah token di-replay lintas endpoint. Implementasi: `Api_base::mint_kiosk_token()` + `require_kiosk_token()`.

Legacy: endpoint kiosk publik tanpa token — bisa di-replay dari mana saja yang bisa reach API.

---

## 5. Ringkasan: Apa yang Baru?

Checklist fitur yang **TIDAK ADA** di `/var/www/html/bukutamu-legacy/` tapi ADA di sistem baru:

- [x] Layanan **Konsultasi DTSEN** + tabel `dtsen_konsultasi` + controller `Dtsen.php` + halaman admin DTSEN
- [x] **3-tier service taxonomy**: SKD / DTSEN / Resepsionis (legacy: 2-tier)
- [x] **Cross-layanan validation**: tolak mix antar-grup
- [x] **Sarana whitelist per grup**: SKD/DTSEN/Resepsionis punya set sarana valid berbeda
- [x] **Soft-correct status gate** di Visits PUT & Consultations PUT (paksa SKD ke `menunggu_evaluasi`)
- [x] **Eval-side gate**: POST evaluasi reject visit non-SKD
- [x] **Strict-mode TV queue call**: dashboard timeout → 502 + audit, DB tidak update
- [x] **Antrian PST difilter**: HANYA 4 inti SKD (DTSEN punya antrian sendiri)
- [x] **Prefix nomor antrian per layanan**: `K`/`P`/`R`/`J`/`D`
- [x] **DELETE visit** dengan cascade ke 3 child + audit log capture
- [x] **Kiosk HMAC continuation token** (profile-update + eval-submit)
- [x] **Face recognition pipeline** dengan warmup 600ms + averaging 5 descriptor + threshold 0.55/margin 0.08
- [x] **Print server lokal di kiosk PC** (browser fetch ke `localhost:5300` langsung — backend tidak mediate)

Yang **identik** dari legacy (jangan refactor tanpa alasan):

- Skema `konsultasi_pengunjung` (kolom `rincian_data`, `wilayah_data`, `tahun_awal/akhir`, `level_data`, `periode_data`, `status_data`, `jenis_publikasi`, `judul_publikasi`, `tahun_publikasi`, `digunakan_nasional`, `kualitas`)
- 16 indikator SKD + skor keseluruhan 1–10 + kualitas per data
- Skema `tamdes_buku` + `tamdes_kunjungan` + `tamdes_evaluasi_detail`
- Controller `Responden.php` (line-for-line)
- Eligible services untuk SKD eval (4 inti)

---

## 6. Reference Files

| Topik | Path |
|---|---|
| Backend gate utama | `backend/application/modules/api/controllers/Api_base.php` |
| Antrian SKD | `backend/application/modules/api/controllers/Consultations.php` |
| Antrian DTSEN | `backend/application/modules/api/controllers/Dtsen.php` |
| Evaluasi SKD | `backend/application/modules/api/controllers/Evaluations.php` |
| DELETE cascade | `backend/application/modules/api/controllers/Visits.php` |
| Frontend role mirror | `frontend/src/lib/role-access.ts` |
| Kiosk service picker | `frontend/src/pages/kiosk/ServiceSelectPage.tsx` |
| Audit lengkap | `docs/AUDIT_2026-05-16.md` |
| Runbook rotasi | `docs/REMAINING_PHASE_B.md` |
| Legacy snapshot | `/var/www/html/bukutamu-legacy/` (READ-ONLY) |

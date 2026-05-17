# Changelog

Semua perubahan signifikan pada sistem Bukutamu BPS Maluku Utara dicatat di file ini.

Format mengikuti [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) dan project menganut [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Documentation
- Tambah `docs/BUKUTAMU_VS_LEGACY.md` — perbandingan lengkap monorepo baru vs PHP legacy (`/var/www/html/bukutamu-legacy/`): diff layanan, taksonomi 3-tier SKD/DTSEN/Resepsionis, diff endpoint controller, skema DB yang sama vs baru, 3-layer gate finalisasi.
- Tambah `docs/SKD_FLOW_PAPER_VS_DIGITAL.md` — flow diagram before/after sistem buku tamu paper-based vs digital terintegrasi. Termasuk swim-lane ASCII, Mermaid flowchart, Mermaid sequence diagram, side-by-side per-step comparison, dan eliminasi double-entry biodata SKD.

---

## [1.0.0] — 2026-05-16

Konsolidasi tiga repo terpisah (`tamdes-web`, `tamdes-frontend`, `tamdes-print`) menjadi satu monorepo `bukutamu/` dengan history preserved via `git subtree add`.

### Added — Fitur Baru yang Tidak Ada di Era Legacy PHP

#### Domain layer
- **Layanan Konsultasi DTSEN** (Data Terpadu Sosial Ekonomi Nasional) — layanan ke-7, alur terpisah dari 4 inti SKD. PST-role tapi skip evaluasi tablet.
- **Tabel `dtsen_konsultasi`** — single-row per visit (vs multi-row `konsultasi_pengunjung`). Kolom: `jenis_konsultasi_dtsen` (TINYINT 1-5), `hasil` (TINYINT 1-3), `catatan`, `nik_dirujuk`.
- **Taksonomi 3-tier** SKD / DTSEN / Resepsionis (legacy: 2-tier PST vs Resepsionis saja).
- **Prefix nomor antrian per layanan**: `K` (Konsultasi Statistik), `P` (Perpustakaan), `R` (Rekomendasi), `J` (Penjualan), `D` (DTSEN). Mempermudah operator membedakan antrian di TV.

#### Defense-in-depth gate
- **Cross-layanan validation** (`Api_base::validate_no_cross_layanan`) — tolak kombinasi SKD ⊕ DTSEN ⊕ Resepsionis dalam satu visit.
- **Sarana whitelist per grup** (`Api_base::validate_sarana_for_layanan`) — SKD: [1,2,4,9,16,32]; DTSEN: [1]; Resepsionis: [33,34,35,36].
- **Soft-correct status gate** di `Visits::status` dan `Consultations::detail` — kalau payload `status='selesai'` tapi visit termasuk SKD-4, BE diam-diam koreksi ke `menunggu_evaluasi` (kecuali bypass role admin/superadmin/operator).
- **Auto-transition** di `Consultations::data` POST — simpan data konsultasi otomatis transisi status via `next_status_after_completion()` (tidak ada bypass — domain rule).
- **Eval-side gate** di `Evaluations::detail` POST — reject submit evaluasi untuk visit non-SKD-4 (defense terhadap manipulasi DB manual).
- **Helper `next_status_after_completion()` di `Api_base.php`** — single source of truth untuk aturan "layanan apa yang trigger evaluasi tablet". Dipakai 3 tempat di BE.

#### Antrian PST
- **Filter antrian Konsultasi**: `GET /api/consultations` hanya menampilkan 4 layanan inti SKD (DTSEN punya antrian sendiri di `/api/dtsen`).
- **Strict-mode TV queue call**: kalau `dashboard-pst.bpsmalut.com` timeout / 5xx saat `PUT /api/visits/{id}/call`, BE return 502 + audit log, **status DB tidak di-update**. Status DB harus selalu reflect kondisi TV display.

#### Admin operations
- **DELETE visit dengan cascade**: `DELETE /api/visits/{id}` (admin/superadmin only) menghapus dengan urutan: audit_log capture → `konsultasi_pengunjung` → `dtsen_konsultasi` → `tamdes_evaluasi_detail` → `tamdes_kunjungan`.
- **Audit log expanded** — capture state penuh sebelum DELETE, plus `call_queue_failed` event saat strict mode reject.

#### Kiosk security
- **HMAC continuation token** untuk endpoint kiosk tanpa login. Format `{purpose}.{bound_id}.{exp_unix}.{base64url-hmac-sha256}`:
  - `profile-update` token (5 menit) — di-mint oleh `Kiosk::profile_gaps`.
  - `eval-submit` token (10 menit) — di-mint oleh `Evaluations::pending`.
  - `purpose` claim mencegah token di-replay lintas endpoint.

#### Frontend
- **Face recognition pipeline** — warmup 600ms → sampling 5 descriptor → averaging → match dengan threshold 0.55 + margin 0.08.
- **Typed API clients** (Axios + TanStack Query) untuk admin endpoints (closes audit M7).
- **Role-access mirror** di `frontend/src/lib/role-access.ts` — cermin lengkap dari `Api_base.php` (fungsi `getServiceGroup`, `isCrossLayanan`, `getAllowedSaranaCodes`, `canFinalizeLayanan`).

#### Print server
- **Print server lokal di kiosk PC** — Node + escpos lib jalan di `localhost:5300` di komputer kiosk masing-masing. Backend bukutamu **tidak** mediate print (no long-distance network failure mode).
- **Payload field aliases** — `no` dan `nomor_antrian` keduanya diterima untuk backward compat.

### Changed

- **Layanan list bertambah dari 6 → 7** di `Services.php` (tambah `konsultasi_dtsen`).
- **Role mapping** di `Api_base.php` tiga fungsi (`require_layanan_role`, `validate_no_cross_layanan`, `next_status_after_completion`) — semua mirror service taxonomy 3-tier.
- **Folder layout**: `tamdes-web` → `bukutamu/backend`, `tamdes-frontend` → `bukutamu/frontend`, `tamdes-print` → `bukutamu/print`.
- **PM2 process name**: `tamdes-frontend` → `bukutamu-frontend` (id 27, port 3060). Ecosystem config hoisted ke root `bukutamu/ecosystem.config.cjs`.
- **Apache vhost**: `bukutamu.bpsmalut.com` melayani backend di port 60 (HTTP) / 460 (HTTPS).
- **Monitoring profile**: `/opt/monitoring/log_profiles.json` key `tamdes-frontend` di-rename ke `bukutamu-frontend`.
- **r2-video-proxy PM2 cwd**: dari `/var/www/html/tamdes-web` ke `/var/www/html/bukutamu/backend` (script-nya sendiri tetap di `/var/www/html/r2-video-proxy.js`).

### Security

- **Soft rate-limit** untuk endpoint kiosk enumeration (mitigates audit M4).
- **MySQL credential rotation Phase B** — 9 service migrated off MySQL root + root password rotated.
- **Admin password hash rotation** — `ADMIN_PASSWORD_HASH` env var sebagai single source.
- **JWT_SECRET** untuk token kiosk HMAC — environment variable, bukan hardcoded.
- **CORS allowed origins** — explicit whitelist (`localhost:5173` dev + `FRONTEND_URL` env).

### Deprecated / Removed

- **Legacy CI3 modules** (closes audit C2 + M2):
  - `application/controllers/Evaluasi.php` top-level duplicate
  - View `selamat_datang/check` (old "new/existing" picker)
  - View `selamat_datang/masuk` (old visit creator dengan bug port 5000 — sudah fixed)
  - View `antrian/cetak/{nomor}` (old print iframe)
- **Non-prod fallback `admin/admin123`** removed kalau env unset.
- **2-tier service taxonomy** (PST vs Resepsionis) di-replace dengan 3-tier (SKD / DTSEN / Resepsionis).

### Fixed

- **FK & insert failure surface** di `Kiosk::register` dan `Kiosk::visit` — error message yang sebenarnya di-return ke FE (sebelumnya silent failure).
- **Print port bug** — view legacy memanggil `localhost:5000/print` (port salah). Fixed di flow baru: kiosk PWA fetch `localhost:5300`.
- **Antrian status drift** — strict mode TV call mencegah status DB out-of-sync dengan TV display.

### Database

- **Schema yang DIPERTAHANKAN dari legacy** (jangan refactor tanpa alasan):
  - `tamdes_buku` (identitas tamu)
  - `tamdes_kunjungan` (per-visit, kolom `jenis_layanan` JSON array string)
  - `konsultasi_pengunjung` (multi-row data konsultasi — kolom `rincian_data`, `wilayah_data`, `tahun_awal/akhir`, `level_data`, `periode_data`, `status_data`, `jenis_publikasi`, `judul_publikasi`, `tahun_publikasi`, `digunakan_nasional`, `kualitas`)
  - `tamdes_evaluasi_detail` (16 indikator SKD per visit, skala Likert 1-10)
- **Schema BARU**:
  - `dtsen_konsultasi` (1 row per visit DTSEN)
- **Catatan**: nama database `db_tamdes` **sengaja tidak di-rename** ke `db_bukutamu` — risk vs zero user-visible benefit. Env var `DB_DATABASE` bisa override kalau rename dilakukan di kemudian hari.

### Operational

- **Rollback safety net** sampai 2026-05-23:
  - `/var/www/html/tamdes-web.frozen-20260516`
  - `/var/www/html/tamdes-frontend.frozen-20260516`
  - `/var/www/html/tamdes-print.frozen-20260516`
  - Snapshot DB di `/var/backups/bukutamu-cutover-20260516-1541/`
- **Legacy archive** tetap online di `old-bukutamu.bpsmalut.com:61` (READ-ONLY, DB user SELECT-only).
- **Pending Phase 9** (2026-05-23+): delete frozen folders (~1.4 GB recoverable).

---

## Era Paper-Based (Pre-Bukutamu) — Historical Reference

> Bukan rilis software — konteks kenapa Bukutamu dibangun.

**Sebelum digital**, pelayanan PST dijalankan paper-based:

- Buku tamu fisik di meja resepsionis (tulis tangan: nama, NIK, instansi, no telp, keperluan).
- Petugas catat manual di buku besar/Excel internal saat melayani.
- Setelah dilayani, tamu diberi **lembar kuesioner SKD kertas** — harus isi **biodata ulang** plus 16 indikator IKM.
- Kuesioner masuk kotak suara → rekap manual akhir bulan/kuartal di Excel.
- Laporan IKM tahunan butuh tally manual nama instansi (sering double-count karena variasi ejaan).

**Pain points era ini**:
- Biodata ditulis 2-3 kali per kunjungan.
- Response rate kuesioner SKD ~30-40% (kuesioner hilang/lembar terselip).
- Inkonsistensi data instansi.
- Latency rekap IKM 2-3 minggu post-kuartal.
- Tidak ada audit trail / link tamu ↔ evaluasi.

**Resolusi**: sistem digital `bukutamu` (sejak PHP era) menghilangkan double-entry via face recognition + auto-link biodata via `id_kunjungan` FK. Detail lengkap di `docs/SKD_FLOW_PAPER_VS_DIGITAL.md`.

---

## Catatan Versioning

Versi 1.0.0 dipilih sebagai marker konsolidasi monorepo 2026-05-16. History pre-konsolidasi dipertahankan via `git subtree add` — jalankan `git log -- backend/` atau `git log -- frontend/` untuk melihat commit asal di bawah masing-masing prefix.

Untuk reference snapshot PHP app pre-konsolidasi, lihat:
- Disk: `/var/www/html/bukutamu-legacy/` (frozen, SELECT-only DB user)
- Domain: `old-bukutamu.bpsmalut.com:61`
- Plan dokumen: `docs/AUDIT_2026-05-16.md`

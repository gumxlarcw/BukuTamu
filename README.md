# Bukutamu — BPS Malut buku-tamu / antrian PST

Monorepo containing the three components of the BPS Maluku Utara buku-tamu (visitor book) / antrian PST (queueing) system:

- **`backend/`** — CodeIgniter PHP API (was `tamdes-web` / `gumxlarcw/BukuTamu`)
- **`frontend/`** — Vite + React + TS PWA (was `tamdes-frontend` / `gumxlarcw/BukuTamu-frontend`)
- **`print/`** — Node + escpos kiosk thermal-print source (was `tamdes-print`, copied to kiosk PCs)

Both `backend/` and `frontend/` histories preserved via `git subtree add` during consolidation on 2026-05-16. Run `git log -- backend/` or `git log -- frontend/` to see the original commit history under each prefix.

## What's new vs legacy

Sistem ini menggantikan dua "legacy" sekaligus:

1. **Era paper-based** — buku tamu fisik + kuesioner SKD kertas yang minta tamu mengisi biodata 2-3 kali. Dipecahkan dengan face recognition + auto-link via `id_kunjungan` FK.
2. **PHP app pre-konsolidasi** (`/var/www/html/bukutamu-legacy/`) — sudah digital tapi belum punya DTSEN, taksonomi 3-tier, atau 3-layer gate finalisasi. Monorepo ini menambahkan domain hardening tersebut.

Highlights yang **baru di monorepo**:

- **Layanan Konsultasi DTSEN** + tabel `dtsen_konsultasi` + antrian terpisah dengan prefix queue `D`
- **Taksonomi 3-tier**: SKD (4 inti, evaluasi tablet) / DTSEN (PST role, skip eval) / Resepsionis (front-office)
- **3-layer defense-in-depth gate** mencegah SKD-eligible visit lolos ke `selesai` tanpa evaluasi
- **Strict-mode TV queue call** — abort DB update kalau dashboard PST tidak respon
- **Cross-layanan & sarana whitelist** per grup layanan
- **HMAC continuation token** untuk endpoint kiosk tanpa login (anti-replay)
- **DELETE cascade** ke 3 child table + audit log capture
- **Face recognition** dengan warmup 600ms + averaging 5 descriptor

Detail lengkap:

- [`CHANGELOG.md`](./CHANGELOG.md) — log perubahan terstruktur
- [`docs/BUKUTAMU_VS_LEGACY.md`](./docs/BUKUTAMU_VS_LEGACY.md) — perbandingan code-level monorepo vs PHP legacy
- [`docs/SKD_FLOW_PAPER_VS_DIGITAL.md`](./docs/SKD_FLOW_PAPER_VS_DIGITAL.md) — flow diagram paper-based → digital (Mermaid + ASCII swim-lane)
- [`docs/AUDIT_2026-05-16.md`](./docs/AUDIT_2026-05-16.md) — audit komprehensif post-konsolidasi

## Note on DB naming

The MySQL database remains named `db_tamdes` (see `backend/application/config/database.php`). Renaming a live production DB carries real risk for zero user-visible benefit, so the asymmetry is deliberate. The `DB_DATABASE` env var can override the default if a rename is ever performed later.

## Process management

- `backend/` is served by Apache vhost `bukutamu.bpsmalut.com` on ports `:60` (HTTP) and `:460` (HTTPS).
- `frontend/` runs under PM2 as `bukutamu-frontend` from the root `ecosystem.config.cjs`.
- `print/` is **not** run on this server — it is the canonical reference; kiosk PCs copy `print/` and run it locally on `localhost:5300`.

## Public domains

- `bukutamu.bpsmalut.com` — production PST kiosk + admin
- `old-bukutamu.bpsmalut.com` — frozen archive (read-only, separate DB, separate folder)

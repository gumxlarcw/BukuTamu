# Bukutamu — BPS Malut buku-tamu / antrian PST

Monorepo containing the three components of the BPS Maluku Utara buku-tamu (visitor book) / antrian PST (queueing) system:

- **`backend/`** — CodeIgniter PHP API (was `tamdes-web` / `gumxlarcw/BukuTamu`)
- **`frontend/`** — Vite + React + TS PWA (was `tamdes-frontend` / `gumxlarcw/BukuTamu-frontend`)
- **`print/`** — Node + escpos kiosk thermal-print source (was `tamdes-print`, copied to kiosk PCs)

Both `backend/` and `frontend/` histories preserved via `git subtree add` during consolidation on 2026-05-16. Run `git log -- backend/` or `git log -- frontend/` to see the original commit history under each prefix.

## Note on DB naming

The MySQL database remains named `db_tamdes` (see `backend/application/config/database.php`). Renaming a live production DB carries real risk for zero user-visible benefit, so the asymmetry is deliberate. The `DB_DATABASE` env var can override the default if a rename is ever performed later.

## Process management

- `backend/` is served by Apache vhost `bukutamu.bpsmalut.com` on ports `:60` (HTTP) and `:460` (HTTPS).
- `frontend/` runs under PM2 as `bukutamu-frontend` from the root `ecosystem.config.cjs`.
- `print/` is **not** run on this server — it is the canonical reference; kiosk PCs copy `print/` and run it locally on `localhost:5300`.

## Public domains

- `bukutamu.bpsmalut.com` — production PST kiosk + admin
- `old-bukutamu.bpsmalut.com` — frozen archive (read-only, separate DB, separate folder)

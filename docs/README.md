# Dokumentasi Sistem BukuTamu BPS Malut

Aplikasi **BukuTamu** (`bukutamu.bpsmalut.com`) adalah sistem manajemen layanan tamu Pelayanan Statistik Terpadu (PST) BPS Provinsi Maluku Utara. Sistem mencakup kiosk pendaftaran tamu (dengan face recognition), antrian konsultasi, evaluasi layanan (IKM), dan dashboard administrasi.

## 📚 Daftar Dokumen

| Dokumen | Untuk siapa | Isi |
|---|---|---|
| **[ARCHITECTURE.md](./ARCHITECTURE.md)** | Developer, DevOps | Diagram arsitektur, dua-stack pattern, skema database, integrasi eksternal, alur autentikasi JWT |
| **[API.md](./API.md)** | Developer (frontend/backend), integrator | Referensi lengkap 40+ endpoint REST: method, auth, request, response, error codes |
| **[DEVELOPER_GUIDE.md](./DEVELOPER_GUIDE.md)** | Developer baru, maintainer | Setup lokal, build, deploy, struktur folder, common tasks, troubleshooting |
| **[USER_GUIDE.md](./USER_GUIDE.md)** | Admin PST, operator front-office, tamu | Cara menggunakan kiosk & dashboard, manage data tamu, panggil antrian, lihat IKM |

## 🚀 Quick Links

- **URL Produksi:** <https://bukutamu.bpsmalut.com>
- **Login Admin:** <https://bukutamu.bpsmalut.com/login>
- **Kiosk Pendaftaran:** <https://bukutamu.bpsmalut.com/kiosk>
- **API Base:** `https://bukutamu.bpsmalut.com/api/`

## 🏗️ Ringkasan Singkat

```
                bukutamu.bpsmalut.com (Apache port 60/460)
                              │
              ┌───────────────┴────────────────┐
              │                                │
       /api/* + /index.php          Semua URL lain
              │                                │
              ▼                                ▼
       tamdes-web (PHP 7.4)         tamdes-frontend (React 19)
       CodeIgniter 3 + HMVC         Vite 8 + TS + Tailwind v4
       JWT auth, MySQL              PM2 process port 3060
       = BACKEND API                = FRONTEND UI/SPA
```

Dua codebase, satu produk. Backend menyediakan REST API + business logic + akses database; frontend memberikan SPA modern untuk kiosk (tablet/touchscreen) dan dashboard admin.

## 🛠️ Stack Teknologi

| Layer | Teknologi |
|---|---|
| Backend | PHP 7.4, CodeIgniter 3 + HMVC (MX), MySQL/MariaDB |
| Frontend | React 19, Vite 8, TypeScript 5.9, Tailwind v4, TanStack Query |
| Auth | JWT HS256 (cookie httpOnly, 4 jam) |
| Web Server | Apache 2.4 (mod_proxy + mod_rewrite + PHP-FPM 7.4) |
| Process Manager | PM2 (frontend serve dist) |
| Auxiliary | Print server lokal (port 5000), Layar antrian eksternal (`dashboard-pst.bpsmalut.com`) |

## 📞 Kontak & Dukungan

- **Penanggung jawab teknis:** Tim IT BPS Provinsi Maluku Utara
- **Repository code:** lokasi server `/var/www/html/tamdes-web` (backend) & `/var/www/html/tamdes-frontend` (frontend)
- **Logs:** `/var/log/apache2/bukutamu60_*.log`, `pm2 logs tamdes-frontend`

---

> Dokumentasi ini ditulis berdasarkan kode aktual di branch `main` per April 2026. Jika ada perbedaan antara dokumentasi dan kode, **kode adalah sumber kebenaran** — silakan update dokumen yang relevan.

# BukuTamu (CodeIgniter HMVC)

Aplikasi Buku Tamu Front Office/PST dengan arsitektur **CodeIgniter 3 + HMVC (Modular Extensions MX)**.  
Dokumen ini merangkum struktur, modul, serta langkah instalasi & konfigurasi berdasarkan struktur proyek yang diberikan.

---

## 📖 Ringkasan

Fungsi utama aplikasi (berdasarkan modul & view yang tersedia):
- **Selamat Datang**: halaman depan, pemilihan status, cetak nomor antrian.
- **Antrian**: manajemen & pencetakan nomor antrian (kiosk).
- **Layanan**: form layanan/konsultasi untuk tamu/pengunjung.
- **Admin**: dashboard, login, daftar tamu/kunjungan, detail, tambah/edit data, antrian konsultasi.
- **Recognize**: halaman pengenalan (kamera/QR/face recognition).
- **Evaluasi**: form evaluasi layanan dan halaman tunggu/selesai.

---

## 🛠️ Stack & Ketergantungan

- **PHP**: 7.4+ disarankan (kompatibel CI3), aktifkan ekstensi umum (mysqli, mbstring, intl, gd).
- **Web Server**: Apache/Nginx.
- **Database**: MySQL/MariaDB.
- **Framework**: CodeIgniter 3 + HMVC (Modular Extensions MX).
- **Front-end**: Bootstrap/jQuery (asumsi dari view).
- (Opsional) Kamera/JS untuk fitur recognize.

---

## 🗂️ Struktur Utama

```
application/
├─ config/                 # Config CI (base_url, autoload, database, routes, dll.)
├─ controllers/            # Controller global (Evaluasi.php) + index.html
├─ core/                   # MY_Controller, MY_Loader, MY_Router (kustom HMVC)
├─ helpers/                # konsultasi_helper.php
├─ language/               # Stub bahasa
├─ libraries/              # Layout.php (wrapper layout/view)
├─ logs/                   # Log CI
├─ models/                 # (belum ada model global)
├─ modules/                # HMVC modules:
│  ├─ admin/
│  │  ├─ controllers/Admin.php
│  │  ├─ models/M_admin.php
│  │  └─ views/
│  │     ├─ dashboard.php, daftar_tamu.php, daftar_kunjungan.php
│  │     ├─ antrian_konsultasi.php, detail_kunjungan.php, detail_modal.php
│  │     ├─ tambah.php, edit.php, form_konsultasi.php, login.php
│  │     └─ layouts/admin_header.php, admin_footer.php
│  ├─ layanan/
│  │  ├─ controllers/Layanan.php
│  │  ├─ models/M_layanan.php
│  │  └─ views/form/layanan.php, layanan_detail.php
│  ├─ recognize/
│  │  ├─ controllers/Recognize.php
│  │  ├─ models/M_recognize.php
│  │  └─ views/view_recognize.php
│  └─ selamat_datang/
│     ├─ controllers/Antrian.php, Selamat_datang.php
│     ├─ models/M_selamat_datang.php, M_user.php
│     └─ views/view_selamat_datang.php, view_pilih_status.php, cetak_antrian.php
├─ third_party/MX/         # Modular Extensions MX
└─ views/                  # views global (errors, evaluasi/*, layout-document-full.php)
```

---

## 🌐 Routing & Akses Halaman (Perkiraan)

Rute pasti ditentukan di `application/config/routes.php`. Contoh umum:
- `/` → `Selamat_datang`
- `/antrian` → `selamat_datang/Antrian`
- `/layanan` → `layanan/Layanan`
- `/admin/login` → halaman login admin
- `/admin` → dashboard admin
- `/admin/daftar_tamu`, `/admin/daftar_kunjungan`
- `/recognize` → halaman pengenalan
- `/evaluasi` → halaman evaluasi layanan

---

## ⚙️ Instalasi & Konfigurasi

1. **Kloning & penempatan berkas**
   - Letakkan source CI di webroot/virtual host. Struktur saat ini: `/var/www/html/tamdes-web/`.

2. **Konfigurasi dasar CI**
   - `application/config/config.php`
     - `base_url`, `encryption_key`, `csrf_protection`
   - `application/config/database.php`
     - Konfigurasi kredensial MySQL/MariaDB.
   - `application/config/autoload.php`
     - Autoload library/helper yang sering dipakai.

3. **Modular Extensions MX**
   - Sudah ada di `third_party/MX`. Pastikan `MY_Controller`, `MY_Loader`, dan `MY_Router` sesuai.

4. **Virtual Host / .htaccess**
   - Contoh `.htaccess`:
     ```apache
     <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /tamdes-web/
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ index.php/$1 [L]
     </IfModule>
     ```

5. **Folder writable**
   - Pastikan `application/logs/`, `application/cache/`, dan folder upload (jika ada) writable.

6. **Database**
   - Import skema DB (jika tersedia).

7. **Login Admin**
   - Periksa logic di `Admin.php` dan `M_admin.php`.

---

## 🔐 Keamanan & Privasi

- Gunakan session & guard untuk halaman admin.
- Validasi form & sanitasi input.
- Aktifkan CSRF protection untuk form publik.
- Batasi tipe/ukuran upload file.
- Tambahkan kebijakan privasi & retensi data.

---

## 🧰 Troubleshooting

- **404 routing**: cek `routes.php`, `base_url`, RewriteBase `.htaccess`.
- **DB error**: cek kredensial di `database.php`.
- **Session**: pastikan `encryption_key` diisi.
- **Permission**: beri izin write pada folder `logs/`, `cache/`, `upload/`.

---

## 🚀 Roadmap Saran

- Export CSV/Excel daftar kunjungan.
- Integrasi nomor antrian ke layar publik + TTS/WA notif.
- API internal untuk integrasi kiosk/QR.
- Audit trail admin.
- Pagination & pencarian server-side.
- UI konsisten dengan layout admin.

---

## 📜 Lisensi

Tambahkan file LICENSE (MIT/Apache-2.0) jika dipublikasikan.

---

## 🤝 Kontribusi

1. Fork repo ini.  
2. Buat branch fitur:  
   ```bash
   git checkout -b fitur/nama-fitur
   ```
3. Commit perubahan:  
   ```bash
   git commit -m "feat: tambah X"
   ```
4. Push branch & buka Pull Request.



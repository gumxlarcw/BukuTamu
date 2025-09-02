BukuTamu (CodeIgniter HMVC) — README (Notepad)
==================================================
Aplikasi Buku Tamu Front Office/PST dengan arsitektur CodeIgniter 3 + HMVC (Modular Extensions MX).
Dokumen ini merangkum struktur, modul, serta langkah instalasi & konfigurasi berdasarkan tree yang Anda kirim.

Ringkasan
--------------------------------------------------
Fungsi utama (berdasarkan modul & view yang tersedia):
- **Selamat Datang**: halaman depan, pemilihan status, cetak nomor antrian.
- **Antrian**: manajemen & pencetakan nomor antrian (kiosk).
- **Layanan**: form layanan/konsultasi untuk tamu/pengunjung.
- **Admin**: dashboard, login, daftar tamu/kunjungan, detail, tambah/edit data, antrian konsultasi.
- **Recognize**: halaman pengenalan (kemungkinan kamera/QR/face, lihat controller Recognize).
- **Evaluasi**: form evaluasi layanan dan halaman tunggu/selesai.

Stack & Ketergantungan
--------------------------------------------------
- **PHP**: 7.4+ disarankan (CI3 kompatibel), aktifkan ekstensi umum (mysqli, mbstring, intl, gd).
- **Web Server**: Apache/Nginx.
- **DB**: MySQL/MariaDB.
- **CI3 + HMVC**: `third_party/MX` tersedia (Modular Extensions MX).
- (Opsional) Kamera/JS untuk recognize (sesuai implementasi di view).
- (Opsional) Library front-end (Bootstrap/jQuery) biasanya di assets/ (tidak tampak di tree application/ — cek di webroot).

Struktur Utama (sesuai tree)
--------------------------------------------------
application/
├─ config/                 # config CI (base_url, autoload, database, routes, dll.)
├─ controllers/            # controller global (Evaluasi.php) + placeholder index.html
├─ core/                   # MY_Controller, MY_Loader, MY_Router (kustom HMVC)
├─ helpers/                # konsultasi_helper.php
├─ language/               # stub bahasa
├─ libraries/              # Layout.php (wrapper layout/view)
├─ logs/                   # log CI
├─ models/                 # (belum ada model global)
├─ modules/                # HMVC modules:
│  ├─ admin/
│  │  ├─ controllers/Admin.php
│  │  ├─ models/M_admin.php
│  │  └─ views/
│  │     ├─ dashboard.php, daftar_tamu.php, daftar_kunjungan.php, antrian_konsultasi.php
│  │     ├─ detail_kunjungan.php, detail_modal.php, tambah.php, edit.php
│  │     ├─ form_konsultasi.php (dan "form_konsultasi copy.php")
│  │     └─ layouts/admin_header.php, admin_footer.php, login.php
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
│     └─ views/view_selamat_datang.php, view_pilih_status.php, cetak_antrian(.php)
├─ third_party/MX/         # Modular Extensions (Base, Controller, Loader, Router, dsb.)
└─ views/                  # views global (errors, evaluasi/*, layout-document-full.php)

Routing & Akses Halaman (Perkiraan)
--------------------------------------------------
> Rute pasti ditentukan di `application/config/routes.php`. Contoh umum untuk HMVC:
- Halaman depan: `Selamat_datang` → `/selamat_datang` atau root.
- Antrian (kiosk): `selamat_datang/Antrian` → `/antrian` (cetak nomor, dsb.).
- Layanan/form konsultasi: `layanan/Layanan` → `/layanan`.
- Admin:
  - Login: `/admin/login` (view: modules/admin/views/login.php)
  - Dashboard: `/admin` atau `/admin/dashboard`
  - Daftar tamu/kunjungan: `/admin/daftar_tamu`, `/admin/daftar_kunjungan`
  - Detail & pengelolaan: `/admin/detail`, `/admin/tambah`, `/admin/edit`
- Recognize: `recognize/Recognize` → `/recognize` (kemungkinan kamera/scan/face).
- Evaluasi: `controllers/Evaluasi.php` dan `views/evaluasi/*` → `/evaluasi`

Silakan buka `routes.php` untuk pemetaan final.

Instalasi & Konfigurasi
--------------------------------------------------
1) **Kloning & penempatan berkas**
   - Letakkan source CI di webroot/virtual host. Tree Anda menunjukkan `application/` berada di `/var/www/html/tamdes-web/application`.
   - Pastikan file `index.php` (webroot CI3) berada di root proyek (mis. `/var/www/html/tamdes-web/index.php`).

2) **Konfigurasi dasar CI**
   - `application/config/config.php`
     - `base_url`: set ke domain/subfolder Anda, mis. `http://example.local/tamdes-web/`
     - `encryption_key`: isi dengan string acak kuat.
     - `csrf_protection`: pertimbangkan `TRUE` untuk form publik.
   - `application/config/database.php`
     - Isi kredensial MySQL/MariaDB (hostname, username, password, database, dbdriver `mysqli`).
   - `application/config/autoload.php`
     - Autoload library/helper yang sering dipakai (session, database, url, form, konsultasi_helper).

3) **Modular Extensions MX (HMVC)**
   - Sudah tersedia di `third_party/MX`. Pastikan `MY_Loader`, `MY_Router`, dan `MY_Controller` sesuai versi CI3 Anda.
   - Struktur modul sudah sesuai: `modules/<nama>/controllers|models|views`.

4) **Virtual Host / .htaccess**
   - Untuk Apache, aktifkan mod_rewrite dan gunakan `.htaccess` untuk `index.php` clean URL.
   - Contoh minimal `.htaccess` (root proyek):
     ```
     <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /tamdes-web/
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ index.php/$1 [L]
     </IfModule>
     ```
   - Sesuaikan `RewriteBase` jika di subfolder.

5) **Folder writable**
   - `application/logs/` dan (jika ada) `application/cache/` harus writable oleh web server.
   - Jika aplikasi menyimpan upload/antrian/QR/foto, pastikan folder terkait writable.

6) **Database**
   - Import skema/migrasi (jika tersedia). Jika belum ada, buat tabel sesuai kebutuhan modul (tamu, kunjungan, layanan, user/admin).

7) **Login Admin**
   - Modul admin menyediakan `views/login.php`. Pastikan rute dan logic autentikasi di `Admin.php` dan `M_admin.php` berjalan (session & password hashing).

Fitur Modul (Ringkas)
--------------------------------------------------
- **selamat_datang**: halaman depan, pilih status, cetak_antrian.php (kanvas cetak), view untuk pengenalan & penyambutan pengunjung.
- **layanan**: form layanan konsultasi (`views/form/layanan.php`), detail layanan, model `M_layanan` untuk transaksi data.
- **admin**: dashboard, daftar tamu/kunjungan, detail modal, tambah/edit, antrian konsultasi, layout header/footer untuk tema admin.
- **recognize**: endpoint halaman pengenalan (kamera/scan/face/QR—sesuaikan implementasi).
- **evaluasi**: proses evaluasi layanan (`form_evaluasi.php` → `tunggu_evaluasi.php` → `selesai.php`).

Keamanan & Privasi
--------------------------------------------------
- **Auth Admin**: pastikan session & middleware/guard diterapkan untuk halaman admin.
- **Validasi Form**: gunakan form_validation & sanitasi input (hindari XSS/SQLi).
- **CSRF**: pertimbangkan enable CSRF untuk form publik.
- **Upload**: batasi tipe & ukuran file, simpan di folder ter-`deny direct access` (via .htaccess).
- **Data Pribadi**: tampilkan notice privasi & kebijakan retensi data pengunjung.

Troubleshooting
--------------------------------------------------
- **404 routing**: cek `routes.php`, `base_url`, RewriteBase .htaccess, dan huruf besar/kecil nama controller (CI case-sensitive di *nix).
- **DB error**: cek kredensial di `database.php` & hak akses user DB.
- **Session**: pastikan `encryption_key` terisi & driver session stabil (file/database).
- **Permission**: beri izin write pada logs/, cache/, upload/ (jika ada).

Roadmap Saran
--------------------------------------------------
- Export CSV/Excel untuk daftar kunjungan & rekap.
- Integrasi nomor antrian ke layar publik + TTS/WA notif (bila relevan).
- API internal untuk integrasi kiosk/QR.
- Audit trail admin (log perubahan data).
- Pagination & pencarian server-side untuk daftar besar.
- Tema UI konsisten (admin_header/footer).

Lisensi
--------------------------------------------------
Tambahkan file LICENSE (MIT/Apache-2.0) jika akan dipublikasikan.

Kontribusi
--------------------------------------------------
1) Fork
2) Branch fitur: `git checkout -b fitur/nama-fitur`
3) Commit: `git commit -m "feat: tambah X"`
4) Push & PR

Catatan
--------------------------------------------------
Dokumen ini disusun berdasar struktur yang Anda kirim (tanpa membaca kode internal).
Silakan sesuaikan nama rute, field DB, dan detail proses sesuai implementasi Anda.


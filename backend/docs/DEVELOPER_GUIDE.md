# Developer Guide — BukuTamu

> Panduan praktis untuk developer baru: setup lokal, struktur kerja, build & deploy, common tasks, troubleshooting. Untuk arsitektur lihat [ARCHITECTURE.md](./ARCHITECTURE.md), untuk endpoint API lihat [API.md](./API.md).

## 1. Prasyarat

| Tool | Versi | Catatan |
|---|---|---|
| PHP | 7.4 (produksi) | ⚠️ EOL — upgrade ke 8.2+ direncanakan |
| Extensions PHP | mysqli, mbstring, intl, gd, curl, json | wajib semua |
| Composer | (opsional) | CI3 tidak butuh, hanya kalau pakai library tambahan |
| MySQL/MariaDB | 5.7+ / 10.4+ | DB: `db_tamdes` |
| Node.js | 20.x LTS | untuk frontend |
| pnpm/npm | npm 10+ | |
| Apache | 2.4 | dengan `mod_rewrite`, `mod_proxy`, `mod_proxy_fcgi`, `mod_proxy_http`, `mod_headers`, `mod_ssl` |
| PHP-FPM | 7.4 | socket di `/run/php/php7.4-fpm.sock` |
| PM2 | latest | manage Node process |

## 2. Setup Lokal (Linux)

### 2.1 Clone Repository

```bash
# Backend
cd /var/www/html
git clone <repo-url> tamdes-web
cd tamdes-web

# Frontend
cd /var/www/html
git clone <repo-url-frontend> tamdes-frontend
```

### 2.2 Backend Setup

```bash
cd /var/www/html/tamdes-web

# 1. Buat .env (jangan commit!)
cat > .env <<'EOF'
DB_HOSTNAME=localhost
DB_USERNAME=root
DB_PASSWORD=<password-mysql-anda>
DB_DATABASE=db_tamdes

CI_ENCRYPTION_KEY=<32-char-hex-random>

ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=<output-php-password_hash>

JWT_SECRET=<32+-char-random-secret>

FRONTEND_URL=http://localhost:5173
EOF

# 2. Generate password hash
php -r 'echo password_hash("rahasia123", PASSWORD_BCRYPT, ["cost"=>12]) . "\n";'

# 3. Generate JWT secret
php -r 'echo bin2hex(random_bytes(32)) . "\n";'

# 4. Pastikan folder writable
chmod -R 775 application/logs application/cache
chown -R www-data:www-data application/logs application/cache

# 5. Import database
mysql -u root -p db_tamdes < schema.sql   # kalau ada file schema
# Atau jalankan migrasi manual — saat ini belum ada migration system
```

### 2.3 Frontend Setup

```bash
cd /var/www/html/tamdes-frontend

# Install dependencies
npm ci

# Buat .env.local untuk dev
cat > .env.local <<'EOF'
VITE_API_URL=http://localhost:8080
EOF

# Jalankan dev server
npm run dev
# → http://localhost:5173 (Vite proxy /api → :8080)
```

### 2.4 Konfigurasi Apache (Lokal Dev)

Untuk dev lokal, biasanya tidak perlu reverse proxy seperti produksi. Bisa langsung:
- Frontend: `npm run dev` di port 5173 (Vite handle hot reload + proxy `/api` ke `:8080`)
- Backend: PHP built-in server di port 8080:
  ```bash
  cd /var/www/html/tamdes-web
  php -S localhost:8080
  ```
- Akses: <http://localhost:5173>

Untuk simulasi produksi penuh di lokal, lihat section 4 (Deploy).

## 3. Struktur Kerja

### 3.1 Workflow Wajib (per CLAUDE.md global)

Setiap kali edit file:

```bash
# 1. BACA dulu
cat path/to/file.php       # atau pakai editor

# 2. BACKUP sebelum edit
cp path/to/file.php path/to/file.php.backup

# 3. EDIT minimal & terarah

# 4. VERIFY dengan diff
diff path/to/file.php.backup path/to/file.php
```

> **`.backup` files dibuat per file, override yang lama.** Jangan commit `.backup` ke git (sudah di-`.gitignore`).

### 3.2 Branch Strategy

```
main                ← branch utama, deploy ke produksi
└── feat/<nama>     ← fitur baru
└── fix/<nama>      ← perbaikan bug
└── docs/<nama>     ← perubahan dokumentasi
```

Commit message format (dari `git log` yang ada):
```
feat: add Visits API controller with CRUD, status, service, summary, queue number
fix: address review findings — FIFO eval order, duplicate guard, shared queue number
docs: update architecture diagram
```

### 3.3 Convention Backend

- **Endpoint baru** → buat di `application/modules/api/controllers/`, extend `Api_base`.
- **Wajib panggil `$this->require_auth()`** di method awal kalau butuh auth.
- **Wajib panggil `$this->audit(...)`** untuk operasi tulis/hapus oleh admin.
- **Wajib log error** dengan `log_message('error', ...)` (bukan `echo` atau `var_dump`).
- **Wajib whitelist kolom** di `SELECT` ketika query `tamdes_buku` (hindari foto longblob).
- **Wajib JSON response** lewat `$this->json_response($data, $status)`.

### 3.4 Convention Frontend

- **API client per resource** di `src/api/<resource>.ts`, ekspor function (bukan kelas).
- **Wajib pakai TanStack Query** untuk fetching, jangan `useEffect + setState` manual.
- **Type semua response** di `src/types/`.
- **UI primitive** dari `src/components/ui/` (sudah ada button, input, dialog, dll).
- **Layout** ditentukan oleh route group: `KioskLayout` untuk `/kiosk/*`, `AdminLayout` untuk `/admin/*`.
- **Lazy import semua page** dengan `lazyRetry()` (lihat `App.tsx`) — handle stale chunk after deploy.

## 4. Build & Deploy

### 4.1 Build Frontend

```bash
cd /var/www/html/tamdes-frontend
npm run build         # tsc -b && vite build
# Output: ./dist/
```

### 4.2 Restart Frontend Process

```bash
pm2 restart tamdes-frontend
pm2 logs tamdes-frontend     # cek tidak ada error
pm2 save                     # persist agar restart server-wide
```

### 4.3 Deploy Backend

Backend tidak perlu "build" — langsung edit file PHP. Tapi:

```bash
# 1. Reload PHP-FPM agar opcache fresh (jika dipakai)
sudo systemctl reload php7.4-fpm

# 2. Reload Apache jika ubah config
sudo systemctl reload apache2

# 3. Cek log
sudo tail -f /var/log/apache2/bukutamu60_error.log
```

### 4.4 Deploy Apache Vhost

File: `/etc/apache2/sites-enabled/bukutamu-60.conf` (HTTP) dan `bukutamu-ssl.conf` (HTTPS).

Konfigurasi inti (lihat versi lengkap di vhost):

```apache
<VirtualHost *:60>
    ServerName bukutamu.bpsmalut.com
    DocumentRoot /var/www/html/tamdes-web

    <Directory /var/www/html/tamdes-web>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php7.4-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    Alias /video /var/www/html/tamdes-frontend/dist/video
    <Directory /var/www/html/tamdes-frontend/dist/video>
        Require all granted
        Options -Indexes
        Header set Accept-Ranges bytes
        Header set Cache-Control "public, max-age=86400"
    </Directory>

    Alias /hls /var/www/html/tamdes-frontend/dist/hls
    <Directory /var/www/html/tamdes-frontend/dist/hls>
        Require all granted
        Options -Indexes
        Header set Cache-Control "public, max-age=86400"
        Header set Access-Control-Allow-Origin "*"
    </Directory>

    ProxyPreserveHost On
    RewriteEngine On

    # Static media → langsung disk
    RewriteRule ^/video/ - [L]
    RewriteRule ^/hls/ - [L]

    # API & CI3 → PHP-FPM
    RewriteRule ^/api/ - [L]
    RewriteRule ^/index\.php - [L]

    # Selain itu → React SPA
    RewriteRule ^(.*)$ http://127.0.0.1:3060$1 [P,L]
    ProxyPassReverse / http://127.0.0.1:3060/

    ErrorLog ${APACHE_LOG_DIR}/bukutamu60_error.log
    CustomLog ${APACHE_LOG_DIR}/bukutamu60_access.log combined
</VirtualHost>
```

Validasi & reload:
```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### 4.5 Konfigurasi PM2

File: `/var/www/html/tamdes-frontend/ecosystem.config.cjs`

```js
module.exports = {
  apps: [{
    name: 'tamdes-frontend',
    script: 'npx',
    args: 'serve dist -p 3060 --no-clipboard -c ../public/serve.json',
    cwd: '/var/www/html/tamdes-frontend',
    interpreter: 'none',
  }],
}
```

`public/serve.json` mengontrol SPA fallback (semua URL non-asset → `index.html`).

Manage:
```bash
pm2 start ecosystem.config.cjs   # pertama kali
pm2 restart tamdes-frontend       # setelah build ulang
pm2 logs tamdes-frontend           # tail logs
pm2 save                           # persist process list
pm2 startup                        # systemd integration (sekali saja)
```

## 5. Common Tasks

### 5.1 Tambah Endpoint Baru

**Skenario:** Tambah `GET /api/reports/monthly`.

1. **Buat controller** `application/modules/api/controllers/Reports.php`:
   ```php
   <?php
   defined('BASEPATH') OR exit('No direct script access allowed');
   require_once APPPATH . 'modules/api/controllers/Api_base.php';

   class Reports extends Api_base {
       public function monthly() {
           if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
               $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
           }
           $this->require_auth();

           $tahun = $this->input->get('tahun') ?: date('Y');
           $rows = $this->db
               ->select('MONTH(date_visit) as bulan, COUNT(*) as total')
               ->where('YEAR(date_visit)', $tahun)
               ->group_by('MONTH(date_visit)')
               ->get('tamdes_kunjungan')->result();

           $this->json_response(['success' => true, 'data' => $rows, 'message' => 'OK']);
       }
   }
   ```

2. **Daftarkan route** di `application/config/routes.php`:
   ```php
   $route['api/reports/monthly'] = 'api/reports/monthly';
   ```

3. **Buat client frontend** `tamdes-frontend/src/api/reports.ts`:
   ```ts
   import apiClient from './client'
   export const fetchMonthly = (tahun?: number) =>
     apiClient.get('/api/reports/monthly', { params: { tahun } }).then(r => r.data.data)
   ```

4. **Pakai di komponen** dengan TanStack Query.

### 5.2 Tambah Page Admin Baru

**Skenario:** Tambah `/admin/reports`.

1. Buat `tamdes-frontend/src/pages/admin/ReportsPage.tsx`.
2. Tambah lazy import + `<Route>` di `App.tsx`:
   ```tsx
   const ReportsPage = lazyRetry(() => import('@/pages/admin/ReportsPage'))
   // ... di dalam <AdminLayout>:
   <Route path="/admin/reports" element={<ReportsPage />} />
   ```
3. Tambah link di sidebar (`src/components/admin/TopNav.tsx` atau sidebar component).
4. `npm run build` & `pm2 restart tamdes-frontend`.

### 5.3 Tambah Field di Tabel Tamu

1. ALTER table:
   ```sql
   ALTER TABLE tamdes_buku ADD COLUMN <nama_field> VARCHAR(100) NULL;
   ```
2. Update `Guests::$safe_columns` agar field baru muncul di list/detail.
3. Update `Guests::detail()` PUT — tambah ke `$allowed`.
4. Update `Kiosk::register()` — tambah ke `$guest_data`.
5. Update TS types (`tamdes-frontend/src/types/guest.ts`).
6. Update form fields di komponen relevan (`GuestAddPage.tsx`, `VisitorFormPage.tsx`).

### 5.4 Tambah Indikator IKM Baru

1. Edit `Evaluations::indikator_list()`:
   ```php
   private function indikator_list() {
       return [
           1  => '...',
           // ...
           18 => 'Indikator baru di sini',
       ];
   }
   ```
2. Frontend (form evaluasi) otomatis dapat — karena fetch list dari endpoint.
3. Untuk historical comparison, evaluasi lama tidak punya skor untuk indikator baru → handle missing data di summary.

### 5.5 Reset Password Admin

```bash
# Generate hash baru
php -r 'echo password_hash("password-baru", PASSWORD_BCRYPT, ["cost"=>12]);'

# Update DB
mysql -u root -p db_tamdes -e "UPDATE admin_users SET password_hash='<hash>' WHERE username='admin'"

# Atau update .env (untuk fallback admin)
# Edit ADMIN_PASSWORD_HASH di .env
```

### 5.6 Tambah Admin User Baru

```sql
INSERT INTO admin_users (username, password_hash, nama, role, active)
VALUES ('petugas01', '<bcrypt-hash>', 'Budi Petugas', 'operator', 1);
```

Atau via UI: login sebagai superadmin → `/admin/users` → tambah.

## 6. Testing

> **Status:** Belum ada test framework yang di-setup di codebase ini. Roadmap: PHPUnit untuk backend, Vitest + React Testing Library untuk frontend.

Untuk sementara, testing manual:
- **API:** pakai Postman / cURL / `httpie`
- **Frontend:** `npm run dev` + browser dev tools
- **End-to-end:** test manual di kiosk fisik (atau emulasi dengan webcam)

## 7. Troubleshooting

### 7.1 "401 Unauthorized" di endpoint admin

- Cek cookie `jwt_token` ada di browser DevTools (Application → Cookies).
- Cek `JWT_SECRET` di `.env` — harus sama antara saat encode (login) dan decode (request).
- Cek `expiry` token: cookie expire 4 jam sejak login. Kalau lewat, login ulang.
- Cek `samesite=Strict`: kalau frontend & backend beda subdomain, cookie tidak terkirim.

### 7.2 "CORS blocked" di browser

```
Access to XMLHttpRequest at 'https://...' from origin 'http://...' has been blocked
```

- Cek `FRONTEND_URL` di `.env` cocok dengan origin browser (termasuk `https://` atau `http://`).
- Cek di Network tab: ada `Access-Control-Allow-Origin` di response header? Kalau tidak, origin tidak match.
- Dev: pastikan akses frontend lewat `http://localhost:5173`, bukan `http://127.0.0.1:5173`.

### 7.3 React SPA 502 / blank page

- Cek `pm2 list` — apakah `tamdes-frontend` `online`?
- Cek `pm2 logs tamdes-frontend` — kalau ada error startup.
- Cek `dist/` ada di `/var/www/html/tamdes-frontend/dist/`. Kalau tidak, jalankan `npm run build`.
- Cek port 3060 tidak terblokir firewall (`ss -tlnp | grep 3060`).

### 7.4 PHP-FPM 502

- Cek socket exists: `ls -l /run/php/php7.4-fpm.sock`
- Cek FPM running: `sudo systemctl status php7.4-fpm`
- Cek error log: `sudo tail -f /var/log/php7.4-fpm.log`
- Reload: `sudo systemctl reload php7.4-fpm`

### 7.5 Database connection error

- Cek `.env` kredensial benar.
- Cek MySQL running: `sudo systemctl status mysql` (atau `mariadb`).
- Cek user MySQL punya privilege: `SHOW GRANTS FOR 'root'@'localhost';`
- Cek DB exists: `mysql -e "SHOW DATABASES" -u root -p | grep db_tamdes`

### 7.6 Face Recognition tidak akurat

- Pencahayaan ruang kiosk mempengaruhi descriptor.
- Threshold default L2 distance 0.5 — bisa diturunkan ke 0.45 untuk lebih ketat (`src/lib/face-detection.ts`).
- Foto harus full-face, frontal. face-api.js tidak akurat untuk side profile.

### 7.7 "Duplicate entry id_user" saat register kiosk

Pernah muncul sebelum ada `LOCK TABLES` di `Kiosk::register()`. Sekarang seharusnya tidak terjadi. Kalau masih, cek:
- Lock query benar dieksekusi (test dengan `SHOW PROCESSLIST` saat 2 kiosk submit bersamaan).
- Storage engine tabel = InnoDB (bukan MyISAM) untuk lock yang andal.

### 7.8 Tiket tidak tercetak

- Cek print server (port 5000) running di mesin kiosk.
- Cek `Kiosk::print_ticket()` log di Apache error log.
- Test manual: `curl -X POST http://localhost:5000/print -H "Content-Type: application/json" -d '{"nomor":"K001"}'`

## 8. Backup & Restore

### 8.1 Backup Database

```bash
# Backup full
mysqldump -u root -p --single-transaction --routines --triggers \
  db_tamdes > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup tanpa foto (lebih ringan)
mysqldump -u root -p --single-transaction \
  --ignore-table=db_tamdes.tamdes_buku \
  db_tamdes > backup_meta.sql
mysqldump -u root -p --single-transaction \
  --where="1=1" --no-data db_tamdes tamdes_buku >> backup_meta.sql
```

### 8.2 Backup Code

Backup folder `tamdes-web` dan `tamdes-frontend`, atau push branch ke remote.

**File yang TIDAK boleh di-backup ke tempat publik:**
- `.env` (kredensial DB, password hash, JWT secret)
- `application/cache/` (cache CI)
- `application/logs/` (log produksi, mungkin ada PII)
- `tamdes-frontend/node_modules/`

### 8.3 Restore

```bash
mysql -u root -p db_tamdes < backup.sql

# Setelah restore, cek auto increment
mysql -e "ALTER TABLE tamdes_kunjungan AUTO_INCREMENT = (SELECT MAX(id_kunjungan)+1 FROM tamdes_kunjungan)" -u root -p db_tamdes
```

## 9. Security Checklist (Pra-Deploy)

- [ ] `.env` tidak ter-commit ke git (cek `.gitignore`).
- [ ] `JWT_SECRET` minimal 32 karakter random.
- [ ] `ADMIN_PASSWORD_HASH` pakai bcrypt cost ≥ 12.
- [ ] Apache tidak expose `index of` directory (`Options -Indexes`).
- [ ] PHP `display_errors = Off` di production (`php.ini`).
- [ ] PHP `expose_php = Off`.
- [ ] HTTPS aktif & redirect HTTP → HTTPS.
- [ ] Cookie `jwt_token` `secure=true` di produksi.
- [ ] MySQL user untuk app punya privilege minimal (tidak `ALL`).
- [ ] Backup otomatis dijadwalkan (cron / pm2 schedule).
- [ ] PHP 7.4 → roadmap upgrade ke 8.2+ sebelum security advisory kritis muncul.

## 10. Reference Files (cheat sheet)

| File | Apa | Kapan diedit |
|---|---|---|
| `application/config/routes.php` | URL → controller | Tiap tambah/hapus endpoint |
| `application/modules/api/controllers/Api_base.php` | Parent class API | Tambah helper umum (audit, role check) |
| `application/libraries/JWT_Helper.php` | JWT encode/decode | Hampir tidak pernah |
| `.env` | Kredensial | Saat ganti DB/JWT secret/admin password |
| `.htaccess` | URL rewrite CI3 | Hampir tidak pernah |
| `index.php` | Entry CI3 | Hampir tidak pernah |
| `tamdes-frontend/src/App.tsx` | Routes SPA | Tiap tambah halaman |
| `tamdes-frontend/src/api/client.ts` | Axios base | Untuk ubah base URL atau interceptor |
| `tamdes-frontend/vite.config.ts` | Build & dev server | Tiap ganti proxy/alias |
| `tamdes-frontend/ecosystem.config.cjs` | PM2 config | Saat ganti port atau path |
| `/etc/apache2/sites-enabled/bukutamu-60.conf` | Vhost HTTP | Saat ubah routing reverse proxy |
| `/etc/apache2/sites-enabled/bukutamu-ssl.conf` | Vhost HTTPS | Saat renew SSL atau ubah config |

# Panduan Pengguna — BukuTamu BPS Malut

> Panduan untuk **petugas PST** (admin, operator front-office) dan **tamu pengunjung** menggunakan sistem BukuTamu BPS Provinsi Maluku Utara. Untuk dokumentasi teknis, lihat [DEVELOPER_GUIDE.md](./DEVELOPER_GUIDE.md).

## 📑 Daftar Isi

- [1. Untuk Tamu Pengunjung](#1-untuk-tamu-pengunjung)
  - [1.1 Daftar Pertama Kali (Kiosk)](#11-daftar-pertama-kali-kiosk)
  - [1.2 Tamu Lama (Sudah Pernah Daftar)](#12-tamu-lama-sudah-pernah-daftar)
  - [1.3 Mengisi Evaluasi Layanan](#13-mengisi-evaluasi-layanan)
- [2. Untuk Petugas PST (Admin Dashboard)](#2-untuk-petugas-pst-admin-dashboard)
  - [2.1 Login & Logout](#21-login--logout)
  - [2.2 Dashboard Beranda](#22-dashboard-beranda)
  - [2.3 Memanggil Antrian Konsultasi](#23-memanggil-antrian-konsultasi)
  - [2.4 Mencatat Hasil Konsultasi](#24-mencatat-hasil-konsultasi)
  - [2.5 Manajemen Data Tamu](#25-manajemen-data-tamu)
  - [2.6 Riwayat Kunjungan (Visit Log)](#26-riwayat-kunjungan-visit-log)
  - [2.7 Manual Entry (Walk-in)](#27-manual-entry-walk-in)
  - [2.8 Ringkasan IKM (Indeks Kepuasan Masyarakat)](#28-ringkasan-ikm-indeks-kepuasan-masyarakat)
  - [2.9 Statistik Antrian](#29-statistik-antrian)
  - [2.10 Responden Tahunan](#210-responden-tahunan)
  - [2.11 Audit Log](#211-audit-log)
  - [2.12 Manajemen User (Superadmin)](#212-manajemen-user-superadmin)
- [3. Peran & Hak Akses](#3-peran--hak-akses)
- [4. FAQ](#4-faq)

---

## 1. Untuk Tamu Pengunjung

### 1.1 Daftar Pertama Kali (Kiosk)

Saat tiba di front office BPS Malut, gunakan **kiosk pendaftaran** (layar sentuh besar di sisi pintu masuk).

#### Langkah 1: Layar Selamat Datang
- Tap layar atau tombol **"Mulai Pendaftaran"**.

#### Langkah 2: Pilih Status
- Pilih kategori Anda:
  - **Mahasiswa / Pelajar**
  - **Pemerintah** (PNS/ASN/instansi pemerintah)
  - **Swasta** (perusahaan, bisnis)
  - **Umum** (lainnya, peneliti independen)

#### Langkah 3: Pilih Layanan yang Dibutuhkan
- Boleh pilih **lebih dari satu**. Layanan yang tersedia:
  - **Perpustakaan** — baca/pinjam publikasi BPS
  - **Konsultasi Statistik** — tanya jawab data dengan petugas
  - **Rekomendasi Kegiatan Statistik** — surat rekomendasi untuk survei
  - **Penjualan Produk Statistik** — beli buku/CD publikasi
  - **Lainnya** — keperluan khusus (tidak akan dapat nomor antrian)

#### Langkah 4: Isi Data Diri
- Nama lengkap
- Email & No. HP
- Jenis kelamin, umur
- Pendidikan terakhir
- Pekerjaan, instansi
- Tujuan pemanfaatan data (penelitian, perencanaan, dll)

#### Langkah 5: Foto Wajah (Opsional, Untuk Kemudahan Kunjungan Berikutnya)
- Hadap kamera, pencahayaan cukup, tidak bermasker.
- Centang **"Saya setuju data biometrik (foto wajah) saya disimpan untuk pengenalan otomatis kunjungan berikutnya"**.
- Tap **"Ambil Foto"** dan tunggu sistem memverifikasi.

> 🛡️ **Tentang Foto:** Foto Anda disimpan secara aman di server BPS Malut, tidak dibagikan keluar, dan hanya digunakan untuk mengidentifikasi Anda saat kunjungan selanjutnya. Anda berhak meminta penghapusan kapan saja kepada petugas.

#### Langkah 6: Cetak Tiket
- Sistem mencetak **tiket kertas** berisi:
  - Nomor antrian (mis. `K005` untuk Konsultasi Statistik)
  - Nama Anda
  - Jenis layanan
  - Tanggal & waktu

> 📋 **Format Nomor Antrian:**  
> Huruf depan = inisial layanan (`K` = Konsultasi, `P` = Perpustakaan/Penjualan, `R` = Rekomendasi). Tiga digit = urutan hari ini.

#### Langkah 7: Tunggu Dipanggil
- Lihat **layar TV** di ruang tunggu.
- Saat nomor Anda muncul + diumumkan via suara, datang ke loket.

### 1.2 Tamu Lama (Sudah Pernah Daftar)

Jika Anda pernah mendaftar dan setuju foto disimpan:

#### Langkah 1: Tap "Saya Pernah Daftar" atau langsung berdiri di depan kiosk
- Sistem akan mendeteksi wajah Anda secara otomatis (~3-5 detik).

#### Langkah 2: Verifikasi
- Layar menampilkan nama Anda. Konfirmasi dengan tap **"Iya, ini saya"**.
- Jika sistem keliru, tap **"Bukan saya"** → kembali ke jalur pendaftaran baru.

#### Langkah 3: Lengkapi Data (Jika Diminta)
- Sistem mungkin meminta Anda mengisi field yang belum lengkap dari kunjungan lalu.

#### Langkah 4: Pilih Layanan & Cetak Tiket
- Sama seperti tamu baru, lewati pendaftaran data diri.
- Total waktu: < 30 detik.

### 1.3 Mengisi Evaluasi Layanan

> ⚠️ **Tidak semua kunjungan perlu mengisi evaluasi.**
> Evaluasi hanya wajib untuk **4 layanan PST**:
> - Perpustakaan
> - Konsultasi Statistik
> - Rekomendasi Kegiatan Statistik
> - Penjualan Produk Statistik
>
> Untuk layanan **Lainnya** dan **Keperluan Pimpinan**, kunjungan langsung selesai setelah resepsionis menyatakan selesai — tidak perlu lewat tablet evaluasi.

Setelah konsultasi/layanan PST selesai, petugas mengarahkan Anda ke **tablet evaluasi** di sisi keluar.

#### Langkah 1: Tablet Standby
- Layar menunjukkan "Silakan menunggu giliran evaluasi" — tunggu hingga giliran Anda muncul otomatis (polling tiap 5 detik).

#### Langkah 2: Form Evaluasi — Blok II Kepuasan terhadap Pelayanan PST BPS

Form berisi **16 indikator** kepuasan (skala Likert 1-10) + **1 nilai keseluruhan** (1-10).

**Skala 1-10:**
| Skor | Arti |
|---|---|
| 1-2 | Sangat tidak puas |
| 3-4 | Kurang puas |
| 5-6 | Cukup puas |
| 7-8 | Puas |
| 9-10 | Sangat puas |

**Daftar 16 indikator:**
1. Informasi pelayanan tersedia melalui media elektronik maupun non elektronik
2. Persyaratan pelayanan mudah dipenuhi/disiapkan oleh konsumen
3. Prosedur/alur pelayanan mudah diikuti/dilakukan
4. Jangka waktu penyelesaian pelayanan sesuai dengan yang ditetapkan
5. Biaya pelayanan sesuai dengan yang ditetapkan
6. Produk pelayanan sesuai dengan yang dijanjikan
7. Sarana dan prasarana pendukung memberikan kenyamanan
8. Data BPS mudah diakses melalui sarana utama
9. Petugas pelayanan/aplikasi merespon dengan baik
10. Petugas/aplikasi memberi informasi yang jelas
11. Fasilitas pengaduan PST mudah diakses (kotak saran, web BPS, email bpshq@bps.go.id)
12. Tidak ada diskriminasi dalam pelayanan
13. Tidak ada pelayanan di luar prosedur/kecurangan
14. Tidak ada penerimaan gratifikasi
15. Tidak ada pungutan liar (pungli)
16. Tidak ada praktik percaloan

> ℹ️ **Form sebelumnya menggunakan 17 indikator dengan dual rating (kepentingan + kepuasan).** Sejak April 2026, struktur disederhanakan ke 16 indikator dengan **kepuasan saja**, sesuai standar PerMenPAN-RB & instruksi BPS pusat untuk pelaporan IKM.

#### Langkah 3: Submit
- Tap **"Kirim Evaluasi"**.
- Layar menampilkan ucapan terima kasih.
- Tablet kembali ke standby untuk tamu berikutnya.

> ⚠️ Setelah disubmit, Anda **tidak bisa mengubah** evaluasi. Pastikan jawaban Anda sudah benar.

---

## 2. Untuk Petugas PST (Admin Dashboard)

URL Dashboard: <https://bukutamu.bpsmalut.com/login>

### 2.1 Login & Logout

#### Login
1. Buka URL login.
2. Masukkan **Username** & **Password**.
3. Klik **Masuk**.

> 🔒 **Catatan keamanan:** Setelah **5 percobaan gagal dalam 15 menit dari IP yang sama**, akun otomatis terkunci 15 menit. Jika Anda lupa password, hubungi superadmin.

#### Logout
- Klik nama Anda di pojok kanan atas → **Logout**.
- Otomatis logout setelah **4 jam** tidak aktif (token JWT expire).

### 2.2 Dashboard Beranda

URL: `/admin`

Halaman ini menampilkan ringkasan operasional hari ini:

- **Kartu Statistik:**
  - Tamu hari ini
  - Antrian aktif
  - Konsultasi selesai
  - Rata-rata durasi pelayanan
- **Grafik:** trend pengunjung 7 hari terakhir
- **Quick actions:** tombol cepat ke modul-modul utama

### 2.3 Memanggil Antrian Konsultasi

URL: `/admin/consultations`

Halaman ini menampilkan **antrian konsultasi hari ini saja**, hanya untuk layanan: Perpustakaan, Konsultasi Statistik, Rekomendasi, Penjualan.

#### Memanggil Tamu Berikutnya
1. Pilih tamu di daftar (status `antri`).
2. Klik tombol **"Panggil"** (icon megaphone 📢).
3. Sistem mengirim sinyal ke **layar TV ruang tunggu** dan **memutar suara TTS**: _"Nomor antrian K005, atas nama Siti Aminah, silakan menuju loket Konsultasi"_.
4. Status tamu otomatis berubah jadi `dipanggil`.

#### Test Suara
- Tombol **"Test Suara"** (icon speaker 🔊) → memanggil nomor `TES` untuk verifikasi audio loudspeaker bekerja.

#### Menandai Selesai
- Setelah konsultasi selesai, klik **"Selesai"**.
- Status berubah `selesai`, durasi pelayanan dihitung otomatis (`selesai - date_visit`).
- **Atau** klik **"Lanjut ke Form Evaluasi"** → status menjadi `menunggu_evaluasi` dan tamu diarahkan ke tablet evaluasi.

### 2.4 Mencatat Hasil Konsultasi

URL: `/admin/consultations/:id/form`

Petugas konsultasi mengisi:

- **Hasil Konsultasi (Ringkasan):** narasi singkat, mis. _"Tamu meminta data IPM Halmahera Utara 2020-2024. Data diberikan via email dalam format Excel."_
- **Kebutuhan Data (multi-row):** untuk setiap data yang diminta tamu:
  - Rincian data (mis. _"IPM Halmahera Utara"_)
  - Wilayah (Kabupaten/Kota/Provinsi)
  - Rentang tahun (awal & akhir)
  - Level data (mis. Kabupaten, Kecamatan)
  - Periode (Bulanan/Triwulan/Tahunan)
  - Status data (Tersedia/Tidak Tersedia/Sedang Disusun)
  - Jenis publikasi (BRS/Statistik Daerah/Inkesra/dll)
  - Judul publikasi (jika ada)
  - Tahun publikasi
  - Apakah digunakan untuk publikasi nasional? (Ya/Tidak)
  - Kualitas data (Baik/Cukup/Kurang)

> 💾 **Save strategi:** Saat klik **"Simpan"**, sistem **menghapus semua row lama** untuk visit ini lalu insert ulang. Jadi pastikan semua row dimasukkan sekaligus, jangan parsial.

Setelah simpan, kembali ke daftar antrian; status biasanya diubah ke **"Menunggu Evaluasi"** untuk diarahkan tamu ke tablet evaluasi.

### 2.5 Manajemen Data Tamu

URL: `/admin/guests`

#### Mencari Tamu
- Kolom **Cari**: ketik nama, email, atau nama instansi → hasil filter live.
- Pagination di bawah: 10 per halaman default.

#### Detail Tamu
- Klik baris → modal/detail page menampilkan biodata + foto + riwayat kunjungan.

#### Edit Tamu
URL: `/admin/guests/:id` (atau modal edit)

- Field yang bisa diubah: nama, email, no.tel, jenis kelamin, umur, disabilitas, pendidikan, pekerjaan, kategori instansi, nama instansi, pemanfaatan, pengaduan.
- Klik **"Simpan"** → otomatis log audit dengan diff `from→to`.

#### Hapus Tamu (admin/superadmin saja)
- Tombol **"Hapus"** → konfirmasi → record dihapus + log audit `action=delete`.

> ⚠️ **Hapus permanen.** Data kunjungan dan evaluasi tamu yang dihapus akan jadi orphan (FK left join). Pertimbangkan **arsipkan** alih-alih hapus.

#### Tambah Tamu Baru (Manual)
URL: `/admin/guests/add`

- Untuk kasus tamu yang tidak menggunakan kiosk (mis. tamu khusus, walk-in tanpa device).
- Otomatis `id_user` baru, `tgldatang=hari ini`, `registered_via=admin:<username>`.

#### Import Tamu Massal
URL: `/admin/guests/import`

- Upload file CSV/Excel berisi data tamu dari sistem lama.
- (Detail format kolom — cek halaman atau hubungi superadmin)

### 2.6 Riwayat Kunjungan (Visit Log)

URL: `/admin/visits`

Catatan semua kunjungan, dengan filter lengkap:
- **Cari:** nama, instansi, jenis layanan, status
- **Filter:** layanan tertentu, status (antri/dipanggil/diproses/menunggu_evaluasi/selesai), tahun, bulan
- **Pagination:** 10 per halaman default

#### Detail Kunjungan
Klik baris → halaman detail menampilkan:
- Biodata tamu
- Detail kunjungan (layanan, sarana, durasi, status)
- Hasil konsultasi (jika ada)
- Hasil evaluasi (skor IKM per indikator, rating keseluruhan)

#### Export
- Tombol **"Export CSV"** → download daftar kunjungan terfilter sebagai CSV (untuk laporan eksternal/pivot di Excel).

### 2.7 Manual Entry (Walk-in)

URL: `/admin/manual-entry`

Untuk tamu yang sudah ada dalam database tapi datang tanpa kiosk (mis. kiosk error, atau internal staff):

1. Cari tamu existing (autocomplete by nama/instansi).
2. Pilih jenis layanan & sarana.
3. Klik **"Catat Kunjungan"** → kunjungan dibuat dengan nomor antrian.

### 2.8 Ringkasan IKM (Indeks Kepuasan Masyarakat)

URL: `/admin/evaluations`

Halaman ringkasan evaluasi untuk laporan IKM bulanan/tahunan.

#### Bagian Atas: Skor Keseluruhan
- **IKM Score** = rata-rata skor kepuasan dari semua indikator semua responden, dikonversi ke skala 100.
- **Total Responden** = jumlah kunjungan yang sudah submit evaluasi.

#### Bagian Tengah: Per-Indikator
Tabel 17 indikator dengan kolom:
- Rata-rata Kepentingan
- Rata-rata Kepuasan
- Jumlah Responden
- Gap (Kepentingan - Kepuasan) — semakin besar gap, semakin prioritas perbaikan

#### Bagian Bawah: Per-Visit
Daftar evaluasi per kunjungan (untuk audit individual).

#### Filter
- Filter **Tahun** → IKM tahun spesifik (untuk laporan tahunan ke pusat).

### 2.9 Statistik Antrian

URL: `/admin/queue-stats`

- Rata-rata waktu tunggu per layanan
- Rata-rata durasi pelayanan per layanan
- Distribusi tamu per jam (peak hours)
- Trend mingguan/bulanan

### 2.10 Responden Tahunan

URL: `/admin/responden`

Daftar responden survei tahunan internal (terkait SKD — Sistem Konsumsi Data). Khusus untuk pelaporan ke BPS Pusat tentang penggunaan data BPS oleh instansi/peneliti.

### 2.11 Audit Log

URL: `/admin/audit` (admin/superadmin saja)

Catatan semua aksi admin:
- Login/logout (dengan IP)
- Edit data tamu (dengan diff field-level)
- Update status kunjungan
- Delete tamu

Filter: berdasarkan user, action, target type, rentang tanggal.

> 🔍 **Untuk apa:** forensik insiden, compliance, investigasi keluhan tamu ("data saya berubah, siapa yang ubah?").

### 2.12 Manajemen User (Superadmin)

URL: `/admin/users` (superadmin saja)

- Daftar admin user
- Tambah user baru:
  - Username, password, nama, role (operator/admin/superadmin), aktif/non-aktif
- Edit user (kecuali username)
- Reset password user
- Non-aktifkan user (lebih disarankan daripada hapus, agar audit log tetap valid)

#### Ganti Password Sendiri
Setiap user (termasuk operator) bisa ganti password sendiri:
- Klik nama → **"Ganti Password"** → masukkan password lama & baru.

---

## 3. Peran & Hak Akses

Sistem mendukung **5 role**, dibagi 2 tier:

**Tier 1 — Operator (scope spesifik):**
- **Resepsionis** — handle layanan front-office: `Lainnya`, `Keperluan Pimpinan`. Tidak perlu evaluasi tamu.
- **Petugas PST** — handle 4 layanan PST: `Perpustakaan`, `Konsultasi Statistik`, `Rekomendasi`, `Penjualan`. Wajib mengisi data konsultasi & arahkan tamu ke tablet evaluasi.
- **Operator** (legacy) — full akses (sama dengan admin tier 1, untuk backward compat user lama).

**Tier 2-3 — Manajemen:**
- **Admin** — semua aksi tier 1 + hapus tamu + lihat audit log + bypass role-layanan.
- **Superadmin** — semua aksi admin + manajemen user.

| Aksi | Resepsionis | Petugas PST | Operator | Admin | Superadmin |
|---|:---:|:---:|:---:|:---:|:---:|
| Login & lihat dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| Lihat daftar tamu | ✓ | ✓ | ✓ | ✓ | ✓ |
| Tambah tamu (manual entry) | ✓ | ✓ | ✓ | ✓ | ✓ |
| Edit tamu | ✓ | ✓ | ✓ | ✓ | ✓ |
| Panggil antrian (semua layanan) | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Selesaikan visit Lainnya/Pimpinan** | ✓ | ✗ | ✓ | ✓ | ✓ |
| **Selesaikan visit PST (Perpus/Konsul/Rekom/Penjualan)** | ✗ | ✓ | ✓ | ✓ | ✓ |
| Catat hasil konsultasi | (sesuai layanan) | (sesuai layanan) | ✓ | ✓ | ✓ |
| Lihat IKM summary | ✓ | ✓ | ✓ | ✓ | ✓ |
| Export CSV | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Hapus tamu** | ✗ | ✗ | ✗ | ✓ | ✓ |
| **Lihat audit log** | ✗ | ✗ | ✗ | ✓ | ✓ |
| **Manajemen user** | ✗ | ✗ | ✗ | ✗ | ✓ |

> Role disimpan di tabel `admin_users.role`. Default role saat tambah user baru: **operator**.

### Alur Penyelesaian Visit Berdasarkan Layanan

```
PST (Perpustakaan, Konsultasi, Rekomendasi, Penjualan):
   antri → dipanggil → diproses → menunggu_evaluasi → [tamu isi tablet] → selesai

Resepsionis (Lainnya, Keperluan Pimpinan):
   antri → dipanggil → diproses → selesai (langsung, tanpa evaluasi)
```

### Visibility di Antrian Konsultasi

- **Resepsionis** hanya melihat visit dengan layanan `Lainnya`/`Keperluan Pimpinan`.
- **Petugas PST** hanya melihat visit dengan layanan PST.
- **Admin/Superadmin/Operator** melihat semua visit.

---

## 4. FAQ

### Q: Apa beda "Antrian Konsultasi" dan "Visit Log"?
**A:** **Antrian Konsultasi** (`/admin/consultations`) hanya menampilkan kunjungan **hari ini** untuk layanan utama yang butuh dipanggil (Konsultasi Statistik, Perpustakaan, dll). **Visit Log** (`/admin/visits`) adalah seluruh riwayat kunjungan (semua hari, semua layanan) untuk pelaporan dan audit.

### Q: Mengapa nomor antrian saya `null`?
**A:** Layanan **"Lainnya"** dan **"Keperluan Pimpinan"** tidak mendapat nomor antrian (sengaja). Tamu langsung diarahkan ke unit terkait.

### Q: Tamu pernah daftar tapi tidak terdeteksi face recognition?
**A:** Beberapa kemungkinan:
1. Saat daftar pertama, tamu **tidak menyetujui** penyimpanan biometrik → `face_descriptor` kosong di DB.
2. Pencahayaan saat ini berbeda jauh dengan saat daftar.
3. Wajah berubah signifikan (jenggot, masker, kacamata baru, rambut).
4. Threshold pencocokan terlalu ketat. Hubungi developer untuk tuning.

**Solusi cepat:** Tap **"Cari Manual"** di kiosk → cari nama → lanjut sebagai tamu lama.

### Q: Tablet evaluasi tidak menampilkan tamu, padahal sudah selesai konsultasi?
**A:** Cek di Visit Log: status visit harus `menunggu_evaluasi`. Kalau masih `dipanggil` atau `diproses`, petugas belum mengubah status. Klik **"Lanjut ke Form Evaluasi"** di antrian konsultasi.

### Q: Saya admin, kenapa tidak bisa hapus user?
**A:** Hapus user hanya bisa oleh **superadmin**. Anda hanya bisa **non-aktifkan** user (set `active=0`) atau ganti rolenya.

### Q: IKM Score saya turun dari bulan lalu, apa artinya?
**A:** Nilai 1-4 dari kepuasan dirata-rata, dikonversi ke skala 100. Untuk diagnosis: lihat **per-indikator** di halaman IKM — indikator mana yang skornya rendah dan **gap kepentingan-kepuasan** terbesar. Itu prioritas perbaikan.

### Q: Mengapa muncul "Terlalu banyak percobaan login"?
**A:** Sistem mengunci IP setelah 5 percobaan gagal dalam 15 menit. Tunggu 15 menit, atau hubungi superadmin yang bisa hapus record di tabel `tamdes_login_attempts` jika mendesak.

### Q: Data saya tidak muncul di laporan tahunan responden?
**A:** Cek apakah kategori instansi & pemanfaatan terisi. Tabel `tamdes_responden_tahunan` digenerate dari hasil konsultasi yang lengkap (judul publikasi, digunakan_nasional, dll).

### Q: Bisakah saya download foto tamu?
**A:** Foto bisa dilihat di halaman detail tamu. Untuk download massal/ekspor, hubungi developer — tidak ada UI standar karena sensitivitas data biometrik.

### Q: Apakah ada mobile app?
**A:** Belum. Dashboard adalah **web responsive** — bisa diakses dari tablet/HP melalui browser. Untuk kiosk kios, dibutuhkan device dengan kamera (Chrome/Edge modern).

### Q: Bagaimana jika listrik mati saat tamu sedang daftar?
**A:** Data yang **belum di-submit** akan hilang (tidak ada draft auto-save). Setelah listrik kembali, tamu daftar ulang. Anti-double-submit di backend mencegah duplikasi kalau dia sempat klik submit sebelum mati.

### Q: Bagaimana jika tamu salah submit evaluasi (skor terlalu rendah/tinggi)?
**A:** Submit evaluasi **tidak bisa diubah** lewat UI — sengaja, untuk integritas data IKM. Kalau benar-benar perlu koreksi (mis. tamu komplain), superadmin bisa edit langsung di DB (`tamdes_evaluasi_detail`) dan log alasan di catatan internal.

### Q: Sistem lambat di jam sibuk?
**A:** Cek dengan tim IT:
- Apache & PHP-FPM tidak crash (cek `pm2 logs` & `systemctl status apache2 php7.4-fpm`)
- Database tidak overload (`SHOW PROCESSLIST`)
- Network OK
- Saat sibuk, prioritas: matikan fitur face recognition sementara → lebih cepat (tamu pakai jalur "tamu baru").

### Q: Privasi data tamu — bagaimana kebijakannya?
**A:** Data tamu (termasuk foto wajah) disimpan di server lokal BPS Malut, **tidak dibagikan ke pihak luar**. Tamu memiliki hak:
- Mengakses data mereka
- Meminta koreksi data
- Meminta penghapusan (right to be forgotten)

Untuk permintaan resmi, tamu mengirim surat ke Kepala BPS Malut atau email ke alamat resmi PST.

---

## 📞 Kontak

- **Helpdesk:** Tim PST BPS Malut — di front office langsung
- **Bug/Error sistem:** lapor ke Tim IT BPS Malut
- **Dokumentasi developer:** lihat folder `docs/` di repository

---

> Dokumen ini akan diperbarui seiring sistem berkembang. Versi: April 2026.

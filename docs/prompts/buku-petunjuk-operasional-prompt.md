# Prompt untuk Claude Desktop — Generate Buku Petunjuk Operasional Aplikasi Buku Tamu Digital PST

> Cara pakai: copy seluruh isi blok di bawah (mulai dari baris `# Tugas` sampai akhir), paste ke Claude Desktop. Claude Desktop akan generate dokumen lengkap minimal 30 halaman.

---

```
# Tugas

Susun **Buku Petunjuk Operasional Aplikasi Buku Tamu Digital PST** lengkap, dalam format Markdown yang siap di-convert ke `.docx` via Pandoc. Dokumen ini adalah bukti fisik kegiatan III.A.17 (Menyusun Petunjuk Operasional Program Aplikasi Sistem Informasi) untuk pengajuan angka kredit Pranata Komputer Ahli Pertama BPS, mengacu PerBPS 2/2021.

Target panjang: **minimal 30 halaman utama** (Bab I–X) di luar halaman pelengkap (sampul, pengesahan, kata pengantar, daftar isi/gambar/tabel, daftar pustaka, lampiran), agar memenuhi syarat angka kredit penuh 0,165.

---

# 1. Konteks Aplikasi (Wajib Dipakai — Jangan Mengarang)

## 1.1 Identitas

| Atribut | Nilai |
|---|---|
| Nama aplikasi | Aplikasi Buku Tamu Digital PST |
| Instansi | BPS Provinsi Maluku Utara |
| URL produksi (kiosk + admin) | https://bukutamu.bpsmalut.com |
| URL pendukung (Dashboard Data Strategis untuk viewer/pimpinan) | https://malika.bpsmalut.com |
| Versi dokumen | 1.0 |
| Tanggal pengesahan | 13 Mei 2026 |
| Penyusun | Wisnu Candra Gumelar |
| NIP penyusun | 199706092019121001 |
| Jabatan penyusun | Pranata Komputer Ahli Pertama |

## 1.2 Fungsi Aplikasi

Aplikasi mencatat kunjungan Pelayanan Statistik Terpadu (PST) yang terintegrasi dengan instrumen **Survei Kebutuhan Data (SKD)** dan modul **evaluasi kepuasan layanan** (16 indikator IKM skala 1–10). Integrasi ini berlaku **kondisional** — hanya untuk empat Jenis Layanan inti. Jenis Layanan non-inti tetap dicatat tapi tidak melewati instrumen SKD/evaluasi.

## 1.3 Taksonomi Jenis Layanan (tiga grup, saling eksklusif)

| Grup | Jenis Layanan | Wajib SKD + Evaluasi? | Prefix Antrian |
|---|---|---|---|
| **SKD** | Perpustakaan | Ya | **P** |
| **SKD** | Konsultasi Statistik | Ya | **K** |
| **SKD** | Rekomendasi Kegiatan Statistik | Ya | **R** |
| **SKD** | Penjualan Produk Statistik | Ya | **J** |
| **DTSEN** | Konsultasi DTSEN | Tidak (alur khusus, antrian + formulir terpisah dari SKD) | **D** |
| **Resepsionis** | Keperluan Pimpinan | Tidak | — (catat manual) |
| **Resepsionis** | Lainnya | Tidak | — (catat manual) |

Mutually exclusive: satu kunjungan tidak boleh mencampur Jenis Layanan dari grup berbeda. Sistem menolak kombinasi lintas-grup di lapis frontend dan backend.

## 1.4 Peran Pengguna

| Peran | Cakupan |
|---|---|
| **Konsumen** | Pengunjung PST. Mendaftar kunjungan secara mandiri di *kiosk* publik (termasuk pemindaian wajah untuk kunjungan ulang dan pencarian nama manual sebagai alur cadangan). Setelah sesi layanan, mengisi SKD dan evaluasi kepuasan layanan secara mandiri di tablet evaluasi (khusus empat Jenis Layanan inti). |
| **Resepsionis** | Menangani kunjungan grup *Resepsionis* (Lainnya, Keperluan Pimpinan) yang tidak melalui antrian *kiosk*: mencatat kunjungan secara manual lewat halaman *Daftar Kunjungan* admin, memilih sarana ruangan internal (kode 33–36 — Halmahera, Vicon, Gamalama, Pimpinan), dan mengarahkan konsumen ke ruangan tujuan. Resepsionis **tidak** mengoperasikan modul pemindaian wajah; modul tersebut dijalankan konsumen sendiri di *kiosk* publik. |
| **Petugas PST** | Petugas yang melayani di meja PST. Mengisi rincian konsultasi / transaksi sesuai Jenis Layanan; menyelesaikan sesi layanan. |
| **Petugas DTSEN** | Petugas khusus Konsultasi DTSEN. Menggunakan antrian dan formulir DTSEN yang terpisah dari Konsultasi SKD (prefix antrian **D**); sesi berakhir langsung ke status `selesai`, tanpa evaluasi tablet. |
| **Admin** (Tim Diseminasi PST) | Melihat seluruh kunjungan, menghapus kunjungan dengan *cascade* + *audit log*, melihat *audit log*, menarik laporan SKD/IKM/rekap kunjungan, mengelola pengguna aplikasi. |
| **Superadmin** | Sama seperti Admin, ditambah hak *bypass* gate finalisasi untuk koreksi data anomali. |

> Catatan: aplikasi Buku Tamu Digital PST **tidak** memiliki peran `viewer` di sisi *backend*. Dashboard ringkasan untuk pimpinan disajikan oleh aplikasi pendukung terpisah di https://malika.bpsmalut.com (Dashboard Data Strategis), yang membaca data PST dari sumber yang sama tetapi memiliki *codebase*, *deployment*, dan otentikasi sendiri. Setiap pembahasan dashboard pimpinan di dokumen ini merujuk ke aplikasi pendukung tersebut, bukan ke modul internal Buku Tamu.

## 1.5 Fitur Kunci Sistem

1. **Soft-correct gate tiga lapis**: kunjungan grup SKD yang belum mengisi evaluasi tidak dapat ditransisikan ke status `selesai`. Gate diterapkan di:
   - Endpoint perubahan status kunjungan (PUT `/api/visits/{id}/status`)
   - Endpoint perubahan status konsultasi (PUT `/api/consultations/{id}`)
   - Endpoint submit evaluasi (POST `/api/evaluations/{id}`)
   Lapisan ini bersifat *defense-in-depth*: salah satu lapis sudah cukup memblokir, kombinasinya membuat status `selesai` tanpa evaluasi mustahil bagi grup SKD. Peran `admin` / `superadmin` dapat *override* lewat jalur khusus (`bypass role`) untuk koreksi data.
2. **Continuation token HMAC** untuk endpoint *kiosk* publik (tanpa *login*):
   - *Token* `profile-update` — masa aktif **5 menit**, di-*mint* saat konsumen membuka modal pelengkap profil.
   - *Token* `eval-submit` — masa aktif **10 menit**, di-*mint* saat tablet evaluasi memuat kunjungan terlama yang menunggu evaluasi.
   - *Format token*: `{purpose}.{bound_id}.{exp_unix}.{base64url-hmac-sha256}`. *Purpose claim* mencegah *replay* lintas *endpoint*.
3. **Whitelist sarana per grup layanan** (validasi *defense-in-depth* di *frontend* + *backend*, daftar bersifat tetap di kode sumber — bukan dikonfigurasi lewat antarmuka *admin*):
   - Grup SKD: kode 1, 2, 4, 9, 16, 32 (datang langsung, *email*, telepon, *web*, media sosial, surat)
   - Grup DTSEN: kode 1 (datang langsung saja)
   - Grup Resepsionis: kode 33–36 (ruangan internal: Halmahera, Vicon, Gamalama, Pimpinan)
4. **DELETE cascade dengan audit log** untuk penghapusan kunjungan oleh admin/superadmin. Urutan eksekusi:
   1. *Snapshot* baris *full* ke `audit_log`
   2. Hapus baris terkait di `konsultasi_pengunjung`
   3. Hapus baris terkait di `dtsen_konsultasi`
   4. Hapus baris terkait di `tamdes_evaluasi_detail`
   5. Hapus baris induk di `tamdes_kunjungan`
5. **Pemindaian wajah** untuk identifikasi konsumen kunjungan ulang, **dijalankan oleh konsumen secara mandiri di *kiosk* publik** (halaman `/kiosk/face-recognize`). *Pipeline*: *warmup* 600 ms → *sampling* 5 *descriptor* → *averaging* → pencocokan dengan ambang batas 0,55 dan *margin* 0,08. Alur cadangan: pencarian nama manual lewat modal di *kiosk*; jika tetap tidak ditemukan, konsumen jatuh ke alur pendaftaran baru. Resepsionis tidak terlibat dalam pemindaian wajah.
6. **Prefix antrian otomatis** per Jenis Layanan (P/K/R/J/D) — memudahkan identifikasi grup layanan di TV antrian.
7. **Pencetakan tiket termal lokal** — *browser kiosk* mengirim *payload* ke `localhost:5300` di komputer *kiosk* (tidak melalui *backend* pusat) untuk *latency* minimal dan *resilience* terhadap gangguan jaringan WAN.
8. **Laporan**: SKD, evaluasi kepuasan layanan (IKM), rekap kunjungan harian/bulanan/triwulanan/tahunan. Penarikan via halaman *admin* dengan opsi *export* CSV.

## 1.6 Daftar 16 Indikator IKM (Wajib Dilampirkan di Bab VI atau Lampiran)

1. Informasi pelayanan pada unit layanan ini tersedia melalui media elektronik maupun non-elektronik.
2. Persyaratan pelayanan yang ditetapkan mudah dipenuhi/disiapkan oleh konsumen.
3. Prosedur/alur pelayanan yang ditetapkan mudah diikuti/dilakukan.
4. Jangka waktu penyelesaian pelayanan yang diterima sesuai dengan yang ditetapkan.
5. Biaya pelayanan yang dibayarkan sesuai dengan biaya yang ditetapkan.
6. Produk pelayanan yang diterima sesuai dengan yang dijanjikan.
7. Sarana dan prasarana pendukung pelayanan memberikan kenyamanan.
8. Data BPS mudah diakses melalui sarana utama yang digunakan.
9. Petugas pelayanan dan/atau aplikasi pelayanan *online* merespon dengan baik.
10. Petugas pelayanan dan/atau aplikasi pelayanan *online* mampu memberikan informasi yang jelas.
11. Fasilitas pengaduan PST mudah diakses (contoh: kotak saran dan pengaduan, *website* https://webapps.bps.go.id/pengaduan, *e-mail* bpshq@bps.go.id).
12. Tidak ada diskriminasi dalam pelayanan.
13. Tidak ada pelayanan di luar prosedur/kecurangan pelayanan.
14. Tidak ada penerimaan gratifikasi.
15. Tidak ada pungutan liar (*pungli*) dalam pelayanan.
16. Tidak ada praktik percaloan dalam pelayanan.

Skala penilaian: Likert 1–10 (1 = sangat tidak puas, 10 = sangat puas). Skor keseluruhan terpisah, juga skala 1–10. Untuk konsultasi yang menghasilkan data, konsumen juga memberikan skor kualitas per data.

---

# 2. Kebutuhan Juknis (Wajib Dipenuhi — PerBPS 2/2021 Bukti Fisik III.A.17)

Dokumen wajib memuat:
1. **Penjelasan singkat**: nama aplikasi, target pengguna per peran, cakupan, tujuan, prasyarat (perangkat keras, perangkat lunak, jaringan), jumlah halaman di metadata, abstrak, versi (histori), keterangan penulis.
2. **Lampiran petunjuk pengoperasian sistem komputer** dasar (menyalakan/mematikan komputer, membuka *browser*, memastikan koneksi internet, *screenshot* tangkapan layar).
3. **Halaman pengesahan** dengan tanda tangan **atasan PPK langsung** (gunakan istilah ini persis).

---

# 3. Ketentuan Format

- Kertas A4, spasi 1,5
- Minimal **30 halaman utama** (Bab I–X) di luar pelengkap
- Setiap fitur **wajib** dilengkapi *screenshot* + penjelasan. Halaman yang hanya berisi *screenshot* tanpa penjelasan **tidak dihitung** per juknis.
- Bahasa Indonesia formal: gunakan "Anda" / "petugas" / "konsumen" (bukan "kamu")
- Istilah bahasa Inggris di-*italic*
- Gunakan kalimat aktif
- Hindari diksi *over-engineered* (jangan: "kontinuitas operasional", "kapasitas diseminasi", "sinergi", "strategis", dll.)
- Konsisten: "evaluasi kepuasan layanan" (bukan "evaluasi kepuasan" telanjang)
- Konsisten: "BPS Provinsi Maluku Utara" (bukan "BPS Malut")
- "antara 10 kabupaten/kota" (bukan "antar 10")
- Awalan terikat **digabung** tanpa spasi: "antarwilayah", "pascaimplementasi", "antarindikator", "prabencana"

---

# 4. Struktur Dokumen yang Diinginkan

## [SAMPUL DEPAN]

- Judul: **PETUNJUK OPERASIONAL APLIKASI BUKU TAMU DIGITAL PST**
- Subjudul: **BPS Provinsi Maluku Utara**
- Versi: **1.0**
- Bulan/Tahun: **Mei 2026**
- Logo BPS (placeholder: `[GAMBAR — Logo BPS Provinsi Maluku Utara]`)
- Nama penyusun: Wisnu Candra Gumelar
- NIP: 199706092019121001

## [HALAMAN PENGESAHAN]

- Tabel pengesahan: disusun oleh penyusun, disahkan oleh **atasan PPK langsung**
- Kolom: Penyusun & Pengesah
- Tanggal: 13 Mei 2026
- Placeholder tanda tangan + nama + NIP atasan PPK langsung

## [KATA PENGANTAR]

Satu halaman, ditulis dari sudut pandang penyusun. Konteks: kebutuhan panduan baku setelah operasional aplikasi berjalan. Hindari klise.

## [PENJELASAN SINGKAT / METADATA]

- Nama aplikasi
- Target pengguna (per peran)
- Cakupan
- Tujuan
- Prasyarat (perangkat keras, perangkat lunak, jaringan)
- Jumlah halaman (placeholder: `[ISI OTOMATIS]`)
- Abstrak (1 paragraf 5–10 baris)
- Versi dokumen: 1.0 (13 Mei 2026)
- Penyusun: Wisnu Candra Gumelar, Pranata Komputer Ahli Pertama BPS Provinsi Maluku Utara

## [DAFTAR ISI] [DAFTAR GAMBAR] [DAFTAR TABEL]

## BAB I PENDAHULUAN

1.1 Latar Belakang Aplikasi
1.2 Tujuan Aplikasi
1.3 Ruang Lingkup Petunjuk Operasional
1.4 Target Pengguna
1.5 Definisi dan Singkatan (SKD, IKM, IPP, PST, DTSEN, PAPI, HMAC, *soft-correct gate*, *whitelist*, *continuation token*, prefix antrian)

## BAB II PRASYARAT SISTEM

2.1 Perangkat Keras Minimum
2.2 Perangkat Lunak (*browser* yang didukung, versi minimum)
2.3 Konektivitas Jaringan
2.4 Akun Pengguna dan Hak Akses

## BAB III ARSITEKTUR UMUM APLIKASI

3.1 Komponen Aplikasi (*Kiosk* Publik untuk Konsumen, Panel *Admin* untuk Petugas dan Resepsionis, Tablet Evaluasi, Layanan Cetak Termal Lokal `localhost:5300`, *Backend* API CodeIgniter, basis data MariaDB `db_tamdes`)
3.2 Alur Layanan PST *End-to-End* (lima diagram lintas-fungsi: Master, Pendaftaran Baru, Pendaftaran Ulang, Pelayanan dan Finalisasi, Evaluasi Tablet — sajikan **gambar** diagram dengan rujukan teks ke dokumen pendamping `docs/FLOW_PENGUNJUNG.md` di *repository*)
3.3 Taksonomi Tiga Grup Layanan (SKD / DTSEN / Resepsionis) dan Konsekuensi terhadap Antrian, Evaluasi, dan Status Finalisasi
3.4 Prefix Antrian per Layanan (P / K / R / J / D)
3.5 Pembagian Tanggung Jawab Peran (matriks: peran × kemampuan)

## BAB IV PETUNJUK PENGOPERASIAN UNTUK RESEPSIONIS

4.1 Login ke Aplikasi (halaman *admin*, peran `resepsionis`)
4.2 Penanganan Konsumen Grup SKD/DTSEN — Pengarahan ke *Kiosk* Publik (pendaftaran mandiri oleh konsumen di *kiosk*; resepsionis hanya membantu jika konsumen kesulitan)
4.3 Pencatatan Manual Kunjungan Grup Resepsionis (Lainnya, Keperluan Pimpinan)
- 4.3.1 Membuka Halaman *Daftar Kunjungan* dan Memulai Entri Baru
- 4.3.2 Pengisian Identitas Konsumen
- 4.3.3 Pemilihan Jenis Layanan (Lainnya / Keperluan Pimpinan) dan Sarana Ruangan Internal (kode 33–36)
- 4.3.4 Penyimpanan dan Pengarahan Konsumen ke Ruangan Tujuan

4.4 Penanganan Kasus Khusus (konsumen tidak ditemukan saat pencarian, pergantian Jenis Layanan, salah pilih grup)
4.5 Logout

## BAB V PETUNJUK PENGOPERASIAN UNTUK PETUGAS PST DI MEJA

5.1 Login
5.2 Pengisian Konsultasi Statistik
5.3 Pengisian Rekomendasi Kegiatan Statistik
5.4 Pengisian Penjualan Produk Statistik
5.5 Pengisian Layanan Perpustakaan
5.6 Pengisian Konsultasi DTSEN
5.7 Penyelesaian Sesi Layanan
5.8 Soft-correct Gate: Apa yang Harus Dilakukan jika Sesi Tidak Bisa Diselesaikan

## BAB VI PETUNJUK PENGOPERASIAN *KIOSK* PUBLIK UNTUK KONSUMEN

6.1 Layar Sambutan dan Pemilihan Jenis Layanan + Sarana
6.2 Penentuan Status Kunjungan (Baru / Ulang)
6.3 Identifikasi Konsumen Kunjungan Ulang melalui Pemindaian Wajah
- 6.3.1 Tahap *Warmup* Kamera (600 ms)
- 6.3.2 Tahap *Sampling* — Pengambilan 5 *descriptor* Wajah
- 6.3.3 Pencocokan dengan Ambang Batas 0,55 dan *Margin* 0,08
- 6.3.4 Alur Cadangan: Pencarian Nama Manual jika Wajah Tidak Cocok

6.4 Pendaftaran Konsumen Baru (formulir profil + pengambilan foto wajah)
6.5 Penerbitan dan Pencetakan Tiket Antrian (cetak termal lokal lewat `localhost:5300`)
6.6 Pengisian SKD (Survei Kebutuhan Data) Pasca Sesi Layanan
6.7 Pengisian Evaluasi Kepuasan Layanan di Tablet Evaluasi (16 indikator IKM skala 1–10)
6.8 Identifikasi Sesi via *Continuation Token* HMAC dan Batas Waktu Sesi (5 menit *profile-update*, 10 menit *eval-submit*)
6.9 Penyelesaian Sesi

## BAB VII PETUNJUK PENGOPERASIAN UNTUK ADMIN

7.1 Login Admin
7.2 Lihat Daftar Kunjungan dan Pencarian Lanjutan
7.3 Penghapusan Kunjungan (DELETE *Cascade* + *Audit Log*) — termasuk peringatan bahwa operasi bersifat *hard delete* dan hanya dapat dieksekusi oleh peran `admin` / `superadmin`
7.4 Lihat *Audit Log* (rekam jejak penghapusan dan perubahan status)
7.5 Pengelolaan Pengguna Aplikasi (peran `resepsionis`, `petugas_pst`, `admin`)
7.6 Penarikan Laporan SKD
7.7 Penarikan Laporan Evaluasi Kepuasan Layanan (IKM)
7.8 Penarikan Rekap Kunjungan Harian / Bulanan / Triwulanan / Tahunan
7.9 Daftar Referensi *Whitelist* Sarana per Grup Layanan (bersifat informatif — daftar tetap di kode sumber `Api_base.php`, perubahan memerlukan *deployment* aplikasi)
7.10 Logout

## BAB VIII AKSES DASHBOARD PIMPINAN (APLIKASI PENDUKUNG MALIKA)

> Bab ini menjelaskan akses pimpinan ke ringkasan PST. Aplikasi Buku Tamu Digital PST sendiri tidak memiliki antarmuka khusus pimpinan; dashboard ringkasan disajikan oleh aplikasi pendukung **Malika — Dashboard Data Strategis** di https://malika.bpsmalut.com yang membaca data dari sumber yang sama. Petunjuk pengoperasian penuh Malika berada di luar cakupan dokumen ini.

8.1 Membuka https://malika.bpsmalut.com dan Otentikasi Pimpinan
8.2 Navigasi ke Modul *Ringkasan PST*
8.3 Membaca Indikator IKM Agregat
8.4 Membaca Rekap Kunjungan
8.5 Pertanyaan / Permintaan Penambahan Indikator — diteruskan ke Tim Diseminasi PST

## BAB IX PENANGANAN KENDALA UMUM (FAQ / *TROUBLESHOOTING*)

9.1 Tidak Bisa *Login*
9.2 Pemindaian Wajah Gagal Mengenali Konsumen Ulang (kamera tidak terdeteksi, *descriptor* tidak konvergen, ambang batas tidak terlewati) — pakai alur cadangan pencarian nama manual
9.3 Tiket Antrian Tidak Tercetak (cek koneksi ke `localhost:5300` di komputer *kiosk*, cek printer termal)
9.4 Sesi SKD / Evaluasi *Time-out* (token kedaluwarsa, langkah memulai ulang)
9.5 *Soft-correct Gate* Memblokir Penyelesaian Sesi SKD (langkah: pastikan konsumen sudah mengisi evaluasi tablet; eskalasi ke *admin* bila perlu *bypass*)
9.6 Validasi Sarana Ditolak (kombinasi sarana di luar *whitelist* grup layanan)
9.7 Validasi Layanan Lintas Grup Ditolak
9.8 Laporan Tidak Bisa Diunduh
9.9 Kontak Tim Diseminasi PST

## BAB X PEMUTAKHIRAN DAN VERSI APLIKASI

10.1 Cara Memeriksa Versi Aplikasi (informasi versi pada halaman *Tentang* di panel admin)
10.2 Mekanisme Pemutakhiran (pemutakhiran dijalankan oleh Tim Diseminasi PST melalui *deployment* di server — tidak ada antarmuka pembaruan mandiri untuk pengguna akhir)
10.3 Cara Melaporkan Permintaan Fitur atau Perbaikan kepada Tim Diseminasi PST
10.4 Histori Versi (tabel: Versi | Tanggal | Penyusun | Ringkasan Perubahan)

## LAMPIRAN A: PETUNJUK PENGOPERASIAN SISTEM KOMPUTER

A.1 Menyalakan dan Mematikan Komputer
A.2 Membuka *Browser* dan Mengakses Aplikasi
A.3 Memastikan Koneksi Internet Stabil
A.4 Cara Menyimpan Tangkapan Layar untuk Pelaporan Kendala

## LAMPIRAN B: GLOSARIUM

Lengkap untuk seluruh istilah teknis. Wajib termasuk: SKD, IKM, IPP, PST, DTSEN, PAPI, HMAC, *soft-correct gate*, *whitelist*, *continuation token*, prefix antrian, kuesioner kondisional, *cascade*, *audit log*, *bypass role*, *defense-in-depth*, *kiosk*, *queue prefix*, *descriptor* wajah, *threshold*, *margin*, *warmup*, *sampling*, *averaging*, peran resepsionis, peran petugas PST, peran admin, peran superadmin, Malika (Dashboard Data Strategis pendukung).

## LAMPIRAN C: KONTAK DAN DUKUNGAN

- Tim Diseminasi dan Layanan Statistik BPS Provinsi Maluku Utara
- Helpdesk teknis (jam operasional)
- *Email* + nomor telepon (placeholder)

---

# 5. Aturan Penulisan Per Bab Pengoperasian (Bab IV–VIII)

Setiap subbab pengoperasian **wajib** memuat struktur berikut, dalam urutan ini:

1. **Tujuan langkah** — satu kalimat ringkas
2. **Prasyarat langkah** — *list* prasyarat (akun, sesi *login*, data, peran)
3. **Langkah demi langkah bernomor** — instruksi konkret: "Klik X. Pilih Y. Isi Z."
4. **Placeholder *screenshot*** dengan *caption* — format wajib:
   ```
   [GAMBAR 4.1 — Halaman login resepsionis menampilkan formulir username dan password]
   ```
   (nomor gambar mengikuti nomor bab)
5. **Catatan / peringatan** (jika ada) — contoh: "Pastikan *whitelist* sarana sudah di-*set* sebelum mencatat kunjungan grup tersebut, karena sistem akan menolak kombinasi sarana yang tidak terdaftar."

# 6. Aturan Penulisan FAQ (Bab IX)

Format Q&A. Setiap entri:
- **Gejala** (yang dilihat / dialami pengguna)
- **Kemungkinan penyebab** (urutkan dari paling sering)
- **Langkah penyelesaian** (bernomor, eskalasi ke helpdesk di akhir)

# 7. Output yang Diharapkan

- **Format**: Markdown lengkap, siap di-*convert* ke `.docx` via Pandoc dengan perintah berikut:
  ```
  pandoc buku.md -o buku.docx --reference-doc=template.docx --toc --toc-depth=3
  ```
- Sertakan *frontmatter* YAML di awal dokumen dengan metadata judul, penyusun, tanggal, versi.
- Tiap heading mengikuti hierarki Markdown standar (`#`, `##`, `###`).
- Tabel pakai sintaks Pandoc `pipe table`.
- *Placeholder screenshot* pakai blok kuotasi atau syntax `[GAMBAR x.y — deskripsi]` agar mudah saya cari dan ganti.

# 8. Hal yang TIDAK Boleh Dilakukan

1. **Jangan mengarang fitur** yang tidak ada di "Konteks Aplikasi" di atas. Kalau ragu, tandai dengan `[KONFIRMASI: deskripsi pertanyaan]`.
2. **Jangan pakai istilah Indonesia yang berpura-pura formal** ("kontinuitas operasional", "kapasitas diseminasi", "sinergi multistakeholder", dst.).
3. **Jangan menulis halaman penuh screenshot tanpa narasi**. Setiap gambar harus diapit penjelasan langkah.
4. **Jangan singkat "BPS Provinsi Maluku Utara"** menjadi "BPS Malut" di dalam dokumen formal.
5. **Jangan generate placeholder tanda tangan dengan nama palsu** untuk atasan PPK langsung — biarkan kosong agar diisi pejabat sesuai SK saat pengesahan.
6. **Jangan menyatakan adanya peran `viewer`** di aplikasi Buku Tamu. Pimpinan mengakses ringkasan via aplikasi pendukung Malika (https://malika.bpsmalut.com).
7. **Jangan menyatakan resepsionis melakukan pemindaian wajah.** Pemindaian wajah dijalankan konsumen secara mandiri di *kiosk* publik.
8. **Jangan menyatakan adanya antarmuka admin untuk mengubah *whitelist* sarana atau notifikasi pembaruan otomatis.** Keduanya tidak ada di aplikasi.

# 8.1 Estimasi Alokasi Halaman (Pedoman, Bukan Batas Keras)

Agar Bab I–X mencapai minimal 30 halaman A4 spasi 1,5, gunakan alokasi indikatif berikut. Angka boleh bergeser; yang penting **total ≥ 30 halaman utama**.

| Bagian | Alokasi indikatif | Catatan |
|---|---|---|
| Bab I Pendahuluan | 2 halaman | Latar belakang ringkas, hindari basa-basi |
| Bab II Prasyarat Sistem | 2 halaman | Tabel perangkat keras + lunak |
| Bab III Arsitektur Umum | 3 halaman | Sajikan 5 diagram dari `FLOW_PENGUNJUNG.md` sebagai gambar |
| Bab IV Resepsionis | 3 halaman | Fokus pencatatan manual grup Resepsionis |
| Bab V Petugas PST | 5 halaman | Empat layanan SKD inti + DTSEN + soft-correct gate |
| Bab VI *Kiosk* Konsumen | 6 halaman | Bab terpanjang: pemindaian wajah, SKD, evaluasi IKM |
| Bab VII Admin | 4 halaman | Termasuk DELETE *cascade*, *audit log*, laporan |
| Bab VIII Dashboard Pimpinan (Malika) | 2 halaman | Ringkas — Malika di luar cakupan utama |
| Bab IX FAQ / *Troubleshooting* | 3 halaman | Sembilan kategori kendala |
| Bab X Pemutakhiran dan Versi | 1 halaman | Histori versi dalam tabel |
| **Total Bab I–X** | **31 halaman** | Memenuhi syarat ≥ 30 |

# 9. Mulai

Generate dokumen lengkap mulai dari sampul depan. Jangan minta konfirmasi tambahan sebelum mulai — gunakan konteks aplikasi di Bagian 1 sebagai sumber kebenaran. Jika ada bagian yang benar-benar perlu klarifikasi (misal kontak helpdesk konkret atau nama atasan PPK), pakai placeholder eksplisit `[KONFIRMASI: ...]` dan lanjutkan generate.

Target halaman: **minimal 30 halaman Bab I–X**.
```

---

## Catatan untuk Anda (Wisnu)

- Prompt di atas sudah self-contained — Claude Desktop tidak perlu akses ke codebase atau dokumen lain.
- Fakta-fakta teknis (16 indikator IKM, 3-tier taxonomy, prefix antrian, whitelist sarana, durasi token HMAC, pipeline face recognition) sudah disisipkan agar Claude Desktop tidak mengarang.
- Bagian `[KONFIRMASI: ...]` akan muncul kalau Desktop merasa perlu data eksternal — Anda tinggal isi sebelum convert ke `.docx`.
- Setelah Claude Desktop selesai generate, jalankan:
  ```bash
  pandoc buku.md -o buku.docx --toc --toc-depth=3
  ```
  (template `.docx` opsional kalau mau styling konsisten dengan template BPS)
- Untuk *screenshot* placeholder: cari pola `[GAMBAR x.y —` di file Markdown, ganti dengan `![caption](path/to/screenshot.png)` setelah ambil tangkapan layar.

## Catatan Tambahan: Hasil *Deep Check* terhadap Kode Sumber (20 Mei 2026)

Tiga poin yang sebelumnya ditandai sebagai "belum diverifikasi" telah ditelusuri ulang dan **diserap langsung ke dalam prompt**, sehingga *Claude Desktop* tidak perlu lagi menebak:

1. **Peran "Viewer" dihapus dari taksonomi.** Verifikasi pada `backend/application/modules/api/controllers/Auth.php` mengkonfirmasi tidak ada peran `viewer`; peran resmi adalah `resepsionis`, `petugas_pst`, `admin`, `superadmin` (`operator` adalah peran *legacy*). Bab VIII di-*rewrite* sebagai "Akses Dashboard Pimpinan via Malika" untuk merujuk aplikasi pendukung https://malika.bpsmalut.com.
2. **Subbab "Pengelolaan Whitelist Sarana" diubah menjadi referensi *read-only*.** `Api_base.php::validate_sarana_for_layanan` (baris 206–245) memuat daftar sarana per grup secara *hardcode*; tidak ada antarmuka admin untuk mengubahnya. Subbab 7.9 sekarang menjelaskan daftar bersifat informatif dan perubahan memerlukan *deployment*.
3. **Subbab "Notifikasi Pembaruan" dihilangkan.** Fitur ini tidak ada di kode (tidak ada *service worker* PWA aktif maupun *push notification*). Bab X di-*rewrite* menjelaskan bahwa pemutakhiran dijalankan Tim Diseminasi via *deployment* dan pengguna melaporkan permintaan fitur melalui jalur kontak di Lampiran C.

Selain itu, tiga koreksi alur yang teridentifikasi saat *cross-check*:

4. **Pemindaian wajah dipindahkan dari Bab IV (Resepsionis) ke Bab VI (Konsumen / *Kiosk*).** `frontend/src/pages/kiosk/FaceCapturePage.tsx` dan `FaceRecognizePage.tsx` membuktikan modul pemindaian wajah dijalankan konsumen secara mandiri di *kiosk* publik. Resepsionis tidak memiliki halaman pemindaian wajah; tugas resepsionis adalah pencatatan manual grup *Resepsionis* (kode sarana 33–36) melalui halaman *Daftar Kunjungan* admin.
5. **Konstanta *tuning* pemindaian wajah** divalidasi ulang dari `frontend/src/hooks/useCamera.ts` (`WARMUP_MS = 600`, `SAMPLE_TARGET = 5`) dan `frontend/src/lib/face-detection.ts` (`threshold = 0.55`, `margin = 0.08`). Nilai-nilai ini tidak berubah dan sudah benar di prompt.
6. **Diagram alur Bab III** sebaiknya merujuk ke `docs/FLOW_PENGUNJUNG.md` di *repository* — sudah ada lima diagram *swimlane* (ISO 5807) yang siap pakai, sehingga *Claude Desktop* tidak perlu menggambar ulang dari nol; cukup di-*embed* sebagai gambar dengan *caption*.

## Poin yang Masih Memerlukan Konfirmasi Manual

- **Nama dan NIP atasan PPK langsung** untuk halaman pengesahan — tidak dapat ditebak dari kode; biarkan placeholder `[KONFIRMASI: ...]` sesuai aturan di Bagian 8 poin 5.
- **Kontak *helpdesk* konkret** (nomor telepon, *e-mail*, jam operasional) untuk Lampiran C.
- **Versi minimum *browser*** yang didukung untuk Bab II — sesuaikan dengan kebijakan TI BPS Maluku Utara saat ini.

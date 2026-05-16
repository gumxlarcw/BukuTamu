# Copilot Instructions — Tamdes Web / BukuTamu (CodeIgniter 3 + HMVC)

Repo ini adalah aplikasi **Buku Tamu** berbasis **CodeIgniter 3** dengan **HMVC (Modular Extensions MX)**. Instruksi ini mengarahkan Copilot agar perubahan konsisten dengan arsitektur, gaya kode, dan praktik keamanan proyek.

## Prioritas Utama

1. **Perubahan minimal dan tepat sasaran**: edit hanya file yang relevan dengan permintaan.
2. **Ikuti pola HMVC**: fitur baru/ubah alur umumnya berada di `application/modules/<module>/`.
3. **Jangan ubah core framework**: hindari mengubah `system/` dan `application/third_party/` kecuali diminta eksplisit.
4. **Keamanan input**: validasi, sanitasi, dan batasi data yang diproses/dicetak.

## Struktur Proyek (ringkas)

- Modul HMVC: `application/modules/`
  - `admin/`, `layanan/`, `recognize/`, `selamat_datang/`
- Controller global: `application/controllers/`
- Core kustom: `application/core/` (`MY_Controller`, `MY_Loader`, `MY_Router`)
- View global: `application/views/`
- Asset statis: `assets/`

## Aturan Implementasi (Backend PHP / CI3)

- **Gunakan pola CI3**:
  - Controller tipis, logika bisnis/DB di Model.
  - Akses DB via **Query Builder** (`$this->db->...`) dan hindari query mentah kecuali benar-benar perlu.
- **Validasi input**:
  - Gunakan `form_validation` untuk POST form.
  - Jangan percaya input dari `$_GET/$_POST`; gunakan `$this->input->get_post()` / `$this->input->post()`.
- **Output aman**:
  - Escape output di view (mis. `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`) untuk data yang berasal dari user.
  - Hindari mencetak HTML mentah dari input user.
- **Session & akses admin**:
  - Endpoint admin harus dilindungi (cek session/role) sesuai pola yang sudah ada di modul `admin`.
- **Routing**:
  - Jika menambah endpoint baru, update `application/config/routes.php` hanya bila dibutuhkan.

## Aturan Implementasi (View / Frontend)

- **Jangan menambah framework baru** (React/Vue, dsb.) tanpa permintaan.
- Simpan perubahan JS/CSS di `assets/` dan ikuti pola pemanggilan yang sudah ada.
- Hindari perubahan UI besar; fokus ke kebutuhan yang diminta.

## Konvensi Kode

- Pertahankan gaya yang sudah ada di file (indentasi, penamaan fungsi/variabel).
- Penamaan:
  - Controller: `PascalCase.php` (CI3 standar)
  - Model: `M_*` bila mengikuti modul yang ada (mis. `M_admin`, `M_layanan`).
- Jangan menambah komentar panjang; tambahkan hanya jika benar-benar menjelaskan *mengapa*.

## File yang Sebaiknya Tidak Diubah

- `system/**` (core CodeIgniter)
- `application/third_party/**` (MX)
- File model weights/asset besar di `assets/models/**`

Jika perubahan pada area ini diperlukan, jelaskan alasannya dan usulkan alternatif yang lebih aman terlebih dahulu.

## Keamanan & Privasi

- Jangan hard-code kredensial/secret. Konfigurasi tetap di `application/config/*`.
- Untuk fitur yang menyimpan data tamu (PII), lakukan:
  - minimisasi data (ambil yang perlu saja),
  - validasi format (email/telepon),
  - escape output,
  - audit akses untuk modul admin.

## Formatting

Repo memiliki Prettier untuk PHP.

- Format (opsional) dengan:
  - `npx prettier --write "application/**/*.php" "assets/**/*.js" "assets/**/*.css"`

Jangan lakukan reformat massal di file yang tidak terkait permintaan.

## Checklist sebelum menyelesaikan tugas

- Perubahan hanya pada file relevan.
- Tidak ada error sintaks PHP (bila memungkinkan, cek dengan `php -l <file>`).
- Tidak mengubah `system/` atau `third_party/` tanpa alasan kuat.
- Form/endpoint baru punya validasi input dan escape output.


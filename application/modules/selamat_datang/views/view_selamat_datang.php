<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
		<meta name="viewport" content="width=device-width" />
		<title>Buku Tamu | BPS Provinsi Maluku Utara</title>
		<link href="<?php echo base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
		<link href="<?php echo base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />
        <link href="<?php echo base_url('assets/form/css/kiosk.css') . '?v=' . @filemtime(FCPATH . 'assets/form/css/kiosk.css'); ?>" rel="stylesheet" />
		<link href='https://fonts.googleapis.com/css?family=Muli:400,300' rel='stylesheet' type='text/css'>
		<link href="<?php echo base_url('assets/form/css/themify-icons.css'); ?>" rel="stylesheet">
	</head>

    <body class="kiosk-page">
        <div class="image-container set-full-height" style="position: relative; overflow: hidden;">
        <video autoplay muted loop playsinline preload="metadata" id="bg-video" style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
            <source src="<?php echo base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
            Browser Anda tidak mendukung video.
        </video>
			<div class="container">
				<div class="row">
					<div class="col-sm-8 col-sm-offset-2">
						<div class="wizard-container">
							<div class="card wizard-card" data-color="green" id="wizard">
								<form action="<?php echo base_url('selamat_datang/add'); ?>" method="post">
                                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                        value="<?php echo $this->security->get_csrf_hash(); ?>">
                                    <div class="wizard-header text-center kiosk-header">
                                        <div class="kiosk-step">Langkah 3 dari 3</div>
                                        <?php $layanan = (string) ($this->session->userdata('jenis_layanan') ?? ''); ?>
                                        <?php if ($layanan !== ''): ?>
                                            <div class="kiosk-header-selected">
                                                <span class="label label-success kiosk-badge">
                                                    Layanan terpilih: <?= htmlspecialchars($layanan, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                            <div class="kiosk-header-actions">
                                                <a href="<?= base_url('layanan') ?>" class="btn btn-default btn-sm">Ganti layanan</a>
                                            </div>
                                        <?php endif; ?>
                                        <h3 class="wizard-title">Aplikasi Buku Tamu</h3>
                                        <p class="category">Isi data diri untuk mendapatkan nomor antrian.</p>
                                        <div class="kiosk-microcopy">Waktu pengisian &plusmn; 3 menit.</div>
                                    </div>
									<div class="wizard-navigation">
										<div class="progress-with-circle">
									    <div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 15%;"></div>
										</div>
										<ul>
											<li>
												<a href="#personal" data-toggle="tab">
													<div class="icon-circle">
														<i class="ti-user"></i>
                                        </div>
													Personal
												</a>
											</li>
											<li>
												<a href="#konsultasi" data-toggle="tab">
													<div class="icon-circle">
														<i class="ti-direction-alt"></i>
													</div>
													Pemanfaatan
												</a>
											</li>
											<li>
                                                <a href="#photo" data-toggle="tab">
                                                    <div class="icon-circle">
                                                        <i class="ti-camera"></i>
                                                    </div>
                                                    Foto
                                                </a>
                                            </li>

										</ul>
									</div>
									<div class="tab-content">
                                        <!-- TAB PERSONAL -->
                                        <div class="tab-pane" id="personal" style="margin-top: 20px;">
                                            <div class="row">
                                                <div class="col-sm-5 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Tanggal Datang <small class="text-danger">*</small></label>
                                                        <input name="tgldatang" type="datetime-local" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Nama Lengkap <small class="text-danger">*</small></label>
                                                        <input name="nama" type="text" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Email <small class="text-danger">*</small></label>
                                                        <input name="email" type="email" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>No. Handphone <small class="text-danger">*</small></label>
                                                        <input name="notel" type="text" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Jenis Kelamin <small class="text-danger">*</small></label><br>
                                                        <label><input type="radio" name="jeniskelamin" value="Laki-laki" required> Laki-laki</label>
                                                        <label><input type="radio" name="jeniskelamin" value="Perempuan"> Perempuan</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Pendidikan Tertinggi <small class="text-danger">*</small></label>
                                                        <select name="pendidikan" class="form-control" required>
                                                            <option value="">Pilih</option>    
                                                            <option value="1">1 : <= SLTA/Sederajat</option>
                                                            <option value="2">2 : D1/D2/D3</option>
                                                            <option value="3">3 : D4/S1</option>
                                                            <option value="4">4 : S2</option>
                                                            <option value="5">5 : S3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Pekerjaan Utama <small class="text-danger">*</small></label>
                                                        <select name="pekerjaan" class="form-control" required>
                                                            <option value="">Pilih</option>
                                                            <option value="1">1 : Pelajar/Mahasiswa</option>
                                                            <option value="2">2 : Peneliti/Dosen</option>
                                                            <option value="3">3 : ASN/TNI/Polri</option>
                                                            <option value="4">4 : Pegawai BUMN/BUMD</option>
                                                            <option value="5">5 : Pegawai Swasta</option>
                                                            <option value="6">6 : Wiraswasta</option>
                                                            <option value="7">7 : Lainnya</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Kategori Instansi <small class="text-danger">*</small></label>
                                                        <select name="kategori_instansi" class="form-control" required>
                                                            <option value="">Pilih</option>
                                                            <option value="1">1 : Lembaga Negara</option>
                                                            <option value="2">2 : Kementerian & Lembaga Pemerintah</option>
                                                            <option value="3">3 : TNI/POLRI/BIN/Kejaksaan</option>
                                                            <option value="4">4 : Pemerintah Daerah</option>
                                                            <option value="5">5 : Lembaga Internasional</option>
                                                            <option value="6">6 : Lembaga Penelitian & Pendidikan</option>
                                                            <option value="7">7 : BUMN/BUMD</option>
                                                            <option value="8">8 : Swasta</option>
                                                            <option value="9">9 : Lainnya</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-10 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Nama Instansi <small class="text-danger">*</small></label>
                                                        <input name="nama_instansi" type="text" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TAB LAYANAN -->
                                        <div class="tab-pane" id="konsultasi" style="margin-top: 20px;">
                                            <div class="row">
                                                <div class="col-sm-10 col-sm-offset-1">
                                                    <div class="form-group">
                                                        <label>Pemanfaatan Utama Hasil Kunjungan dan/atau Akses Layanan <small class="text-danger">*</small></label>
                                                        <select name="pemanfaatan" class="form-control" required>
                                                            <option value="">Pilih</option>
                                                            <option value="1">1 : Tugas Sekolah/Tugas Kuliah</option>
                                                            <option value="2">2 : Pemerintah</option>
                                                            <option value="3">3 : Komersial</option>
                                                            <option value="4">4 : Penelitian</option>
                                                            <option value="5">5 : Lainnya</option>
                                                        </select>
                                                    </div>
                                                                                                        
                                                    <div class="form-group">
                                                        <label>Apakah pernah melakukan pengaduan terkait PST? <small class="text-danger">*</small></label><br>
                                                        <label><input type="radio" name="pengaduan" value="Ya" required> Ya</label>
                                                        <label><input type="radio" name="pengaduan" value="Tidak"> Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <!-- TAB FOTO -->
                                    <div class="tab-pane" id="photo" style="margin-top: 20px;">
                                        <div class="row">
                    						<div id="loadingOverlay" style="
                                            display: none;
                                            position: absolute;
                                            top: 0; left: 0;
                                            width: 100%; height: 100%;
                                            background: rgba(255,255,255,0.8);
                                            z-index: 9999;
                                            text-align: center;
                                            padding-top: 150px;
                                            font-size: 20px;
                                            font-weight: bold;
                                            color: #333;">
                    								<span id="loadingOverlaySpinner" class="kiosk-spinner"></span>
                    								<span id="loadingOverlayMessage" style="margin-left:10px;">Memproses wajah, mohon tunggu...</span>
                                        </div>
                                            <div class="video-wrapper">
                                                <video id="camera" class="kiosk-video" autoplay muted playsinline></video>
                                                <div id="face-frame"></div>
                                            </div>
                                    
                                            <div class="button-wrapper">                                   
                                                <button type="button" class="btn btn-warning" id="reloadCamera">Muat Ulang Kamera</button>
                                            </div>

                                            <!-- Tambahkan elemen penting berikut -->
                                            <input type="hidden" name="foto" id="foto">
                                            <input type="hidden" name="face_descriptor" id="face_descriptor">
                                            <canvas id="canvas" style="display: none;"></canvas>
                                            <img id="preview" style="margin-top: 15px; max-width: 100%; border: 2px solid #ccc; display: none;" />
                                            <div id="photoConfirmation" style="display: none; text-align: center; margin-top: 10px;">
                                                <button type="button" class="btn btn-success" id="confirmPhoto">OK</button>
                                                <button type="button" class="btn btn-danger" id="retakePhoto">Ulangi</button>
                                            </div>
                                        </div>
                                    </div> 
 
									<div class="wizard-footer" style="margin-top: 20px;">
                                        <div class="pull-right">
                                            <button type='button' class='btn btn-next btn-fill btn-success btn-wd' name='next'>Lanjut</button>
                                            <button type='submit' class='btn btn-finish btn-fill btn-success btn-wd' name='finish'>Selesai</button>
                                        </div>
                                        <div class="pull-left">
                                            <button type='button' class='btn btn-previous btn-default btn-wd' name='previous'>Kembali</button>
											<a href="<?= base_url('layanan') ?>" class="btn btn-danger btn-wd" style="margin-left: 8px;">Batal</a>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
								</form>
							</div>
						</div>
					</div>
	      </div>
	    </div>
		</div>
        <!-- Load jQuery -->
        <script src="<?php echo base_url('assets/form/js/jquery-2.2.4.min.js'); ?>"></script>

        <!-- Load SweetAlert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Load Bootstrap dan Wizard -->
        <script src="<?php echo base_url('assets/form/js/bootstrap.min.js'); ?>"></script>
        <script src="<?php echo base_url('assets/form/js/jquery.bootstrap.wizard.js'); ?>"></script>
        <script src="<?php echo base_url('assets/form/js/paper-bootstrap-wizard.js'); ?>"></script>
        <script src="<?php echo base_url('assets/form/js/jquery.validate.min.js'); ?>"></script>
        <script>
            $(function() {
                const form = $('form');

                // Penting: Validasi semua field termasuk yang tersembunyi (tab lain)
                form.validate({
                    ignore: [], // <--- inilah kuncinya
                    errorClass: 'text-danger',
                });

                // Cegah pindah tab jika field tab sebelumnya belum benar
                $('.btn-next').on('click', function() {
                    if (form.valid()) {
                        $('#wizard').bootstrapWizard('next');
                    }
                });

                // Cegah submit jika masih ada field kosong
                form.on('submit', function(e) {
                    if (!form.valid()) {
                        e.preventDefault();
                    }
                });
            });
            </script>


        <!-- Load face-api.js (wajib sebelum camera.js) -->
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

        <!-- Set base_url untuk camera.js -->
        <script>
            window.base_url = "<?php echo base_url(); ?>";
        </script>

        <script>
        $(document).ready(function () {
        <?php if (isset($active_tab) && $active_tab === 'photo'): ?>
            // Force wizard ke tab #photo
            $('#wizard').bootstrapWizard('show', 2); // index ke-2 = tab photo
        <?php endif; ?>
        });
        </script>

        <!-- Load camera.js (logika deteksi wajah & kamera) -->
        <script src="<?= base_url('assets/form/js/camera.js?v=' . time()); ?>"></script>

        
        <?php if (isset($success)): ?>
        <script>
        (function(){
            const no = <?= isset($nomor_antrian) && $nomor_antrian ? json_encode($nomor_antrian) : 'null' ?>;
            const successMsg = <?= json_encode((string) ($success ?? 'Data berhasil disimpan.')) ?>;
            const layanan = <?= json_encode((string) ($layanan ?? '')) ?>;
            const ada_antrian = ['Perpustakaan', 'Konsultasi Statistik', 'Rekomendasi Kegiatan Statistik', 'Penjualan Produk Statistik'].includes(layanan);

            // Daftar layanan dengan antrian
            const layanan_dengan_antrian = [
                'Perpustakaan',
                'Konsultasi Statistik', 
                'Rekomendasi Kegiatan Statistik',
                'Penjualan Produk Statistik'
            ];

            // Coba cetak hanya jika ada nomor antrian dan layanan memerlukan antrian
            if (no && ada_antrian) {
                fetch("http://localhost:5000/print", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ no: String(no) })
                })
                .then(() => {
                    showSwalWithTicket(no, true);
                })
                .catch((err) => {
                    console.error("Print error:", err);
                    showSwalWithTicket(no, false);
                });
            } else {
                // Tidak ada nomor antrian (Lainnya / Keperluan Pimpinan)
                showSwalNoTicket();
            }
        })();

        // Fungsi tampilkan ticket dengan cetak berhasil
        function showSwalWithTicket(no, printed) {
            let intervalId = null;
            const printMsg = printed ? '' : '<p style="font-size: 14px; color: #ff6b6b; margin: 10px 0;">⚠️ <b>Printer tidak tersambung</b> - Silakan catat nomor di atas</p>';

            Swal.fire({
                icon: 'success',
                title: '🎫 NOMOR ANTRIAN ANDA',
                html: `
                    <div style="text-align: center; padding: 20px;">
                        <p style="font-size: 18px; margin-bottom: 15px; color: #333;">✅ Data Anda Berhasil Disimpan!</p>
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; margin: 20px 0; box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
                            <p style="font-size: 72px; font-weight: bold; color: white; margin: 0; line-height: 1;">
                                ${htmlEscape(String(no))}
                            </p>
                        </div>
                        ${printMsg}
                        <p style="font-size: 14px; color: #666; margin-top: 20px;">📋 Silakan catat nomor di atas</p>
                        <p style="font-size: 14px; color: #666;">Nomor ini akan ditampilkan di layar antrian</p>
                    </div>
                    <p style="margin-top: 30px; font-size: 14px; color: #999;">Anda akan dialihkan dalam <b style="color: #667eea; font-size: 18px;"><span id="timer">10</span></b> detik</p>
                `,
                confirmButtonText: 'OK',
                timer: 10000,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    const content = Swal.getHtmlContainer();
                    const timerEl = content ? content.querySelector('#timer') : null;
                    let timeLeft = 10;
                    if (timerEl) timerEl.textContent = String(timeLeft);

                    intervalId = setInterval(() => {
                        timeLeft = Math.max(0, timeLeft - 1);
                        if (timerEl) timerEl.textContent = String(timeLeft);
                    }, 1000);
                },
                willClose: () => {
                    if (intervalId) clearInterval(intervalId);
                }
            }).then(() => {
                sessionStorage.clear();
                try { if (typeof stopCamera === 'function') stopCamera(); } catch(e) {}
                window.location.href = "<?= base_url('layanan') ?>";
            });
        }

        // Fungsi tampilkan pesan untuk layanan tanpa antrian
        function showSwalNoTicket() {
            let intervalId = null;

            Swal.fire({
                icon: 'success',
                title: '✅ Berhasil',
                html: `
                    <div style="text-align: center; padding: 20px;">
                        <p style="font-size: 18px; margin-bottom: 15px; color: #333;">✅ Data Anda Berhasil Disimpan!</p>
                        <div style="background: #f0f4ff; border-left: 4px solid #667eea; border-radius: 8px; padding: 20px; margin: 20px 0;">
                            <p style="font-size: 16px; color: #333; margin: 0;">
                                Terima kasih telah berkunjung.<br>
                                Layanan ini tidak memerlukan nomor antrian.
                            </p>
                        </div>
                    </div>
                    <p style="margin-top: 30px; font-size: 14px; color: #999;">Anda akan dialihkan dalam <b style="color: #667eea; font-size: 18px;"><span id="timer">10</span></b> detik</p>
                `,
                confirmButtonText: 'OK',
                timer: 10000,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    const content = Swal.getHtmlContainer();
                    const timerEl = content ? content.querySelector('#timer') : null;
                    let timeLeft = 10;
                    if (timerEl) timerEl.textContent = String(timeLeft);

                    intervalId = setInterval(() => {
                        timeLeft = Math.max(0, timeLeft - 1);
                        if (timerEl) timerEl.textContent = String(timeLeft);
                    }, 1000);
                },
                willClose: () => {
                    if (intervalId) clearInterval(intervalId);
                }
            }).then(() => {
                sessionStorage.clear();
                try { if (typeof stopCamera === 'function') stopCamera(); } catch(e) {}
                window.location.href = "<?= base_url('layanan') ?>";
            });
        }

        function htmlEscape(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
        </script>
        <?php endif; ?>
        <!-- Modal Disclaimer -->
        <div class="modal fade" id="disclaimerModal" tabindex="-1" aria-labelledby="disclaimerLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="disclaimerLabel">Pernyataan Sebelum Mengambil Foto</h5>
            </div>
            <div class="modal-body text-dark">
                Foto wajah yang diambil hanya digunakan untuk keperluan pencatatan kunjungan dalam sistem Buku Tamu. Kami menjamin bahwa data pribadi Anda akan dijaga kerahasiaannya dan tidak akan disalahgunakan.
            </div>
            <div class="modal-footer">
                <button type="button" id="acceptDisclaimer" class="btn btn-success">Saya Mengerti</button>
            </div>
            </div>
        </div>
        </div>

        <script>
        $(document).ready(function () {
            let goToPhoto = false;

            // Saat user ingin ke tab Foto
            $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                if ($(e.target).attr("href") === "#photo" && !sessionStorage.getItem('fotoDisclaimer')) {
                    e.preventDefault(); // tahan dulu
                    goToPhoto = true;
                    $('#disclaimerModal').modal('show');
                }
            });

            // Saat klik tombol modal
            $('#acceptDisclaimer').on('click', function () {
                this.blur(); // Lepaskan fokus agar tidak kena warning aria
                $('#disclaimerModal').modal('hide');
            });

            // Setelah modal tertutup, baru masuk tab Foto
            $('#disclaimerModal').on('hidden.bs.modal', function () {
                if (goToPhoto) {
                    sessionStorage.setItem('fotoDisclaimer', true);
                    $('#wizard').bootstrapWizard('show', 2);
                    goToPhoto = false;
                }
            });
        });
        </script>
        <script>
            let faceCaptured = false;

            // Nonaktifkan tombol Selesai sampai foto dikonfirmasi
            document.querySelector("button[name='finish']").disabled = true;
            document.querySelector("button[name='finish']").classList.add('disabled');


            // Saat tombol OK ditekan → izinkan submit
            document.getElementById('confirmPhoto')?.addEventListener('click', function () {
                faceCaptured = true;
                const finishBtn = document.querySelector("button[name='finish']");
                finishBtn.disabled = false;
                finishBtn.classList.remove('disabled');
            });

            // Saat tombol "Selesai" ditekan tanpa konfirmasi foto
            document.querySelector('form')?.addEventListener('submit', function (e) {
                if (!faceCaptured) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Wajah belum dikonfirmasi!',
                        text: 'Silakan klik tombol OK setelah mengambil foto.'
                    });
                    return;
                }

                // Foto sudah dikonfirmasi: matikan kamera supaya tidak tetap berjalan saat submit/redirect
                try {
                    if (typeof stopCamera === 'function') stopCamera({ keepData: true });
                } catch (e) {}
            });
        </script>
        <script>
        // Daftar layanan yang HARUS generate nomor antrian
        const layanan_dengan_antrian = [
            'Perpustakaan',
            'Konsultasi Statistik',
            'Rekomendasi Kegiatan Statistik',
            'Penjualan Produk Statistik'
        ];
        </script>
        <script>
        // Daftar field yang mau disimpan otomatis
        const fields = [
            "tgldatang","nama","email","notel",
            "jeniskelamin","pendidikan","pekerjaan",
            "kategori_instansi","nama_instansi",
            "pemanfaatan","pengaduan"
        ];

        // Restore ketika halaman dibuka
        fields.forEach(name => {
            let el = document.querySelector(`[name="${name}"]`);
            if (!el) return;

            let saved = localStorage.getItem("form_" + name);
            if (saved !== null) {
                if (el.type === "radio") {
                    const radio = document.querySelector(`[name="${name}"][value="${saved}"]`);
                    if (radio) radio.checked = true;
                } else {
                    el.value = saved;
                }
            }
        });

        // Auto save ketika user mengetik / memilih
        document.addEventListener("input", function(e){
            if (!fields.includes(e.target.name)) return;
            if (e.target.type === "radio") {
                localStorage.setItem("form_" + e.target.name, e.target.value);
            } else {
                localStorage.setItem("form_" + e.target.name, e.target.value.trim());
            }
        });

        // Saat form berhasil submit → hapus data tersimpan
        document.querySelector('form').addEventListener('submit', function(){
            fields.forEach(name => localStorage.removeItem("form_" + name));
        });
        </script>
        <script>
        // Intercept regular form submit and submit via AJAX to avoid navigating to /selamat_datang/add
        (function(){
            const form = document.querySelector('form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                // If the submit is triggered programmatically or by non-JS, let it proceed
                if (e.__submitted_by_js) return;

                e.preventDefault();

                const fd = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                .then(r => r.json())
                .then(result => {
                    if (result && result.status === 'success') {
                        const no = result.nomor_antrian || null;
                        const msg = result.message || 'Data berhasil disimpan.';
                        const layanan_terpilih = window.sessionStorage?.getItem('jenis_layanan') || '';
                        const ada_antrian = layanan_dengan_antrian.includes(layanan_terpilih);

                        // Jika ada nomor antrian, tampilkan dengan besar
                        let html = '';
                        if (no && ada_antrian) {
                            html = `
                                <div style="text-align: center; padding: 20px;">
                                    <p style="font-size: 18px; margin-bottom: 15px; color: #333;">✅ Data Anda Berhasil Disimpan!</p>
                                    <p style="font-size: 16px; color: #666; margin-bottom: 20px;">Nomor Antrian Anda:</p>
                                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; margin: 20px 0; box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
                                        <p style="font-size: 72px; font-weight: bold; color: white; margin: 0; line-height: 1;">
                                            ${htmlEscape(String(no))}
                                        </p>
                                    </div>
                                    <p style="font-size: 14px; color: #666; margin-top: 20px;">📋 Silakan catat nomor di atas</p>
                                    <p style="font-size: 14px; color: #666;">Nomor ini akan ditampilkan di layar antrian</p>
                                </div>
                            `;
                        } else {
                            // Jika tidak ada nomor antrian (Lainnya / Keperluan Pimpinan)
                            html = `
                                <div style="text-align: center; padding: 20px;">
                                    <p style="font-size: 18px; margin-bottom: 15px; color: #333;">✅ Data Anda Berhasil Disimpan!</p>
                                    <div style="background: #f0f4ff; border-left: 4px solid #667eea; border-radius: 8px; padding: 20px; margin: 20px 0;">
                                        <p style="font-size: 16px; color: #333; margin: 0;">
                                            Terima kasih telah berkunjung.<br>
                                            Layanan ini tidak memerlukan nomor antrian.
                                        </p>
                                    </div>
                                </div>
                            `;
                        }

                        // 10-second countdown modal
                        let intervalId = null;
                        Swal.fire({
                            icon: 'success',
                            title: ada_antrian && no ? '🎫 NOMOR ANTRIAN ANDA' : '✅ Berhasil',
                            html: html + '<p style="margin-top: 30px; font-size: 14px; color: #999;">Anda akan dialihkan dalam <b style="color: #667eea; font-size: 18px;"><span id="timer">10</span></b> detik</p>',
                            confirmButtonText: 'OK',
                            timer: 10000,
                            timerProgressBar: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                const content = Swal.getHtmlContainer();
                                const timerEl = content ? content.querySelector('#timer') : null;
                                let timeLeft = 10;
                                if (timerEl) timerEl.textContent = String(timeLeft);

                                intervalId = setInterval(() => {
                                    timeLeft = Math.max(0, timeLeft - 1);
                                    if (timerEl) timerEl.textContent = String(timeLeft);
                                }, 1000);
                            },
                            willClose: () => {
                                if (intervalId) clearInterval(intervalId);
                            }
                        }).then(() => {
                            // Clear saved form state and redirect to layanan
                            try { sessionStorage.clear(); } catch(e) {}
                            try { if (typeof stopCamera === 'function') stopCamera(); } catch(e) {}
                            window.location.href = window.base_url + 'layanan';
                        });
                    } else {
                        Swal.fire('❌ Gagal', (result && result.message) ? result.message : 'Gagal menyimpan data.', 'error');
                    }
                })
                .catch(err => {
                    console.error('AJAX submit error', err);
                    Swal.fire({ icon: 'error', title: 'Gangguan koneksi', text: 'Tidak bisa menghubungi server. Coba lagi sebentar.' });
                });
            });
        })();

        // Helper function to escape HTML
        function htmlEscape(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
        </script>
    </body>
</html>

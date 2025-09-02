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
		<link href='https://fonts.googleapis.com/css?family=Muli:400,300' rel='stylesheet' type='text/css'>
		<link href="<?php echo base_url('assets/form/css/themify-icons.css'); ?>" rel="stylesheet">
	</head>

	<body>
        <div class="image-container set-full-height" style="position: relative; overflow: hidden;">
        <video autoplay muted loop playsinline id="bg-video" style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
            <source src="<?php echo base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
            Browser Anda tidak mendukung video.
        </video>
			<div class="container">
				<div class="row">
					<div class="col-sm-8 col-sm-offset-2">
						<div class="wizard-container">
							<div class="card wizard-card" data-color="green" id="wizard">
								<form action="<?php echo base_url() . 'selamat_datang/add'; ?>" method="post">
									<div class="wizard-header">
										<h3 class="wizard-title">Aplikasi Buku Tamu</h3>
										<p class="category">Selamat datang, silahkan isi data diri Anda dengan benar.</p>
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
													Konsultasi
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
                                        <div class="tab-pane" id="personal" style="margin-top: 50px;">
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
                                                        <label><input type="radio" name="jeniskelamin" value="Perempuan" required> Perempuan</label>
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
                                        <div class="tab-pane" id="konsultasi" style="margin-top: 50px;">
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
                                                        <label><input type="radio" name="pengaduan" value="Tidak" required> Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <!-- TAB FOTO -->
                                    <div class="tab-pane" id="photo" style="margin-top: 50px;">
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
                                            ⏳ Memproses wajah, mohon tunggu...
                                        </div>
                                            <div class="video-wrapper">
                                                <video id="camera" autoplay muted playsinline></video>
                                                <div id="face-frame"></div>
                                            </div>
                                    
                                            <div class="button-wrapper">
                                                <button type="button" class="btn btn-success" id="snap">Ambil Foto Manual</button>
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
        <script src="<?php echo base_url('assets/form/js/camera.js'); ?>"></script>?>  
        
        <?php if (isset($success) && isset($nomor_antrian)): ?>
        <script>
        fetch("http://localhost:5000/print", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ no: "<?= $nomor_antrian ?>" })
        })
        .then(() => {
            // Jika sukses print
            showSwal("Berhasil!", "<?= $success ?> <br>Nomor Antrian Anda: <b><?= $nomor_antrian ?></b><br><br>", 'success');
        })
        .catch((err) => {
            console.error("Print error:", err);
            // Jika gagal koneksi ke print server
            showSwal("Berhasil tanpa Cetak!", "<?= $success ?> <br><b>Gagal mencetak nomor antrian</b><br>Silakan catat nomor berikut: <b><?= $nomor_antrian ?></b><br><br>", 'warning');
        });

        // Fungsi untuk tampilkan notifikasi dan redirect
        function showSwal(title, message, icon) {
            Swal.fire({
                icon: icon,
                title: title,
                html: message + 'Anda akan dialihkan dalam <b><span id="timer">3</span></b> detik.',
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    const content = Swal.getHtmlContainer();
                    const $ = content.querySelector.bind(content);
                    let timeLeft = 5;
                    setInterval(() => $('#timer').textContent = --timeLeft, 1000);
                }
            }).then(() => {
                sessionStorage.clear(); // ✅ clear setelah sukses
                window.location.href = "<?= base_url('layanan') ?>"; // ✅ redirect
            });
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
            let faceCaptured = false; // status wajah sudah diproses

            // Tambahkan flag saat user klik "OK" di konfirmasi foto
            document.getElementById('confirmPhoto')?.addEventListener('click', function () {
                faceCaptured = true;
            });

            // Cek sebelum submit
            document.querySelector('form')?.addEventListener('submit', function (e) {
                const currentTab = $('.tab-pane.active').attr('id');
                if (currentTab === 'photo' && !faceCaptured) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Wajah belum dikenali!',
                        text: 'Silakan ambil dan konfirmasi foto terlebih dahulu.'
                    });
                }
            });
        </script>



    </body>
</html>

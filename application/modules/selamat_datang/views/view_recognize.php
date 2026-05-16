<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Wajah | Buku Tamu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link href="<?= base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/form/css/kiosk.css') . '?v=' . @filemtime(FCPATH . 'assets/form/css/kiosk.css'); ?>" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Muli:400,300" rel="stylesheet">
    <link href="<?= base_url('assets/form/css/themify-icons.css'); ?>" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Select2 (untuk dropdown manual jika wajah tidak cocok) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Face API -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        video {
            border: 2px solid #ccc;
            border-radius: 5px;
            margin-top: 20px;
        }
        #result {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
        }
        .btn-recognize {
            display: none;
        }

        /* SweetAlert2 modal styling (scoped) */
        .swal2-container {
            align-items: center !important; /* vertical center */
            justify-content: center !important; /* horizontal center */
        }

        .kiosk-swal {
            width: min(520px, calc(100% - 24px)) !important;
            margin: 0 !important;
            padding: 18px 18px 16px !important;
            border-radius: 12px !important;
        }

        .kiosk-swal .swal2-html-container {
            overflow: visible !important; /* allow Select2 dropdown to render cleanly */
        }

        /* Select2 inside SweetAlert2 (scoped) */
        .kiosk-swal .select2-container {
            width: 100% !important;
        }

        .kiosk-swal .select2-container--default .select2-selection--single {
            height: 46px !important;
            display: flex;
            align-items: center;
            padding: 0 12px !important;
            font-size: 16px !important;
            border: 1px solid #ccc !important;
            border-radius: 8px !important;
            box-sizing: border-box;
        }

        .kiosk-swal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.2 !important;
            padding-left: 0 !important;
        }

        .kiosk-swal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            top: 50% !important;
            transform: translateY(-50%);
            right: 8px;
        }

        .kiosk-swal .select2-container--default .select2-search--dropdown .select2-search__field {
            height: 42px;
            padding: 8px 10px;
            font-size: 16px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .kiosk-swal .select2-container .select2-dropdown {
            border-radius: 10px;
            overflow: hidden;
        }

        .kiosk-swal .select2-results__option {
            padding: 10px 12px;
            font-size: 16px;
        }

    </style>
</head>
    <body class="kiosk-recognize">
    <div class="image-container kiosk-page">
            <div class="kiosk-bg-fallback" aria-hidden="true"></div>
        <video autoplay muted loop playsinline preload="metadata" id="bg-video">
            <source src="<?= base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
        </video>

        <div class="container full-height-center">
            <div class="col-md-8 col-md-offset-2">
                <div class="card wizard-card text-center" style="padding: 40px 20px;">
                    <div id="modelLoading" class="kiosk-loading" aria-live="polite" aria-busy="true">
                        <div>
                            <div id="modelLoadingTitle" class="kiosk-loading-title">Memuat sistem verifikasi…</div>
                            <div class="kiosk-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div id="modelLoadingBar"></div>
                            </div>
                            <div id="modelLoadingMeta" class="kiosk-loading-meta">0%</div>
                        </div>
                    </div>
                    <?php $layanan = (string) ($this->session->userdata('jenis_layanan') ?? ''); ?>
                    <div class="wizard-header text-center kiosk-header">
                        <div class="kiosk-step">Langkah 3 dari 3</div>
                        <?php if ($layanan !== ''): ?>
                            <div class="kiosk-header-selected">
                                <span class="label label-success kiosk-badge">
                                    Layanan terpilih: <?= htmlspecialchars($layanan, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <div class="kiosk-header-actions">
                                <a href="<?= base_url('layanan') ?>" class="btn btn-default btn-sm">Ganti layanan</a>
                                <a href="<?= base_url('selamat_datang/check') ?>" class="btn btn-default btn-sm" style="margin-left: 6px;">Kembali</a>
                            </div>
                        <?php endif; ?>
                        <h3 class="wizard-title">Verifikasi Wajah</h3>
                        <p class="category">Arahkan wajah Anda ke kamera untuk memverifikasi kunjungan.</p>
                        <div class="kiosk-microcopy">Biasanya selesai dalam beberapa detik.</div>
                    </div>

                    <video id="camera" class="kiosk-video" width="640" height="480" autoplay muted playsinline></video>
                    <br>
                    <button class="btn btn-success btn-fill btn-lg btn-recognize" onclick="startRecognition()">Mulai Verifikasi</button>
                    <div style="margin-top: 10px;">
                        <span id="statusBadge" class="label label-default">Siap</span>
                    </div>
                    <div id="result" style="min-height: 28px;">Menunggu deteksi wajah…</div>
                </div>
            </div>
        </div>
    </div>

<script>
const base_url = "<?= base_url(); ?>";
const video = document.getElementById('camera');
let faceMatcher = null;
let recognitionRunning = false;
let modelsLoaded = false;
let nameMap = {};
let allTamu = [];

const statusBadge = document.getElementById('statusBadge');
const resultEl = document.getElementById('result');
const modelLoadingEl = document.getElementById('modelLoading');
const modelLoadingTitleEl = document.getElementById('modelLoadingTitle');
const modelLoadingBarEl = document.getElementById('modelLoadingBar');
const modelLoadingMetaEl = document.getElementById('modelLoadingMeta');

// Smart timeout helper
const HELP_NO_DETECTION_MS = 20000;
const HELP_UNKNOWN_MS = 20000;
let recognitionStartedAt = 0;
let lastDetectionAt = 0;
let lastUnknownAt = 0;
let smartHelpShown = false;

function setBadge(text, type) {
    if (!statusBadge) return;
    statusBadge.className = 'label label-' + (type || 'default');
    statusBadge.textContent = text;
}

function setResult(text) {
    if (!resultEl) return;
    resultEl.textContent = text;
}

function showModelLoading(show, percent, title) {
    if (!modelLoadingEl) return;
    if (show) {
        modelLoadingEl.classList.add('is-visible');
        if (typeof title === 'string' && modelLoadingTitleEl) modelLoadingTitleEl.textContent = title;
        const safePercent = Math.max(0, Math.min(100, Math.round(percent || 0)));
        if (modelLoadingBarEl) modelLoadingBarEl.style.width = safePercent + '%';
        if (modelLoadingMetaEl) modelLoadingMetaEl.textContent = safePercent + '%';
        const pb = modelLoadingEl.querySelector('[role="progressbar"]');
        if (pb) pb.setAttribute('aria-valuenow', String(safePercent));
    } else {
        modelLoadingEl.classList.remove('is-visible');
    }
}

function restartRecognitionLoop() {
    // Reset smart-help timers for a fresh attempt
    recognitionStartedAt = Date.now();
    lastDetectionAt = 0;
    lastUnknownAt = 0;
    smartHelpShown = false;

    setBadge('Mencari', 'warning');
    setResult('Mengulang pemindaian…');

    recognitionRunning = true;
    setTimeout(recognizeLoop, 250);
}

async function showSmartHelp(reason) {
    if (smartHelpShown) return;
    smartHelpShown = true;
    recognitionRunning = false;

    setBadge('Bantuan', 'info');
    setResult('Butuh bantuan untuk verifikasi?');

    const message = reason === 'no-detection'
        ? 'Kami belum bisa mendeteksi wajah. Pastikan wajah terlihat jelas, pencahayaan cukup, dan posisi wajah di tengah kamera.'
        : 'Wajah terdeteksi tapi belum dikenali. Anda bisa coba lagi atau pilih nama secara manual.';

    const res = await Swal.fire({
        icon: 'info',
        title: 'Verifikasi belum berhasil',
        text: message,
        position: 'center',
        heightAuto: false,
        customClass: { popup: 'kiosk-swal' },
        showCancelButton: true,
        confirmButtonText: 'Coba Lagi',
        cancelButtonText: 'Pilih Manual',
        reverseButtons: true
    });

    // Reset timers for the next attempt
    recognitionStartedAt = Date.now();
    lastDetectionAt = 0;
    lastUnknownAt = 0;

    if (res.isConfirmed) {
        // Full restart: re-init camera + matcher
        smartHelpShown = false;
        recognitionRunning = false;
        await startRecognition();
        return;
    }

    // Cancel path => choose manual
    smartHelpShown = false;
    tampilkanDropdownManual();
}

/* ✅ Tunggu video siap */
function waitForVideoReady(videoElement) {
    return new Promise(resolve => {
        const check = () => {
            if (videoElement.videoWidth > 0 && videoElement.videoHeight > 0) {
                resolve();
            } else {
                requestAnimationFrame(check);
            }
        };
        check();
    });
}

/* ✅ Load model face-api */
async function loadModels() {
    if (modelsLoaded) return;

    setBadge('Memuat', 'info');
    setResult('Memuat model verifikasi…');
    showModelLoading(true, 0, 'Memuat model verifikasi…');

    const modelUrl = base_url + 'assets/models';
    const tasks = [
        { label: 'Deteksi wajah', promise: faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl) },
        { label: 'Landmark wajah', promise: faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl) },
        { label: 'Pengenalan wajah', promise: faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl) }
    ];

    let done = 0;
    const total = tasks.length;
    tasks.forEach(t => {
        t.promise.then(() => {
            done += 1;
            const pct = (done / total) * 100;
            showModelLoading(true, pct, 'Memuat model… (' + t.label + ')');
        });
    });

    try {
        await Promise.all(tasks.map(t => t.promise));
        modelsLoaded = true;
        showModelLoading(true, 100, 'Model siap');
        setBadge('Siap', 'success');
        setResult('Mengaktifkan kamera…');
        setTimeout(() => showModelLoading(false), 250);
        console.log('✅ Model face-api.js dimuat');
    } catch (err) {
        console.error('❌ Gagal memuat model face-api:', err);
        showModelLoading(false);
        setBadge('Gagal', 'danger');
        setResult('Gagal memuat model. Periksa koneksi.');
        Swal.fire({
            icon: 'error',
            title: 'Gagal memuat sistem verifikasi',
            text: 'Periksa koneksi internet/jaringan, lalu coba lagi.',
            position: 'center',
            heightAuto: false,
            customClass: { popup: 'kiosk-swal' },
            confirmButtonText: 'Coba Lagi'
        }).then(() => startRecognition());
        throw err;
    }
}

/* ✅ Aktifkan kamera */
async function startCamera() {
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera.');
        }
        setBadge('Kamera', 'info');
        setResult('Meminta izin kamera…');
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        console.log('🎥 Kamera aktif');
    } catch (err) {
        console.error(err);
        setBadge('Kamera', 'danger');
        setResult('Kamera tidak bisa diakses.');
        Swal.fire({
            icon: 'error',
            title: 'Gagal mengakses kamera',
            html: 'Pastikan Anda menekan <b>Allow</b> saat diminta izin kamera, lalu coba lagi.',
            position: 'center',
            heightAuto: false,
            customClass: { popup: 'kiosk-swal' },
            confirmButtonText: 'Coba Lagi'
        }).then(() => startRecognition());
        console.error(err);
    }
}

/* ✅ Ambil data wajah dari server */
async function getKnownDescriptors() {
    const res = await fetch(base_url + 'selamat_datang/get_face_data');
    const data = await res.json();
    allTamu = data;
    nameMap = {};

    return data.map(entry => {
        nameMap[entry.id_user.toString()] = entry.nama;
        return new faceapi.LabeledFaceDescriptors(
            entry.id_user.toString(),
            [new Float32Array(JSON.parse(entry.face_descriptor))]
        );
    });
}

/* ✅ Loop deteksi wajah */
async function recognizeLoop() {
    if (!recognitionRunning) return;

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        setBadge('Mencari', 'warning');
        setResult('Wajah belum terdeteksi. Posisikan wajah di tengah kamera.');

        // Smart timeout: no face detected for too long
        if (!smartHelpShown && recognitionStartedAt > 0) {
            const sinceStart = Date.now() - recognitionStartedAt;
            const sinceLast = lastDetectionAt > 0 ? (Date.now() - lastDetectionAt) : sinceStart;
            if (sinceLast >= HELP_NO_DETECTION_MS) {
                await showSmartHelp('no-detection');
                return;
            }
        }
    } else {
        lastDetectionAt = Date.now();
        const match = faceMatcher.findBestMatch(detection.descriptor);
        const matchedId = match.label;
        setBadge(matchedId === 'unknown' ? 'Tidak dikenal' : 'Terdeteksi', matchedId === 'unknown' ? 'warning' : 'success');
        setResult(matchedId === 'unknown'
            ? 'Wajah terdeteksi tapi belum dikenali. Dekatkan wajah dan pastikan pencahayaan cukup.'
            : `Wajah dikenali: ${nameMap[matchedId] || 'Pengunjung'}`
        );

        // Smart timeout: keeps returning unknown for too long
        if (matchedId === 'unknown') {
            if (lastUnknownAt === 0) lastUnknownAt = Date.now();
            const unknownFor = Date.now() - lastUnknownAt;
            if (!smartHelpShown && unknownFor >= HELP_UNKNOWN_MS) {
                await showSmartHelp('unknown');
                return;
            }
        } else {
            lastUnknownAt = 0;
        }

        if (matchedId !== 'unknown') {
            recognitionRunning = false;
            setBadge('Konfirmasi', 'info');
            setResult('Konfirmasi identitas…');

            Swal.fire({
                icon: 'success',
                title: `✅ Selamat datang, ${nameMap[matchedId] || 'Pengunjung'}!`,
                text: 'Apakah data ini sudah benar?',
                position: 'center',
                heightAuto: false,
                customClass: { popup: 'kiosk-swal' },
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Ya, Benar',
                denyButtonText: 'Ulangi Scan',
                cancelButtonText: 'Bukan Saya',
                reverseButtons: true
            }).then(async (res) => {
                if (res.isConfirmed) {
                    setBadge('Menyimpan', 'info');
                    setResult('Mencatat kunjungan…');
                    await simpanKunjungan(matchedId);
                } else if (res.isDenied) {
                    // Retry: continue scanning without leaving the page
                    restartRecognitionLoop();
                } else {
                    // jika user menolak, tampilkan dropdown manual
                    setBadge('Manual', 'warning');
                    setResult('Silakan pilih nama dari daftar.');
                    tampilkanDropdownManual();
                }
            });

            return;
        }

    }

    setTimeout(recognizeLoop, 1500);
}

/* ✅ Simpan kunjungan */
async function simpanKunjungan(id_user) {
    try {
        setBadge('Menyimpan', 'info');
        setResult('Mencatat kunjungan…');
        const res = await fetch(base_url + 'selamat_datang/masuk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_user })
        });

        const result = await res.json();

        if (result.status === 'success') {
            const no = result.nomor_antrian || null;
            
            if (no) {
                // Ada nomor antrian - coba cetak
                try {
                    await fetch("http://localhost:5000/print", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ no: no })
                    });
                    console.log("🖨️ Tiket antrian terkirim ke printer");
                } catch (err) {
                    console.warn("⚠️ Printer tidak tersambung:", err);
                }
                
                setBadge('Selesai', 'success');
                setResult('Data tersimpan. Menampilkan nomor antrian…');
                showTicketModal(no);
            } else {
                // Tidak ada nomor antrian (Keperluan Pimpinan / Lainnya)
                setBadge('Selesai', 'success');
                setResult('Data tersimpan. Layanan ini tidak memerlukan nomor antrian.');
                showNoTicketModal();
            }
        } else {
            setBadge('Gagal', 'danger');
            setResult('Gagal mencatat kunjungan.');
            Swal.fire('❌ Gagal', (result && result.message) ? result.message : 'Gagal mencatat kunjungan.', 'error');
        }
    } catch (err) {
        console.error("❌ Error kirim ke /masuk:", err);
        setBadge('Gagal', 'danger');
        setResult('Terjadi gangguan koneksi.');
        Swal.fire({
            icon: 'error',
            title: 'Gangguan koneksi',
            text: 'Tidak bisa menghubungi server. Coba lagi sebentar.',
            position: 'center',
            heightAuto: false,
            customClass: { popup: 'kiosk-swal' },
            confirmButtonText: 'Coba Lagi'
        }).then(() => {
            recognitionRunning = true;
            recognizeLoop();
        });
    }
}

/* ✅ Tampilkan modal ticket dengan countdown 10 detik */
function showTicketModal(no) {
    let intervalId = null;

    Swal.fire({
        icon: 'success',
        title: '🎫 NOMOR ANTRIAN ANDA',
        html: `
            <div style="text-align: center; padding: 20px;">
                <p style="font-size: 18px; margin-bottom: 15px; color: #333;">✅ Data Anda Berhasil Disimpan!</p>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; margin: 20px 0; box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
                    <p style="font-size: 72px; font-weight: bold; color: white; margin: 0; line-height: 1;">
                        ${htmlEscape(String(no || '-'))}
                    </p>
                </div>
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
        position: 'center',
        heightAuto: false,
        customClass: { popup: 'kiosk-swal' },
        didOpen: () => {
            const content = Swal.getPopup();
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
        location.href = base_url + 'layanan';
    });
}

/* ✅ Tampilkan modal untuk layanan tanpa antrian (Keperluan Pimpinan / Lainnya) */
function showNoTicketModal() {
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
        position: 'center',
        heightAuto: false,
        customClass: { popup: 'kiosk-swal' },
        didOpen: () => {
            const content = Swal.getPopup();
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
        location.href = base_url + 'layanan';
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

/* ✅ Jika wajah tidak cocok: tampilkan dropdown pilih tamu manual */
async function tampilkanDropdownManual() {
    const selectHTML = `
        <select id="manualSelect" style="width:100%">
            <option value="">— Pilih tamu —</option>
            ${allTamu.map(t => `
                <option value="${t.id_user}">
                    ${t.nama} (${t.nama_instansi ?? '-'})
                </option>`
            ).join('')}
        </select>
    `;

    Swal.fire({
        title: 'Wajah Tidak Dikenali',
        html: `
            <p>Pilih tamu dari daftar berikut jika wajah tidak dikenali:</p>
            ${selectHTML}
        `,
        position: 'center',
        heightAuto: false,
        customClass: { popup: 'kiosk-swal' },
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        didOpen: () => {
            const popup = Swal.getPopup();
            const $popup = popup ? $(popup) : $('.swal2-popup');
            $('#manualSelect').select2({
                placeholder: 'Cari nama / instansi…',
                dropdownParent: $popup,
                width: '100%',
                minimumResultsForSearch: 0
            });

            // Focus search input for faster manual lookup
            setTimeout(() => {
                try {
                    $('#manualSelect').select2('open');
                } catch (e) {}
            }, 50);
        },
        preConfirm: () => {
            let val = $('#manualSelect').val();
            if (!val) {
                Swal.showValidationMessage('Pilih tamu terlebih dahulu!');
                return false;
            }
            return val;
        }
    }).then(async (res) => {
        if (res.isConfirmed) {
            await simpanKunjungan(res.value);
        } else {
            recognitionRunning = true;
            recognizeLoop();
        }
    });
}


/* ✅ Mulai deteksi */
async function startRecognition() {
    if (recognitionRunning) return;
    setBadge('Memulai', 'info');
    setResult('Menyiapkan verifikasi…');

    recognitionStartedAt = Date.now();
    lastDetectionAt = 0;
    lastUnknownAt = 0;
    smartHelpShown = false;

    if (!modelsLoaded) await loadModels();
    await startCamera();
    await waitForVideoReady(video);

    const labeledDescriptors = await getKnownDescriptors();
    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

    recognitionRunning = true;
    setBadge('Mencari', 'warning');
    setResult('Arahkan wajah ke kamera.');
    recognizeLoop();
}

/* ✅ Jalankan otomatis saat halaman dibuka */
window.addEventListener('DOMContentLoaded', async () => {
    // Mark bg video as ready (prevents black flash while loading)
    const bgVideo = document.getElementById('bg-video');
    if (bgVideo) {
        const markReady = () => document.body.classList.add('kiosk-bg-ready');
        bgVideo.addEventListener('playing', markReady, { once: true });
        bgVideo.addEventListener('canplay', markReady, { once: true });
    }
    await startRecognition();
});
</script>
</body>
</html>

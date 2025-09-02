<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Wajah | Buku Tamu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link href="<?= base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Muli:400,300" rel="stylesheet">
    <link href="<?= base_url('assets/form/css/themify-icons.css'); ?>" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .swal2-large {
            font-size: 1.8rem !important;
        }
    </style>
</head>
<body>
    <div class="image-container set-full-height" style="position: relative; overflow: hidden;">
        <video autoplay muted loop playsinline id="bg-video" style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
            <source src="<?= base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
        </video>

        <div class="container full-height-center">
            <div class="col-md-8 col-md-offset-2">
                <div class="card wizard-card text-center" style="padding: 40px 20px;">
                    <h3 class="wizard-title">Verifikasi Wajah</h3>
                    <p class="category">Arahkan wajah Anda ke kamera untuk memverifikasi kunjungan.</p>

                    <video id="camera" width="640" height="480" autoplay muted playsinline></video>
                    <br>
                    <button class="btn btn-success btn-fill btn-lg btn-recognize" onclick="startRecognition()">🎥 Mulai Deteksi Wajah</button>
                    <div id="result">Menunggu deteksi wajah...</div>
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

        async function loadModels() {
            await faceapi.nets.tinyFaceDetector.loadFromUri(base_url + 'assets/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri(base_url + 'assets/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri(base_url + 'assets/models');
            modelsLoaded = true;
            console.log('✅ Model face-api.js dimuat');
        }

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                console.log('🎥 Kamera aktif');
            } catch (err) {
                alert('❌ Gagal mengakses kamera. Periksa izin kamera.');
                console.error(err);
            }
        }

        async function getKnownDescriptors() {
            const res = await fetch(base_url + 'selamat_datang/get_face_data');
            const data = await res.json();

            nameMap = {};
            return data.map(entry => {
                nameMap[entry.id_user.toString()] = entry.nama;
                return new faceapi.LabeledFaceDescriptors(
                    entry.id_user.toString(),
                    [new Float32Array(JSON.parse(entry.face_descriptor))]
                );
            });
        }

        async function recognizeLoop() {
            if (!recognitionRunning) return;

            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                document.getElementById('result').textContent = '🚫 Wajah tidak terdeteksi';
            } else {
                const match = faceMatcher.findBestMatch(detection.descriptor);
                const matchedId = match.label;
                document.getElementById('result').textContent = `👤 ${match.toString()}`;

                if (matchedId !== 'unknown') {
                    recognitionRunning = false;

                    Swal.fire({
                        icon: 'success',
                        title: `✅ Selamat datang, ${nameMap[matchedId] || 'Pengunjung'}!`,
                        text: 'Wajah berhasil diverifikasi.',
                        showConfirmButton: false,
                        timer: 3000,
                        customClass: { popup: 'swal2-large' }
                    });

                    try {
                        const res = await fetch(base_url + 'selamat_datang/masuk', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_user: matchedId })
                        });

                        const result = await res.json();

                        if (result.status === 'success' && result.nomor_antrian) {
                            // Coba cetak, tapi jika gagal tetap lanjut
                            try {
                                await fetch("http://localhost:5000/print", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({ no: result.nomor_antrian })
                                });
                                console.log("🖨️ Tiket antrian terkirim ke printer");
                            } catch (err) {
                                console.error("❌ Gagal mencetak tiket:", err);
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Printer Tidak Tersambung',
                                    text: 'Tiket tidak dicetak, tetapi kunjungan tetap dicatat.',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }

                            // Tetap redirect ke halaman layanan
                            setTimeout(() => {
                                location.href = base_url + 'layanan';
                            }, 3000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal mencatat kunjungan.',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (err) {
                        console.error("❌ Error kirim ke /masuk atau /print:", err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Koneksi ke printer gagal atau server bermasalah.',
                            confirmButtonText: 'OK'
                        });
                    }
                    return;
                }
            }

            setTimeout(recognizeLoop, 1500); // loop lanjut jika belum dikenali
        }


        async function startRecognition() {
            if (recognitionRunning) return;
            document.getElementById('result').textContent = '⏳ Memulai...';

            if (!modelsLoaded) await loadModels();
            await startCamera();
            await waitForVideoReady(video);

            video.width = video.videoWidth;
            video.height = video.videoHeight;

            const labeledDescriptors = await getKnownDescriptors();
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

            recognitionRunning = true;
            recognizeLoop();
        }

        window.addEventListener('DOMContentLoaded', async () => {
            await startRecognition();
        });
    </script>
</body>
</html>

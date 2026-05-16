<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Face Recognition</title>

    <!-- ✅ face-api.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 30px;
        }

        video {
            border: 2px solid #ccc;
            margin-top: 20px;
        }

        #result {
            font-size: 22px;
            margin-top: 15px;
            font-weight: bold;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }

        /* Modal styling */
        #confirmModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            text-align: left;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content select {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }

        .modal-footer {
            margin-top: 15px;
            text-align: right;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>

<h2>🔎 Face Recognition dari Kamera</h2>

<video id="camera" width="640" height="480" autoplay muted></video>
<br>
<button onclick="startRecognition()">🎥 Mulai Deteksi Wajah</button>
<div id="result">Menunggu deteksi wajah...</div>

<!-- 🧩 Modal Konfirmasi -->
<div id="confirmModal">
    <div class="modal-content">
        <h3>Konfirmasi Kecocokan Wajah</h3>
        <p id="confirmText">Apakah wajah ini cocok dengan tamu yang terdeteksi?</p>

        <label for="selectTamu">Pilih tamu manual (jika tidak cocok):</label>
        <select id="selectTamu">
            <option value="">-- Pilih dari daftar tamu --</option>
        </select>

        <div class="modal-footer">
            <button class="btn btn-secondary" id="btnCancel">Batal</button>
            <button class="btn btn-primary" id="btnSave">Simpan</button>
        </div>
    </div>
</div>

<script>
const base_url = '<?= base_url(); ?>';
const jenis_layanan = '<?= htmlspecialchars((string) ($this->session->userdata('jenis_layanan') ?? ''), ENT_QUOTES, 'UTF-8') ?>';
let video = document.getElementById('camera');
let faceMatcher = null;
let modelsLoaded = false;
let recognitionRunning = false;
let recognizedName = '';
let allTamu = [];

/* ------------------------
   🔹 LOAD MODEL
------------------------ */
async function loadModels() {
    const modelUrl = base_url + 'assets/models';
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
        faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl)
    ]);
    modelsLoaded = true;
    console.log('✅ Semua model face-api dimuat');
}

/* ------------------------
   🔹 START CAMERA
------------------------ */
async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        console.log('🎥 Kamera berhasil dinyalakan');
    } catch (err) {
        alert('❌ Gagal mengakses kamera. Periksa izin kamera.');
        console.error(err);
    }
}

/* ------------------------
   🔹 GET DATA TAMU / FACE DESCRIPTORS
------------------------ */
async function getKnownDescriptors() {
    const res = await fetch(base_url + 'recognize/data');
    const data = await res.json();

    // Simpan semua tamu untuk dropdown manual
    allTamu = data;

    const select = document.getElementById('selectTamu');
    select.innerHTML = '<option value="">-- Pilih dari daftar tamu --</option>';
    data.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id_user ?? '';
        opt.textContent = t.name ?? 'Tanpa Nama';
        select.appendChild(opt);
    });

    return data.map(entry => new faceapi.LabeledFaceDescriptors(
        entry.name,
        [new Float32Array(entry.descriptor)]
    ));
}

/* ------------------------
   🔹 FACE RECOGNITION LOOP
------------------------ */
async function recognizeLoop() {
    if (!recognitionRunning) return;

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        document.getElementById('result').textContent = '🚫 Wajah tidak terdeteksi';
    } else {
        const alignedDetection = faceapi.resizeResults(detection, {
            width: video.videoWidth,
            height: video.videoHeight
        });
        const match = faceMatcher.findBestMatch(alignedDetection.descriptor);
        document.getElementById('result').textContent = `👤 ${match.toString()}`;

        if (match.label !== 'unknown') {
            recognitionRunning = false; // hentikan sementara
            showConfirmModal(match.label);
        }
    }

    setTimeout(recognizeLoop, 1500); // loop tiap 1.5 detik
}

/* ------------------------
   🔹 START RECOGNITION
------------------------ */
async function startRecognition() {
    if (recognitionRunning) return;
    document.getElementById('result').textContent = '⏳ Memuat...';

    if (!modelsLoaded) await loadModels();
    await startCamera();

    const labeledDescriptors = await getKnownDescriptors();
    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.42);

    recognitionRunning = true;
    recognizeLoop();
}

/* ------------------------
   🔹 MODAL KONFIRMASI
------------------------ */
function showConfirmModal(name) {
    recognizedName = name;
    document.getElementById('confirmText').innerText =
        `Apakah wajah ini cocok dengan "${name}"?`;
    document.getElementById('confirmModal').style.display = 'flex';
}

document.getElementById('btnCancel').onclick = () => {
    document.getElementById('confirmModal').style.display = 'none';
    document.getElementById('selectTamu').value = '';
    recognitionRunning = true; // lanjut lagi
    recognizeLoop();
};

document.getElementById('btnSave').onclick = async () => {
    const manualId = document.getElementById('selectTamu').value;
    document.getElementById('confirmModal').style.display = 'none';
    await saveRecognitionResult(recognizedName, manualId);
};

/* ------------------------
   🔹 SIMPAN HASIL
------------------------ */
async function saveRecognitionResult(name, manualId = '') {
    // include jenis_layanan explicitly to avoid relying solely on session cookie
    const payload = { name, manualId, jenis_layanan };
    const res = await fetch(base_url + 'recognize/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const result = await res.json();

    if (result.status === 'success') {
        const nomor = result.nomor_antrian ? `\nNomor antrian: ${result.nomor_antrian}` : '';
        alert((result.message || '✅ Data berhasil disimpan.') + nomor);
        if (result.print_url) {
            window.open(result.print_url, '_blank');
        }
    } else {
        alert((result && result.message) ? result.message : '❌ Gagal menyimpan data kunjungan.');
    }

    // Setelah simpan, lanjut deteksi lagi
    recognitionRunning = true;
    recognizeLoop();
}
</script>

</body>
</html>

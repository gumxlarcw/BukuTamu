<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Face Recognition</title>

    <!-- ✅ Gunakan hanya face-api dari CDN yang sudah dibundel untuk browser -->
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
    </style>
</head>
<body>

<h2>🔎 Face Recognition dari Kamera</h2>

<video id="camera" width="640" height="480" autoplay muted></video>
<br>
<button onclick="startRecognition()">🎥 Mulai Deteksi Wajah</button>
<div id="result">Menunggu deteksi wajah...</div>

<script>
const base_url = '<?= base_url(); ?>';
let video = document.getElementById('camera');
let faceMatcher = null;
let modelsLoaded = false;
let recognitionRunning = false;

async function loadModels() {
    await faceapi.nets.ssdMobilenetv1.loadFromUri(base_url + 'assets/models'); // ✅ Lebih akurat
    await faceapi.nets.faceRecognitionNet.loadFromUri(base_url + 'assets/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri(base_url + 'assets/models');
    modelsLoaded = true;
    console.log('✅ Semua model face-api dimuat');
}

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

async function getKnownDescriptors() {
    const res = await fetch(base_url + 'recognize/data');
    const data = await res.json();
    return data.map(entry => new faceapi.LabeledFaceDescriptors(
        entry.name,
        [new Float32Array(entry.descriptor)]
    ));
}

async function recognizeLoop() {
    if (!recognitionRunning) return;

    const detection = await faceapi
        .detectSingleFace(video)
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        document.getElementById('result').textContent = '🚫 Wajah tidak terdeteksi';
    } else {
        const match = faceMatcher.findBestMatch(detection.descriptor);
        document.getElementById('result').textContent = `👤 ${match.toString()}`;
    }

    setTimeout(recognizeLoop, 1500); // tiap 1.5 detik
}

async function startRecognition() {
    if (recognitionRunning) return;
    document.getElementById('result').textContent = '⏳ Memuat...';

    if (!modelsLoaded) await loadModels();
    await startCamera();

    const labeledDescriptors = await getKnownDescriptors();
    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);

    recognitionRunning = true;
    recognizeLoop();
}
</script>

</body>
</html>

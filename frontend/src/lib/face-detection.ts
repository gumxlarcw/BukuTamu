// Loaded via CDN: face-api.js@0.22.2 (lihat index.html)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
declare const faceapi: any

let modelsLoaded = false

function waitForFaceApi(timeout = 15000): Promise<void> {
  return new Promise((resolve, reject) => {
    if (typeof faceapi !== 'undefined') return resolve()
    const start = Date.now()
    const interval = setInterval(() => {
      if (typeof faceapi !== 'undefined') {
        clearInterval(interval)
        resolve()
      } else if (Date.now() - start > timeout) {
        clearInterval(interval)
        reject(new Error('face-api.js gagal dimuat dari CDN'))
      }
    }, 50)
  })
}

let loadPromise: Promise<void> | null = null

export async function loadFaceModels(): Promise<void> {
  if (modelsLoaded) return
  if (loadPromise) return loadPromise
  loadPromise = (async () => {
    try {
      await waitForFaceApi()
      const MODEL_URL = '/models'
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
      ])
      modelsLoaded = true
    } catch (e) {
      loadPromise = null
      throw e
    }
  })()
  return loadPromise
}

/** Fire-and-forget preload — call early so models are cached */
export function preloadFaceModels(): void {
  loadFaceModels().catch(() => {})
}

export async function detectFace(
  video: HTMLVideoElement
): Promise<{ descriptor: Float32Array; score: number } | null> {
  if (typeof faceapi === 'undefined') {
    console.warn('detectFace: faceapi not loaded yet')
    return null
  }
  if (!modelsLoaded) {
    console.warn('detectFace: models not loaded yet')
    return null
  }
  const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
  const detection = await faceapi
    .detectSingleFace(video, options)
    .withFaceLandmarks()
    .withFaceDescriptor()
  if (!detection) return null
  // score = confidence dari face detector (0-1). Kita gate downstream pakai ini.
  return { descriptor: detection.descriptor, score: detection.detection.score }
}

export interface KnownFace {
  id: number
  nama: string
  descriptor: Float32Array
}

export interface FaceMatch {
  id: number
  nama: string
  distance: number
}

/**
 * Cocokkan descriptor terhadap daftar wajah dikenal dengan TWO gates:
 * 1. Best distance harus <= threshold (default 0.55, lebih strict dari 0.6 untuk reduce false-positive).
 * 2. Margin antara best & 2nd-best harus >= margin (default 0.08) — kalau dua orang
 *    sama-sama "mirip" descriptor, kita tolak supaya tidak salah pilih.
 *
 * Return null kalau salah satu gate gagal. Tuning constants ada di parameter — bisa
 * di-override per use-case (kios PST mungkin perlu lebih strict dari kios resepsionis).
 */
export function matchFace(
  descriptor: Float32Array,
  knownFaces: KnownFace[],
  threshold = 0.55,
  margin = 0.08,
): FaceMatch | null {
  if (knownFaces.length === 0) return null

  const ranked = knownFaces
    .map(k => ({ id: k.id, nama: k.nama, distance: euclideanDistance(descriptor, k.descriptor) }))
    .sort((a, b) => a.distance - b.distance)

  const best = ranked[0]
  if (best.distance > threshold) return null

  // Margin check (kalau hanya 1 known face, margin tidak relevan — skip).
  const second = ranked[1]
  if (second && (second.distance - best.distance) < margin) return null

  return best
}

function euclideanDistance(a: Float32Array, b: Float32Array): number {
  let sum = 0
  for (let i = 0; i < a.length; i++) {
    const diff = a[i] - b[i]
    sum += diff * diff
  }
  return Math.sqrt(sum)
}

/**
 * Average N descriptor menjadi satu. Mengurangi noise per-frame (auto-exposure,
 * micro-movement, JPEG compression artifact). Hasil descriptor lebih stabil
 * dibanding single frame. Standar di sistem biometric.
 */
export function averageDescriptors(descriptors: Float32Array[]): Float32Array {
  if (descriptors.length === 0) throw new Error('averageDescriptors: empty array')
  const len = descriptors[0].length
  const result = new Float32Array(len)
  for (const d of descriptors) {
    for (let i = 0; i < len; i++) result[i] += d[i]
  }
  for (let i = 0; i < len; i++) result[i] /= descriptors.length
  return result
}

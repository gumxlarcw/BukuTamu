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
): Promise<{ descriptor: Float32Array } | null> {
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
  return { descriptor: detection.descriptor }
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

export function matchFace(
  descriptor: Float32Array,
  knownFaces: KnownFace[],
  threshold = 0.6
): FaceMatch | null {
  let bestMatch: FaceMatch | null = null
  let bestDistance = Infinity

  for (const known of knownFaces) {
    const distance = euclideanDistance(descriptor, known.descriptor)
    if (distance < bestDistance) {
      bestDistance = distance
      bestMatch = { id: known.id, nama: known.nama, distance }
    }
  }

  if (bestMatch && bestDistance <= threshold) {
    return bestMatch
  }
  return null
}

function euclideanDistance(a: Float32Array, b: Float32Array): number {
  let sum = 0
  for (let i = 0; i < a.length; i++) {
    const diff = a[i] - b[i]
    sum += diff * diff
  }
  return Math.sqrt(sum)
}

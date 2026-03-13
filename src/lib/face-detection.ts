import * as faceapi from 'face-api.js'

let modelsLoaded = false

export async function loadFaceModels(): Promise<void> {
  if (modelsLoaded) return
  const MODEL_URL = '/models'
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
    faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
  ])
  modelsLoaded = true
}

export async function detectFace(
  video: HTMLVideoElement
): Promise<faceapi.WithFaceDescriptor<faceapi.WithFaceLandmarks<{ detection: faceapi.FaceDetection }, faceapi.FaceLandmarks68>> | null> {
  const detection = await faceapi
    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks(true)
    .withFaceDescriptor()
  return detection ?? null
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

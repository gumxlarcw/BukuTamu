import { useRef, useState, useEffect, useCallback } from 'react'
import { loadFaceModels, detectFace, averageDescriptors } from '@/lib/face-detection'

// ── Tuning constants — adjust kalau akurasi atau speed perlu di-balance ──
// Warmup: tunggu sekian milidetik setelah kamera mulai sebelum percaya descriptor.
// Time-based (bukan frame-based) supaya predictable di CPU lambat. Webcam modern
// settle auto-exposure dalam 400-700ms; 600ms = aman tanpa over-conservative.
const WARMUP_MS = 600
// Berapa descriptor yang di-collect sebelum averaging. 5 sample memberikan ~10%
// extra noise reduction dibanding 4 (σ_avg: 0.447σ vs 0.5σ) dengan biaya +300ms
// scan. User pilih akurasi over speed untuk ruang BPS yang lighting tidak selalu
// optimal (siang/sore beda intensitas, AC blow ke wajah, dll).
const SAMPLE_TARGET = 5
// Confidence detector minimum (0-1). Skip descriptor dari deteksi yang ragu-ragu
// (wajah miring ekstrem, blur, partially occluded). face-api 0.7 = cukup yakin.
const MIN_DETECTION_SCORE = 0.7
// Detection loop interval. 300ms = ~3 fps, cukup untuk UX responsif tanpa burn CPU.
const DETECTION_INTERVAL_MS = 300

interface UseCameraReturn {
  videoRef: React.RefObject<HTMLVideoElement | null>
  isModelLoading: boolean
  isCameraActive: boolean
  faceDetected: boolean
  error: string | null
  /** Descriptor dari frame TERAKHIR (mentah, bisa noisy). Backward compat. */
  currentDescriptor: Float32Array | null
  /** Descriptor STABIL hasil averaging — null sampai sampling selesai. Prefer ini. */
  stableDescriptor: Float32Array | null
  /** Jumlah descriptor berkualitas yang sudah di-collect (0..SAMPLE_TARGET). */
  sampleCount: number
  /** Target jumlah sample (constant) — untuk render progress (n/N). */
  sampleTarget: number
  /** True sebelum warmup beres — gunakan untuk message "Mempersiapkan kamera". */
  isWarmingUp: boolean
  startCamera: () => Promise<void>
  stopCamera: () => void
  capturePhoto: () => { photo: string; descriptor: Float32Array | null } | null
}

export function useCamera(): UseCameraReturn {
  const videoRef = useRef<HTMLVideoElement | null>(null)
  const streamRef = useRef<MediaStream | null>(null)
  const detectionIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null)

  // Refs untuk akumulator (state setInterval-closure stale). Refs always current.
  const bufferRef = useRef<Float32Array[]>([])
  const cameraStartedAtRef = useRef(0)
  const warmupTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  // State untuk re-render UX
  const [sampleCount, setSampleCount] = useState(0)
  const [isWarmingUp, setIsWarmingUp] = useState(false)

  const [isModelLoading, setIsModelLoading] = useState(false)
  const [isCameraActive, setIsCameraActive] = useState(false)
  const [faceDetected, setFaceDetected] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [currentDescriptor, setCurrentDescriptor] = useState<Float32Array | null>(null)
  const [stableDescriptor, setStableDescriptor] = useState<Float32Array | null>(null)

  const stopCamera = useCallback(() => {
    if (detectionIntervalRef.current) {
      clearInterval(detectionIntervalRef.current)
      detectionIntervalRef.current = null
    }
    if (streamRef.current) {
      streamRef.current.getTracks().forEach(track => track.stop())
      streamRef.current = null
    }
    if (videoRef.current) {
      videoRef.current.srcObject = null
    }
    if (warmupTimerRef.current) {
      clearTimeout(warmupTimerRef.current)
      warmupTimerRef.current = null
    }
    bufferRef.current = []
    cameraStartedAtRef.current = 0
    setIsCameraActive(false)
    setFaceDetected(false)
    setCurrentDescriptor(null)
    setStableDescriptor(null)
    setSampleCount(0)
    setIsWarmingUp(false)
  }, [])

  const startCamera = useCallback(async () => {
    setError(null)
    setIsModelLoading(true)
    try {
      await loadFaceModels()
    } catch {
      setError('Gagal memuat model pengenalan wajah')
      setIsModelLoading(false)
      return
    }
    setIsModelLoading(false)

    try {
      if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Browser tidak mendukung akses kamera. Pastikan menggunakan HTTPS.')
      }
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { width: 640, height: 480, facingMode: 'user' },
      })
      streamRef.current = stream
      if (videoRef.current) {
        videoRef.current.srcObject = stream
        await videoRef.current.play()
      }
      // Reset sampling state untuk session baru
      bufferRef.current = []
      cameraStartedAtRef.current = Date.now()
      setSampleCount(0)
      setStableDescriptor(null)
      setIsCameraActive(true)
      setIsWarmingUp(true)
      // Schedule warmup completion — flag jadi false setelah WARMUP_MS, predictable
      // di CPU lambat (tidak tergantung berapa frame yang sempat di-process).
      if (warmupTimerRef.current) clearTimeout(warmupTimerRef.current)
      warmupTimerRef.current = setTimeout(() => setIsWarmingUp(false), WARMUP_MS)

      let detecting = false
      detectionIntervalRef.current = setInterval(async () => {
        if (detecting) return
        if (!videoRef.current || videoRef.current.readyState < 2) return
        if (!streamRef.current) return
        detecting = true
        try {
          const detection = await detectFace(videoRef.current)

          if (!detection) {
            setFaceDetected(false)
            setCurrentDescriptor(null)
            // Jangan reset buffer — biarkan recover kalau wajah re-detect dalam 1-2 frame
            return
          }

          setFaceDetected(true)
          setCurrentDescriptor(detection.descriptor)

          // Quality gates: skip warmup window + skip low-confidence detection.
          // Time-based warmup (lebih predictable dari frame count di CPU lambat).
          if (Date.now() - cameraStartedAtRef.current < WARMUP_MS) return
          if (detection.score < MIN_DETECTION_SCORE) return

          // Stable descriptor sudah siap → tidak perlu collect lagi
          if (bufferRef.current.length >= SAMPLE_TARGET) return

          bufferRef.current.push(detection.descriptor)
          setSampleCount(bufferRef.current.length)

          if (bufferRef.current.length >= SAMPLE_TARGET) {
            const avg = averageDescriptors(bufferRef.current)
            setStableDescriptor(avg)
          }
        } catch (err) {
          console.warn('Face detection error:', err)
        } finally {
          detecting = false
        }
      }, DETECTION_INTERVAL_MS)
    } catch (err) {
      setError('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.')
      console.error(err)
    }
  }, [])

  const capturePhoto = useCallback((): { photo: string; descriptor: Float32Array | null } | null => {
    if (!videoRef.current || !isCameraActive) return null
    const canvas = document.createElement('canvas')
    canvas.width = videoRef.current.videoWidth || 640
    canvas.height = videoRef.current.videoHeight || 480
    const ctx = canvas.getContext('2d')
    if (!ctx) return null
    ctx.drawImage(videoRef.current, 0, 0, canvas.width, canvas.height)
    const photo = canvas.toDataURL('image/jpeg', 0.85)
    // Prefer stableDescriptor (averaged) supaya foto yang disimpan ke DB punya
    // descriptor berkualitas — recognize berikutnya untuk orang ini lebih akurat.
    return { photo, descriptor: stableDescriptor ?? currentDescriptor }
  }, [isCameraActive, currentDescriptor, stableDescriptor])

  useEffect(() => {
    return () => {
      if (detectionIntervalRef.current) clearInterval(detectionIntervalRef.current)
      if (warmupTimerRef.current) clearTimeout(warmupTimerRef.current)
      if (streamRef.current) streamRef.current.getTracks().forEach(t => t.stop())
    }
  }, [])

  return {
    videoRef,
    isModelLoading,
    isCameraActive,
    faceDetected,
    error,
    currentDescriptor,
    stableDescriptor,
    sampleCount,
    sampleTarget: SAMPLE_TARGET,
    isWarmingUp,
    startCamera,
    stopCamera,
    capturePhoto,
  }
}

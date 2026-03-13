import { useRef, useState, useEffect, useCallback } from 'react'
import { loadFaceModels, detectFace } from '@/lib/face-detection'

interface UseCameraReturn {
  videoRef: React.RefObject<HTMLVideoElement | null>
  isModelLoading: boolean
  isCameraActive: boolean
  faceDetected: boolean
  error: string | null
  currentDescriptor: Float32Array | null
  startCamera: () => Promise<void>
  stopCamera: () => void
  capturePhoto: () => { photo: string; descriptor: Float32Array | null } | null
}

export function useCamera(): UseCameraReturn {
  const videoRef = useRef<HTMLVideoElement | null>(null)
  const streamRef = useRef<MediaStream | null>(null)
  const detectionIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null)

  const [isModelLoading, setIsModelLoading] = useState(false)
  const [isCameraActive, setIsCameraActive] = useState(false)
  const [faceDetected, setFaceDetected] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [currentDescriptor, setCurrentDescriptor] = useState<Float32Array | null>(null)

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
    setIsCameraActive(false)
    setFaceDetected(false)
    setCurrentDescriptor(null)
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
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { width: 640, height: 480, facingMode: 'user' },
      })
      streamRef.current = stream
      if (videoRef.current) {
        videoRef.current.srcObject = stream
        await videoRef.current.play()
      }
      setIsCameraActive(true)

      detectionIntervalRef.current = setInterval(async () => {
        if (!videoRef.current || videoRef.current.readyState < 2) return
        try {
          const detection = await detectFace(videoRef.current)
          if (detection) {
            setFaceDetected(true)
            setCurrentDescriptor(detection.descriptor)
          } else {
            setFaceDetected(false)
            setCurrentDescriptor(null)
          }
        } catch {
          // detection errors are non-fatal
        }
      }, 500)
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
    return { photo, descriptor: currentDescriptor }
  }, [isCameraActive, currentDescriptor])

  useEffect(() => {
    return () => {
      if (detectionIntervalRef.current) clearInterval(detectionIntervalRef.current)
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
    startCamera,
    stopCamera,
    capturePhoto,
  }
}

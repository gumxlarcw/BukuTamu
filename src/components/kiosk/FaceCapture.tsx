import { useState, useEffect } from 'react'
import { useCamera } from '@/hooks/useCamera'
import { Camera, RotateCcw, Check, Loader2 } from 'lucide-react'

interface FaceCaptureProps {
  onConfirm: (photo: string, descriptor: Float32Array | null) => void
}

export function FaceCapture({ onConfirm }: FaceCaptureProps) {
  const {
    videoRef,
    isModelLoading,
    isCameraActive,
    faceDetected,
    error,
    startCamera,
    stopCamera,
    capturePhoto,
  } = useCamera()

  const [captured, setCaptured] = useState<{ photo: string; descriptor: Float32Array | null } | null>(null)

  useEffect(() => {
    startCamera()
    return () => stopCamera()
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleCapture = () => {
    const result = capturePhoto()
    if (result) {
      setCaptured(result)
      stopCamera()
    }
  }

  const handleRetake = () => {
    setCaptured(null)
    startCamera()
  }

  const handleConfirm = () => {
    if (captured) {
      onConfirm(captured.photo, captured.descriptor)
    }
  }

  if (error) {
    return (
      <div className="flex flex-col items-center gap-6 text-center">
        <div className="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center">
          <Camera className="w-10 h-10 text-red-400" />
        </div>
        <p className="text-red-300 text-lg">{error}</p>
        <button
          onClick={() => startCamera()}
          className="px-8 py-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white font-bold text-lg"
        >
          Coba Lagi
        </button>
      </div>
    )
  }

  return (
    <div className="flex flex-col items-center gap-6">
      {/* Video / Preview area */}
      <div className="relative w-80 h-80 rounded-full overflow-hidden border-4 border-teal-400 shadow-2xl bg-gray-900">
        {/* Oval face guide overlay */}
        {!captured && (
          <div className="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
            <div
              className={`w-56 h-64 rounded-full border-4 transition-colors duration-300 ${
                faceDetected ? 'border-teal-400 shadow-lg' : 'border-white/40'
              }`}
            />
          </div>
        )}

        {/* Loading overlay */}
        {isModelLoading && (
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 z-20">
            <Loader2 className="w-12 h-12 text-teal-400 animate-spin mb-3" />
            <p className="text-white text-sm">Memuat model...</p>
          </div>
        )}

        {/* Video element */}
        {!captured && (
          <video
            ref={videoRef}
            className="w-full h-full object-cover"
            muted
            playsInline
          />
        )}

        {/* Preview image */}
        {captured && (
          <img
            src={captured.photo}
            alt="Captured"
            className="w-full h-full object-cover"
          />
        )}
      </div>

      {/* Status text */}
      {!captured && isCameraActive && (
        <p className={`text-lg font-semibold ${faceDetected ? 'text-teal-300' : 'text-white/60'}`}>
          {faceDetected ? '✓ Wajah terdeteksi' : 'Posisikan wajah Anda dalam lingkaran'}
        </p>
      )}
      {captured && (
        <p className="text-lg font-semibold text-teal-300">Foto berhasil diambil</p>
      )}

      {/* Action buttons */}
      {!captured ? (
        <button
          onClick={handleCapture}
          disabled={!faceDetected || !isCameraActive}
          className={`flex items-center gap-3 px-10 py-5 rounded-2xl text-xl font-bold shadow-xl transition-all
            ${faceDetected && isCameraActive
              ? 'bg-teal-500 hover:bg-teal-400 text-white active:scale-95'
              : 'bg-white/20 text-white/40 cursor-not-allowed'
            }`}
        >
          <Camera className="w-6 h-6" />
          Ambil Foto
        </button>
      ) : (
        <div className="flex gap-4">
          <button
            onClick={handleRetake}
            className="flex items-center gap-2 px-8 py-4 rounded-xl border-2 border-white/30 text-white text-lg font-semibold hover:bg-white/10 transition-all active:scale-95"
          >
            <RotateCcw className="w-5 h-5" />
            Ulangi
          </button>
          <button
            onClick={handleConfirm}
            className="flex items-center gap-2 px-8 py-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white text-lg font-bold shadow-lg transition-all active:scale-95"
          >
            <Check className="w-5 h-5" />
            Konfirmasi
          </button>
        </div>
      )}
    </div>
  )
}

import { useState, useEffect } from 'react'
import { useQuery } from '@tanstack/react-query'
import { kioskApi } from '@/api/kiosk'
import { useCamera } from '@/hooks/useCamera'
import { matchFace, type KnownFace } from '@/lib/face-detection'
import { Loader2, UserCheck, User } from 'lucide-react'

interface MatchResult {
  id: number
  nama: string
}

interface FaceRecognizeProps {
  onMatch: (match: MatchResult) => void
  onNoMatch: () => void
}

export function FaceRecognize({ onMatch, onNoMatch }: FaceRecognizeProps) {
  const {
    videoRef,
    isModelLoading,
    isCameraActive,
    faceDetected,
    error,
    currentDescriptor,
    startCamera,
    stopCamera,
  } = useCamera()

  const [matched, setMatched] = useState<MatchResult | null>(null)
  const { data: faceData, isLoading: isFaceDataLoading } = useQuery({
    queryKey: ['face-data'],
    queryFn: () => kioskApi.getFaceData().then(r => r.data.data),
  })

  // Start camera on mount
  useEffect(() => {
    startCamera()
    return () => stopCamera()
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  // Run face matching when descriptor and face data are available
  useEffect(() => {
    if (!faceData || !currentDescriptor || matched) return

    const knownFaces: KnownFace[] = faceData
      .filter(f => f.face_descriptor && f.face_descriptor.length > 0)
      .map(f => ({
        id: f.id_user,
        nama: f.nama,
        descriptor: new Float32Array(f.face_descriptor),
      }))

    if (knownFaces.length === 0) return

    const result = matchFace(currentDescriptor, knownFaces)
    if (result) {
      setMatched({ id: result.id, nama: result.nama })
      stopCamera()
    }
  }, [currentDescriptor, faceData, matched, stopCamera])

  const handleConfirm = () => {
    if (matched) onMatch(matched)
  }

  const handleNotMe = () => {
    setMatched(null)
    onNoMatch()
  }

  const isLoading = isModelLoading || isFaceDataLoading

  if (error) {
    return (
      <div className="flex flex-col items-center gap-6 text-center">
        <div className="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center">
          <User className="w-10 h-10 text-red-400" />
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
      {/* Video area */}
      <div className="relative w-80 h-80 rounded-full overflow-hidden border-4 border-teal-400 shadow-2xl bg-gray-900">
        {/* Face guide oval */}
        {!matched && (
          <div className="absolute inset-0 flex items-center justify-center z-10 pointer-events-none">
            <div
              className={`w-56 h-64 rounded-full border-4 transition-colors duration-300 ${
                faceDetected ? 'border-teal-400' : 'border-white/40'
              }`}
            />
          </div>
        )}

        {/* Loading overlay */}
        {isLoading && (
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 z-20">
            <Loader2 className="w-12 h-12 text-teal-400 animate-spin mb-3" />
            <p className="text-white text-sm">
              {isModelLoading ? 'Memuat model...' : 'Memuat data wajah...'}
            </p>
          </div>
        )}

        {/* Match overlay */}
        {matched && (
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-teal-900/80 z-20">
            <UserCheck className="w-16 h-16 text-teal-300 mb-3" />
            <p className="text-white font-bold text-lg text-center px-4">{matched.nama}</p>
          </div>
        )}

        <video
          ref={videoRef}
          className={`w-full h-full object-cover ${matched ? 'opacity-30' : ''}`}
          muted
          playsInline
        />
      </div>

      {/* Status text */}
      {!matched && isCameraActive && !isLoading && (
        <p className={`text-lg font-semibold ${faceDetected ? 'text-teal-300' : 'text-white/60'}`}>
          {faceDetected ? 'Sedang mencari kecocokan...' : 'Posisikan wajah Anda dalam lingkaran'}
        </p>
      )}

      {/* Match result */}
      {matched && (
        <div className="text-center">
          <p className="text-teal-300 text-xl font-bold mb-1">Wajah Dikenali!</p>
          <p className="text-white text-2xl font-bold">{matched.nama}</p>
          <p className="text-white/70 mt-1">Apakah ini Anda?</p>
        </div>
      )}

      {/* Buttons */}
      {matched && (
        <div className="flex gap-4">
          <button
            onClick={handleNotMe}
            className="px-8 py-4 rounded-xl border-2 border-white/30 text-white text-lg font-semibold hover:bg-white/10 transition-all active:scale-95"
          >
            Bukan Saya
          </button>
          <button
            onClick={handleConfirm}
            className="px-8 py-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white text-lg font-bold shadow-lg transition-all active:scale-95"
          >
            Ya, Itu Saya
          </button>
        </div>
      )}

      {!matched && !isLoading && (
        <button
          onClick={onNoMatch}
          className="px-8 py-3 text-white/60 hover:text-white underline text-base transition-colors"
        >
          Daftar sebagai pengunjung baru
        </button>
      )}
    </div>
  )
}

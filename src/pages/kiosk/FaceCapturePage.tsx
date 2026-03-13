import { useState } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import { useMutation } from '@tanstack/react-query'
import { kioskApi } from '@/api/kiosk'
import { PhotoDisclaimer } from '@/components/kiosk/PhotoDisclaimer'
import { FaceCapture } from '@/components/kiosk/FaceCapture'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import { STORAGE_KEY } from '@/components/kiosk/VisitorForm'
import type { GuestFormData } from '@/types/guest'

export default function FaceCapturePage() {
  const navigate = useNavigate()
  const location = useLocation()

  const formData: GuestFormData = location.state?.formData
  const jenis_layanan: string = location.state?.jenis_layanan ?? ''

  const [consentAccepted, setConsentAccepted] = useState(false)
  const [submitError, setSubmitError] = useState<string | null>(null)

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const registerMutation = useMutation({
    mutationFn: (payload: { photo: string; descriptor: Float32Array | null }) =>
      kioskApi.register({
        ...formData,
        foto: payload.photo,
        face_descriptor: payload.descriptor ? Array.from(payload.descriptor) : [],
        jenis_layanan,
        biometric_consent: true,
        consent_timestamp: new Date().toISOString(),
      } as Parameters<typeof kioskApi.register>[0]),
    onSuccess: (res) => {
      localStorage.removeItem(STORAGE_KEY)
      const visitId = res.data.data.id_kunjungan
      navigate(`/kiosk/ticket/${visitId}`)
    },
    onError: (err: Error) => {
      setSubmitError(err.message || 'Terjadi kesalahan. Silakan coba lagi.')
    },
  })

  const handleDecline = () => {
    navigate('/kiosk/form', { state: { formData, jenis_layanan } })
  }

  const handlePhotoConfirm = (photo: string, descriptor: Float32Array | null) => {
    setSubmitError(null)
    registerMutation.mutate({ photo, descriptor })
  }

  if (!formData) {
    return (
      <div className="text-white text-center">
        <p className="text-xl mb-4">Data tidak ditemukan</p>
        <button
          onClick={() => navigate('/kiosk')}
          className="px-8 py-4 bg-teal-500 rounded-xl text-white font-bold"
        >
          Mulai Ulang
        </button>
      </div>
    )
  }

  return (
    <>
      {/* Photo consent disclaimer */}
      {!consentAccepted && (
        <PhotoDisclaimer
          onAccept={() => setConsentAccepted(true)}
          onDecline={handleDecline}
        />
      )}

      <div className="flex flex-col items-center text-white px-4 max-w-2xl w-full mx-auto">
        <h1 className="text-3xl font-bold mb-2 drop-shadow-lg">Verifikasi Wajah</h1>
        <p className="text-white/80 mb-8 text-center">
          Ambil foto wajah Anda untuk menyelesaikan pendaftaran
        </p>

        {registerMutation.isPending ? (
          <div className="flex flex-col items-center gap-4">
            <LoadingSpinner />
            <p className="text-white/80 text-lg">Mendaftarkan pengunjung...</p>
          </div>
        ) : (
          <>
            {consentAccepted && <FaceCapture onConfirm={handlePhotoConfirm} />}

            {submitError && (
              <div className="mt-6 p-4 rounded-xl bg-red-500/20 border border-red-400/40 text-red-200 text-center">
                {submitError}
              </div>
            )}
          </>
        )}

        <button
          onClick={() => navigate('/kiosk/form', { state: { formData, jenis_layanan } })}
          className="mt-8 px-8 py-3 text-white/70 hover:text-white underline text-lg transition-colors"
          disabled={registerMutation.isPending}
        >
          Kembali
        </button>
      </div>
    </>
  )
}

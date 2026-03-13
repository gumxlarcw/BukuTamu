import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useMutation } from '@tanstack/react-query'
import { useQuery } from '@tanstack/react-query'
import { kioskApi } from '@/api/kiosk'
import { servicesApi } from '@/api/services'
import { FaceRecognize } from '@/components/kiosk/FaceRecognize'
import { ServiceBubble } from '@/components/kiosk/ServiceBubble'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import type { Service } from '@/api/services'

interface MatchedGuest {
  id: number
  nama: string
}

export default function FaceRecognizePage() {
  const navigate = useNavigate()
  const [matchedGuest, setMatchedGuest] = useState<MatchedGuest | null>(null)
  const [selectedService, setSelectedService] = useState<Service | null>(null)
  const [submitError, setSubmitError] = useState<string | null>(null)

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const { data: services, isLoading: servicesLoading } = useQuery({
    queryKey: ['services'],
    queryFn: () => servicesApi.list().then(r => r.data.data),
    enabled: matchedGuest !== null,
  })

  const visitMutation = useMutation({
    mutationFn: (data: { guest_id: number; jenis_layanan: string }) =>
      kioskApi.visit(data),
    onSuccess: (res) => {
      const visitId = res.data.data.visit_id
      navigate(`/kiosk/ticket/${visitId}`)
    },
    onError: (err: Error) => {
      setSubmitError(err.message || 'Terjadi kesalahan. Silakan coba lagi.')
    },
  })

  const handleMatch = (guest: MatchedGuest) => {
    setMatchedGuest(guest)
  }

  const handleNoMatch = () => {
    navigate('/kiosk/service')
  }

  const handleConfirmVisit = () => {
    if (!matchedGuest || !selectedService) return
    setSubmitError(null)
    visitMutation.mutate({
      guest_id: matchedGuest.id,
      jenis_layanan: selectedService.name,
    })
  }

  return (
    <div className="flex flex-col items-center text-white px-4 max-w-2xl w-full mx-auto">
      <h1 className="text-3xl font-bold mb-2 drop-shadow-lg text-center">Pengenalan Wajah</h1>
      <p className="text-white/80 mb-8 text-center">
        {matchedGuest
          ? `Selamat datang kembali, ${matchedGuest.nama}!`
          : 'Arahkan wajah Anda ke kamera'}
      </p>

      {/* Face recognition view */}
      {!matchedGuest && (
        <FaceRecognize onMatch={handleMatch} onNoMatch={handleNoMatch} />
      )}

      {/* Service selection after match */}
      {matchedGuest && (
        <div className="w-full">
          <h2 className="text-2xl font-bold mb-6 text-center">Pilih Layanan</h2>

          {servicesLoading && <LoadingSpinner />}

          {services && (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
              {services.map(service => (
                <ServiceBubble
                  key={service.id}
                  service={service}
                  selected={selectedService?.id === service.id}
                  onSelect={() => setSelectedService(service)}
                />
              ))}
            </div>
          )}

          {submitError && (
            <div className="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-400/40 text-red-200 text-center">
              {submitError}
            </div>
          )}

          <div className="flex gap-4">
            <button
              onClick={() => {
                setMatchedGuest(null)
                setSelectedService(null)
              }}
              className="flex-1 px-6 py-5 rounded-xl border-2 border-white/30 text-white text-lg font-semibold hover:bg-white/10 transition-all active:scale-95"
              disabled={visitMutation.isPending}
            >
              Kembali
            </button>
            <button
              onClick={handleConfirmVisit}
              disabled={!selectedService || visitMutation.isPending}
              className={`flex-1 px-6 py-5 rounded-xl text-lg font-bold transition-all active:scale-95
                ${selectedService && !visitMutation.isPending
                  ? 'bg-teal-500 hover:bg-teal-400 text-white shadow-xl'
                  : 'bg-white/20 text-white/40 cursor-not-allowed'
                }`}
            >
              {visitMutation.isPending ? 'Memproses...' : 'Konfirmasi'}
            </button>
          </div>
        </div>
      )}
    </div>
  )
}

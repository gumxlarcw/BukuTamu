import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { servicesApi } from '@/api/services'
import { ServiceBubble } from '@/components/kiosk/ServiceBubble'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import type { Service } from '@/api/services'

export default function ServiceSelectPage() {
  const navigate = useNavigate()
  const [selectedService, setSelectedService] = useState<Service | null>(null)

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const { data, isLoading, isError } = useQuery({
    queryKey: ['services'],
    queryFn: () => servicesApi.list().then(r => r.data.data),
  })

  const handleNext = () => {
    if (!selectedService) return
    navigate('/kiosk/form', { state: { jenis_layanan: selectedService.name } })
  }

  return (
    <div className="flex flex-col items-center text-white px-4 max-w-4xl w-full mx-auto">
      <h1 className="text-3xl md:text-4xl font-bold mb-2 drop-shadow-lg text-center">
        Pilih Layanan
      </h1>
      <p className="text-white/80 mb-8 text-lg text-center">
        Silakan pilih jenis layanan yang Anda butuhkan
      </p>

      {isLoading && <LoadingSpinner className="py-12" />}
      {isError && (
        <div className="text-red-300 text-center py-8">
          Gagal memuat daftar layanan. Silakan coba lagi.
        </div>
      )}

      {data && (
        <div className="grid grid-cols-2 md:grid-cols-3 gap-4 w-full">
          {data.map(service => (
            <ServiceBubble
              key={service.id}
              service={service}
              selected={selectedService?.id === service.id}
              onSelect={() => setSelectedService(service)}
            />
          ))}
        </div>
      )}

      <div className="flex gap-4 mt-10 w-full max-w-md">
        <button
          onClick={() => navigate('/kiosk/status')}
          className="flex-1 px-8 py-5 rounded-xl border-2 border-white/30 text-white text-lg font-semibold hover:bg-white/10 transition-all active:scale-95"
        >
          Kembali
        </button>
        <button
          onClick={handleNext}
          disabled={!selectedService}
          className={`flex-1 px-8 py-5 rounded-xl text-lg font-bold transition-all active:scale-95
            ${selectedService
              ? 'bg-teal-500 hover:bg-teal-400 text-white shadow-xl'
              : 'bg-white/20 text-white/40 cursor-not-allowed'
            }`}
        >
          Lanjut
        </button>
      </div>
    </div>
  )
}

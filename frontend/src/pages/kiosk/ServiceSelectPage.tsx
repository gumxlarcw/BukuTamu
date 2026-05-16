import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { servicesApi } from '@/api/services'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import { SARANA_OPTIONS } from '@/types/guest'
import { CheckCircle, Lock } from 'lucide-react'
import { wouldBeCross, getAllowedSaranaCodes } from '@/lib/role-access'
import type { Service } from '@/api/services'

export default function ServiceSelectPage() {
  const navigate = useNavigate()
  const [selectedServices, setSelectedServices] = useState<string[]>([])
  const [layananLainnya, setLayananLainnya] = useState('')
  const [selectedSarana, setSelectedSarana] = useState<number[]>([])
  const [saranaLainnya, setSaranaLainnya] = useState('')

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const { data, isLoading, isError } = useQuery({
    queryKey: ['services'],
    queryFn: () => servicesApi.list().then(r => r.data.data),
  })

  const allowedSaranaCodes = getAllowedSaranaCodes(selectedServices)

  // Saat grup layanan berubah, buang sarana yang sudah dipilih tapi tidak valid lagi.
  useEffect(() => {
    setSelectedSarana(prev => prev.filter(v => allowedSaranaCodes.includes(v)))
    if (!allowedSaranaCodes.includes(32)) setSaranaLainnya('')
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedServices])

  const toggleService = (s: Service) => {
    setSelectedServices(prev => {
      const removing = prev.includes(s.name)
      if (s.name === 'Lainnya' && removing) setLayananLainnya('')
      return removing ? prev.filter(n => n !== s.name) : [...prev, s.name]
    })
  }

  const toggleSarana = (val: number) => {
    setSelectedSarana(prev => {
      const removing = prev.includes(val)
      if (val === 32 && removing) setSaranaLainnya('')
      return removing ? prev.filter(v => v !== val) : [...prev, val]
    })
  }

  const isValid =
    selectedServices.length > 0 &&
    (!selectedServices.includes('Lainnya') || layananLainnya.trim() !== '') &&
    selectedSarana.length > 0 &&
    (!selectedSarana.includes(32) || saranaLainnya.trim() !== '')

  const handleNext = () => {
    if (!isValid) return
    navigate('/kiosk/status', {
      state: {
        jenis_layanan: selectedServices,
        layanan_lainnya: layananLainnya,
        sarana: selectedSarana,
        sarana_lainnya: saranaLainnya,
      },
    })
  }

  return (
    <div className="flex flex-col text-gray-800 w-full max-w-4xl mx-auto h-full">
      {/* Header */}
      <div className="shrink-0 text-center px-4 pb-2">
        <h1 className="kiosk-enter text-xl font-bold mb-0.5">Layanan & Sarana</h1>
        <p className="kiosk-enter text-gray-500 text-[10px] leading-snug" style={{ animationDelay: '100ms' }}>
          Pertanyaan berikut merujuk pada periode <span className="font-semibold text-gray-700">1 Januari {new Date().getFullYear()}</span> hingga saat ini.
          <br />Pilih layanan dan sarana yang pernah Anda gunakan (boleh lebih dari satu).
        </p>
      </div>

      {/* Scrollable content */}
      <div className="kiosk-enter kiosk-scroll flex-1 min-h-0 px-4" style={{ animationDelay: '200ms' }}>

        {/* Jenis Layanan */}
        <div className="mb-4">
          <p className="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Jenis Layanan *</p>
          {isLoading && <LoadingSpinner className="py-6" />}
          {isError && <p className="text-red-600 text-xs py-4">Gagal memuat layanan.</p>}
          {data && (
            <>
              <div className="grid grid-cols-4 gap-2">
                {data.map(service => {
                  const active = selectedServices.includes(service.name)
                  const blocked = wouldBeCross(selectedServices, service.name)
                  return (
                    <button
                      key={service.id}
                      onClick={() => !blocked && toggleService(service)}
                      disabled={blocked}
                      title={blocked ? 'Tidak boleh campur kategori — pilih satu grup saja (SKD inti, Konsultasi DTSEN, atau Front-office)' : undefined}
                      className={`relative flex items-center gap-2 p-3 rounded-xl border-2 text-left transition-all overflow-hidden
                        ${active
                          ? 'border-orange-400 bg-orange-500 text-white active:scale-95 cursor-pointer'
                          : blocked
                            ? 'border-gray-200 bg-gray-100/70 text-gray-400 cursor-not-allowed'
                            : 'border-gray-200 bg-white/70 text-gray-800 hover:bg-white/90 active:scale-95 cursor-pointer'
                        }`}
                    >
                      {active && <CheckCircle className="w-4 h-4 shrink-0" />}
                      {blocked && <Lock className="w-3.5 h-3.5 shrink-0" />}
                      <span className="text-xs font-semibold leading-snug break-words">{service.name}</span>
                    </button>
                  )
                })}
              </div>
              {selectedServices.length > 0 && (
                <p className="text-[10px] text-orange-600 mt-1.5 px-1">
                  ℹ️ Pilih <em>satu</em> kategori: layanan inti SKD (Perpustakaan/Konsultasi/Rekomendasi/Penjualan), <em>atau</em> Konsultasi DTSEN, <em>atau</em> Layanan front-office (Lainnya/Keperluan Pimpinan).
                </p>
              )}
            </>
          )}
          {selectedServices.includes('Lainnya') && (
            <input
              type="text"
              className="w-full mt-2 px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-white/60 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-400 focus:bg-white/80 transition-colors"
              placeholder="Sebutkan layanan lainnya"
              value={layananLainnya}
              onChange={e => setLayananLainnya(e.target.value)}
            />
          )}
        </div>

        {/* Sarana */}
        <div>
          <p className="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Sarana yang Digunakan *</p>
          {selectedServices.length === 0 && (
            <p className="text-[10px] text-gray-500 italic mb-1.5 px-1">
              Pilih jenis layanan terlebih dahulu untuk mengaktifkan sarana yang tersedia.
            </p>
          )}
          <div className="grid grid-cols-3 gap-2">
            {SARANA_OPTIONS.map(opt => {
              const active = selectedSarana.includes(opt.value)
              const disabled = !allowedSaranaCodes.includes(opt.value)
              return (
                <button
                  key={opt.value}
                  onClick={() => !disabled && toggleSarana(opt.value)}
                  disabled={disabled}
                  title={disabled && selectedServices.length === 0
                    ? 'Pilih jenis layanan dulu'
                    : disabled
                      ? 'Sarana ini tidak tersedia untuk layanan yang Anda pilih'
                      : undefined}
                  className={`relative flex items-center gap-2 p-3 rounded-xl border-2 text-left transition-all overflow-hidden
                    ${active
                      ? 'border-orange-400 bg-orange-500 text-white active:scale-95 cursor-pointer'
                      : disabled
                        ? 'border-gray-200 bg-gray-100/70 text-gray-400 cursor-not-allowed'
                        : 'border-gray-200 bg-white/70 text-gray-800 hover:bg-white/90 active:scale-95 cursor-pointer'
                    }`}
                >
                  {active && <CheckCircle className="w-4 h-4 shrink-0" />}
                  {disabled && <Lock className="w-3.5 h-3.5 shrink-0" />}
                  <span className="text-xs font-semibold leading-snug break-words">{opt.label}</span>
                </button>
              )
            })}
          </div>
          {selectedSarana.includes(32) && (
            <input
              type="text"
              className="w-full mt-2 px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-white/60 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-400 focus:bg-white/80 transition-colors"
              placeholder="Sebutkan sarana lainnya"
              value={saranaLainnya}
              onChange={e => setSaranaLainnya(e.target.value)}
            />
          )}
        </div>
      </div>

      {/* Footer buttons */}
      <div className="shrink-0 flex gap-3 px-4 pt-3 pb-1">
        <button
          onClick={() => navigate('/kiosk')}
          className="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-white/60 transition-all active:scale-95 cursor-pointer"
        >
          Kembali
        </button>
        <button
          onClick={handleNext}
          disabled={!isValid}
          className={`flex-1 px-4 py-3 rounded-xl text-sm font-bold transition-all active:scale-95
            ${isValid
              ? 'bg-orange-500 hover:bg-orange-400 text-white shadow-xl cursor-pointer'
              : 'bg-gray-200 text-gray-400 cursor-not-allowed'
            }`}
        >
          Lanjut
        </button>
      </div>
    </div>
  )
}

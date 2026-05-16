import { useState } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import { VisitorForm } from '@/components/kiosk/VisitorForm'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import type { GuestFormData } from '@/types/guest'

const EMPTY_FORM: GuestFormData = {
  tgldatang: '',
  nama: '',
  email: '',
  notel: '',
  jeniskelamin: 'Laki-laki',
  umur: 0,
  disabilitas: 0,
  jenis_disabilitas: 0,
  pendidikan: 0,
  pekerjaan: 0,
  pekerjaan_lainnya: '',
  kategori_instansi: 0,
  kategori_lainnya: '',
  nama_instansi: '',
  pemanfaatan: 0,
  pemanfaatan_lainnya: '',
  pengaduan: 'Tidak',
}

export default function VisitorFormPage() {
  const navigate = useNavigate()
  const location = useLocation()
  const visitState = location.state ?? {}
  const jenis_layanan: string[] = visitState.jenis_layanan ?? []

  const [formData, setFormData] = useState<GuestFormData>(EMPTY_FORM)

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const isValid =
    formData.nama.trim() !== '' &&
    formData.email.trim() !== '' &&
    formData.notel.trim() !== '' &&
    formData.umur > 0 &&
    formData.disabilitas > 0 &&
    (formData.disabilitas !== 1 || formData.jenis_disabilitas > 0) &&
    formData.pendidikan > 0 &&
    formData.pekerjaan > 0 &&
    (formData.pekerjaan !== 7 || formData.pekerjaan_lainnya.trim() !== '') &&
    formData.kategori_instansi > 0 &&
    (formData.kategori_instansi !== 9 || formData.kategori_lainnya.trim() !== '') &&
    formData.nama_instansi.trim() !== '' &&
    formData.pemanfaatan > 0 &&
    (formData.pemanfaatan !== 5 || formData.pemanfaatan_lainnya.trim() !== '')

  const handleNext = () => {
    if (!isValid) return
    navigate('/kiosk/capture', { state: { formData, ...visitState } })
  }

  return (
    <div className="flex flex-col text-gray-800 w-full max-w-2xl mx-auto h-full">
      {/* Fixed header */}
      <div className="shrink-0 text-center px-4 pb-2">
        <h1 className="kiosk-enter text-xl font-bold mb-0.5">Data Diri</h1>
        <p className="kiosk-enter text-gray-500 text-xs" style={{ animationDelay: '100ms' }}>
          Layanan: <span className="font-semibold text-orange-600">{jenis_layanan.length > 0 ? jenis_layanan.join(', ') : '-'}</span>
        </p>
      </div>

      {/* Scrollable form area — only this part scrolls (invisible scrollbar) */}
      <div className="kiosk-enter kiosk-scroll flex-1 min-h-0 px-4" style={{ animationDelay: '200ms' }}>
        <div className="w-full bg-white/80 backdrop-blur-sm rounded-2xl p-4 border border-gray-200 shadow-lg overflow-hidden">
          <VisitorForm value={formData} onChange={setFormData} />
        </div>
      </div>

      {/* Fixed footer buttons */}
      <div className="shrink-0 flex gap-3 px-4 pt-3 pb-1">
        <button
          onClick={() => navigate('/kiosk/status', { state: visitState })}
          className="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-white/60 transition-all active:scale-95 cursor-pointer"
        >
          Kembali
        </button>
        <button
          onClick={handleNext}
          disabled={!isValid}
          className={`flex-1 px-4 py-3 rounded-xl text-sm font-bold transition-all active:scale-95
            ${isValid
              ? 'bg-orange-500 hover:bg-orange-400 text-white shadow-xl'
              : 'bg-gray-200 text-gray-400 cursor-not-allowed'
            }`}
        >
          Lanjut
        </button>
      </div>
    </div>
  )
}

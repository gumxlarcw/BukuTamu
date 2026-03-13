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
  pendidikan: 0,
  pekerjaan: 0,
  kategori_instansi: 0,
  nama_instansi: '',
  pemanfaatan: 0,
  pengaduan: 'Tidak',
}

export default function VisitorFormPage() {
  const navigate = useNavigate()
  const location = useLocation()
  const jenis_layanan: string = location.state?.jenis_layanan ?? ''

  const [formData, setFormData] = useState<GuestFormData>(EMPTY_FORM)

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  const isValid =
    formData.nama.trim() !== '' &&
    formData.email.trim() !== '' &&
    formData.notel.trim() !== '' &&
    formData.pendidikan > 0 &&
    formData.pekerjaan > 0 &&
    formData.kategori_instansi > 0 &&
    formData.nama_instansi.trim() !== '' &&
    formData.pemanfaatan > 0

  const handleNext = () => {
    if (!isValid) return
    navigate('/kiosk/capture', { state: { formData, jenis_layanan } })
  }

  return (
    <div className="flex flex-col items-center text-white px-4 w-full max-w-2xl mx-auto py-8">
      <h1 className="text-3xl font-bold mb-2 text-center drop-shadow-lg">Data Diri</h1>
      <p className="text-white/80 mb-8 text-center">
        Layanan: <span className="font-semibold text-teal-300">{jenis_layanan || '-'}</span>
      </p>

      <div className="w-full bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
        <VisitorForm value={formData} onChange={setFormData} />
      </div>

      <div className="flex gap-4 mt-8 w-full">
        <button
          onClick={() => navigate('/kiosk/service')}
          className="flex-1 px-6 py-5 rounded-xl border-2 border-white/30 text-white text-lg font-semibold hover:bg-white/10 transition-all active:scale-95"
        >
          Kembali
        </button>
        <button
          onClick={handleNext}
          disabled={!isValid}
          className={`flex-1 px-6 py-5 rounded-xl text-lg font-bold transition-all active:scale-95
            ${isValid
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

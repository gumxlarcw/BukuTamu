import { useState } from 'react'
import {
  UMUR_OPTIONS,
  DISABILITAS_OPTIONS,
  JENIS_DISABILITAS_OPTIONS,
  PENDIDIKAN_OPTIONS,
  PEKERJAAN_OPTIONS,
  KATEGORI_INSTANSI_OPTIONS,
  PEMANFAATAN_OPTIONS,
} from '@/types/guest'

interface ProfileGapsModalProps {
  gaps: string[]
  onSubmit: (data: Record<string, unknown>) => void
  onSkip: () => void
  isSubmitting: boolean
}

const FIELD_CONFIG: Record<string, { label: string; type: 'select' | 'text'; options?: readonly { value: number; label: string }[]; placeholder?: string }> = {
  umur:              { label: 'Umur', type: 'select', options: UMUR_OPTIONS },
  disabilitas:       { label: 'Penyandang/Pendamping Disabilitas?', type: 'select', options: DISABILITAS_OPTIONS },
  jenis_disabilitas: { label: 'Jenis Disabilitas', type: 'select', options: JENIS_DISABILITAS_OPTIONS },
  pendidikan:        { label: 'Pendidikan Tertinggi', type: 'select', options: PENDIDIKAN_OPTIONS },
  pekerjaan:         { label: 'Pekerjaan Utama', type: 'select', options: PEKERJAAN_OPTIONS },
  kategori_instansi: { label: 'Kategori Instansi', type: 'select', options: KATEGORI_INSTANSI_OPTIONS },
  nama_instansi:     { label: 'Nama Instansi', type: 'text', placeholder: 'Nama perusahaan/instansi' },
  pemanfaatan:       { label: 'Pemanfaatan Utama', type: 'select', options: PEMANFAATAN_OPTIONS },
  email:             { label: 'Email', type: 'text', placeholder: 'contoh@email.com' },
  notel:             { label: 'Nomor Handphone', type: 'text', placeholder: '08xxxxxxxxxx' },
}

export function ProfileGapsModal({ gaps, onSubmit, onSkip, isSubmitting }: ProfileGapsModalProps) {
  const [form, setForm] = useState<Record<string, string | number>>({})

  const update = (key: string, val: string | number) => {
    setForm(prev => {
      const next = { ...prev, [key]: val }
      // Reset jenis_disabilitas if disabilitas changed to "Tidak"
      if (key === 'disabilitas' && val === 2) {
        next.jenis_disabilitas = 0
      }
      return next
    })
  }

  // Build visible gaps — show jenis_disabilitas only if disabilitas = 1
  const visibleGaps = gaps.filter(g => {
    if (g === 'jenis_disabilitas') {
      return (form.disabilitas ?? 0) === 1
    }
    return true
  })

  const isValid = visibleGaps.every(g => {
    const val = form[g]
    if (val === undefined || val === '' || val === 0) return false
    return true
  })

  const handleSubmit = () => {
    if (!isValid) return
    // Only send fields that are in visibleGaps and have values
    const payload: Record<string, string | number> = {}
    for (const g of visibleGaps) {
      if (form[g] !== undefined && form[g] !== '' && form[g] !== 0) {
        payload[g] = form[g]
      }
    }
    onSubmit(payload)
  }

  const fieldClass = "w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 bg-white/60 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-400 focus:bg-white/80 transition-colors"
  const selectClass = `${fieldClass} appearance-none`

  if (gaps.length === 0) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onSkip} />
      <div
        className="relative w-full max-w-md flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white/95 backdrop-blur-xl shadow-2xl"
        style={{ maxHeight: 'calc(100vh / 1.75 - 2rem)', animation: 'modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)' }}
      >
        {/* Header */}
        <div className="shrink-0 px-5 pt-5 pb-3">
          <h2 className="text-base font-bold text-gray-800">Lengkapi Data Anda</h2>
          <p className="text-xs text-gray-400 mt-0.5">
            Beberapa data belum terisi. Bantu kami melengkapi untuk layanan yang lebih baik.
          </p>
        </div>

        {/* Scrollable form */}
        <div className="flex-1 min-h-0 overflow-y-auto px-5 pb-2" style={{ scrollbarWidth: 'none' }}>
          <style>{`div::-webkit-scrollbar { display: none; }`}</style>
          <div className="space-y-3">
            {visibleGaps.map(field => {
              const cfg = FIELD_CONFIG[field]
              if (!cfg) return null

              return (
                <div key={field}>
                  <label className="block text-gray-700 font-semibold mb-0.5 text-xs">{cfg.label} *</label>
                  {cfg.type === 'select' && cfg.options ? (
                    <select
                      className={selectClass}
                      value={form[field] ?? ''}
                      onChange={e => update(field, Number(e.target.value))}
                    >
                      <option value="" disabled className="bg-white">-- Pilih --</option>
                      {cfg.options.map(o => (
                        <option key={o.value} value={o.value} className="bg-white">{o.label}</option>
                      ))}
                    </select>
                  ) : (
                    <input
                      type="text"
                      className={fieldClass}
                      placeholder={cfg.placeholder}
                      value={form[field] ?? ''}
                      onChange={e => update(field, e.target.value)}
                    />
                  )}
                </div>
              )
            })}
          </div>
        </div>

        {/* Footer */}
        <div className="shrink-0 flex gap-3 px-5 pt-3 pb-4">
          <button
            onClick={onSkip}
            className="flex-1 px-4 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-white/60 transition-all active:scale-95 cursor-pointer"
          >
            Lewati
          </button>
          <button
            onClick={handleSubmit}
            disabled={!isValid || isSubmitting}
            className={`flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all active:scale-95
              ${isValid && !isSubmitting
                ? 'bg-orange-500 hover:bg-orange-400 text-white shadow-lg cursor-pointer'
                : 'bg-gray-200 text-gray-400 cursor-not-allowed'
              }`}
          >
            {isSubmitting ? 'Menyimpan...' : 'Simpan'}
          </button>
        </div>
      </div>

      <style>{`
        @keyframes modalSlideUp {
          from { opacity: 0; transform: translateY(24px) scale(0.96); }
          to { opacity: 1; transform: translateY(0) scale(1); }
        }
      `}</style>
    </div>
  )
}

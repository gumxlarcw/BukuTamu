import { useEffect } from 'react'
import type { GuestFormData } from '@/types/guest'
import {
  PENDIDIKAN_OPTIONS,
  PEKERJAAN_OPTIONS,
  KATEGORI_INSTANSI_OPTIONS,
  PEMANFAATAN_OPTIONS,
} from '@/types/guest'

const STORAGE_KEY = 'kiosk_visitor_form'

interface VisitorFormProps {
  value: GuestFormData
  onChange: (data: GuestFormData) => void
}

function getNowIso(): string {
  const now = new Date()
  // format for datetime-local input: YYYY-MM-DDTHH:mm
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`
}

export function VisitorForm({ value, onChange }: VisitorFormProps) {
  // Restore from localStorage on mount
  useEffect(() => {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
      try {
        const parsed = JSON.parse(saved) as GuestFormData
        onChange({ ...parsed, tgldatang: getNowIso() })
        return
      } catch {
        // ignore
      }
    }
    onChange({ ...value, tgldatang: getNowIso() })
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  // Auto-save to localStorage on change
  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(value))
  }, [value])

  const update = <K extends keyof GuestFormData>(key: K, val: GuestFormData[K]) => {
    onChange({ ...value, [key]: val })
  }

  const fieldClass = "w-full px-4 py-4 text-lg rounded-xl border-2 border-white/30 bg-white/10 text-white placeholder-white/50 focus:outline-none focus:border-teal-400 focus:bg-white/15 transition-colors"
  const labelClass = "block text-white font-semibold mb-2 text-base"
  const selectClass = `${fieldClass} appearance-none`

  return (
    <div className="space-y-5 w-full">
      {/* Nama */}
      <div>
        <label className={labelClass}>Nama Lengkap *</label>
        <input
          type="text"
          className={fieldClass}
          placeholder="Masukkan nama lengkap"
          value={value.nama}
          onChange={e => update('nama', e.target.value)}
        />
      </div>

      {/* Email */}
      <div>
        <label className={labelClass}>Email *</label>
        <input
          type="email"
          className={fieldClass}
          placeholder="contoh@email.com"
          value={value.email}
          onChange={e => update('email', e.target.value)}
        />
      </div>

      {/* No Telepon */}
      <div>
        <label className={labelClass}>Nomor Telepon *</label>
        <input
          type="tel"
          className={fieldClass}
          placeholder="08xxxxxxxxxx"
          value={value.notel}
          onChange={e => update('notel', e.target.value)}
        />
      </div>

      {/* Jenis Kelamin */}
      <div>
        <label className={labelClass}>Jenis Kelamin *</label>
        <div className="flex gap-4">
          {(['Laki-laki', 'Perempuan'] as const).map(jk => (
            <button
              key={jk}
              type="button"
              onClick={() => update('jeniskelamin', jk)}
              className={`flex-1 py-4 rounded-xl border-2 text-lg font-semibold transition-all
                ${value.jeniskelamin === jk
                  ? 'border-teal-400 bg-teal-500/70 text-white'
                  : 'border-white/30 bg-white/10 text-white/80 hover:bg-white/20'
                }`}
            >
              {jk}
            </button>
          ))}
        </div>
      </div>

      {/* Pendidikan */}
      <div>
        <label className={labelClass}>Pendidikan Terakhir *</label>
        <select
          className={selectClass}
          value={value.pendidikan || ''}
          onChange={e => update('pendidikan', Number(e.target.value))}
        >
          <option value="" disabled className="bg-gray-800">-- Pilih Pendidikan --</option>
          {PENDIDIKAN_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value} className="bg-gray-800">{opt.label}</option>
          ))}
        </select>
      </div>

      {/* Pekerjaan */}
      <div>
        <label className={labelClass}>Pekerjaan *</label>
        <select
          className={selectClass}
          value={value.pekerjaan || ''}
          onChange={e => update('pekerjaan', Number(e.target.value))}
        >
          <option value="" disabled className="bg-gray-800">-- Pilih Pekerjaan --</option>
          {PEKERJAAN_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value} className="bg-gray-800">{opt.label}</option>
          ))}
        </select>
      </div>

      {/* Kategori Instansi */}
      <div>
        <label className={labelClass}>Kategori Instansi *</label>
        <select
          className={selectClass}
          value={value.kategori_instansi || ''}
          onChange={e => update('kategori_instansi', Number(e.target.value))}
        >
          <option value="" disabled className="bg-gray-800">-- Pilih Kategori Instansi --</option>
          {KATEGORI_INSTANSI_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value} className="bg-gray-800">{opt.label}</option>
          ))}
        </select>
      </div>

      {/* Nama Instansi */}
      <div>
        <label className={labelClass}>Nama Instansi *</label>
        <input
          type="text"
          className={fieldClass}
          placeholder="Nama perusahaan/instansi"
          value={value.nama_instansi}
          onChange={e => update('nama_instansi', e.target.value)}
        />
      </div>

      {/* Pemanfaatan */}
      <div>
        <label className={labelClass}>Tujuan Pemanfaatan Data *</label>
        <select
          className={selectClass}
          value={value.pemanfaatan || ''}
          onChange={e => update('pemanfaatan', Number(e.target.value))}
        >
          <option value="" disabled className="bg-gray-800">-- Pilih Tujuan --</option>
          {PEMANFAATAN_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value} className="bg-gray-800">{opt.label}</option>
          ))}
        </select>
      </div>

      {/* Pengaduan */}
      <div>
        <label className={labelClass}>Apakah Ada Pengaduan? *</label>
        <div className="flex gap-4">
          {(['Ya', 'Tidak'] as const).map(val => (
            <button
              key={val}
              type="button"
              onClick={() => update('pengaduan', val)}
              className={`flex-1 py-4 rounded-xl border-2 text-lg font-semibold transition-all
                ${value.pengaduan === val
                  ? 'border-teal-400 bg-teal-500/70 text-white'
                  : 'border-white/30 bg-white/10 text-white/80 hover:bg-white/20'
                }`}
            >
              {val}
            </button>
          ))}
        </div>
      </div>
    </div>
  )
}

export { STORAGE_KEY }

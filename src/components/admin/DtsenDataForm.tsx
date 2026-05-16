import {
  JENIS_KONSULTASI_DTSEN_OPTIONS,
  HASIL_DTSEN_OPTIONS,
  type DtsenDataRow,
} from '@/types/visit'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface DtsenDataFormProps {
  value: Partial<DtsenDataRow>
  onChange: (next: Partial<DtsenDataRow>) => void
}

export function DtsenDataForm({ value, onChange }: DtsenDataFormProps) {
  const update = <K extends keyof DtsenDataRow>(key: K, val: DtsenDataRow[K]) =>
    onChange({ ...value, [key]: val })

  return (
    <div className="space-y-4">
      <div className="space-y-1.5">
        <Label>
          Jenis Konsultasi DTSEN <span className="text-red-500">*</span>
        </Label>
        <select
          value={value.jenis_konsultasi_dtsen ?? ''}
          onChange={e => update('jenis_konsultasi_dtsen', Number(e.target.value))}
          className="w-full border rounded-md px-3 py-2 text-sm bg-background"
        >
          <option value="">-- Pilih --</option>
          {JENIS_KONSULTASI_DTSEN_OPTIONS.map(o => (
            <option key={o.value} value={o.value}>{o.label}</option>
          ))}
        </select>
      </div>

      <div className="space-y-1.5">
        <Label>
          Hasil Konsultasi <span className="text-red-500">*</span>
        </Label>
        <select
          value={value.hasil ?? ''}
          onChange={e => update('hasil', Number(e.target.value))}
          className="w-full border rounded-md px-3 py-2 text-sm bg-background"
        >
          <option value="">-- Pilih --</option>
          {HASIL_DTSEN_OPTIONS.map(o => (
            <option key={o.value} value={o.value}>{o.label}</option>
          ))}
        </select>
      </div>

      <div className="space-y-1.5">
        <Label htmlFor="nik_dirujuk">
          NIK yang Dirujuk{' '}
          <span className="text-muted-foreground text-xs font-normal">
            (opsional, 16 digit kalau konsultasi soal data perorangan)
          </span>
        </Label>
        <Input
          id="nik_dirujuk"
          inputMode="numeric"
          maxLength={16}
          placeholder="Mis. 8201234567890001"
          value={value.nik_dirujuk ?? ''}
          onChange={e => update('nik_dirujuk', e.target.value.replace(/\D/g, '').slice(0, 16) || null)}
        />
      </div>

      <div className="space-y-1.5">
        <Label htmlFor="catatan">
          Catatan <span className="text-red-500">*</span>
        </Label>
        <textarea
          id="catatan"
          rows={5}
          className="w-full border rounded-md px-3 py-2 text-sm bg-background resize-none focus:outline-none focus:ring-2 focus:ring-ring"
          placeholder="Ringkasan permasalahan, tindakan yang dilakukan, dan keputusan/rekomendasi..."
          value={value.catatan ?? ''}
          onChange={e => update('catatan', e.target.value || null)}
        />
      </div>
    </div>
  )
}

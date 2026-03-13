import type { Guest } from '@/types/guest'
import { SERVICE_OPTIONS } from '@/types/visit'
import { Label } from '@/components/ui/label'

interface ManualEntryFormProps {
  guests: Guest[]
  selectedGuestId: number | null
  selectedService: string
  onGuestChange: (id: number | null) => void
  onServiceChange: (service: string) => void
}

export function ManualEntryForm({
  guests,
  selectedGuestId,
  selectedService,
  onGuestChange,
  onServiceChange,
}: ManualEntryFormProps) {
  return (
    <div className="space-y-4">
      <div className="space-y-1.5">
        <Label htmlFor="guest-select">Pilih Pengunjung</Label>
        <select
          id="guest-select"
          value={selectedGuestId ?? ''}
          onChange={e => onGuestChange(e.target.value ? Number(e.target.value) : null)}
          className="w-full h-10 border rounded px-3 text-sm bg-background"
        >
          <option value="">-- Pilih Pengunjung --</option>
          {guests.map(g => (
            <option key={g.id_user} value={g.id_user}>
              {g.nama} — {g.nama_instansi}
            </option>
          ))}
        </select>
      </div>

      <div className="space-y-1.5">
        <Label htmlFor="service-select">Jenis Layanan</Label>
        <select
          id="service-select"
          value={selectedService}
          onChange={e => onServiceChange(e.target.value)}
          className="w-full h-10 border rounded px-3 text-sm bg-background"
        >
          <option value="">-- Pilih Layanan --</option>
          {SERVICE_OPTIONS.map(s => (
            <option key={s} value={s}>{s}</option>
          ))}
        </select>
      </div>
    </div>
  )
}

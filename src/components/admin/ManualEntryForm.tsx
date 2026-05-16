import { useState, useMemo, useRef, useEffect } from 'react'
import type { Guest } from '@/types/guest'
import { SARANA_OPTIONS } from '@/types/guest'
import { SERVICE_OPTIONS } from '@/types/visit'
import { Label } from '@/components/ui/label'
import { CheckCircle, Search, X } from 'lucide-react'

interface ManualEntryFormProps {
  guests: Guest[]
  selectedGuestId: number | null
  selectedServices: string[]
  layananLainnya: string
  selectedSarana: number[]
  saranaLainnya: string
  onGuestChange: (id: number | null) => void
  onServicesChange: (services: string[]) => void
  onLayananLainnyaChange: (val: string) => void
  onSaranaChange: (sarana: number[]) => void
  onSaranaLainnyaChange: (val: string) => void
}

function GuestSearchSelect({ guests, selectedId, onChange }: { guests: Guest[]; selectedId: number | null; onChange: (id: number | null) => void }) {
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const inputRef = useRef<HTMLInputElement>(null)
  const containerRef = useRef<HTMLDivElement>(null)

  const selected = guests.find(g => g.id_user === selectedId)

  const filtered = useMemo(() => {
    if (!query.trim()) return guests
    const q = query.toLowerCase()
    return guests.filter(g =>
      g.nama.toLowerCase().includes(q) ||
      (g.nama_instansi && g.nama_instansi.toLowerCase().includes(q)) ||
      (g.email && g.email.toLowerCase().includes(q))
    )
  }, [guests, query])

  // Close on click outside
  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) setOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const handleSelect = (g: Guest) => {
    onChange(g.id_user)
    setQuery('')
    setOpen(false)
  }

  const handleClear = () => {
    onChange(null)
    setQuery('')
  }

  return (
    <div ref={containerRef} className="relative">
      {/* Display selected or search input */}
      {selected && !open ? (
        <div className="flex items-center gap-2 w-full h-10 border rounded px-3 text-sm bg-background">
          <span className="flex-1 truncate font-medium">{selected.nama} <span className="text-muted-foreground font-normal">— {selected.nama_instansi}</span></span>
          <button type="button" onClick={handleClear} className="shrink-0 text-muted-foreground hover:text-foreground">
            <X className="w-4 h-4" />
          </button>
        </div>
      ) : (
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={e => { setQuery(e.target.value); setOpen(true) }}
            onFocus={() => setOpen(true)}
            placeholder="Cari nama, instansi, atau email..."
            className="w-full h-10 border rounded pl-9 pr-3 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-orange-400/50 focus:border-orange-400"
          />
        </div>
      )}

      {/* Dropdown */}
      {open && (
        <div className="absolute z-50 top-full left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border bg-background shadow-lg">
          {filtered.length === 0 ? (
            <div className="px-4 py-3 text-sm text-muted-foreground text-center">Tidak ditemukan</div>
          ) : (
            filtered.map(g => (
              <button
                key={g.id_user}
                type="button"
                onClick={() => handleSelect(g)}
                className={`w-full text-left px-4 py-2.5 text-sm hover:bg-orange-50 transition-colors border-b last:border-b-0 ${selectedId === g.id_user ? 'bg-orange-50 font-medium' : ''}`}
              >
                <span className="font-medium">{g.nama}</span>
                {g.nama_instansi && <span className="text-muted-foreground"> — {g.nama_instansi}</span>}
                {g.email && <span className="text-muted-foreground text-xs ml-2">({g.email})</span>}
              </button>
            ))
          )}
        </div>
      )}
    </div>
  )
}

export function ManualEntryForm({
  guests,
  selectedGuestId,
  selectedServices,
  layananLainnya,
  selectedSarana,
  saranaLainnya,
  onGuestChange,
  onServicesChange,
  onLayananLainnyaChange,
  onSaranaChange,
  onSaranaLainnyaChange,
}: ManualEntryFormProps) {
  const toggleService = (s: string) => {
    const next = selectedServices.includes(s) ? selectedServices.filter(x => x !== s) : [...selectedServices, s]
    onServicesChange(next)
    if (s === 'Lainnya' && selectedServices.includes('Lainnya')) onLayananLainnyaChange('')
  }

  const toggleSarana = (val: number) => {
    const next = selectedSarana.includes(val) ? selectedSarana.filter(v => v !== val) : [...selectedSarana, val]
    onSaranaChange(next)
    if (val === 32 && selectedSarana.includes(32)) onSaranaLainnyaChange('')
  }

  const btnClass = (active: boolean) =>
    `flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium cursor-pointer transition-all ${
      active ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-gray-200 bg-background text-gray-700 hover:bg-gray-50'
    }`

  return (
    <div className="space-y-5">
      {/* Guest search select */}
      <div className="space-y-1.5">
        <Label>Pilih Pengunjung *</Label>
        <GuestSearchSelect guests={guests} selectedId={selectedGuestId} onChange={onGuestChange} />
      </div>

      {/* Layanan — multi-select */}
      <div className="space-y-1.5">
        <Label>Jenis Layanan * <span className="text-xs font-normal text-muted-foreground">(boleh lebih dari satu)</span></Label>
        <div className="flex flex-wrap gap-2">
          {SERVICE_OPTIONS.map(s => (
            <button key={s} type="button" onClick={() => toggleService(s)} className={btnClass(selectedServices.includes(s))}>
              {selectedServices.includes(s) && <CheckCircle className="w-3.5 h-3.5" />}
              {s}
            </button>
          ))}
        </div>
        {selectedServices.includes('Lainnya') && (
          <input type="text" className="w-full mt-2 border rounded px-3 py-2 text-sm bg-background" placeholder="Sebutkan layanan lainnya" value={layananLainnya} onChange={e => onLayananLainnyaChange(e.target.value)} />
        )}
      </div>

      {/* Sarana — multi-select */}
      <div className="space-y-1.5">
        <Label>Sarana yang Digunakan * <span className="text-xs font-normal text-muted-foreground">(boleh lebih dari satu)</span></Label>
        <div className="flex flex-wrap gap-2">
          {SARANA_OPTIONS.map(o => (
            <button key={o.value} type="button" onClick={() => toggleSarana(o.value)} className={btnClass(selectedSarana.includes(o.value))}>
              {selectedSarana.includes(o.value) && <CheckCircle className="w-3.5 h-3.5" />}
              {o.label}
            </button>
          ))}
        </div>
        {selectedSarana.includes(32) && (
          <input type="text" className="w-full mt-2 border rounded px-3 py-2 text-sm bg-background" placeholder="Sebutkan sarana lainnya" value={saranaLainnya} onChange={e => onSaranaLainnyaChange(e.target.value)} />
        )}
      </div>
    </div>
  )
}

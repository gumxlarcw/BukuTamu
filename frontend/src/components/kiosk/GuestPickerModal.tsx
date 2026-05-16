import { useState, useMemo, useRef, useEffect } from 'react'
import { useQuery } from '@tanstack/react-query'
import { kioskApi } from '@/api/kiosk'
import { Search, X, User, Building2, Loader2 } from 'lucide-react'

interface GuestPickerModalProps {
  open: boolean
  onClose: () => void
  onSelect: (guest: { id: number; nama: string }) => void
}

export function GuestPickerModal({ open, onClose, onSelect }: GuestPickerModalProps) {
  const [search, setSearch] = useState('')
  const inputRef = useRef<HTMLInputElement>(null)
  const listRef = useRef<HTMLDivElement>(null)

  const { data: guests, isLoading } = useQuery({
    queryKey: ['kiosk-guest-list'],
    queryFn: () => kioskApi.getGuestList().then(r => r.data.data),
    enabled: open,
    staleTime: 30_000,
  })

  // Auto-focus search on open
  useEffect(() => {
    if (open) {
      setSearch('')
      setTimeout(() => inputRef.current?.focus(), 100)
    }
  }, [open])

  // Unique guests sorted alphabetically
  const sortedGuests = useMemo(() => {
    if (!guests) return []
    const seen = new Set<number>()
    return guests
      .filter(g => {
        if (seen.has(g.id_user)) return false
        seen.add(g.id_user)
        return true
      })
      .sort((a, b) => a.nama.localeCompare(b.nama, 'id'))
  }, [guests])

  // Filter by search
  const filtered = useMemo(() => {
    if (!search.trim()) return sortedGuests
    const q = search.toLowerCase()
    return sortedGuests.filter(
      g =>
        g.nama.toLowerCase().includes(q) ||
        (g.nama_instansi && g.nama_instansi.toLowerCase().includes(q)),
    )
  }, [sortedGuests, search])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/70 backdrop-blur-sm"
        onClick={onClose}
      />

      {/* Modal — max height fits within zoomed viewport (100vh/1.75 - padding) */}
      <div
        className="relative w-full max-w-md flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white/95 backdrop-blur-xl shadow-2xl"
        style={{
          maxHeight: 'calc(100vh / 1.75 - 2rem)',
          animation: 'modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        }}
      >
        {/* Header */}
        <div className="flex-shrink-0 px-4 pt-4 pb-3">
          <div className="flex items-center justify-between mb-3">
            <div>
              <h2 className="text-base font-bold text-gray-800">Pilih Tamu</h2>
              <p className="text-xs text-gray-400 mt-0.5">
                {sortedGuests.length > 0
                  ? `${sortedGuests.length} tamu terdaftar`
                  : 'Memuat...'}
              </p>
            </div>
            <button
              onClick={onClose}
              className="w-8 h-8 rounded-full flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 transition-all active:scale-90"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          {/* Search */}
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref={inputRef}
              type="text"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder="Cari nama atau instansi..."
              className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 placeholder:text-gray-400 focus:outline-none focus:border-orange-400 focus:bg-white transition-all text-sm"
            />
            {search && (
              <button
                onClick={() => setSearch('')}
                className="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 transition-all"
              >
                <X className="w-3 h-3" />
              </button>
            )}
          </div>
        </div>

        {/* Divider */}
        <div className="flex-shrink-0 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent" />

        {/* Guest list */}
        <div ref={listRef} className="flex-1 overflow-y-auto px-3 py-2 min-h-0">
          {isLoading && (
            <div className="flex flex-col items-center justify-center py-8 text-gray-400">
              <Loader2 className="w-6 h-6 animate-spin mb-2" />
              <p className="text-xs">Memuat daftar tamu...</p>
            </div>
          )}

          {!isLoading && filtered.length === 0 && (
            <div className="flex flex-col items-center justify-center py-8 text-gray-400">
              <User className="w-8 h-8 mb-2 opacity-50" />
              <p className="text-xs">
                {search ? 'Tidak ditemukan' : 'Belum ada tamu terdaftar'}
              </p>
            </div>
          )}

          {!isLoading &&
            filtered.map((guest, i) => (
              <button
                key={guest.id_user}
                onClick={() => onSelect({ id: guest.id_user, nama: guest.nama })}
                className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left hover:bg-orange-50 active:bg-orange-100 transition-all group active:scale-[0.98]"
                style={{
                  animation: `fadeInItem 0.2s ease-out ${Math.min(i * 0.02, 0.3)}s both`,
                }}
              >
                {/* Avatar */}
                <div className="flex-shrink-0 w-9 h-9 rounded-full bg-orange-50 border border-orange-200 flex items-center justify-center group-hover:border-orange-400 transition-colors">
                  <span className="text-orange-500 font-bold text-xs">
                    {guest.nama.charAt(0).toUpperCase()}
                  </span>
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <p className="text-gray-800 font-medium text-xs break-words leading-snug group-hover:text-orange-600 transition-colors">
                    {highlightMatch(guest.nama, search)}
                  </p>
                  {guest.nama_instansi && (
                    <p className="text-gray-400 text-[10px] break-words leading-snug flex items-center gap-1 mt-0.5">
                      <Building2 className="w-2.5 h-2.5 flex-shrink-0" />
                      {highlightMatch(guest.nama_instansi, search)}
                    </p>
                  )}
                </div>
              </button>
            ))}
        </div>

        {/* Footer hint */}
        {!isLoading && filtered.length > 0 && (
          <>
            <div className="flex-shrink-0 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent" />
            <div className="flex-shrink-0 px-4 py-2 text-center">
              <p className="text-gray-400 text-[10px]">
                {search && filtered.length !== sortedGuests.length
                  ? `${filtered.length} dari ${sortedGuests.length} tamu`
                  : 'Ketuk nama untuk memilih'}
              </p>
            </div>
          </>
        )}
      </div>

      <style>{`
        @keyframes modalSlideUp {
          from {
            opacity: 0;
            transform: translateY(24px) scale(0.96);
          }
          to {
            opacity: 1;
            transform: translateY(0) scale(1);
          }
        }
        @keyframes fadeInItem {
          from {
            opacity: 0;
            transform: translateX(-8px);
          }
          to {
            opacity: 1;
            transform: translateX(0);
          }
        }
      `}</style>
    </div>
  )
}

function highlightMatch(text: string, query: string) {
  if (!query.trim()) return text
  const idx = text.toLowerCase().indexOf(query.toLowerCase())
  if (idx === -1) return text
  return (
    <>
      {text.slice(0, idx)}
      <span className="text-orange-300 font-semibold">
        {text.slice(idx, idx + query.length)}
      </span>
      {text.slice(idx + query.length)}
    </>
  )
}

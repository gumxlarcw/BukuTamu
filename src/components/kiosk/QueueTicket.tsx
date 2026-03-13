import { Printer, Clock, User, LayoutGrid } from 'lucide-react'
import type { TicketData } from '@/api/kiosk'

interface QueueTicketProps {
  ticket: TicketData
  onPrint?: () => void
  isPrinting?: boolean
}

function formatDateTime(iso: string): string {
  try {
    const d = new Date(iso)
    return d.toLocaleString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return iso
  }
}

export function QueueTicket({ ticket, onPrint, isPrinting }: QueueTicketProps) {
  return (
    <div className="bg-white/95 text-gray-800 rounded-3xl shadow-2xl p-8 max-w-md w-full text-center">
      {/* Header */}
      <div className="mb-6">
        <p className="text-teal-600 font-semibold text-sm uppercase tracking-widest mb-1">
          Tiket Antrian
        </p>
        <h2 className="text-2xl font-bold text-gray-900">Pelayanan Statistik Terpadu</h2>
      </div>

      {/* Queue number */}
      <div className="bg-teal-50 border-2 border-teal-200 rounded-2xl py-6 px-4 mb-6">
        <p className="text-teal-600 text-sm font-semibold mb-2">Nomor Antrian</p>
        {ticket.no_antrian ? (
          <p className="text-7xl font-black text-teal-600 leading-none">{ticket.no_antrian}</p>
        ) : (
          <p className="text-2xl font-bold text-gray-500 italic">Langsung Dilayani</p>
        )}
      </div>

      {/* Details */}
      <div className="space-y-4 text-left">
        <div className="flex items-start gap-3">
          <User className="w-5 h-5 text-teal-500 mt-0.5 shrink-0" />
          <div>
            <p className="text-xs text-gray-500 font-medium uppercase">Nama</p>
            <p className="font-semibold text-gray-800">{ticket.nama}</p>
          </div>
        </div>

        <div className="flex items-start gap-3">
          <LayoutGrid className="w-5 h-5 text-teal-500 mt-0.5 shrink-0" />
          <div>
            <p className="text-xs text-gray-500 font-medium uppercase">Layanan</p>
            <p className="font-semibold text-gray-800">{ticket.jenis_layanan}</p>
          </div>
        </div>

        <div className="flex items-start gap-3">
          <Clock className="w-5 h-5 text-teal-500 mt-0.5 shrink-0" />
          <div>
            <p className="text-xs text-gray-500 font-medium uppercase">Waktu</p>
            <p className="font-semibold text-gray-800 text-sm">{formatDateTime(ticket.tgldatang)}</p>
          </div>
        </div>
      </div>

      {/* Print button */}
      {onPrint && (
        <button
          onClick={onPrint}
          disabled={isPrinting}
          className="mt-6 flex items-center justify-center gap-2 w-full py-3 rounded-xl border-2 border-teal-500 text-teal-600 font-semibold hover:bg-teal-50 active:bg-teal-100 transition-all disabled:opacity-50"
        >
          <Printer className="w-5 h-5" />
          {isPrinting ? 'Mencetak...' : 'Cetak Ulang'}
        </button>
      )}
    </div>
  )
}

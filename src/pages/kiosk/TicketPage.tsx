import { useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { kioskApi } from '@/api/kiosk'
import { QueueTicket } from '@/components/kiosk/QueueTicket'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { usePrint } from '@/hooks/usePrint'
import { Home } from 'lucide-react'

export default function TicketPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()
  const { printTicket, isPrinting } = usePrint()

  const { data: ticket, isLoading, isError } = useQuery({
    queryKey: ['ticket', id],
    queryFn: () => kioskApi.getTicket(Number(id)).then(r => r.data.data),
    enabled: !!id,
  })

  // Auto-print on mount when ticket data is available
  useEffect(() => {
    if (ticket) {
      printTicket({
        nomor_antrian: ticket.nomor_antrian,
        nama: ticket.nama,
        jenis_layanan: ticket.jenis_layanan,
        date_visit: ticket.date_visit,
      })
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ticket?.id_kunjungan])

  const handleReprint = () => {
    if (ticket) {
      printTicket({
        nomor_antrian: ticket.nomor_antrian,
        nama: ticket.nama,
        jenis_layanan: ticket.jenis_layanan,
        date_visit: ticket.date_visit,
      })
    }
  }

  return (
    <div className="flex flex-col items-center text-white px-4 max-w-lg w-full mx-auto">
      {isLoading && (
        <div className="flex flex-col items-center gap-4">
          <LoadingSpinner />
          <p className="text-white/80">Memuat tiket...</p>
        </div>
      )}

      {isError && (
        <div className="text-center">
          <p className="text-red-300 text-xl mb-6">Gagal memuat tiket</p>
          <button
            onClick={() => navigate('/kiosk')}
            className="px-8 py-4 bg-teal-500 rounded-xl text-white font-bold"
          >
            Mulai Ulang
          </button>
        </div>
      )}

      {ticket && (
        <>
          <h1 className="text-3xl font-bold mb-6 drop-shadow-lg">Pendaftaran Berhasil!</h1>
          <QueueTicket ticket={ticket} onPrint={handleReprint} isPrinting={isPrinting} />

          <button
            onClick={() => navigate('/kiosk')}
            className="mt-8 flex items-center gap-3 px-10 py-5 rounded-2xl bg-teal-500 hover:bg-teal-400 active:bg-teal-600 text-white text-xl font-bold shadow-xl transition-all active:scale-95"
          >
            <Home className="w-6 h-6" />
            Selesai
          </button>

          <p className="mt-4 text-white/60 text-sm text-center">
            Silakan tunggu panggilan nomor antrian Anda
          </p>
        </>
      )}
    </div>
  )
}

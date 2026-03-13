import { useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'
import { consultationsApi } from '@/api/consultations'
import { QueueList } from '@/components/admin/QueueList'
import { QueueCallButton } from '@/components/admin/QueueCallButton'
import type { Visit } from '@/types/visit'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ExternalLink, Volume2, ClipboardList, CheckCircle } from 'lucide-react'

export default function ConsultationQueuePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const { data: visits, isLoading } = useQuery({
    queryKey: ['consultations-queue'],
    queryFn: () => consultationsApi.list().then(r => r.data.data),
    refetchInterval: 30000,
  })

  const statusMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: string }) =>
      consultationsApi.updateStatus(id, status),
    onSuccess: () => {
      toast.success('Status berhasil diperbarui')
      queryClient.invalidateQueries({ queryKey: ['consultations-queue'] })
    },
    onError: () => toast.error('Gagal memperbarui status'),
  })

  const handleTestSound = async () => {
    try {
      await consultationsApi.testSound(0)
      toast.success('Tes suara dikirim ke TV')
    } catch {
      toast.error('Gagal mengirim tes suara')
    }
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <h1 className="text-2xl font-bold">Antrian Konsultasi</h1>
          <p className="text-muted-foreground text-sm">Manajemen antrian layanan hari ini</p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" onClick={handleTestSound}>
            <Volume2 className="w-4 h-4 mr-2" />
            Tes Suara ke TV
          </Button>
          <a
            href="https://dashboard-pst.bpsmalut.com"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Button variant="outline">
              <ExternalLink className="w-4 h-4 mr-2" />
              Dashboard PST
            </Button>
          </a>
        </div>
      </div>

      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 5 }).map((_, i) => (
            <Skeleton key={i} className="h-20 rounded-xl" />
          ))}
        </div>
      ) : (
        <QueueList
          visits={visits ?? []}
          renderActions={(visit: Visit) => (
            <>
              <QueueCallButton
                visitId={visit.id_kunjungan}
                nomor_antrian={visit.nomor_antrian}
              />
              <Button
                size="sm"
                variant="outline"
                onClick={() => navigate(`/admin/consultations/${visit.id_kunjungan}/form`)}
              >
                <ClipboardList className="w-3.5 h-3.5 mr-1" />
                Mulai
              </Button>
              {visit.status !== 'selesai' && visit.status !== 'menunggu_evaluasi' && (
                <Button
                  size="sm"
                  variant="outline"
                  className="text-green-700 hover:text-green-800 hover:bg-green-50"
                  onClick={() =>
                    statusMutation.mutate({
                      id: visit.id_kunjungan,
                      status: 'menunggu_evaluasi',
                    })
                  }
                  disabled={statusMutation.isPending}
                >
                  <CheckCircle className="w-3.5 h-3.5 mr-1" />
                  Selesai
                </Button>
              )}
            </>
          )}
        />
      )}
    </div>
  )
}

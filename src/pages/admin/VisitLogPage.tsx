import { useState, useEffect } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'
import { visitsApi } from '@/api/visits'
import { consultationsApi } from '@/api/consultations'
import type { Visit, VisitStatus } from '@/types/visit'
import { SERVICE_OPTIONS } from '@/types/visit'
import { VisitFilters } from '@/components/admin/VisitFilters'
import type { VisitFilterState } from '@/components/admin/VisitFilters'
import { StatusBadge } from '@/components/shared/StatusBadge'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ChevronLeft, ChevronRight, ChevronDown, ChevronUp } from 'lucide-react'

const STATUS_FLOW: VisitStatus[] = ['antri', 'proses', 'menunggu_evaluasi', 'selesai']

function formatDate(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

function VisitDetailPanel({ visit }: { visit: Visit }) {
  const queryClient = useQueryClient()
  const [editLayanan, setEditLayanan] = useState(visit.jenis_layanan)
  const [editRingkasan, setEditRingkasan] = useState('')

  const { data: consultationData } = useQuery({
    queryKey: ['consultation-data', visit.id_kunjungan],
    queryFn: () => consultationsApi.getData(visit.id_kunjungan).then(r => r.data.data),
  })

  const statusMutation = useMutation({
    mutationFn: (status: VisitStatus) => visitsApi.updateStatus(visit.id_kunjungan, status),
    onSuccess: () => {
      toast.success('Status diperbarui')
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: () => toast.error('Gagal memperbarui status'),
  })

  const serviceMutation = useMutation({
    mutationFn: () => visitsApi.updateService(visit.id_kunjungan, editLayanan),
    onSuccess: () => {
      toast.success('Layanan diperbarui')
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: () => toast.error('Gagal memperbarui layanan'),
  })

  const summaryMutation = useMutation({
    mutationFn: () => visitsApi.updateSummary(visit.id_kunjungan, editRingkasan),
    onSuccess: () => {
      toast.success('Ringkasan disimpan')
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: () => toast.error('Gagal menyimpan ringkasan'),
  })

  const nextStatus = STATUS_FLOW[STATUS_FLOW.indexOf(visit.status) + 1]

  return (
    <div className="px-4 pb-4 space-y-4 bg-muted/30 rounded-b-lg border-t">
      {/* Visitor info */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4 text-sm">
        <div>
          <p className="text-muted-foreground">Nama</p>
          <p className="font-medium">{visit.nama}</p>
        </div>
        <div>
          <p className="text-muted-foreground">Instansi</p>
          <p className="font-medium">{visit.nama_instansi}</p>
        </div>
        <div>
          <p className="text-muted-foreground">No. Antrian</p>
          <p className="font-medium">{visit.nomor_antrian ?? '-'}</p>
        </div>
        {visit.rating_pengunjung !== null && (
          <div>
            <p className="text-muted-foreground">Rating</p>
            <p className="font-medium">{visit.rating_pengunjung}/10</p>
          </div>
        )}
        {visit.durasi_detik !== null && (
          <div>
            <p className="text-muted-foreground">Durasi</p>
            <p className="font-medium">{Math.round(visit.durasi_detik / 60)} menit</p>
          </div>
        )}
      </div>

      {/* Consultation data */}
      {consultationData && consultationData.length > 0 && (
        <div className="text-sm">
          <p className="font-semibold mb-2">Data Konsultasi ({consultationData.length} item)</p>
          <div className="space-y-1 text-muted-foreground">
            {consultationData.map((row, i) => (
              <p key={i}>{i + 1}. {row.rincian_data} — {row.wilayah_data}</p>
            ))}
          </div>
        </div>
      )}

      {/* Status actions */}
      {nextStatus && (
        <div className="flex items-center gap-2">
          <span className="text-sm text-muted-foreground">Ubah status ke:</span>
          <Button
            size="sm"
            variant="outline"
            onClick={() => statusMutation.mutate(nextStatus)}
            disabled={statusMutation.isPending}
          >
            <StatusBadge status={nextStatus} />
          </Button>
        </div>
      )}

      {/* Edit layanan */}
      <div className="flex items-end gap-2">
        <div className="space-y-1 flex-1 max-w-xs">
          <p className="text-sm text-muted-foreground">Edit Layanan</p>
          <select
            value={editLayanan}
            onChange={e => setEditLayanan(e.target.value)}
            className="w-full h-9 border rounded px-3 text-sm bg-background"
          >
            {SERVICE_OPTIONS.map(s => (
              <option key={s} value={s}>{s}</option>
            ))}
          </select>
        </div>
        <Button
          size="sm"
          variant="outline"
          onClick={() => serviceMutation.mutate()}
          disabled={serviceMutation.isPending || editLayanan === visit.jenis_layanan}
        >
          Simpan
        </Button>
      </div>

      {/* Edit ringkasan */}
      <div className="space-y-1">
        <p className="text-sm text-muted-foreground">Ringkasan / Catatan</p>
        <textarea
          rows={3}
          className="w-full border rounded px-3 py-2 text-sm bg-background resize-none"
          placeholder="Catatan ringkasan..."
          value={editRingkasan}
          onChange={e => setEditRingkasan(e.target.value)}
        />
        <Button
          size="sm"
          variant="outline"
          onClick={() => summaryMutation.mutate()}
          disabled={summaryMutation.isPending || !editRingkasan.trim()}
        >
          {summaryMutation.isPending ? 'Menyimpan...' : 'Simpan Ringkasan'}
        </Button>
      </div>
    </div>
  )
}

export default function VisitLogPage() {
  const [filters, setFilters] = useState<VisitFilterState>({
    q: '',
    layanan: '',
    tahun: '',
    bulan: '',
  })
  const [debouncedFilters, setDebouncedFilters] = useState(filters)
  const [page, setPage] = useState(1)
  const [limit] = useState(20)
  const [expandedId, setExpandedId] = useState<number | null>(null)

  useEffect(() => {
    const t = setTimeout(() => {
      setDebouncedFilters(filters)
      setPage(1)
    }, 400)
    return () => clearTimeout(t)
  }, [filters])

  const { data, isLoading } = useQuery({
    queryKey: ['visits', { ...debouncedFilters, page, limit }],
    queryFn: () =>
      visitsApi
        .list({
          q: debouncedFilters.q || undefined,
          layanan: debouncedFilters.layanan || undefined,
          tahun: debouncedFilters.tahun || undefined,
          bulan: debouncedFilters.bulan || undefined,
          page,
          limit,
        })
        .then(r => r.data),
  })

  const visits = data?.data ?? []
  const pagination = data?.pagination

  const toggleExpand = (id: number) => {
    setExpandedId(prev => (prev === id ? null : id))
  }

  return (
    <div className="space-y-5">
      <div>
        <h1 className="text-2xl font-bold">Daftar Kunjungan</h1>
        <p className="text-muted-foreground text-sm">Log semua kunjungan PST</p>
      </div>

      <VisitFilters filters={filters} onChange={setFilters} />

      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 10 }).map((_, i) => (
            <Skeleton key={i} className="h-12 rounded-lg" />
          ))}
        </div>
      ) : visits.length === 0 ? (
        <div className="text-center py-12 text-muted-foreground">
          Tidak ada data kunjungan ditemukan.
        </div>
      ) : (
        <div className="rounded-md border overflow-hidden">
          {/* Header */}
          <div className="grid grid-cols-[40px_1fr_1fr_120px_100px_80px] gap-2 px-4 py-2 bg-muted/50 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
            <span>No</span>
            <span>Nama</span>
            <span>Layanan</span>
            <span>Tanggal</span>
            <span>Status</span>
            <span></span>
          </div>
          {visits.map((visit: Visit, idx: number) => (
            <div key={visit.id_kunjungan} className="border-t">
              {/* Row */}
              <div
                className="grid grid-cols-[40px_1fr_1fr_120px_100px_80px] gap-2 px-4 py-3 items-center cursor-pointer hover:bg-muted/30 transition-colors"
                onClick={() => toggleExpand(visit.id_kunjungan)}
              >
                <span className="text-sm text-muted-foreground">
                  {(page - 1) * limit + idx + 1}
                </span>
                <span className="text-sm font-medium truncate">{visit.nama}</span>
                <span className="text-sm text-muted-foreground truncate">{visit.jenis_layanan}</span>
                <span className="text-sm text-muted-foreground">{formatDate(visit.date_visit)}</span>
                <StatusBadge status={visit.status} />
                <span className="flex justify-end text-muted-foreground">
                  {expandedId === visit.id_kunjungan ? (
                    <ChevronUp className="w-4 h-4" />
                  ) : (
                    <ChevronDown className="w-4 h-4" />
                  )}
                </span>
              </div>

              {/* Expandable detail panel */}
              {expandedId === visit.id_kunjungan && (
                <VisitDetailPanel visit={visit} />
              )}
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      {pagination && (
        <div className="flex items-center justify-between gap-4 flex-wrap">
          <span className="text-sm text-muted-foreground">
            Total: <strong>{pagination.total}</strong> kunjungan
          </span>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              disabled={page <= 1}
              onClick={() => setPage(p => p - 1)}
            >
              <ChevronLeft className="w-4 h-4" />
            </Button>
            <span className="text-sm">
              {page} / {pagination.totalPages}
            </span>
            <Button
              variant="outline"
              size="sm"
              disabled={page >= pagination.totalPages}
              onClick={() => setPage(p => p + 1)}
            >
              <ChevronRight className="w-4 h-4" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}

import { useState, useEffect } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'
import { visitsApi } from '@/api/visits'
import { consultationsApi } from '@/api/consultations'
import type { Visit, VisitStatus } from '@/types/visit'
import { SERVICE_OPTIONS, parseLayanan, parseSarana, saranaLabel } from '@/types/visit'
import { SARANA_OPTIONS } from '@/types/guest'
import { CheckCircle, Lock, Trash2 } from 'lucide-react'
import { VisitFilters } from '@/components/admin/VisitFilters'
import type { VisitFilterState } from '@/components/admin/VisitFilters'
import { StatusBadge } from '@/components/shared/StatusBadge'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ChevronLeft, ChevronRight, ChevronDown, ChevronUp, Download } from 'lucide-react'
import { exportCsv } from '@/lib/export-csv'
import { useAuth } from '@/providers/AuthProvider'
import { canFinalizeLayanan, parseLayananForRole } from '@/lib/role-access'

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
  const { user } = useAuth()
  const role = user?.role
  const canEdit = canFinalizeLayanan(role, parseLayananForRole(visit.jenis_layanan))
  // Hard delete kunjungan = aksi admin-only (mirror Api_base::require_role('admin')).
  // Operator legacy NOT included supaya konsisten dengan backend.
  const canDelete = role === 'admin' || role === 'superadmin'

  const [editServices, setEditServices] = useState<string[]>(() => parseLayanan(visit.jenis_layanan))
  const [editLayananLainnya, setEditLayananLainnya] = useState(visit.layanan_lainnya ?? '')
  const [editSarana, setEditSarana] = useState<number[]>(() => parseSarana(visit.sarana))
  const [editSaranaLainnya, setEditSaranaLainnya] = useState(visit.sarana_lainnya ?? '')
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
    mutationFn: () => visitsApi.updateService(visit.id_kunjungan, {
      jenis_layanan: editServices,
      layanan_lainnya: editLayananLainnya || undefined,
      sarana: editSarana,
      sarana_lainnya: editSaranaLainnya || undefined,
    }),
    onSuccess: () => {
      toast.success('Layanan & sarana diperbarui')
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: () => toast.error('Gagal memperbarui'),
  })

  const summaryMutation = useMutation({
    mutationFn: () => visitsApi.updateSummary(visit.id_kunjungan, editRingkasan),
    onSuccess: () => {
      toast.success('Ringkasan disimpan')
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: () => toast.error('Gagal menyimpan ringkasan'),
  })

  const deleteMutation = useMutation({
    mutationFn: () => visitsApi.delete(visit.id_kunjungan),
    onSuccess: () => {
      const label = visit.nomor_antrian ?? `#${visit.id_kunjungan}`
      toast.success(`Kunjungan ${label} dihapus`)
      // Invalidate — row akan hilang dari list, panel collapse otomatis.
      queryClient.invalidateQueries({ queryKey: ['visits'] })
    },
    onError: (e: unknown) => {
      const msg = e && typeof e === 'object' && 'response' in e
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        ? (e as any).response?.data?.message
        : null
      toast.error(msg || 'Gagal menghapus kunjungan')
    },
  })

  const handleDelete = () => {
    const label = visit.nomor_antrian ?? `#${visit.id_kunjungan}`
    const layananStr = parseLayanan(visit.jenis_layanan).join(', ') || '-'
    const confirmed = window.confirm(
      `Hapus kunjungan ${label}?\n\n` +
      `Tamu    : ${visit.nama}\n` +
      `Layanan : ${layananStr}\n` +
      `Tanggal : ${formatDate(visit.date_visit)}\n\n` +
      `Aksi ini juga menghapus data konsultasi & evaluasi terkait.\n` +
      `TIDAK BISA di-undo.`
    )
    if (confirmed) deleteMutation.mutate()
  }

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
          <p className="text-muted-foreground">Sumber</p>
          <p className="font-medium">
            {visit.created_by === 'kiosk' ? (
              <span className="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Kiosk</span>
            ) : visit.created_by?.startsWith('admin:') ? (
              <span className="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">Admin ({visit.created_by.replace('admin:', '')})</span>
            ) : (
              <span className="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">{visit.created_by ?? '-'}</span>
            )}
          </p>
        </div>
        <div>
          <p className="text-muted-foreground">No. Antrian</p>
          <p className="font-medium">{visit.nomor_antrian ?? '-'}</p>
        </div>
        <div>
          <p className="text-muted-foreground">Sarana</p>
          <div className="flex flex-wrap gap-1 mt-0.5">
            {parseSarana(visit.sarana).map((c, i) => (
              <span key={i} className="inline-block px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">{saranaLabel(c)}</span>
            ))}
            {visit.sarana_lainnya && <span className="text-xs text-muted-foreground">({visit.sarana_lainnya})</span>}
            {!visit.sarana && <span className="text-sm">-</span>}
          </div>
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

      {/* Read-only banner untuk role yang tidak berwenang atas layanan visit ini */}
      {!canEdit && (
        <div className="flex items-center gap-2 px-3 py-2 rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-xs">
          <Lock className="w-3.5 h-3.5 shrink-0" />
          <span>
            Read-only: layanan visit ini di luar kewenangan role Anda. Hanya petugas
            yang berwenang yang bisa mengubah data, status, atau ringkasan.
          </span>
        </div>
      )}

      {/* Status actions */}
      {nextStatus && canEdit && (
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

      {/* Edit layanan + sarana */}
      <div className={`space-y-3 ${canEdit ? '' : 'opacity-50 pointer-events-none select-none'}`}>
        <div className="space-y-1.5">
          <p className="text-sm font-medium">Layanan <span className="text-xs font-normal text-muted-foreground">(boleh lebih dari satu)</span></p>
          <div className="flex flex-wrap gap-1.5">
            {SERVICE_OPTIONS.map(s => {
              const active = editServices.includes(s)
              return (
                <button key={s} type="button"
                  onClick={() => {
                    setEditServices(prev => active ? prev.filter(x => x !== s) : [...prev, s])
                    if (s === 'Lainnya' && active) setEditLayananLainnya('')
                  }}
                  className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs font-medium transition-all ${active ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`}
                >
                  {active && <CheckCircle className="w-3 h-3" />}
                  {s}
                </button>
              )
            })}
          </div>
          {editServices.includes('Lainnya') && (
            <input type="text" className="w-full max-w-xs h-8 border rounded px-3 text-xs bg-background mt-1" placeholder="Sebutkan layanan lainnya" value={editLayananLainnya} onChange={e => setEditLayananLainnya(e.target.value)} />
          )}
        </div>

        <div className="space-y-1.5">
          <p className="text-sm font-medium">Sarana <span className="text-xs font-normal text-muted-foreground">(boleh lebih dari satu)</span></p>
          <div className="flex flex-wrap gap-1.5">
            {SARANA_OPTIONS.map(o => {
              const active = editSarana.includes(o.value)
              return (
                <button key={o.value} type="button"
                  onClick={() => {
                    setEditSarana(prev => active ? prev.filter(v => v !== o.value) : [...prev, o.value])
                    if (o.value === 32 && active) setEditSaranaLainnya('')
                  }}
                  className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs font-medium transition-all ${active ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`}
                >
                  {active && <CheckCircle className="w-3 h-3" />}
                  {o.label}
                </button>
              )
            })}
          </div>
          {editSarana.includes(32) && (
            <input type="text" className="w-full max-w-xs h-8 border rounded px-3 text-xs bg-background mt-1" placeholder="Sebutkan sarana lainnya" value={editSaranaLainnya} onChange={e => setEditSaranaLainnya(e.target.value)} />
          )}
        </div>

        <Button
          size="sm"
          variant="outline"
          onClick={() => serviceMutation.mutate()}
          disabled={serviceMutation.isPending || editServices.length === 0 || editSarana.length === 0}
        >
          {serviceMutation.isPending ? 'Menyimpan...' : 'Simpan Layanan & Sarana'}
        </Button>
      </div>

      {/* Edit ringkasan */}
      <div className={`space-y-1 ${canEdit ? '' : 'opacity-50 pointer-events-none select-none'}`}>
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
          disabled={summaryMutation.isPending || !editRingkasan.trim() || !canEdit}
        >
          {summaryMutation.isPending ? 'Menyimpan...' : 'Simpan Ringkasan'}
        </Button>
      </div>

      {/* Zona Berbahaya — admin/superadmin only. Hard delete kunjungan + cascade. */}
      {canDelete && (
        <div className="border-t border-red-200 pt-3 mt-3 bg-red-50/30 -mx-4 px-4 pb-3 rounded-b-lg">
          <p className="text-xs font-bold text-red-700 mb-2 uppercase tracking-wide">⚠ Zona Berbahaya</p>
          <div className="flex items-start gap-3 flex-wrap">
            <Button
              size="sm"
              variant="outline"
              onClick={handleDelete}
              disabled={deleteMutation.isPending}
              className="text-red-700 border-red-300 hover:bg-red-100 hover:text-red-800 hover:border-red-400"
            >
              <Trash2 className="w-3.5 h-3.5 mr-1.5" />
              {deleteMutation.isPending ? 'Menghapus...' : 'Hapus Kunjungan'}
            </Button>
            <p className="text-xs text-red-700/80 flex-1 min-w-[200px]">
              Menghapus data kunjungan + data konsultasi + evaluasi terkait.
              Audit log tetap tersimpan, tapi data utama <strong>tidak bisa di-undo</strong>.
            </p>
          </div>
        </div>
      )}
    </div>
  )
}

const expandStyle = document.createElement('style')
expandStyle.textContent = `
.animate-in { animation: expandIn 0.2s ease-out; }
@keyframes expandIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
`
if (!document.head.querySelector('[data-visit-log-anim]')) {
  expandStyle.setAttribute('data-visit-log-anim', '')
  document.head.appendChild(expandStyle)
}

export default function VisitLogPage() {
  const [filters, setFilters] = useState<VisitFilterState>({
    q: '',
    layanan: '',
    status: '',
    tahun: '',
    bulan: '',
  })
  const [debouncedFilters, setDebouncedFilters] = useState(filters)
  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)
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
          status: debouncedFilters.status || undefined,
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
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <h1 className="admin-h1">Daftar Kunjungan</h1>
          <p className="admin-subtitle">Log semua kunjungan PST</p>
        </div>
        <Button
          variant="outline"
          onClick={() => {
            visitsApi.list({ ...debouncedFilters, limit: 10000 }).then(r => {
              exportCsv('log-kunjungan', r.data.data.map((v: Visit) => ({
                nama: v.nama, instansi: v.nama_instansi,
                layanan: parseLayanan(v.jenis_layanan).join('; '),
                sarana: parseSarana(v.sarana).map(saranaLabel).join('; '),
                tanggal: v.date_visit, status: v.status,
                nomor_antrian: v.nomor_antrian, rating: v.rating_pengunjung,
              })))
            })
          }}
        >
          <Download className="w-4 h-4 mr-2" />
          Export CSV
        </Button>
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
          <div className="grid grid-cols-[40px_1fr_1.5fr_1fr_120px_90px_40px] gap-2 px-4 py-2 bg-muted/50 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
            <span>No</span>
            <span>Nama</span>
            <span>Layanan</span>
            <span>Sarana</span>
            <span>Tanggal</span>
            <span>Status</span>
            <span></span>
          </div>
          {visits.map((visit: Visit, idx: number) => (
            <div key={visit.id_kunjungan} className="border-t">
              {/* Row */}
              <div
                className="grid grid-cols-[40px_1fr_1.5fr_1fr_120px_90px_40px] gap-2 px-4 py-3 items-center cursor-pointer hover:bg-muted/30 transition-colors"
                onClick={() => toggleExpand(visit.id_kunjungan)}
              >
                <span className="text-sm text-muted-foreground">
                  {(page - 1) * limit + idx + 1}
                </span>
                <span className="text-sm font-medium truncate">{visit.nama}</span>
                <span className="flex flex-wrap gap-1">
                  {parseLayanan(visit.jenis_layanan).map((l, i) => (
                    <span key={i} className="inline-block px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[11px] font-medium">{l}</span>
                  ))}
                </span>
                <span className="flex flex-wrap gap-1">
                  {parseSarana(visit.sarana).map((c, i) => (
                    <span key={i} className="inline-block px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[11px] font-medium">{saranaLabel(c)}</span>
                  ))}
                  {!visit.sarana && <span className="text-xs text-muted-foreground">-</span>}
                </span>
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
                <div className="overflow-hidden animate-in">
                  <VisitDetailPanel visit={visit} />
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      {pagination && (
        <div className="flex items-center justify-between gap-4 flex-wrap">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <span>Tampilkan</span>
            <select value={limit} onChange={e => { setLimit(Number(e.target.value)); setPage(1) }} className="border rounded px-2 py-1 text-sm bg-background">
              {[10, 25, 50, 100].map(n => <option key={n} value={n}>{n}</option>)}
            </select>
            <span>per halaman</span>
            <span className="ml-2">Total: <strong>{pagination.total}</strong></span>
          </div>
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

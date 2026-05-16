import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { queueStatsApi } from '@/api/queueStats'
import { Skeleton } from '@/components/ui/skeleton'
import { Label } from '@/components/ui/label'
import { Timer, Clock, TrendingUp, BarChart3 } from 'lucide-react'
import { parseLayanan } from '@/types/visit'

function fmt(seconds: number | null | undefined): string {
  if (!seconds) return '-'
  const m = Math.floor(seconds / 60)
  return m > 0 ? `${m} menit` : `${Math.round(seconds)} detik`
}

const BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

function Bar({ label, value, max, color = 'bg-orange-500' }: { label: string; value: number; max: number; color?: string }) {
  const pct = max > 0 ? Math.round((value / max) * 100) : 0
  return (
    <div className="flex items-center gap-2 text-xs">
      <span className="w-16 text-right text-muted-foreground shrink-0">{label}</span>
      <div className="flex-1 h-5 bg-muted rounded overflow-hidden">
        <div className={`h-full rounded ${color} flex items-center px-2`} style={{ width: `${Math.max(pct, 2)}%` }}>
          {pct > 15 && <span className="text-white text-[10px] font-bold">{value}</span>}
        </div>
      </div>
      {pct <= 15 && <span className="text-muted-foreground w-8">{value}</span>}
    </div>
  )
}

export default function QueueStatsPage() {
  const currentYear = new Date().getFullYear().toString()
  const [tahun, setTahun] = useState(currentYear)
  const years = Array.from({ length: 5 }, (_, i) => String(new Date().getFullYear() - i))

  const { data, isLoading } = useQuery({
    queryKey: ['queue-stats', tahun],
    queryFn: () => queueStatsApi.get({ tahun }).then(r => r.data.data),
  })

  return (
    <div className="space-y-5">
      <div>
        <h1 className="admin-h1">Analisis Antrian</h1>
        <p className="admin-subtitle">Performa dan pola kunjungan</p>
      </div>

      <div className="flex flex-wrap items-end gap-3">
        <div className="space-y-1">
          <Label htmlFor="qs-tahun">Tahun</Label>
          <select id="qs-tahun" value={tahun} onChange={e => setTahun(e.target.value)} className="h-9 border rounded px-3 text-sm bg-background">
            {years.map(y => <option key={y} value={y}>{y}</option>)}
          </select>
        </div>
      </div>

      {isLoading ? (
        <div className="grid grid-cols-2 xl:grid-cols-4 gap-4">{Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-24 rounded-xl" />)}</div>
      ) : !data ? (
        <div className="text-center py-16 text-muted-foreground">Tidak ada data.</div>
      ) : (
        <>
          {/* Overview cards */}
          <div className="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><Timer className="w-5 h-5 text-blue-600" /></div>
              <div><p className="text-xl font-bold">{fmt(data.avg_wait?.avg_durasi)}</p><p className="text-xs text-muted-foreground">Rata-rata Durasi</p></div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center"><Clock className="w-5 h-5 text-green-600" /></div>
              <div><p className="text-xl font-bold">{fmt(data.avg_wait?.min_durasi)}</p><p className="text-xs text-muted-foreground">Tercepat</p></div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center"><Clock className="w-5 h-5 text-red-600" /></div>
              <div><p className="text-xl font-bold">{fmt(data.avg_wait?.max_durasi)}</p><p className="text-xs text-muted-foreground">Terlama</p></div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center"><TrendingUp className="w-5 h-5 text-orange-600" /></div>
              <div><p className="text-xl font-bold">{data.avg_wait?.total_selesai ?? 0}</p><p className="text-xs text-muted-foreground">Total Selesai</p></div>
            </div>
          </div>

          <div className="grid grid-cols-1 xl:grid-cols-2 gap-5">
            {/* Hourly distribution */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-3 flex items-center gap-2"><BarChart3 className="w-4 h-4" /> Distribusi per Jam</h2>
              <div className="space-y-1.5">
                {(data.hourly ?? []).map((h: { jam: number; jumlah: number }) => (
                  <Bar key={h.jam} label={`${String(h.jam).padStart(2, '0')}:00`} value={h.jumlah} max={Math.max(0, ...(data.hourly ?? []).map((x: { jumlah: number }) => x.jumlah))} />
                ))}
              </div>
            </div>

            {/* Monthly distribution */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-3 flex items-center gap-2"><BarChart3 className="w-4 h-4" /> Distribusi per Bulan</h2>
              <div className="space-y-1.5">
                {(data.monthly ?? []).map((m: { bulan: number; jumlah: number }) => (
                  <Bar key={m.bulan} label={BULAN[m.bulan]} value={m.jumlah} max={Math.max(0, ...(data.monthly ?? []).map((x: { jumlah: number }) => x.jumlah))} color="bg-blue-500" />
                ))}
              </div>
            </div>

            {/* Service distribution */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-3">Distribusi Layanan</h2>
              <div className="space-y-1.5">
                {(data.services ?? []).map((s: { jenis_layanan: string; jumlah: number }) => (
                  <Bar key={s.jenis_layanan} label={parseLayanan(s.jenis_layanan)[0] ?? s.jenis_layanan} value={s.jumlah} max={Math.max(0, ...(data.services ?? []).map((x: { jumlah: number }) => x.jumlah))} color="bg-amber-500" />
                ))}
              </div>
            </div>

            {/* Status distribution */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-3">Distribusi Status</h2>
              <div className="space-y-1.5">
                {(data.statuses ?? []).map((s: { status: string; jumlah: number }) => (
                  <Bar key={s.status} label={s.status} value={s.jumlah} max={Math.max(0, ...(data.statuses ?? []).map((x: { jumlah: number }) => x.jumlah))} color={s.status === 'selesai' ? 'bg-green-500' : 'bg-gray-400'} />
                ))}
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  )
}

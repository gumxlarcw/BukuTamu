import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { evaluationsApi } from '@/api/evaluations'
import type { EvaluationSummaryVisit, EvaluationSummaryIndicator } from '@/api/evaluations'
import { parseLayanan } from '@/types/visit'
import { Skeleton } from '@/components/ui/skeleton'
import { Star, TrendingUp, Users, BarChart3, Download } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { exportCsv } from '@/lib/export-csv'

function formatDate(d: string) {
  try { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }
  catch { return d }
}

function ScoreBar({ label, score, max = 10 }: { label: string; score: number; max?: number }) {
  const pct = Math.round((score / max) * 100)
  const color = pct >= 80 ? 'bg-green-500' : pct >= 60 ? 'bg-amber-500' : 'bg-red-500'
  return (
    <div className="flex items-center gap-3 text-sm">
      <span className="w-8 text-right font-bold text-foreground">{score.toFixed(1)}</span>
      <div className="flex-1 h-2.5 bg-muted rounded-full overflow-hidden">
        <div className={`h-full rounded-full transition-all ${color}`} style={{ width: `${pct}%` }} />
      </div>
      <span className="w-[45%] text-xs text-muted-foreground leading-snug">{label}</span>
    </div>
  )
}

export default function EvaluationSummaryPage() {
  const currentYear = new Date().getFullYear().toString()
  const [tahun, setTahun] = useState(currentYear)

  const { data, isLoading } = useQuery({
    queryKey: ['evaluation-summary', tahun],
    queryFn: () => evaluationsApi.getSummary({ tahun: tahun || undefined }).then(r => r.data.data),
  })

  const years = Array.from({ length: 5 }, (_, i) => String(new Date().getFullYear() - i))

  return (
    <div className="space-y-5">
      <div>
        <h1 className="admin-h1">Hasil Evaluasi Layanan</h1>
        <p className="admin-subtitle">Indeks Kepuasan Masyarakat (IKM)</p>
      </div>

      <div className="flex flex-wrap items-end gap-3">
        <div className="space-y-1">
          <Label htmlFor="eval-tahun">Tahun</Label>
          <select id="eval-tahun" value={tahun} onChange={e => setTahun(e.target.value)} className="h-9 border rounded px-3 text-sm bg-background">
            <option value="">Semua Tahun</option>
            {years.map(y => <option key={y} value={y}>{y}</option>)}
          </select>
        </div>
        {data && data.overall.total_responden > 0 && (
          <Button variant="outline" size="sm" onClick={() => {
            exportCsv('evaluasi-ikm', data.indicators.map(ind => ({
              indikator_id: ind.indikator_id,
              indikator: data.labels[ind.indikator_id] ?? `Indikator ${ind.indikator_id}`,
              avg_kepuasan: Number(ind.avg_kepuasan).toFixed(2),
              responden: ind.responden,
            })))
          }}>
            <Download className="w-4 h-4 mr-2" />
            Export CSV
          </Button>
        )}
      </div>

      {isLoading ? (
        <div className="grid grid-cols-1 xl:grid-cols-[1fr_1fr] gap-5">
          <Skeleton className="h-64 rounded-xl" />
          <Skeleton className="h-64 rounded-xl" />
        </div>
      ) : !data || data.overall.total_responden === 0 ? (
        <div className="text-center py-16 text-muted-foreground">Belum ada data evaluasi untuk periode ini.</div>
      ) : (
        <>
          {/* Overview cards */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                <Star className="w-5 h-5 text-orange-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-foreground">{Number(data.overall.ikm_score).toFixed(2)}</p>
                <p className="text-xs text-muted-foreground">Skor IKM (1-10)</p>
              </div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <Users className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-foreground">{data.overall.total_responden}</p>
                <p className="text-xs text-muted-foreground">Total Responden</p>
              </div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <TrendingUp className="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-foreground">
                  {data.indicators.length > 0 ? Math.max(...data.indicators.map(i => Number(i.avg_kepuasan))).toFixed(1) : '-'}
                </p>
                <p className="text-xs text-muted-foreground">Skor Tertinggi</p>
              </div>
            </div>
            <div className="admin-card p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <BarChart3 className="w-5 h-5 text-red-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-foreground">
                  {data.indicators.length > 0 ? Math.min(...data.indicators.map(i => Number(i.avg_kepuasan))).toFixed(1) : '-'}
                </p>
                <p className="text-xs text-muted-foreground">Skor Terendah</p>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 xl:grid-cols-2 gap-5">
            {/* Left — Per-indicator IKM breakdown */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-4 text-[--admin-text]">Skor Kepuasan per Indikator</h2>
              <div className="space-y-2.5">
                {data.indicators.map((ind: EvaluationSummaryIndicator) => (
                  <ScoreBar
                    key={ind.indikator_id}
                    score={Number(ind.avg_kepuasan)}
                    label={data.labels[ind.indikator_id] ?? `Indikator ${ind.indikator_id}`}
                  />
                ))}
              </div>
            </div>

            {/* Right — Per-visit evaluations */}
            <div className="admin-card p-5">
              <h2 className="text-sm font-bold mb-4 text-[--admin-text]">Evaluasi per Kunjungan</h2>
              {data.visits.length === 0 ? (
                <p className="text-sm text-muted-foreground">Belum ada evaluasi.</p>
              ) : (
                <div className="space-y-2">
                  {data.visits.map((v: EvaluationSummaryVisit) => (
                    <div key={v.id_kunjungan} className="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/30 transition-colors">
                      <div className="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                        <span className="text-sm font-bold text-orange-600">{v.rating_pengunjung ?? '-'}</span>
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">{v.nama}</p>
                        <div className="flex items-center gap-2 mt-0.5">
                          {parseLayanan(v.jenis_layanan).map((l, i) => (
                            <span key={i} className="text-[10px] px-1.5 py-0.5 rounded bg-orange-100 text-orange-700">{l}</span>
                          ))}
                          <span className="text-xs text-muted-foreground">{formatDate(v.date_visit)}</span>
                        </div>
                      </div>
                      <div className="text-right shrink-0">
                        <p className="text-xs text-muted-foreground">Kepuasan</p>
                        <p className="text-sm font-bold">{Number(v.avg_kepuasan).toFixed(1)}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </>
      )}
    </div>
  )
}

import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { auditApi, type AuditEntry } from '@/api/audit'
import { Skeleton } from '@/components/ui/skeleton'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { ChevronLeft, ChevronRight, User, Edit, Trash2, RefreshCw, LogIn, LogOut, Key } from 'lucide-react'

const ACTION_ICONS: Record<string, typeof Edit> = {
  update: Edit,
  update_status: RefreshCw,
  update_service: Edit,
  delete: Trash2,
  create: User,
  login: LogIn,
  logout: LogOut,
  change_password: Key,
}

const ACTION_COLORS: Record<string, string> = {
  update: 'bg-blue-100 text-blue-700',
  update_status: 'bg-amber-100 text-amber-700',
  update_service: 'bg-blue-100 text-blue-700',
  delete: 'bg-red-100 text-red-700',
  create: 'bg-green-100 text-green-700',
  login: 'bg-emerald-100 text-emerald-700',
  logout: 'bg-gray-100 text-gray-600',
  change_password: 'bg-purple-100 text-purple-700',
}

const ACTION_LABELS: Record<string, string> = {
  update: 'mengubah',
  update_status: 'ubah status',
  update_service: 'ubah layanan',
  delete: 'menghapus',
  create: 'menambah',
  login: 'login',
  logout: 'logout',
  change_password: 'ganti password',
}

function formatDate(d: string) {
  try { return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
  catch { return d }
}

export default function AuditLogPage() {
  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(20)

  const { data, isLoading } = useQuery({
    queryKey: ['audit-log', page, limit],
    queryFn: () => auditApi.list({ page, limit }).then(r => r.data),
  })

  const entries: AuditEntry[] = data?.data ?? []
  const pagination = data?.pagination

  return (
    <div className="space-y-5">
      <div>
        <h1 className="admin-h1">Audit Log</h1>
        <p className="admin-subtitle">Riwayat perubahan data oleh admin</p>
      </div>

      {isLoading ? (
        <div className="space-y-2">{Array.from({ length: 10 }).map((_, i) => <Skeleton key={i} className="h-12 rounded-md" />)}</div>
      ) : entries.length === 0 ? (
        <div className="text-center py-16 text-muted-foreground">Belum ada log aktivitas.</div>
      ) : (
        <div className="space-y-2">
          {entries.map(e => {
            const Icon = ACTION_ICONS[e.action] ?? Edit
            const color = ACTION_COLORS[e.action] ?? 'bg-gray-100 text-gray-700'
            let detail = null
            try { detail = e.detail ? JSON.parse(e.detail) : null } catch { /* */ }

            return (
              <div key={e.id} className="admin-card flex items-start gap-4 p-4">
                <div className={`w-9 h-9 rounded-lg flex items-center justify-center shrink-0 ${color}`}>
                  <Icon className="w-4 h-4" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm">
                    <span className="font-semibold">{e.admin_user}</span>
                    {' '}<span className="text-muted-foreground">{ACTION_LABELS[e.action] ?? e.action}</span>
                    {' '}<span className="font-medium">{e.target_type}</span>
                    {e.target_id && <span className="text-muted-foreground"> #{e.target_id}</span>}
                  </p>
                  {detail && (
                    <div className="text-xs text-muted-foreground mt-0.5 break-words space-y-0.5">
                      {Object.entries(detail).map(([k, v]) => {
                        // Diff format: { from: ..., to: ... }
                        if (v && typeof v === 'object' && 'from' in v && 'to' in v) {
                          return <p key={k}><span className="font-medium text-foreground">{k}</span>: <span className="line-through text-red-400">{String(v.from ?? '-')}</span> → <span className="text-green-600">{String(v.to ?? '-')}</span></p>
                        }
                        return <p key={k}><span className="font-medium text-foreground">{k}</span>: {String(v)}</p>
                      })}
                    </div>
                  )}
                </div>
                <div className="text-right shrink-0">
                  <p className="text-xs text-muted-foreground">{formatDate(e.created_at)}</p>
                  {e.ip_address && <p className="text-[10px] text-muted-foreground">{e.ip_address}</p>}
                </div>
              </div>
            )
          })}
        </div>
      )}

      {pagination && (
        <div className="flex items-center justify-between gap-4 flex-wrap">
          <div className="flex flex-wrap items-end gap-3">
            <div className="space-y-1">
              <Label htmlFor="audit-limit">Per halaman</Label>
              <select id="audit-limit" value={limit} onChange={e => { setLimit(Number(e.target.value)); setPage(1) }} className="h-9 border rounded px-3 text-sm bg-background">
                {[20, 50, 100].map(n => <option key={n} value={n}>{n}</option>)}
              </select>
            </div>
            <span className="text-sm text-muted-foreground pb-2">Total: <strong>{pagination.total}</strong></span>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(p => p - 1)}>
              <ChevronLeft className="w-4 h-4" />
            </Button>
            <span className="text-sm">{page} / {pagination.totalPages}</span>
            <Button variant="outline" size="sm" disabled={page >= pagination.totalPages} onClick={() => setPage(p => p + 1)}>
              <ChevronRight className="w-4 h-4" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}

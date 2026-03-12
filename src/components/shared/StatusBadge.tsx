import { Badge } from '@/components/ui/badge'
import type { VisitStatus } from '@/types/visit'

const STATUS_CONFIG: Record<VisitStatus, { label: string; className: string }> = {
  antri: { label: 'Antri', className: 'bg-gray-500 text-white' },
  proses: { label: 'Proses', className: 'bg-blue-500 text-white' },
  menunggu_evaluasi: { label: 'Menunggu Evaluasi', className: 'bg-amber-500 text-white' },
  selesai: { label: 'Selesai', className: 'bg-green-500 text-white' },
}

export function StatusBadge({ status }: { status: VisitStatus }) {
  const config = STATUS_CONFIG[status]
  return <Badge className={config.className}>{config.label}</Badge>
}

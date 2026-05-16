import type { ReactNode } from 'react'

interface StatsCardProps {
  label: string
  value: string | number
  icon?: ReactNode
  accent?: 'primary' | 'secondary'
}

export function StatsCard({ label, value, icon, accent = 'primary' }: StatsCardProps) {
  const iconClass = accent === 'secondary'
    ? 'bg-[var(--admin-secondary-light)] text-[var(--admin-secondary)]'
    : 'bg-[var(--admin-primary-light)] text-[var(--admin-primary)]'

  const isLong = typeof value === 'string' && value.length > 12

  return (
    <div className="admin-card p-5">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0 flex-1">
          <p className="text-xs font-medium uppercase tracking-wide mb-2" style={{ color: 'var(--admin-text-muted)' }}>
            {label}
          </p>
          <p className={`font-bold leading-tight break-words ${isLong ? 'text-sm' : 'text-xl'}`} style={{ color: 'var(--admin-text)' }}>
            {value}
          </p>
        </div>
        {icon && (
          <span className={`shrink-0 w-10 h-10 rounded-xl flex items-center justify-center ${iconClass}`}>
            {icon}
          </span>
        )}
      </div>
    </div>
  )
}

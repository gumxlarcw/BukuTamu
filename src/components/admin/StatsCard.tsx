import { Card, CardContent } from '@/components/ui/card'

interface StatsCardProps {
  label: string
  value: string | number
  icon?: string
}

export function StatsCard({ label, value, icon }: StatsCardProps) {
  return (
    <Card className="ring-1 ring-border/50">
      <CardContent className="p-4">
        <div className="flex items-start justify-between gap-2">
          <div className="min-w-0 flex-1">
            <p className="text-2xl font-bold text-foreground truncate">{value}</p>
            <p className="text-xs text-muted-foreground mt-1 leading-tight">{label}</p>
          </div>
          {icon && (
            <span className="text-2xl shrink-0 mt-0.5">{icon}</span>
          )}
        </div>
      </CardContent>
    </Card>
  )
}

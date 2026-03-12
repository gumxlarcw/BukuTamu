import { Skeleton } from '@/components/ui/skeleton'

export function SkeletonCard() {
  return (
    <div className="rounded-xl border bg-card p-4 shadow-sm">
      <Skeleton className="h-4 w-1/3 mb-2" />
      <Skeleton className="h-8 w-1/2" />
    </div>
  )
}

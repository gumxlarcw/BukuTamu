interface EmptyStateProps {
  icon?: string
  message: string
  action?: string
}

export function EmptyState({ icon = '📋', message, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
      <span className="text-4xl mb-4">{icon}</span>
      <p className="text-lg">{message}</p>
      {action && <p className="text-sm mt-2">{action}</p>}
    </div>
  )
}

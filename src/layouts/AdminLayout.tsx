import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '@/providers/AuthProvider'
import { Sidebar } from '@/components/admin/Sidebar'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'

export function AdminLayout() {
  const { user, isLoading } = useAuth()

  if (isLoading) return <LoadingSpinner className="min-h-screen" />
  if (!user) return <Navigate to="/login" replace />

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      <main className="flex-1 p-6 overflow-auto">
        <Outlet />
      </main>
    </div>
  )
}

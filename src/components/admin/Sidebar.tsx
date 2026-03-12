import { NavLink } from 'react-router-dom'
import { useAuth } from '@/providers/AuthProvider'
import { ThemeToggle } from '@/components/shared/ThemeToggle'
import { cn } from '@/lib/utils'

const NAV_ITEMS = [
  { to: '/admin', label: 'Dashboard', icon: '📊', end: true },
  { to: '/admin/guests', label: 'Daftar Tamu', icon: '👥' },
  { to: '/admin/consultations', label: 'Antrian Konsultasi', icon: '📋' },
  { to: '/admin/visits', label: 'Daftar Kunjungan', icon: '📝' },
  { to: '/admin/manual-entry', label: 'Tambah Kunjungan Manual', icon: '✏️' },
]

export function Sidebar() {
  const { logout } = useAuth()

  return (
    <aside className="flex flex-col w-64 min-h-screen bg-card border-r">
      <div className="p-6">
        <h1 className="text-xl font-bold text-primary">Tamdes Admin</h1>
      </div>
      <nav className="flex-1 px-3">
        {NAV_ITEMS.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 px-4 py-3 rounded-xl mb-1 text-sm transition-colors',
                isActive
                  ? 'bg-primary/10 text-primary font-semibold border-l-4 border-primary'
                  : 'text-muted-foreground hover:bg-muted'
              )
            }
          >
            <span>{item.icon}</span>
            <span>{item.label}</span>
          </NavLink>
        ))}
      </nav>
      <div className="p-4 border-t flex items-center justify-between">
        <ThemeToggle />
        <button
          onClick={logout}
          className="text-sm text-muted-foreground hover:text-destructive transition-colors"
        >
          Logout
        </button>
      </div>
    </aside>
  )
}

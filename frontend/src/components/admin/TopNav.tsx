import { NavLink } from 'react-router-dom'
import { useAuth } from '@/providers/AuthProvider'
import { cn } from '@/lib/utils'
import { InstallPWAButton } from '@/components/admin/InstallPWAButton'
import {
  LayoutDashboard,
  Users,
  ClipboardList,
  Database,
  FileText,
  PlusCircle,
  Star,
  CalendarDays,
  Shield,
  BarChart3,
  UserCog,
  LogOut,
} from 'lucide-react'
import type { LucideIcon } from 'lucide-react'

type Role = 'superadmin' | 'admin' | 'operator'

interface NavItem {
  to: string
  label: string
  icon: LucideIcon
  end?: boolean
  minRole: Role // minimum role required to see this item
}

// Role hierarchy: superadmin > admin > operator
const ROLE_LEVEL: Record<Role, number> = { operator: 1, admin: 2, superadmin: 3 }

const NAV_ITEMS: NavItem[] = [
  { to: '/admin', label: 'Dashboard', icon: LayoutDashboard, end: true, minRole: 'operator' },
  { to: '/admin/guests', label: 'Daftar Tamu', icon: Users, minRole: 'operator' },
  { to: '/admin/consultations', label: 'PST', icon: ClipboardList, minRole: 'operator' },
  { to: '/admin/dtsen', label: 'DTSEN', icon: Database, minRole: 'operator' },
  { to: '/admin/visits', label: 'Kunjungan', icon: FileText, minRole: 'operator' },
  { to: '/admin/manual-entry', label: 'Tambah Manual', icon: PlusCircle, minRole: 'operator' },
  { to: '/admin/evaluations', label: 'Evaluasi', icon: Star, minRole: 'admin' },
  { to: '/admin/responden', label: 'Responden Tahunan', icon: CalendarDays, minRole: 'admin' },
  { to: '/admin/queue-stats', label: 'Analisis', icon: BarChart3, minRole: 'admin' },
  { to: '/admin/users', label: 'Users', icon: UserCog, minRole: 'superadmin' },
  { to: '/admin/audit', label: 'Audit', icon: Shield, minRole: 'superadmin' },
]

export function TopNav() {
  const { user, logout } = useAuth()
  const userRole = (user?.role ?? 'operator') as Role
  const userLevel = ROLE_LEVEL[userRole] ?? 1

  const visibleItems = NAV_ITEMS.filter(item => userLevel >= ROLE_LEVEL[item.minRole])

  return (
    <header className="admin-topnav">
      <div className="admin-topnav-inner">
        {/* Logo + brand */}
        <div className="flex items-center gap-3 shrink-0">
          <img
            src="/logo-bps.png"
            alt="BPS"
            className="h-8 w-auto object-contain"
            onError={(e) => { e.currentTarget.style.display = 'none' }}
          />
          <div className="hidden sm:block">
            <p className="text-sm font-bold leading-tight text-[--admin-text]">Admin Buku Tamu 8200</p>
            <p className="text-[10px] leading-tight text-[--admin-text-muted]">BPS Provinsi Maluku Utara</p>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex items-center gap-1">
          {visibleItems.map((item) => {
            const Icon = item.icon
            return (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  cn(
                    'admin-nav-item',
                    isActive && 'admin-nav-active'
                  )
                }
              >
                <Icon className="w-4 h-4" />
                <span className="hidden md:inline">{item.label}</span>
              </NavLink>
            )
          })}
        </nav>

        {/* Actions */}
        <div className="flex items-center gap-3 shrink-0">
          <InstallPWAButton />
          {user && (
            <span className="hidden lg:inline text-xs text-[--admin-text-muted]">
              {user.nama}
            </span>
          )}
          <button
            onClick={logout}
            className="admin-nav-item !gap-1.5 text-[--admin-text-muted]"
          >
            <LogOut className="w-4 h-4" />
            <span className="hidden sm:inline text-xs">Keluar</span>
          </button>
        </div>
      </div>
    </header>
  )
}

import { createContext, useContext, useEffect, useState, useRef, useCallback, type ReactNode } from 'react'
import { authApi, type AuthUser } from '@/api/auth'

const SESSION_TIMEOUT = 4 * 60 * 60 * 1000 // 4 hours in ms

interface AuthContextType {
  user: AuthUser | null
  isLoading: boolean
  login: (username: string, password: string) => Promise<void>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  // Start "loading" only on pages that actually run authApi.check() below
  // (admin/login). Kiosk pages never check, so they begin un-loaded — this
  // lazy initializer replaces a synchronous setIsLoading(false) in the effect.
  const [isLoading, setIsLoading] = useState(
    () => window.location.pathname.startsWith('/admin') || window.location.pathname === '/login',
  )
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  const doLogout = useCallback(async () => {
    try { await authApi.logout() } catch { /* ignore */ }
    setUser(null)
    if (window.location.pathname.startsWith('/admin')) {
      window.location.href = '/login'
    }
  }, [])

  // Reset idle timer on any user activity
  const resetTimer = useCallback(() => {
    if (!user) return
    if (timeoutRef.current) clearTimeout(timeoutRef.current)
    timeoutRef.current = setTimeout(() => {
      doLogout()
    }, SESSION_TIMEOUT)
  }, [user, doLogout])

  // Attach activity listeners when user is logged in
  useEffect(() => {
    if (!user) return
    const events = ['mousedown', 'keydown', 'scroll', 'touchstart']
    events.forEach(e => window.addEventListener(e, resetTimer, { passive: true }))
    resetTimer() // start timer
    return () => {
      events.forEach(e => window.removeEventListener(e, resetTimer))
      if (timeoutRef.current) clearTimeout(timeoutRef.current)
    }
  }, [user, resetTimer])

  useEffect(() => {
    const isAdminPage = window.location.pathname.startsWith('/admin') || window.location.pathname === '/login'
    if (!isAdminPage) return // isLoading already false via lazy init; skip the auth check
    authApi.check()
      .then((res) => setUser(res.data.data))
      .catch(() => setUser(null))
      .finally(() => setIsLoading(false))
  }, [])

  const login = async (username: string, password: string) => {
    const res = await authApi.login(username, password)
    setUser(res.data.data)
  }

  const logout = async () => {
    await doLogout()
  }

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

// Co-located with its provider by React convention. Splitting into a separate
// file (10 importers) is disproportionate churn for a dev-only HMR lint rule
// with zero runtime/build impact.
// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

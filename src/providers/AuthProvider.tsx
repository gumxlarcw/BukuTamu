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
  const [isLoading, setIsLoading] = useState(true)
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
    if (!isAdminPage) {
      setIsLoading(false)
      return
    }
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

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

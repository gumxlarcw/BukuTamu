import { useState, useEffect } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import axios from 'axios'
import { toast } from 'sonner'
import { useAuth } from '@/hooks/useAuth'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { Lock, User } from 'lucide-react'

export default function LoginPage() {
  const { user, isLoading, login } = useAuth()
  const navigate = useNavigate()
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    const t = setTimeout(() => setMounted(true), 80)
    return () => clearTimeout(t)
  }, [])

  if (isLoading) return <LoadingSpinner className="min-h-screen" />
  if (user) return <Navigate to="/admin" replace />

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!username.trim() || !password.trim()) {
      toast.error('Username dan password wajib diisi')
      return
    }
    setSubmitting(true)
    try {
      await login(username, password)
      navigate('/admin', { replace: true })
    } catch (err: unknown) {
      let msg = 'Login gagal. Periksa username dan password.'
      if (axios.isAxiosError(err)) {
        msg = err.response?.data?.message || msg
      }
      toast.error(msg)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap');

        .login-warm {
          font-family: 'DM Sans', system-ui, sans-serif;
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f7f5f0;
          position: relative;
          overflow: hidden;
        }

        /* Geometric batik-inspired pattern */
        .login-pattern {
          position: absolute;
          inset: 0;
          opacity: 0.035;
          background-image:
            repeating-linear-gradient(
              45deg,
              transparent,
              transparent 20px,
              #2a2016 20px,
              #2a2016 21px
            ),
            repeating-linear-gradient(
              -45deg,
              transparent,
              transparent 20px,
              #2a2016 20px,
              #2a2016 21px
            );
        }

        /* Warm accent blob */
        .login-accent {
          position: absolute;
          border-radius: 50%;
          filter: blur(120px);
          pointer-events: none;
        }

        .login-fade {
          opacity: 0;
          transform: translateY(14px);
          transition: all 0.65s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .login-fade.show { opacity: 1; transform: translateY(0); }

        .login-field {
          width: 100%;
          padding: 14px 16px 14px 44px;
          border: 1.5px solid rgba(42, 32, 22, 0.12);
          border-radius: 12px;
          font-size: 15px;
          font-family: inherit;
          background: rgba(255,255,255,0.7);
          color: #2a2016;
          transition: all 0.2s ease;
          outline: none;
        }
        .login-field::placeholder { color: #9a9088; }
        .login-field:focus {
          border-color: #c4570a;
          background: #fff;
          box-shadow: 0 0 0 3px rgba(196, 87, 10, 0.08);
        }

        .login-submit {
          width: 100%;
          padding: 14px;
          border: none;
          border-radius: 12px;
          font-size: 15px;
          font-weight: 600;
          font-family: inherit;
          background: #c4570a;
          color: #fff;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        .login-submit:hover { background: #b04d09; }
        .login-submit:active { transform: scale(0.98); }
        .login-submit:disabled { opacity: 0.6; cursor: not-allowed; }
      `}</style>

      <div className="login-warm">
        <div className="login-pattern" />
        <div
          className="login-accent"
          style={{ width: 500, height: 500, top: '-15%', right: '-10%', background: 'radial-gradient(circle, rgba(196,87,10,0.08), transparent 70%)' }}
        />
        <div
          className="login-accent"
          style={{ width: 400, height: 400, bottom: '-10%', left: '-8%', background: 'radial-gradient(circle, rgba(12,112,117,0.06), transparent 70%)' }}
        />

        <div className="relative z-10 w-full max-w-md px-6">
          {/* Logo + Branding */}
          <div className={`login-fade text-center mb-10 ${mounted ? 'show' : ''}`}>
            <div className="flex justify-center mb-5">
              <img
                src="/logo-bps.png"
                alt="Logo BPS"
                className="h-20 w-auto object-contain"
                onError={(e) => { e.currentTarget.style.display = 'none' }}
              />
            </div>
            <h1 style={{ fontSize: 28, fontWeight: 700, color: '#2a2016', letterSpacing: '-0.5px', margin: 0 }}>
              Buku Tamu Admin Panel
            </h1>
            <p style={{ fontSize: 14, color: '#9a9088', marginTop: 6 }}>
              BPS Provinsi Maluku Utara
            </p>
          </div>

          {/* Form */}
          <form
            onSubmit={handleSubmit}
            className={`login-fade ${mounted ? 'show' : ''}`}
            style={{ transitionDelay: '150ms' }}
          >
            <div style={{ marginBottom: 20 }}>
              <label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#5c5347', marginBottom: 6 }}>
                Username
              </label>
              <div style={{ position: 'relative' }}>
                <User style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', width: 18, height: 18, color: '#9a9088' }} />
                <input
                  type="text"
                  placeholder="Masukkan username"
                  value={username}
                  onChange={e => setUsername(e.target.value)}
                  autoComplete="username"
                  disabled={submitting}
                  className="login-field"
                />
              </div>
            </div>

            <div style={{ marginBottom: 28 }}>
              <label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#5c5347', marginBottom: 6 }}>
                Password
              </label>
              <div style={{ position: 'relative' }}>
                <Lock style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', width: 18, height: 18, color: '#9a9088' }} />
                <input
                  type="password"
                  placeholder="Masukkan password"
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  autoComplete="current-password"
                  disabled={submitting}
                  className="login-field"
                />
              </div>
            </div>

            <button type="submit" className="login-submit" disabled={submitting}>
              {submitting ? 'Memproses...' : 'Masuk'}
            </button>
          </form>

          {/* Footer */}
          <p
            className={`login-fade text-center ${mounted ? 'show' : ''}`}
            style={{ transitionDelay: '300ms', fontSize: 12, color: '#9a9088', marginTop: 32 }}
          >
            Sensus Ekonomi 2026 &middot; #DataMencerdaskanBangsa
          </p>
        </div>
      </div>
    </>
  )
}

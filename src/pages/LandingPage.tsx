import { useNavigate } from 'react-router-dom'
import { useEffect, useState, useRef, useCallback } from 'react'
import { BookOpen, Shield, ClipboardCheck } from 'lucide-react'
import { SnakeLines } from '@/components/shared/SnakeLines'

export default function LandingPage() {
  const navigate = useNavigate()
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    const t = setTimeout(() => setMounted(true), 80)
    return () => clearTimeout(t)
  }, [])

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        .landing {
          font-family: 'Outfit', system-ui, sans-serif;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          background: linear-gradient(
            135deg,
            #f8f5f0 0%,
            #fef3ec 25%,
            #f0f4f8 50%,
            #fdf6ee 75%,
            #f8f5f0 100%
          );
          background-size: 400% 400%;
          animation: gradientShift 15s ease infinite;
          position: relative;
          overflow: hidden;
        }
        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          25% { background-position: 100% 0%; }
          50% { background-position: 100% 100%; }
          75% { background-position: 0% 100%; }
          100% { background-position: 0% 50%; }
        }

        /* ── SplitText-style letter animation ── */
        .split-letter {
          display: inline-block;
          opacity: 0;
          transform: translateY(32px) rotateX(40deg);
          animation: letterReveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes letterReveal {
          to { opacity: 1; transform: translateY(0) rotateX(0deg); }
        }

        /* ── GradientText-style animated gradient ── */
        .gradient-text-animated {
          background: linear-gradient(
            90deg,
            #ea580c, #f97316, #d97706, #f59e0b, #ea580c
          );
          background-size: 300% 100%;
          -webkit-background-clip: text;
          background-clip: text;
          -webkit-text-fill-color: transparent;
          animation: gradientFlow 6s ease infinite;
        }
        @keyframes gradientFlow {
          0% { background-position: 0% 50%; }
          50% { background-position: 100% 50%; }
          100% { background-position: 0% 50%; }
        }

        /* ── DecryptedText-style scramble ── */
        .decrypt-char {
          display: inline-block;
          animation: decryptFlicker 0.1s steps(1) infinite;
        }
        .decrypt-char.revealed {
          animation: none;
        }
        @keyframes decryptFlicker {
          50% { opacity: 0.6; }
        }

        /* ── Fade up ── */
        .landing .fade-up {
          opacity: 0;
          transform: translateY(24px);
          transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .landing .fade-up.show {
          opacity: 1;
          transform: translateY(0);
        }

        /* ── SpotlightCard (React Bits inspired) ── */
        .spotlight-card {
          position: relative;
          overflow: hidden;
          background: rgba(255,255,255,0.7);
          backdrop-filter: blur(24px);
          -webkit-backdrop-filter: blur(24px);
          border: 1px solid rgba(42,32,22,0.08);
          transition: border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .spotlight-card::before {
          content: '';
          position: absolute;
          width: 300px;
          height: 300px;
          border-radius: 50%;
          background: radial-gradient(circle, rgba(249,115,22,0.1), transparent 70%);
          pointer-events: none;
          opacity: 0;
          transition: opacity 0.4s ease;
          transform: translate(-50%, -50%);
        }
        .spotlight-card:hover::before {
          opacity: 1;
        }
        .spotlight-card:hover {
          border-color: rgba(249, 115, 22, 0.3);
          box-shadow:
            0 0 0 1px rgba(249, 115, 22, 0.08),
            0 24px 48px -12px rgba(42, 32, 22, 0.12);
        }
        .spotlight-card:active {
          transform: scale(0.97);
          transition: transform 0.15s ease;
        }

        /* ── Divider glow ── */
        .divider-glow {
          height: 1px;
          background: linear-gradient(90deg, transparent, rgba(249,115,22,0.5), transparent);
          width: 0;
          transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .divider-glow.show { width: 120px; }
      `}</style>

      <div className="landing select-none px-6">

        <SnakeLines />

        <div className="relative z-10 flex flex-col items-center w-full max-w-xl" style={{ perspective: '800px' }}>

          {/* Logos */}
          <div
            className={`fade-up flex items-center justify-center gap-6 mb-10 ${mounted ? 'show' : ''}`}
          >
            <img
              src="/logo-bps.png"
              alt="Logo BPS"
              className="h-16 w-auto object-contain drop-shadow-lg"
              onError={(e) => { e.currentTarget.style.display = 'none' }}
            />
            <div className="h-12 w-px bg-gray-300" />
            <img
              src="/logo-se2026.png?v=2"
              alt="Logo SE2026"
              className="h-16 w-auto object-contain drop-shadow-lg"
              onError={(e) => { e.currentTarget.style.display = 'none' }}
            />
          </div>

          {/* Title */}
          <h1
            className={`fade-up text-center leading-tight mb-1 ${mounted ? 'show' : ''}`}
            style={{ margin: 0, fontSize: 'clamp(1.8rem, 5vw, 2.6rem)', fontWeight: 800, color: '#2a2016', transitionDelay: '200ms' }}
          >
            BPS Provinsi
            <br />
            <span className="gradient-text-animated">
              Maluku Utara
            </span>
          </h1>

          {/* Divider */}
          <div
            className={`divider-glow my-5 ${mounted ? 'show' : ''}`}
            style={{ transitionDelay: '800ms' }}
          />

          {/* Subtitle with decrypt effect */}
          <DecryptText
            text="Sistem Manajemen Tamu Digital"
            show={mounted}
            delay={900}
            className="text-gray-500 text-sm font-medium tracking-[0.25em] uppercase mb-12"
          />

          {/* Option cards */}
          <div className="w-full flex flex-col sm:flex-row gap-5">
            <SpotlightCard
              className={`fade-up flex-1 rounded-2xl p-8 text-left cursor-pointer ${mounted ? 'show' : ''}`}
              style={{ transitionDelay: '1100ms' }}
              onClick={() => navigate('/kiosk')}
            >
              <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center mb-5 shadow-lg shadow-orange-500/20">
                <BookOpen className="w-7 h-7 text-white" />
              </div>
              <h2 className="text-xl font-bold text-gray-800 mb-2" style={{ margin: 0 }}>Buku Tamu</h2>
              <p className="text-gray-500 text-sm leading-relaxed">
                Daftar kunjungan, ambil nomor antrian, dan isi evaluasi layanan
              </p>
              <div className="mt-5 flex items-center gap-2 text-orange-600 text-xs font-semibold tracking-wide uppercase">
                <span>Masuk</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </div>
            </SpotlightCard>

            <SpotlightCard
              className={`fade-up flex-1 rounded-2xl p-8 text-left cursor-pointer ${mounted ? 'show' : ''}`}
              style={{ transitionDelay: '1250ms' }}
              onClick={() => navigate('/login')}
            >
              <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center mb-5 shadow-lg shadow-gray-500/15">
                <Shield className="w-7 h-7 text-white" />
              </div>
              <h2 className="text-xl font-bold text-gray-800 mb-2" style={{ margin: 0 }}>Halaman Admin</h2>
              <p className="text-gray-500 text-sm leading-relaxed">
                Kelola data pengunjung, antrian konsultasi, dan laporan kunjungan
              </p>
              <div className="mt-5 flex items-center gap-2 text-gray-500 text-xs font-semibold tracking-wide uppercase">
                <span>Login</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </div>
            </SpotlightCard>

            <SpotlightCard
              className={`fade-up flex-1 rounded-2xl p-8 text-left cursor-pointer ${mounted ? 'show' : ''}`}
              style={{ transitionDelay: '1400ms' }}
              onClick={() => navigate('/kiosk/evaluasi')}
            >
              <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-5 shadow-lg shadow-emerald-500/20">
                <ClipboardCheck className="w-7 h-7 text-white" />
              </div>
              <h2 className="text-xl font-bold text-gray-800 mb-2" style={{ margin: 0 }}>Tablet Evaluasi</h2>
              <p className="text-gray-500 text-sm leading-relaxed">
                Layar evaluasi kepuasan layanan PST. Tamu mengisi setelah selesai konsultasi.
              </p>
              <div className="mt-5 flex items-center gap-2 text-emerald-600 text-xs font-semibold tracking-wide uppercase">
                <span>Mulai</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </div>
            </SpotlightCard>
          </div>

        </div>
      </div>
    </>
  )
}

/* ─── DecryptText component (React Bits inspired) ─── */
function DecryptText({ text, show, delay, className }: { text: string; show: boolean; delay: number; className: string }) {
  const [displayed, setDisplayed] = useState(text.split('').map(() => ''))
  const [revealed, setRevealed] = useState(text.split('').map(() => false))
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  const started = useRef(false)

  useEffect(() => {
    if (!show || started.current) return
    started.current = true

    const timer = setTimeout(() => {
      let step = 0
      const interval = setInterval(() => {
        setDisplayed(prev =>
          prev.map((_, i) => {
            if (i <= step) return text[i]
            return chars[Math.floor(Math.random() * chars.length)]
          }),
        )
        setRevealed(prev => prev.map((_, i) => i <= step))
        step++
        if (step >= text.length) clearInterval(interval)
      }, 35)
    }, delay)

    return () => clearTimeout(timer)
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [show])

  if (!show) return <p className={className}>{'\u00A0'}</p>

  return (
    <p className={className}>
      {displayed.map((char, i) => (
        <span key={i} className={`decrypt-char ${revealed[i] ? 'revealed' : ''}`}>
          {char || '\u00A0'}
        </span>
      ))}
    </p>
  )
}

/* ─── SpotlightCard component (React Bits inspired) ─── */
function SpotlightCard({
  children,
  className = '',
  style,
  onClick,
}: {
  children: React.ReactNode
  className?: string
  style?: React.CSSProperties
  onClick?: () => void
}) {
  const cardRef = useRef<HTMLButtonElement>(null)

  const handleMove = useCallback((e: React.PointerEvent) => {
    if (!cardRef.current) return
    const rect = cardRef.current.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top
    cardRef.current.style.setProperty('--spotlight-x', `${x}px`)
    cardRef.current.style.setProperty('--spotlight-y', `${y}px`)
    const before = cardRef.current.querySelector('::before') as HTMLElement | null
    if (!before) {
      // CSS handles ::before positioning via custom properties
      cardRef.current.style.cssText += `; --spotlight-x: ${x}px; --spotlight-y: ${y}px;`
    }
  }, [])

  return (
    <button
      ref={cardRef}
      className={`spotlight-card ${className}`}
      style={{
        ...style,
        // Position the ::before pseudo via CSS custom props
      }}
      onClick={onClick}
      onPointerMove={handleMove}
    >
      <style>{`
        .spotlight-card::before {
          left: var(--spotlight-x, 50%);
          top: var(--spotlight-y, 50%);
        }
      `}</style>
      {children}
    </button>
  )
}

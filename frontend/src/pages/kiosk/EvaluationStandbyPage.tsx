import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { evaluationsApi } from '@/api/evaluations'
import { ClipboardList } from 'lucide-react'

// Dots dekoratif background. Di-generate sekali di module-scope supaya
// stabil lintas render (hindari react-hooks/purity warning untuk Math.random
// di useMemo factory, dan cegah flicker saat strict-mode double-render).
const DOTS = Array.from({ length: 12 }, (_, i) => ({
  width: Math.random() * 200 + 50,
  height: Math.random() * 200 + 50,
  left: Math.random() * 100,
  top: Math.random() * 100,
  delay: i * 0.4,
  duration: 3 + i * 0.5,
}))

export default function EvaluationStandbyPage() {
  const navigate = useNavigate()

  const { data } = useQuery({
    queryKey: ['evaluation-pending'],
    queryFn: () => evaluationsApi.getPending().then(r => r.data),
    refetchInterval: 5000,
  })

  useEffect(() => {
    if (data?.data?.id_kunjungan) {
      // /pending mints a 10-min kiosk_token bound to this id_kunjungan.
      // Pass it via route state — EvaluationPage uses it for both getForm
      // (GET /api/evaluations/{id}) and submit (POST /api/evaluations/{id}).
      navigate(`/kiosk/evaluasi/${data.data.id_kunjungan}`, {
        state: { kiosk_token: data.data.kiosk_token },
      })
    }
  }, [data, navigate])

  const dots = DOTS

  return (
    <div className="overflow-hidden flex flex-col items-center justify-center bg-gradient-to-br from-orange-50 to-amber-100 text-gray-800 px-8" style={{ width: 'calc(100vw / 1.75)', height: 'calc(100vh / 1.75)', zoom: 1.75, fontFamily: "'Outfit', system-ui, sans-serif" }}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        .kiosk-enter { opacity:0; transform:translateY(20px); animation:kioskFadeUp 0.6s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes kioskFadeUp { to { opacity:1; transform:translateY(0); } }
      `}</style>
      {/* Animated background dots */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        {dots.map((dot, i) => (
          <div
            key={i}
            className="absolute rounded-full bg-orange-300/10 animate-pulse"
            style={{
              width: `${dot.width}px`,
              height: `${dot.height}px`,
              left: `${dot.left}%`,
              top: `${dot.top}%`,
              animationDelay: `${dot.delay}s`,
              animationDuration: `${dot.duration}s`,
            }}
          />
        ))}
      </div>

      <div className="relative z-10 flex flex-col items-center text-center">
        {/* Icon with pulsing animation */}
        <div className="relative mb-5">
          <div className="absolute inset-0 rounded-full bg-orange-300/30 animate-ping" />
          <div className="relative w-24 h-24 rounded-full bg-orange-100 border-4 border-orange-400 flex items-center justify-center">
            <ClipboardList className="w-12 h-12 text-orange-500" />
          </div>
        </div>

        <h1 className="kiosk-enter text-3xl md:text-4xl font-bold mb-3">
          Terminal Evaluasi
        </h1>
        <p className="kiosk-enter text-lg text-gray-500 mb-3" style={{ animationDelay: '0.1s' }}>
          Menunggu pengunjung untuk mengisi evaluasi...
        </p>

        {/* Animated dots */}
        <div className="flex gap-2 mt-2">
          {[0, 1, 2].map(i => (
            <div
              key={i}
              className="w-3 h-3 rounded-full bg-orange-500 animate-bounce"
              style={{ animationDelay: `${i * 0.2}s` }}
            />
          ))}
        </div>

        <p className="mt-4 text-gray-400 text-xs">
          Halaman ini akan otomatis membuka formulir evaluasi saat dibutuhkan
        </p>
      </div>
    </div>
  )
}

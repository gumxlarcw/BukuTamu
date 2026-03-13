import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { evaluationsApi } from '@/api/evaluations'
import { ClipboardList } from 'lucide-react'

export default function EvaluationStandbyPage() {
  const navigate = useNavigate()

  const { data } = useQuery({
    queryKey: ['evaluation-pending'],
    queryFn: () => evaluationsApi.getPending().then(r => r.data),
    refetchInterval: 5000,
  })

  useEffect(() => {
    if (data?.data?.id_kunjungan) {
      navigate(`/kiosk/evaluasi/${data.data.id_kunjungan}`)
    }
  }, [data, navigate])

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-teal-800 to-teal-900 text-white px-8">
      {/* Animated background dots */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        {Array.from({ length: 12 }).map((_, i) => (
          <div
            key={i}
            className="absolute rounded-full bg-white/5 animate-pulse"
            style={{
              width: `${Math.random() * 200 + 50}px`,
              height: `${Math.random() * 200 + 50}px`,
              left: `${Math.random() * 100}%`,
              top: `${Math.random() * 100}%`,
              animationDelay: `${i * 0.4}s`,
              animationDuration: `${3 + i * 0.5}s`,
            }}
          />
        ))}
      </div>

      <div className="relative z-10 flex flex-col items-center text-center">
        {/* Icon with pulsing animation */}
        <div className="relative mb-8">
          <div className="absolute inset-0 rounded-full bg-teal-400/30 animate-ping" />
          <div className="relative w-32 h-32 rounded-full bg-teal-600/60 border-4 border-teal-400 flex items-center justify-center">
            <ClipboardList className="w-16 h-16 text-teal-200" />
          </div>
        </div>

        <h1 className="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
          Terminal Evaluasi
        </h1>
        <p className="text-xl text-teal-200 mb-4">
          Menunggu pengunjung untuk mengisi evaluasi...
        </p>

        {/* Animated dots */}
        <div className="flex gap-2 mt-4">
          {[0, 1, 2].map(i => (
            <div
              key={i}
              className="w-3 h-3 rounded-full bg-teal-400 animate-bounce"
              style={{ animationDelay: `${i * 0.2}s` }}
            />
          ))}
        </div>

        <p className="mt-8 text-teal-300/70 text-sm">
          Halaman ini akan otomatis membuka formulir evaluasi saat dibutuhkan
        </p>
      </div>
    </div>
  )
}

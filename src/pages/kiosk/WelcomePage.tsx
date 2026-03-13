import { useNavigate } from 'react-router-dom'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'

export default function WelcomePage() {
  const navigate = useNavigate()

  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  return (
    <div className="flex flex-col items-center justify-center text-center text-white px-8 max-w-3xl mx-auto">
      <div className="mb-8">
        <div className="w-24 h-24 mx-auto mb-6 rounded-full bg-teal-500/30 flex items-center justify-center border-4 border-teal-400">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            className="w-12 h-12 text-teal-300"
          >
            <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
          </svg>
        </div>
        <h1 className="text-4xl md:text-5xl font-bold leading-tight drop-shadow-lg mb-4">
          Selamat Datang di
        </h1>
        <h2 className="text-3xl md:text-4xl font-bold text-teal-300 drop-shadow-lg mb-2">
          Pelayanan Statistik Terpadu
        </h2>
        <p className="text-lg text-white/80 mt-4">
          Badan Pusat Statistik
        </p>
      </div>

      <button
        onClick={() => navigate('/kiosk/status')}
        className="mt-8 px-16 py-6 text-2xl font-bold rounded-2xl bg-teal-500 hover:bg-teal-400 active:bg-teal-600 text-white shadow-2xl transition-all duration-200 transform hover:scale-105 active:scale-95 min-w-64"
      >
        Mulai
      </button>

      <p className="mt-6 text-white/60 text-sm">
        Sentuh layar untuk memulai
      </p>
    </div>
  )
}

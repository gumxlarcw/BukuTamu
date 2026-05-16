import { Link } from 'react-router-dom'
import { ArrowLeft } from 'lucide-react'

export default function NotFoundPage() {
  return (
    <div
      className="flex flex-col items-center justify-center min-h-screen gap-6 px-6 text-center"
      style={{
        fontFamily: "'Outfit', system-ui, sans-serif",
        background: '#08070b',
      }}
    >
      <img
        src="/logo-bps.png"
        alt="Logo BPS"
        className="h-14 w-auto opacity-40 mb-4"
        onError={(e) => { e.currentTarget.style.display = 'none' }}
      />
      <h1
        className="text-8xl font-extrabold tracking-tighter"
        style={{
          background: 'linear-gradient(135deg, rgba(249,115,22,0.6), rgba(251,191,36,0.3))',
          WebkitBackgroundClip: 'text',
          WebkitTextFillColor: 'transparent',
        }}
      >
        404
      </h1>
      <p className="text-white/70 text-lg">Halaman tidak ditemukan</p>
      <Link
        to="/"
        className="mt-4 flex items-center gap-2 px-8 py-4 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 text-white font-semibold transition-all active:scale-95"
      >
        <ArrowLeft className="w-5 h-5" />
        Kembali ke Beranda
      </Link>
    </div>
  )
}

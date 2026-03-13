import { useNavigate } from 'react-router-dom'
import { useInactivityTimeout } from '@/hooks/useInactivityTimeout'
import { UserCheck, UserPlus } from 'lucide-react'

export default function StatusSelectPage() {
  const navigate = useNavigate()
  useInactivityTimeout(() => navigate('/kiosk'), 120000)

  return (
    <div className="flex flex-col items-center text-center text-white px-4 max-w-4xl w-full mx-auto">
      <h1 className="text-3xl md:text-4xl font-bold mb-2 drop-shadow-lg">
        Apakah Anda Sudah Terdaftar?
      </h1>
      <p className="text-white/80 mb-12 text-lg">Pilih salah satu opsi di bawah ini</p>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
        {/* Returning visitor */}
        <button
          onClick={() => navigate('/kiosk/recognize')}
          className="group flex flex-col items-center justify-center gap-6 p-10 rounded-3xl bg-white/10 hover:bg-teal-500/80 active:bg-teal-600/80 border-2 border-white/20 hover:border-teal-300 backdrop-blur-sm shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 min-h-64 cursor-pointer"
        >
          <div className="w-20 h-20 rounded-full bg-teal-400/30 group-hover:bg-white/20 flex items-center justify-center transition-colors">
            <UserCheck className="w-10 h-10 text-teal-300 group-hover:text-white" />
          </div>
          <div>
            <h2 className="text-2xl font-bold mb-2">Sudah Pernah Daftar</h2>
            <p className="text-white/70 group-hover:text-white/90 text-sm">
              Kenali wajah saya untuk melanjutkan
            </p>
          </div>
        </button>

        {/* New visitor */}
        <button
          onClick={() => navigate('/kiosk/service')}
          className="group flex flex-col items-center justify-center gap-6 p-10 rounded-3xl bg-white/10 hover:bg-teal-500/80 active:bg-teal-600/80 border-2 border-white/20 hover:border-teal-300 backdrop-blur-sm shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 min-h-64 cursor-pointer"
        >
          <div className="w-20 h-20 rounded-full bg-teal-400/30 group-hover:bg-white/20 flex items-center justify-center transition-colors">
            <UserPlus className="w-10 h-10 text-teal-300 group-hover:text-white" />
          </div>
          <div>
            <h2 className="text-2xl font-bold mb-2">Belum Pernah Daftar</h2>
            <p className="text-white/70 group-hover:text-white/90 text-sm">
              Daftarkan diri sebagai pengunjung baru
            </p>
          </div>
        </button>
      </div>

      <button
        onClick={() => navigate('/kiosk')}
        className="mt-10 px-8 py-3 text-white/70 hover:text-white underline text-lg transition-colors"
      >
        Kembali
      </button>
    </div>
  )
}

import { ShieldCheck, X } from 'lucide-react'

interface PhotoDisclaimerProps {
  onAccept: () => void
  onDecline: () => void
}

export function PhotoDisclaimer({ onAccept, onDecline }: PhotoDisclaimerProps) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <div className="bg-white/95 text-gray-800 rounded-3xl shadow-2xl max-w-lg w-full p-8">
        <div className="flex items-center gap-4 mb-6">
          <div className="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
            <ShieldCheck className="w-8 h-8 text-orange-600" />
          </div>
          <div>
            <h2 className="text-2xl font-bold text-gray-900">Persetujuan Biometrik</h2>
            <p className="text-gray-500 text-sm">Informasi penggunaan data wajah</p>
          </div>
        </div>

        <div className="space-y-4 text-gray-700 text-base leading-relaxed mb-8">
          <p>
            Sistem kami akan mengambil dan menyimpan foto serta data biometrik wajah Anda
            untuk keperluan identifikasi pada kunjungan berikutnya.
          </p>
          <ul className="list-disc pl-6 space-y-2 text-sm">
            <li>Data wajah disimpan secara terenkripsi di server kami</li>
            <li>Data hanya digunakan untuk pengenalan identitas pengunjung</li>
            <li>Data tidak dibagikan kepada pihak ketiga</li>
            <li>Anda dapat meminta penghapusan data kapan saja</li>
          </ul>
          <p className="font-semibold text-gray-800">
            Dengan menekan "Saya Setuju", Anda menyetujui pengambilan dan penyimpanan
            data biometrik wajah Anda sesuai kebijakan privasi kami.
          </p>
        </div>

        <div className="flex gap-4">
          <button
            onClick={onDecline}
            className="flex-1 flex items-center justify-center gap-2 px-6 py-4 rounded-xl border-2 border-gray-300 text-gray-600 text-lg font-semibold hover:bg-gray-50 active:bg-gray-100 transition-all"
          >
            <X className="w-5 h-5" />
            Tidak
          </button>
          <button
            onClick={onAccept}
            className="flex-1 px-6 py-4 rounded-xl bg-orange-500 hover:bg-orange-400 active:bg-orange-600 text-white text-lg font-bold shadow-lg transition-all"
          >
            Saya Setuju
          </button>
        </div>
      </div>
    </div>
  )
}

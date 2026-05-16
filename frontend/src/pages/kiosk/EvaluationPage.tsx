import { useNavigate, useParams } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { evaluationsApi } from '@/api/evaluations'
import { EvaluationForm } from '@/components/kiosk/EvaluationForm'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import { CheckCircle } from 'lucide-react'
import type { EvaluationSubmission } from '@/types/evaluation'

export default function EvaluationPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()

  const { data: formData, isLoading, isError } = useQuery({
    queryKey: ['evaluation-form', id],
    queryFn: () => evaluationsApi.getForm(Number(id)).then(r => r.data.data),
    enabled: !!id,
  })

  const submitMutation = useMutation({
    mutationFn: (data: EvaluationSubmission) =>
      evaluationsApi.submit(Number(id), data),
    onSuccess: () => {
      navigate('/kiosk/evaluasi')
    },
  })

  return (
    <div
      className="flex flex-col overflow-hidden"
      style={{
        width: 'calc(100vw / 1.75)',
        height: 'calc(100vh / 1.75)',
        zoom: 1.75,
        fontFamily: "'Outfit', system-ui, sans-serif",
        background: 'linear-gradient(135deg, #f8f5f0 0%, #fef3ec 25%, #f0f4f8 50%, #fdf6ee 75%, #f8f5f0 100%)',
        backgroundSize: '400% 400%',
        animation: 'gradientShift 15s ease infinite',
      }}
    >
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          25% { background-position: 100% 0%; }
          50% { background-position: 100% 100%; }
          75% { background-position: 0% 100%; }
          100% { background-position: 0% 50%; }
        }
        .kiosk-enter {
          opacity: 0;
          transform: translateY(20px);
          animation: kioskFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes kioskFadeUp {
          to { opacity: 1; transform: translateY(0); }
        }
        .kiosk-scroll { overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .kiosk-scroll::-webkit-scrollbar { display: none; }
        .kiosk-scroll { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>

      {/* Header */}
      <header className="kiosk-enter shrink-0 px-6 py-4 text-center">
        <h1 className="text-xl font-bold text-gray-800">
          Formulir Evaluasi Layanan
        </h1>
        <p className="text-gray-500 text-sm mt-1">
          Bantu kami meningkatkan kualitas pelayanan
        </p>
      </header>

      <main className="flex-1 min-h-0 px-4 pb-4 max-w-2xl mx-auto w-full kiosk-scroll">
        {isLoading && (
          <div className="flex flex-col items-center gap-4 py-16">
            <LoadingSpinner />
            <p className="text-gray-400">Memuat formulir evaluasi...</p>
          </div>
        )}

        {isError && (
          <div className="text-center py-16">
            <p className="text-red-600 text-xl mb-6">Gagal memuat formulir evaluasi</p>
            <button
              onClick={() => navigate('/kiosk/evaluasi')}
              className="px-8 py-4 bg-orange-500 rounded-xl text-white font-bold hover:bg-orange-400 active:scale-95 transition-all"
            >
              Kembali
            </button>
          </div>
        )}

        {submitMutation.isSuccess && (
          <div className="kiosk-enter text-center py-16">
            <div className="w-20 h-20 mx-auto mb-4 rounded-full bg-orange-100 border-2 border-orange-300 flex items-center justify-center">
              <CheckCircle className="w-10 h-10 text-orange-500" />
            </div>
            <h2 className="text-2xl font-bold text-gray-800 mb-2">Terima Kasih!</h2>
            <p className="text-gray-500">Evaluasi Anda telah berhasil dikirim.</p>
          </div>
        )}

        {formData && !submitMutation.isSuccess && (
          <div className="kiosk-enter" style={{ animationDelay: '150ms' }}>
            <EvaluationForm
              indicators={formData.indicators}
              konsultasiKualitas={formData.konsultasiKualitas}
              onSubmit={data => submitMutation.mutate(data)}
              isSubmitting={submitMutation.isPending}
            />
          </div>
        )}

        {submitMutation.isError && (
          <div className="mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-center">
            Gagal mengirim evaluasi. Silakan coba lagi.
          </div>
        )}
      </main>
    </div>
  )
}

import { useNavigate, useParams } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { evaluationsApi } from '@/api/evaluations'
import { EvaluationForm } from '@/components/kiosk/EvaluationForm'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'
import type { EvaluationSubmission } from '@/types/evaluation'

export default function EvaluationPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()

  const { data: indicators, isLoading, isError } = useQuery({
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
    <div className="min-h-screen bg-gray-50 flex flex-col">
      {/* Header */}
      <header className="bg-teal-600 text-white px-6 py-5 shadow-lg">
        <h1 className="text-2xl font-bold text-center">Formulir Evaluasi Layanan</h1>
        <p className="text-teal-200 text-center text-sm mt-1">
          Bantu kami meningkatkan kualitas pelayanan
        </p>
      </header>

      <main className="flex-1 px-4 py-8 max-w-2xl mx-auto w-full">
        {isLoading && (
          <div className="flex flex-col items-center gap-4 py-16">
            <LoadingSpinner />
            <p className="text-gray-600">Memuat formulir evaluasi...</p>
          </div>
        )}

        {isError && (
          <div className="text-center py-16">
            <p className="text-red-500 text-xl mb-6">Gagal memuat formulir evaluasi</p>
            <button
              onClick={() => navigate('/kiosk/evaluasi')}
              className="px-8 py-4 bg-teal-500 rounded-xl text-white font-bold"
            >
              Kembali
            </button>
          </div>
        )}

        {submitMutation.isSuccess && (
          <div className="text-center py-16">
            <div className="w-20 h-20 mx-auto mb-4 rounded-full bg-teal-100 flex items-center justify-center">
              <span className="text-4xl">✓</span>
            </div>
            <h2 className="text-2xl font-bold text-gray-800 mb-2">Terima Kasih!</h2>
            <p className="text-gray-600">Evaluasi Anda telah berhasil dikirim.</p>
          </div>
        )}

        {indicators && !submitMutation.isSuccess && (
          <EvaluationForm
            indicators={indicators}
            onSubmit={data => submitMutation.mutate(data)}
            isSubmitting={submitMutation.isPending}
          />
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

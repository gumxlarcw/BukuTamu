import { useState } from 'react'
import { Star } from 'lucide-react'
import type {
  EvaluationIndicator,
  EvaluationSubmission,
  KonsultasiKualitas,
} from '@/types/evaluation'

interface EvaluationFormProps {
  indicators: EvaluationIndicator[]
  konsultasiKualitas?: KonsultasiKualitas[]
  onSubmit: (data: EvaluationSubmission) => void
  isSubmitting?: boolean
}

interface StarScaleProps {
  value: number
  onChange: (val: number) => void
  size?: 'sm' | 'md'
}

function StarScale({ value, onChange, size = 'sm' }: StarScaleProps) {
  const starSize = size === 'sm' ? 'w-7 h-7' : 'w-10 h-10'
  const numSize = size === 'sm' ? 'text-[11px]' : 'text-sm'
  return (
    <div className="w-full">
      <div className="grid grid-cols-10 gap-0.5 w-full">
        {Array.from({ length: 10 }, (_, i) => i + 1).map(score => {
          const filled = score <= value
          return (
            <button
              key={score}
              type="button"
              onClick={() => onChange(score)}
              aria-label={`${score} dari 10`}
              className="flex flex-col items-center justify-center gap-0.5 py-1 transition-transform hover:scale-110 active:scale-90 cursor-pointer"
            >
              <Star
                className={`${starSize} transition-colors ${
                  filled
                    ? 'fill-orange-500 text-orange-500 drop-shadow-sm'
                    : 'fill-transparent text-gray-300'
                }`}
              />
              <span
                className={`${numSize} font-medium ${
                  filled ? 'text-orange-600' : 'text-gray-400'
                }`}
              >
                {score}
              </span>
            </button>
          )
        })}
      </div>
      <div className="flex justify-between text-[10px] text-gray-400 mt-1.5 px-1">
        <span>Sangat tidak puas</span>
        <span>Sangat puas</span>
      </div>
    </div>
  )
}

export function EvaluationForm({
  indicators,
  konsultasiKualitas = [],
  onSubmit,
  isSubmitting,
}: EvaluationFormProps) {
  const [satisfaction, setSatisfaction] = useState<Record<number, number>>(
    Object.fromEntries(indicators.map(ind => [ind.id, 0])),
  )
  const [kualitasMap, setKualitasMap] = useState<Record<number, number>>(
    Object.fromEntries(konsultasiKualitas.map(k => [k.id, 0])),
  )
  const [overallScore, setOverallScore] = useState(0)

  const setScore = (id: number, value: number) => {
    setSatisfaction(prev => ({ ...prev, [id]: value }))
  }
  const setKualitas = (id: number, value: number) => {
    setKualitasMap(prev => ({ ...prev, [id]: value }))
  }

  const isComplete =
    overallScore > 0 &&
    indicators.every(ind => (satisfaction[ind.id] ?? 0) > 0) &&
    konsultasiKualitas.every(k => (kualitasMap[k.id] ?? 0) > 0)

  const handleSubmit = () => {
    if (!isComplete) return
    const data: EvaluationSubmission = {
      indicators: indicators.map(ind => ({
        id: ind.id,
        satisfaction: satisfaction[ind.id] ?? 0,
      })),
      overall_score: overallScore,
      kualitas_per_konsultasi: konsultasiKualitas.length
        ? Object.fromEntries(konsultasiKualitas.map(k => [k.id, kualitasMap[k.id] ?? 0]))
        : undefined,
    }
    onSubmit(data)
  }

  return (
    <div className="space-y-4 w-full">
      {/* Block II header */}
      <div className="bg-orange-50 backdrop-blur-sm rounded-2xl p-4 border border-orange-200">
        <h2 className="text-sm font-bold text-gray-800 mb-1">
          Blok II. Kepuasan terhadap Pelayanan Data dan Informasi Statistik BPS
        </h2>
        <p className="text-xs text-gray-600 leading-snug">
          Mohon berikan penilaian tingkat kepuasan Anda untuk masing-masing aspek pelayanan berikut menggunakan
          skala <span className="font-semibold">1 (sangat tidak puas)</span> sampai
          <span className="font-semibold"> 10 (sangat puas)</span>.
        </p>
      </div>

      {/* Indicator ratings — single column (kepuasan only) */}
      <div className="space-y-3">
        {indicators.map((indicator, idx) => (
          <div
            key={indicator.id}
            className="bg-white/70 backdrop-blur-sm rounded-2xl p-4 border border-gray-200 shadow-sm overflow-hidden"
          >
            <p className="font-semibold text-gray-800 mb-2 text-xs break-words leading-snug">
              {idx + 1}. {indicator.label}
            </p>
            <StarScale
              value={satisfaction[indicator.id] ?? 0}
              onChange={val => setScore(indicator.id, val)}
            />
          </div>
        ))}
      </div>

      {/* Kualitas data per item — hanya tampil jika tamu mendapatkan data */}
      {konsultasiKualitas.length > 0 && (
        <div className="bg-emerald-50 backdrop-blur-sm rounded-2xl p-5 border border-emerald-200 overflow-hidden">
          <p className="font-bold text-gray-800 mb-1 text-sm">
            Kualitas Data yang Diperoleh
          </p>
          <p className="text-xs text-gray-600 mb-4 leading-snug">
            Beri penilaian kualitas untuk masing-masing data yang Anda dapatkan dari pelayanan ini.
          </p>
          <div className="space-y-4">
            {konsultasiKualitas.map((k, idx) => (
              <div
                key={k.id}
                className="bg-white/70 rounded-xl p-3 border border-emerald-100"
              >
                <p className="font-medium text-gray-800 mb-2 text-xs leading-snug">
                  {idx + 1}. {k.rincian_data || `Data #${idx + 1}`}
                </p>
                <StarScale
                  value={kualitasMap[k.id] ?? 0}
                  onChange={val => setKualitas(k.id, val)}
                />
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Overall score */}
      <div className="bg-orange-50 backdrop-blur-sm rounded-2xl p-6 border border-orange-200 overflow-hidden">
        <p className="font-bold text-gray-800 mb-1 text-base">
          Nilai Kepuasan Keseluruhan
        </p>
        <p className="text-xs text-gray-500 mb-4">
          Penilaian secara umum untuk pelayanan yang Anda terima hari ini.
        </p>
        <StarScale
          value={overallScore}
          onChange={setOverallScore}
          size="md"
        />
        {overallScore > 0 && (
          <p className="mt-3 text-orange-600 font-semibold">Nilai: {overallScore}/10</p>
        )}
      </div>

      {/* Submit button */}
      <button
        onClick={handleSubmit}
        disabled={!isComplete || isSubmitting}
        className={`w-full py-5 rounded-2xl text-xl font-bold transition-all active:scale-95 ${
          isComplete && !isSubmitting
            ? 'bg-orange-500 hover:bg-orange-400 text-white shadow-xl shadow-orange-500/20'
            : 'bg-gray-200 text-gray-400 cursor-not-allowed'
        }`}
      >
        {isSubmitting ? 'Mengirim...' : 'Kirim Evaluasi'}
      </button>
    </div>
  )
}

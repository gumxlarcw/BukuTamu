import { useState } from 'react'
import type { EvaluationIndicator, EvaluationSubmission } from '@/types/evaluation'

interface EvaluationFormProps {
  indicators: EvaluationIndicator[]
  onSubmit: (data: EvaluationSubmission) => void
  isSubmitting?: boolean
}

interface LikertScaleProps {
  value: number
  onChange: (val: number) => void
}

function LikertScale({ value, onChange }: LikertScaleProps) {
  return (
    <div>
      <div className="flex flex-wrap gap-1.5">
        {Array.from({ length: 10 }, (_, i) => i + 1).map(score => (
          <button
            key={score}
            type="button"
            onClick={() => onChange(score)}
            className={`w-9 h-9 rounded-lg font-semibold text-sm transition-all hover:scale-105 active:scale-95 ${
              score === value
                ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30'
                : 'bg-white/80 text-gray-600 border border-gray-200 hover:border-orange-400'
            }`}
          >
            {score}
          </button>
        ))}
      </div>
      <div className="flex justify-between text-[10px] text-gray-400 mt-1.5 px-1">
        <span>Sangat tidak puas</span>
        <span>Sangat puas</span>
      </div>
    </div>
  )
}

export function EvaluationForm({ indicators, onSubmit, isSubmitting }: EvaluationFormProps) {
  const [satisfaction, setSatisfaction] = useState<Record<number, number>>(
    Object.fromEntries(indicators.map(ind => [ind.id, 0])),
  )
  const [overallScore, setOverallScore] = useState(0)

  const setScore = (id: number, value: number) => {
    setSatisfaction(prev => ({ ...prev, [id]: value }))
  }

  const isComplete = overallScore > 0 && indicators.every(ind => (satisfaction[ind.id] ?? 0) > 0)

  const handleSubmit = () => {
    if (!isComplete) return
    const data: EvaluationSubmission = {
      indicators: indicators.map(ind => ({
        id: ind.id,
        satisfaction: satisfaction[ind.id] ?? 0,
      })),
      overall_score: overallScore,
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
            <LikertScale
              value={satisfaction[indicator.id] ?? 0}
              onChange={val => setScore(indicator.id, val)}
            />
          </div>
        ))}
      </div>

      {/* Overall score */}
      <div className="bg-orange-50 backdrop-blur-sm rounded-2xl p-6 border border-orange-200 overflow-hidden">
        <p className="font-bold text-gray-800 mb-1 text-base">
          Nilai Kepuasan Keseluruhan
        </p>
        <p className="text-xs text-gray-500 mb-4">
          Penilaian secara umum untuk pelayanan yang Anda terima hari ini.
        </p>
        <div className="flex gap-2 flex-wrap">
          {Array.from({ length: 10 }, (_, i) => i + 1).map(score => (
            <button
              key={score}
              type="button"
              onClick={() => setOverallScore(score)}
              className={`w-12 h-12 rounded-xl font-bold text-lg transition-all hover:scale-105 active:scale-95 ${
                score === overallScore
                  ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30'
                  : 'bg-white/70 text-gray-600 border border-gray-200 hover:border-orange-400'
              }`}
            >
              {score}
            </button>
          ))}
        </div>
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

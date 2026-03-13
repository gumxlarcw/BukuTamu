import { useState } from 'react'
import type { EvaluationIndicator, EvaluationSubmission } from '@/types/evaluation'

interface EvaluationFormProps {
  indicators: EvaluationIndicator[]
  onSubmit: (data: EvaluationSubmission) => void
  isSubmitting?: boolean
}

interface StarRatingProps {
  value: number
  onChange: (val: number) => void
  max?: number
  label: string
  color?: string
}

function StarRating({ value, onChange, max = 10, label, color = 'text-yellow-400' }: StarRatingProps) {
  return (
    <div className="flex flex-col gap-1">
      <span className="text-xs font-medium text-gray-600">{label}</span>
      <div className="flex gap-1">
        {Array.from({ length: max }, (_, i) => i + 1).map(star => (
          <button
            key={star}
            type="button"
            onClick={() => onChange(star)}
            className={`text-xl transition-transform hover:scale-110 active:scale-95 ${
              star <= value ? color : 'text-gray-300'
            }`}
          >
            ★
          </button>
        ))}
      </div>
      <span className="text-xs text-gray-500">{value > 0 ? `${value}/10` : '-'}</span>
    </div>
  )
}

export function EvaluationForm({ indicators, onSubmit, isSubmitting }: EvaluationFormProps) {
  const [ratings, setRatings] = useState<Record<number, { importance: number; satisfaction: number }>>(
    Object.fromEntries(indicators.map(ind => [ind.id, { importance: 0, satisfaction: 0 }]))
  )
  const [overallScore, setOverallScore] = useState(0)

  const updateRating = (id: number, key: 'importance' | 'satisfaction', value: number) => {
    setRatings(prev => ({
      ...prev,
      [id]: { ...prev[id], [key]: value },
    }))
  }

  const isComplete =
    overallScore > 0 &&
    indicators.every(ind => ratings[ind.id]?.importance > 0 && ratings[ind.id]?.satisfaction > 0)

  const handleSubmit = () => {
    if (!isComplete) return
    const data: EvaluationSubmission = {
      indicators: indicators.map(ind => ({
        id: ind.id,
        importance: ratings[ind.id]?.importance ?? 0,
        satisfaction: ratings[ind.id]?.satisfaction ?? 0,
      })),
      overall_score: overallScore,
    }
    onSubmit(data)
  }

  return (
    <div className="space-y-6 w-full">
      {/* Indicator ratings */}
      <div className="space-y-4">
        {indicators.map((indicator, idx) => (
          <div key={indicator.id} className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <p className="font-semibold text-gray-800 mb-3 text-sm">
              {idx + 1}. {indicator.label}
            </p>
            <div className="grid grid-cols-2 gap-4">
              <StarRating
                value={ratings[indicator.id]?.importance ?? 0}
                onChange={val => updateRating(indicator.id, 'importance', val)}
                label="Kepentingan"
                color="text-blue-400"
              />
              <StarRating
                value={ratings[indicator.id]?.satisfaction ?? 0}
                onChange={val => updateRating(indicator.id, 'satisfaction', val)}
                label="Kepuasan"
                color="text-yellow-400"
              />
            </div>
          </div>
        ))}
      </div>

      {/* Overall score */}
      <div className="bg-teal-50 rounded-2xl p-6 border-2 border-teal-200">
        <p className="font-bold text-gray-800 mb-4 text-base">
          Nilai Kepuasan Keseluruhan
        </p>
        <div className="flex gap-2 flex-wrap">
          {Array.from({ length: 10 }, (_, i) => i + 1).map(score => (
            <button
              key={score}
              type="button"
              onClick={() => setOverallScore(score)}
              className={`w-12 h-12 rounded-xl font-bold text-lg transition-all hover:scale-105 active:scale-95 ${
                score === overallScore
                  ? 'bg-teal-500 text-white shadow-md'
                  : 'bg-white text-gray-600 border-2 border-gray-200 hover:border-teal-300'
              }`}
            >
              {score}
            </button>
          ))}
        </div>
        {overallScore > 0 && (
          <p className="mt-3 text-teal-700 font-semibold">Nilai: {overallScore}/10</p>
        )}
      </div>

      {/* Submit button */}
      <button
        onClick={handleSubmit}
        disabled={!isComplete || isSubmitting}
        className={`w-full py-5 rounded-2xl text-xl font-bold transition-all active:scale-95 ${
          isComplete && !isSubmitting
            ? 'bg-teal-500 hover:bg-teal-400 text-white shadow-xl'
            : 'bg-gray-200 text-gray-400 cursor-not-allowed'
        }`}
      >
        {isSubmitting ? 'Mengirim...' : 'Kirim Evaluasi'}
      </button>
    </div>
  )
}

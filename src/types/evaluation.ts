export interface EvaluationIndicator {
  id: number
  label: string
  satisfaction: number
}

export interface KonsultasiKualitas {
  id: number
  rincian_data: string
  status_data: number
  kualitas: number | null
}

export interface EvaluationFormData {
  indicators: EvaluationIndicator[]
  konsultasiKualitas: KonsultasiKualitas[]
}

export interface EvaluationSubmission {
  indicators: { id: number; satisfaction: number }[]
  overall_score: number
  kualitas_per_konsultasi?: Record<number, number>
}

export interface EvaluationResult {
  visit_id: number
  guest_nama: string
  indicators: EvaluationIndicator[]
  overall_score: number
  submitted_at: string
}

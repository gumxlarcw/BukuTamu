export interface EvaluationIndicator {
  id: number
  label: string
  satisfaction: number
}

export interface EvaluationSubmission {
  indicators: { id: number; satisfaction: number }[]
  overall_score: number
}

export interface EvaluationResult {
  visit_id: number
  guest_nama: string
  indicators: EvaluationIndicator[]
  overall_score: number
  submitted_at: string
}

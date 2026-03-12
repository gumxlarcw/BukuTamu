export interface EvaluationIndicator {
  id: number
  label: string
  importance: number
  satisfaction: number
}

export interface EvaluationSubmission {
  indicators: { id: number; importance: number; satisfaction: number }[]
  overall_score: number
}

export interface EvaluationResult {
  visit_id: number
  guest_nama: string
  indicators: EvaluationIndicator[]
  overall_score: number
  submitted_at: string
}

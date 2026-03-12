import apiClient from './client'
import type { ApiResponse } from '@/types/api'
import type { EvaluationIndicator, EvaluationSubmission, EvaluationResult } from '@/types/evaluation'

export const evaluationsApi = {
  getPending: () => apiClient.get<ApiResponse<{ visit_id: number } | null>>('/api/evaluations/pending'),
  getForm: (id: number) =>
    apiClient.get<ApiResponse<EvaluationIndicator[]>>(`/api/evaluations/${id}`),
  submit: (id: number, data: EvaluationSubmission) =>
    apiClient.post<ApiResponse<null>>(`/api/evaluations/${id}`, data),
  getResults: (id: number) =>
    apiClient.get<ApiResponse<EvaluationResult>>(`/api/evaluations/${id}/results`),
}

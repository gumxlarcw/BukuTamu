import apiClient from './client'
import type { ApiResponse } from '@/types/api'
import type {
  EvaluationIndicator,
  EvaluationSubmission,
  EvaluationResult,
  EvaluationFormData,
  KonsultasiKualitas,
} from '@/types/evaluation'

interface EvaluationFormBackendShape {
  indikator: Record<string, string>
  evaluation: unknown[]
  konsultasi_kualitas: KonsultasiKualitas[]
}

export const evaluationsApi = {
  getPending: () => apiClient.get<ApiResponse<{ id_kunjungan: number } | null>>('/api/evaluations/pending'),
  getForm: async (id: number) => {
    const r = await apiClient.get<ApiResponse<EvaluationFormBackendShape>>(`/api/evaluations/${id}`)
    const indikator = r.data.data?.indikator ?? {}
    const indicators: EvaluationIndicator[] = Object.entries(indikator).map(([key, label]) => ({
      id: Number(key),
      label,
      satisfaction: 0,
    }))
    const konsultasiKualitas: KonsultasiKualitas[] = (r.data.data?.konsultasi_kualitas ?? []).map(k => ({
      id: Number(k.id),
      rincian_data: k.rincian_data ?? '',
      status_data: Number(k.status_data),
      kualitas: k.kualitas !== null && k.kualitas !== undefined ? Number(k.kualitas) : null,
    }))
    const formData: EvaluationFormData = { indicators, konsultasiKualitas }
    return { ...r, data: { ...r.data, data: formData } }
  },
  submit: (id: number, data: EvaluationSubmission) => {
    const payload = {
      skor_keseluruhan: data.overall_score,
      kepuasan: Object.fromEntries(data.indicators.map(i => [i.id, i.satisfaction])),
      kualitas_per_konsultasi: data.kualitas_per_konsultasi ?? {},
    }
    return apiClient.post<ApiResponse<null>>(`/api/evaluations/${id}`, payload)
  },
  getResults: (id: number) =>
    apiClient.get<ApiResponse<EvaluationResult>>(`/api/evaluations/${id}/results`),
  getSummary: (params?: { tahun?: string }) =>
    apiClient.get<ApiResponse<EvaluationSummary>>('/api/evaluations/summary', { params }),
}

export interface EvaluationSummaryVisit {
  id_kunjungan: number
  nama: string
  jenis_layanan: string
  date_visit: string
  rating_pengunjung: number | null
  avg_kepentingan: number
  avg_kepuasan: number
  jumlah_indikator: number
}

export interface EvaluationSummaryIndicator {
  indikator_id: number
  avg_kepentingan: number
  avg_kepuasan: number
  responden: number
}

export interface EvaluationSummary {
  visits: EvaluationSummaryVisit[]
  indicators: EvaluationSummaryIndicator[]
  overall: { ikm_score: number; total_responden: number }
  labels: Record<string, string>
}

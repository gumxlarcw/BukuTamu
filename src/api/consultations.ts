import apiClient from './client'
import type { ApiResponse } from '@/types/api'
import type { Visit, ConsultationDataRow } from '@/types/visit'

export const consultationsApi = {
  list: () => apiClient.get<ApiResponse<Visit[]>>('/api/consultations'),
  updateStatus: (id: number, status: string) =>
    apiClient.put<ApiResponse<Visit>>(`/api/consultations/${id}`, { status }),
  call: (id: number) => apiClient.post<ApiResponse<null>>(`/api/consultations/${id}/call`),
  testSound: (id: number) => apiClient.post<ApiResponse<null>>(`/api/consultations/${id}/test-sound`),
  getData: (id: number) =>
    apiClient.get<ApiResponse<ConsultationDataRow[]>>(`/api/consultations/${id}/data`),
  saveData: (id: number, payload: { kebutuhan_data: ConsultationDataRow[]; hasil_konsultasi?: string }) =>
    apiClient.post<ApiResponse<null>>(`/api/consultations/${id}/data`, payload),
}

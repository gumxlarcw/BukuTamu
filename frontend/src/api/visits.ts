import apiClient from './client'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import type { Visit, VisitStatus } from '@/types/visit'

export const visitsApi = {
  list: (params: { q?: string; layanan?: string; status?: string; tahun?: string; bulan?: string; page?: number; limit?: number }) =>
    apiClient.get<PaginatedResponse<Visit>>('/api/visits', { params }),
  get: (id: number) => apiClient.get<ApiResponse<Visit>>(`/api/visits/${id}`),
  create: (data: { id_user: number; jenis_layanan: string[]; layanan_lainnya?: string; sarana: number[]; sarana_lainnya?: string }) =>
    apiClient.post<ApiResponse<Visit>>('/api/visits', data),
  updateStatus: (id: number, status: VisitStatus) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/status`, { status }),
  updateService: (id: number, data: { jenis_layanan: string[]; layanan_lainnya?: string; sarana: number[]; sarana_lainnya?: string }) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/service`, data),
  updateSummary: (id: number, ringkasan: string) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/summary`, { ringkasan }),
  delete: (id: number) =>
    apiClient.delete<ApiResponse<null>>(`/api/visits/${id}`),
}

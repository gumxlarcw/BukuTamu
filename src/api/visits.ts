import apiClient from './client'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import type { Visit, VisitStatus } from '@/types/visit'

export const visitsApi = {
  list: (params: { q?: string; layanan?: string; tahun?: string; bulan?: string; page?: number; limit?: number }) =>
    apiClient.get<PaginatedResponse<Visit>>('/api/visits', { params }),
  get: (id: number) => apiClient.get<ApiResponse<Visit>>(`/api/visits/${id}`),
  create: (data: { guest_id: number; jenis_layanan: string }) =>
    apiClient.post<ApiResponse<Visit>>('/api/visits', data),
  updateStatus: (id: number, status: VisitStatus) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/status`, { status }),
  updateService: (id: number, jenis_layanan: string) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/service`, { jenis_layanan }),
  updateSummary: (id: number, ringkasan: string) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/summary`, { ringkasan }),
}

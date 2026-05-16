import apiClient from './client'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import type { Guest } from '@/types/guest'

export interface GuestVisit {
  id_kunjungan: number
  jenis_layanan: string | null
  date_visit: string
  status: string
  nomor_antrian: string | null
  rating_pengunjung: number | null
}

export const guestsApi = {
  list: (params: { search?: string; page?: number; limit?: number }) =>
    apiClient.get<PaginatedResponse<Guest>>('/api/guests', { params }),
  get: (id: number) => apiClient.get<ApiResponse<Guest>>(`/api/guests/${id}`),
  create: (data: Partial<Guest>) => apiClient.post<ApiResponse<Guest>>('/api/guests', data),
  update: (id: number, data: Partial<Guest>) =>
    apiClient.put<ApiResponse<Guest>>(`/api/guests/${id}`, data),
  delete: (id: number) => apiClient.delete<ApiResponse<null>>(`/api/guests/${id}`),
  // Visit history for a specific guest — used in profile-view modals.
  getVisits: (id: number) =>
    apiClient.get<ApiResponse<GuestVisit[]>>(`/api/guests/${id}/visits`),
}

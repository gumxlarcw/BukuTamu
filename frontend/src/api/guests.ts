import apiClient from './client'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import type { Guest } from '@/types/guest'

export const guestsApi = {
  list: (params: { search?: string; page?: number; limit?: number }) =>
    apiClient.get<PaginatedResponse<Guest>>('/api/guests', { params }),
  get: (id: number) => apiClient.get<ApiResponse<Guest>>(`/api/guests/${id}`),
  create: (data: Partial<Guest>) => apiClient.post<ApiResponse<Guest>>('/api/guests', data),
  update: (id: number, data: Partial<Guest>) =>
    apiClient.put<ApiResponse<Guest>>(`/api/guests/${id}`, data),
  delete: (id: number) => apiClient.delete<ApiResponse<null>>(`/api/guests/${id}`),
}

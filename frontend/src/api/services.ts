import apiClient from './client'
import type { ApiResponse } from '@/types/api'

export interface Service {
  id: string
  name: string
  icon: string
  description: string
}

export const servicesApi = {
  list: () => apiClient.get<ApiResponse<Service[]>>('/api/services'),
}

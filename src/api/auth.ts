import apiClient from './client'
import type { ApiResponse } from '@/types/api'

export interface AuthUser {
  id: number
  username: string
  nama: string
}

export const authApi = {
  check: () => apiClient.get<ApiResponse<AuthUser>>('/api/auth/check'),
  login: (username: string, password: string) =>
    apiClient.post<ApiResponse<AuthUser>>('/api/auth/login', { username, password }),
  logout: () => apiClient.post<ApiResponse<null>>('/api/auth/logout'),
}

import apiClient from './client'
import type { PaginatedResponse } from '@/types/api'

export interface AuditEntry {
  id: number
  admin_user: string
  action: string
  target_type: string
  target_id: number | null
  detail: string | null
  ip_address: string | null
  created_at: string
}

export const auditApi = {
  list: (params: { page?: number; limit?: number }) =>
    apiClient.get<PaginatedResponse<AuditEntry>>('/api/audit', { params }),
}

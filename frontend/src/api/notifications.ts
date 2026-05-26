import apiClient from './client'
import type { ApiResponse } from '@/types/api'

export type NotificationType = 'critical' | 'warning' | 'info'

export interface AppNotification {
  id: string
  type: NotificationType
  title: string
  message: string
  action_url: string
  count?: number
  ts: number
}

export interface NotificationsResponse {
  notifications: AppNotification[]
  count: number
}

export const notificationsApi = {
  list: () => apiClient.get<ApiResponse<NotificationsResponse>>('/api/notifications'),
}

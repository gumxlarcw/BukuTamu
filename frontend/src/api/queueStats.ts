import apiClient from './client'
import type { ApiResponse } from '@/types/api'

export interface QueueStatsAvgWait {
  avg_durasi: number | null
  min_durasi: number | null
  max_durasi: number | null
  total_selesai: number | null
}

export interface QueueStatsHourly { jam: number; jumlah: number }
export interface QueueStatsDaily { hari: string; dow: number; jumlah: number }
export interface QueueStatsMonthly { bulan: number; jumlah: number }
export interface QueueStatsService { jenis_layanan: string; jumlah: number }
export interface QueueStatsStatus { status: string; jumlah: number }

export interface QueueStats {
  avg_wait: QueueStatsAvgWait | null
  hourly: QueueStatsHourly[]
  daily: QueueStatsDaily[]
  monthly: QueueStatsMonthly[]
  services: QueueStatsService[]
  statuses: QueueStatsStatus[]
}

export const queueStatsApi = {
  get: (params: { tahun?: string }) =>
    apiClient.get<ApiResponse<QueueStats>>('/api/queue-stats', { params }),
}

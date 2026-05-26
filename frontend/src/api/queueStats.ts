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
export interface QueueStatsMonthly { bulan: number; jumlah: number; avg_durasi: number | null }
export interface QueueStatsQuarterly { triwulan: number; jumlah: number; selesai: number; avg_durasi: number | null }
export interface QueueStatsService { jenis_layanan: string; jumlah: number }
export interface QueueStatsStatus { status: string; jumlah: number }
export interface QueueStatsSource { source: string; jumlah: number }
export interface QueueStatsSarana { code: number; jumlah: number }
export interface QueueStatsInstansi { nama_instansi: string; kategori_instansi: string | null; jumlah: number }
export interface QueueStatsKategori { kategori_instansi: string; jumlah: number }
export interface QueueStatsGender { gender: string; jumlah: number }

export interface QueueStats {
  avg_wait: QueueStatsAvgWait | null
  total_visits: number
  distinct_visitors: number
  repeat_visitors: number
  hourly: QueueStatsHourly[]
  daily: QueueStatsDaily[]
  monthly: QueueStatsMonthly[]
  quarterly: QueueStatsQuarterly[]
  services: QueueStatsService[]
  statuses: QueueStatsStatus[]
  sources: QueueStatsSource[]
  sarana_dist: QueueStatsSarana[]
  top_instansi: QueueStatsInstansi[]
  kategori_instansi: QueueStatsKategori[]
  gender_dist: QueueStatsGender[]
}

export const queueStatsApi = {
  get: (params: { tahun?: string }) =>
    apiClient.get<ApiResponse<QueueStats>>('/api/queue-stats', { params }),
}

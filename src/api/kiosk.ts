import apiClient from './client'
import type { ApiResponse } from '@/types/api'
import type { GuestFormData } from '@/types/guest'

export interface TicketData {
  id_kunjungan: number
  nomor_antrian: string | null
  nama: string
  jenis_layanan: string
  date_visit: string
}

export interface FaceData {
  id_user: number
  nama: string
  face_descriptor: number[]
}

export const kioskApi = {
  getFaceData: () => apiClient.get<ApiResponse<FaceData[]>>('/api/kiosk/face-data'),
  register: (data: GuestFormData & { foto: string; face_descriptor: number[]; jenis_layanan: string }) =>
    apiClient.post<ApiResponse<{ id_kunjungan: number; id_user: number; nomor_antrian: string | null }>>('/api/kiosk/register', data),
  visit: (data: { id_user: number; jenis_layanan: string }) =>
    apiClient.post<ApiResponse<{ id_kunjungan: number; nomor_antrian: string | null }>>('/api/kiosk/visit', data),
  getTicket: (id: number) => apiClient.get<ApiResponse<TicketData>>(`/api/kiosk/ticket/${id}`),
}

import apiClient from './client'
import type { ApiResponse } from '@/types/api'
import type { GuestFormData } from '@/types/guest'

export interface TicketData {
  id: number
  no_antrian: string | null
  nama: string
  jenis_layanan: string
  tgldatang: string
}

export interface FaceData {
  guest_id: number
  nama: string
  face_descriptor: number[]
}

export const kioskApi = {
  getFaceData: () => apiClient.get<ApiResponse<FaceData[]>>('/api/kiosk/face-data'),
  register: (data: GuestFormData & { foto: string; face_descriptor: number[]; jenis_layanan: string }) =>
    apiClient.post<ApiResponse<{ visit_id: number }>>('/api/kiosk/register', data),
  visit: (data: { guest_id: number; jenis_layanan: string }) =>
    apiClient.post<ApiResponse<{ visit_id: number }>>('/api/kiosk/visit', data),
  getTicket: (id: number) => apiClient.get<ApiResponse<TicketData>>(`/api/kiosk/ticket/${id}`),
}

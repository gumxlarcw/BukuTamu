export type VisitStatus = 'antri' | 'proses' | 'menunggu_evaluasi' | 'selesai'

export interface Visit {
  id_kunjungan: number
  id_user: number
  nama: string
  nama_instansi: string
  jenis_layanan: string
  nomor_antrian: string | null
  status: VisitStatus
  date_visit: string
  durasi_detik: number | null
  selesai_timestamp: string | null
  rating_pengunjung: number | null
}

export interface ConsultationDataRow {
  id?: number
  rincian_data: string
  wilayah_data: string
  tahun_awal: number
  tahun_akhir: number
  level_data: number
  periode_data: number
  status_data: number
  jenis_publikasi: string | null
  judul_publikasi: string | null
  tahun_publikasi: number | null
  digunakan_nasional: number | null
  kualitas: number | null
}

export const LEVEL_DATA_OPTIONS = [
  { value: 1, label: 'Nasional' },
  { value: 2, label: 'Provinsi' },
  { value: 3, label: 'Kabupaten/Kota' },
  { value: 4, label: 'Kecamatan' },
  { value: 5, label: 'Desa/Kelurahan' },
  { value: 6, label: 'Individu' },
  { value: 7, label: 'Lainnya' },
] as const

export const PERIODE_DATA_OPTIONS = [
  { value: 1, label: 'Sepuluh Tahunan' },
  { value: 2, label: 'Lima Tahunan' },
  { value: 3, label: 'Tiga Tahunan' },
  { value: 4, label: 'Tahunan' },
  { value: 5, label: 'Semesteran' },
  { value: 6, label: 'Triwulanan' },
  { value: 7, label: 'Bulanan' },
  { value: 8, label: 'Mingguan' },
  { value: 9, label: 'Harian' },
  { value: 10, label: 'Lainnya' },
] as const

export const STATUS_DATA_OPTIONS = [
  { value: 1, label: 'Ya sesuai' },
  { value: 2, label: 'Ya tidak sesuai' },
  { value: 3, label: 'Tidak diperoleh' },
  { value: 4, label: 'Belum Diperoleh' },
] as const

export const JENIS_PUBLIKASI_OPTIONS = [
  'Publikasi', 'Data Mikro', 'Peta', 'Tabulasi Data', 'Tabel di Website',
] as const

export const SERVICE_OPTIONS = [
  'Perpustakaan',
  'Konsultasi Statistik',
  'Rekomendasi Kegiatan Statistik',
  'Penjualan Produk Statistik',
  'Keperluan Pimpinan',
  'Lainnya',
] as const

export interface DashboardStats {
  total_kunjungan: number
  tamu_unik: number
  jumlah_hari: number
  rata_rata_per_hari: number
  hari_tersibuk: string
  periode_aktif: string
  selesai: number
  antri: number
  tingkat_selesai: number
  rata_rata_durasi: string
  layanan_terbanyak: string
  instansi_terbanyak: string
}

export interface CalendarEvent {
  id: string
  title: string
  start: string
  end?: string
  color: string
}

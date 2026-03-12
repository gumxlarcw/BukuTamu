export interface Guest {
  id: number
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  pendidikan: number
  pekerjaan: number
  kategori_instansi: number
  nama_instansi: string
  face_descriptor: number[] | null
  created_at: string
}

export interface GuestFormData {
  tgldatang: string
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  pendidikan: number
  pekerjaan: number
  kategori_instansi: number
  nama_instansi: string
  pemanfaatan: number
  pengaduan: 'Ya' | 'Tidak'
}

export const PENDIDIKAN_OPTIONS = [
  { value: 1, label: '<=SLTA' },
  { value: 2, label: 'D1/D2/D3' },
  { value: 3, label: 'D4/S1' },
  { value: 4, label: 'S2' },
  { value: 5, label: 'S3' },
] as const

export const PEKERJAAN_OPTIONS = [
  { value: 1, label: 'Pelajar/Mahasiswa' },
  { value: 2, label: 'Peneliti/Dosen' },
  { value: 3, label: 'ASN/TNI/Polri' },
  { value: 4, label: 'Pegawai BUMN/BUMD' },
  { value: 5, label: 'Pegawai Swasta' },
  { value: 6, label: 'Wiraswasta' },
  { value: 7, label: 'Lainnya' },
] as const

export const KATEGORI_INSTANSI_OPTIONS = [
  { value: 1, label: 'Lembaga Negara' },
  { value: 2, label: 'Kementerian & Lembaga Pemerintah' },
  { value: 3, label: 'TNI/POLRI/BIN/Kejaksaan' },
  { value: 4, label: 'Pemerintah Daerah' },
  { value: 5, label: 'Lembaga Internasional' },
  { value: 6, label: 'Lembaga Penelitian & Pendidikan' },
  { value: 7, label: 'BUMN/BUMD' },
  { value: 8, label: 'Swasta' },
  { value: 9, label: 'Lainnya' },
] as const

export const PEMANFAATAN_OPTIONS = [
  { value: 1, label: 'Tugas Sekolah/Kuliah' },
  { value: 2, label: 'Pemerintah' },
  { value: 3, label: 'Komersial' },
  { value: 4, label: 'Penelitian' },
  { value: 5, label: 'Lainnya' },
] as const

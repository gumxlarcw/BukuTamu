export interface Guest {
  id_user: number
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  umur: number
  disabilitas: number
  jenis_disabilitas: number | null
  pendidikan: number
  pekerjaan: number
  pekerjaan_lainnya: string
  kategori_instansi: number
  kategori_lainnya: string
  nama_instansi: string
  pemanfaatan: number
  pemanfaatan_lainnya: string
  pengaduan?: string | null
  registered_via?: string | null
  face_descriptor: number[] | null
  tgldatang: string
}

export interface GuestFormData {
  tgldatang: string
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  umur: number
  disabilitas: number
  jenis_disabilitas: number
  pendidikan: number
  pekerjaan: number
  pekerjaan_lainnya: string
  kategori_instansi: number
  kategori_lainnya: string
  nama_instansi: string
  pemanfaatan: number
  pemanfaatan_lainnya: string
  pengaduan: 'Ya' | 'Tidak'
}

export const PENDIDIKAN_OPTIONS = [
  { value: 1, label: '≤ SLTA/Sederajat' },
  { value: 2, label: 'D1/D2/D3' },
  { value: 3, label: 'D4/S1' },
  { value: 4, label: 'S2' },
  { value: 5, label: 'S3' },
] as const

export const UMUR_OPTIONS = [
  { value: 1, label: '< 17 tahun' },
  { value: 2, label: '17 – 25 tahun' },
  { value: 3, label: '26 – 34 tahun' },
  { value: 4, label: '35 – 44 tahun' },
  { value: 5, label: '45 – 54 tahun' },
  { value: 6, label: '55 – 65 tahun' },
  { value: 7, label: '> 65 tahun' },
] as const

export const DISABILITAS_OPTIONS = [
  { value: 1, label: 'Ya' },
  { value: 2, label: 'Tidak' },
] as const

export const JENIS_DISABILITAS_OPTIONS = [
  { value: 1, label: 'Disabilitas Fisik' },
  { value: 2, label: 'Disabilitas Intelektual' },
  { value: 3, label: 'Disabilitas Mental' },
  { value: 4, label: 'Disabilitas Sensorik' },
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

export const SARANA_OPTIONS = [
  { value: 1, label: 'PST (datang langsung)' },
  { value: 2, label: 'PST Online (pst.bps.go.id)' },
  { value: 4, label: 'Website BPS / AllStats BPS' },
  { value: 9, label: 'Surat / E-mail' },
  { value: 16, label: 'Aplikasi Chat (WA, Telegram, dll.)' },
  { value: 32, label: 'Lainnya' },
  { value: 33, label: 'Ruang Halmahera' },
  { value: 34, label: 'Ruang Vicon' },
  { value: 35, label: 'Ruang Gamalama' },
  { value: 36, label: 'Ruang Pimpinan' },
] as const

export const PEMANFAATAN_OPTIONS = [
  { value: 1, label: 'Tugas Sekolah/Kuliah' },
  { value: 2, label: 'Pemerintahan' },
  { value: 3, label: 'Komersial' },
  { value: 4, label: 'Penelitian' },
  { value: 5, label: 'Lainnya' },
] as const

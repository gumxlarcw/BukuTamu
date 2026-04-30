import type { UserRole } from '@/api/auth'

const PST_SERVICES = [
  'Perpustakaan',
  'Konsultasi Statistik',
  'Rekomendasi Kegiatan Statistik',
  'Penjualan Produk Statistik',
] as const

const RESEPSIONIS_SERVICES = ['Lainnya', 'Keperluan Pimpinan'] as const

const BYPASS_ROLES: UserRole[] = ['superadmin', 'admin', 'operator']

export function parseLayananForRole(jenis_layanan: string | null | undefined): string[] {
  if (!jenis_layanan) return []
  const trimmed = jenis_layanan.trim()
  if (trimmed.startsWith('[')) {
    try {
      const parsed = JSON.parse(trimmed)
      return Array.isArray(parsed) ? parsed.map(String) : [trimmed]
    } catch {
      return [trimmed]
    }
  }
  return [trimmed]
}

/**
 * Cermin dari Api_base::require_layanan_role di backend.
 * Mengembalikan true jika role user boleh menyelesaikan (selesai/menunggu_evaluasi)
 * sebuah visit dengan kombinasi layanan tertentu.
 */
export function canFinalizeLayanan(role: UserRole | undefined, layanan_list: string[]): boolean {
  if (!role) return false
  if (BYPASS_ROLES.includes(role)) return true

  for (const layanan of layanan_list) {
    const isPst = (PST_SERVICES as readonly string[]).includes(layanan)
    const isResep = (RESEPSIONIS_SERVICES as readonly string[]).includes(layanan)

    if (isPst && role !== 'petugas_pst') return false
    if (isResep && role !== 'resepsionis') return false
  }
  return true
}

/**
 * Cermin Api_base::next_status_after_completion. Tentukan status finalisasi:
 * - PST → 'menunggu_evaluasi' (perlu evaluasi di tablet)
 * - Resepsionis (Lainnya, Keperluan Pimpinan) → 'selesai' langsung (skip evaluasi)
 * - Multi-layanan: jika ada PST, dianggap perlu evaluasi.
 */
export function nextStatusAfterCompletion(layanan_list: string[]): 'menunggu_evaluasi' | 'selesai' {
  for (const layanan of layanan_list) {
    if ((PST_SERVICES as readonly string[]).includes(layanan)) {
      return 'menunggu_evaluasi'
    }
  }
  return 'selesai'
}

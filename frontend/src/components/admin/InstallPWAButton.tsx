import { Download } from 'lucide-react'
import { toast } from 'sonner'
import { useInstallPrompt } from '@/hooks/useInstallPrompt'

/**
 * Tombol Install PWA — hanya muncul jika browser fire `beforeinstallprompt`
 * (Chromium/Edge desktop & Android) DAN app belum di-install. Di iOS Safari
 * dan saat sudah installed, return null.
 */
export function InstallPWAButton() {
  const { canInstall, promptInstall } = useInstallPrompt()

  if (!canInstall) return null

  const handleClick = async () => {
    const outcome = await promptInstall()
    if (outcome === 'accepted') {
      toast.success('Aplikasi sedang dipasang…')
    } else if (outcome === 'dismissed') {
      toast.info('Pemasangan dibatalkan')
    }
  }

  return (
    <button
      type="button"
      onClick={handleClick}
      className="admin-nav-item !gap-1.5"
      title="Pasang Admin Buku Tamu 8200 sebagai aplikasi"
    >
      <Download className="w-4 h-4" />
      <span className="hidden sm:inline text-xs font-medium">Install App</span>
    </button>
  )
}

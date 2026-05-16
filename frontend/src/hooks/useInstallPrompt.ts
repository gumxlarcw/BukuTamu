import { useEffect, useState, useCallback } from 'react'

interface BeforeInstallPromptEvent extends Event {
  readonly platforms: string[]
  prompt: () => Promise<void>
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>
}

function isStandalone(): boolean {
  if (typeof window === 'undefined') return false
  return (
    window.matchMedia('(display-mode: standalone)').matches ||
    (window.navigator as Navigator & { standalone?: boolean }).standalone === true
  )
}

/**
 * Tangkap event `beforeinstallprompt` Chromium/Edge agar UI bisa men-trigger
 * dialog install secara manual. Event ditangkap secara dini di main.tsx
 * (sebelum React render) dan disimpan ke `window.__deferredInstallPrompt`,
 * lalu hook ini membacanya — menghindari race condition kalau event fire
 * sebelum komponen yang pakai hook ini mount.
 *
 * Safari iOS tidak fire event ini — user harus pakai Share → "Add to Home Screen".
 */
export function useInstallPrompt() {
  const [installEvent, setInstallEvent] = useState<BeforeInstallPromptEvent | null>(
    () => (typeof window !== 'undefined' ? (window.__deferredInstallPrompt as BeforeInstallPromptEvent | null) : null)
  )
  const [installed, setInstalled] = useState<boolean>(() => isStandalone())

  useEffect(() => {
    const onAvailable = () => {
      setInstallEvent(window.__deferredInstallPrompt as BeforeInstallPromptEvent | null)
    }
    const onInstalled = () => {
      setInstalled(true)
      setInstallEvent(null)
    }

    window.addEventListener('pwa-install-available', onAvailable)
    window.addEventListener('pwa-installed', onInstalled)
    return () => {
      window.removeEventListener('pwa-install-available', onAvailable)
      window.removeEventListener('pwa-installed', onInstalled)
    }
  }, [])

  const promptInstall = useCallback(async () => {
    if (!installEvent) return 'unavailable' as const
    await installEvent.prompt()
    const choice = await installEvent.userChoice
    window.__deferredInstallPrompt = null
    setInstallEvent(null)
    return choice.outcome
  }, [installEvent])

  return {
    canInstall: !!installEvent && !installed,
    installed,
    promptInstall,
  }
}

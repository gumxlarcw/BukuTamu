import { useEffect, useRef, useCallback } from 'react'

export function useInactivityTimeout(
  onTimeout: () => void,
  timeoutMs: number = 120000
): void {
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const onTimeoutRef = useRef(onTimeout)

  // Keep ref updated without re-triggering effect
  useEffect(() => {
    onTimeoutRef.current = onTimeout
  }, [onTimeout])

  const resetTimer = useCallback(() => {
    if (timerRef.current) clearTimeout(timerRef.current)
    timerRef.current = setTimeout(() => {
      onTimeoutRef.current()
    }, timeoutMs)
  }, [timeoutMs])

  useEffect(() => {
    const events = ['mousedown', 'touchstart', 'touchmove', 'touchend', 'keydown', 'scroll'] as const
    const handler = () => resetTimer()

    events.forEach(evt => window.addEventListener(evt, handler, { passive: true }))
    resetTimer()

    return () => {
      events.forEach(evt => window.removeEventListener(evt, handler))
      if (timerRef.current) clearTimeout(timerRef.current)
    }
  }, [resetTimer])
}

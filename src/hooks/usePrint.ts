import { useState, useCallback } from 'react'

interface PrintData {
  [key: string]: unknown
}

interface UsePrintReturn {
  printTicket: (data: PrintData) => Promise<void>
  isPrinting: boolean
  printError: string | null
}

export function usePrint(): UsePrintReturn {
  const [isPrinting, setIsPrinting] = useState(false)
  const [printError, setPrintError] = useState<string | null>(null)

  const printTicket = useCallback(async (data: PrintData) => {
    setIsPrinting(true)
    setPrintError(null)
    try {
      const res = await fetch('http://localhost:5000/print', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!res.ok) {
        throw new Error(`Print server responded with ${res.status}`)
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Print server tidak tersedia'
      setPrintError(msg)
      console.warn('Print error (non-fatal):', msg)
    } finally {
      setIsPrinting(false)
    }
  }, [])

  return { printTicket, isPrinting, printError }
}

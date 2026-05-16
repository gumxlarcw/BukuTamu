import { useState, useCallback } from 'react'
import { toast } from 'sonner'

// Direct fetch ke print server LOKAL di komputer kiosk (bukan via backend).
// Backend `bukutamu.bpsmalut.com` tidak bisa reach printer fisik yang ada di
// kiosk PC, jadi browser kiosk lah yang langsung POST ke printernya sendiri.
// Pattern ini cocok dengan legacy QZ Tray & cetak_antrian.php yang juga
// fetch ke localhost dari browser. Chrome allow HTTPS→http://localhost karena
// localhost dianggap secure context.
const PRINT_SERVER_URL = 'http://localhost:5300/print'

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
      // Dual-field payload: `no` untuk kompat dengan print server legacy yang
      // baca {no: "..."} (lihat tamdes-web-legacy/.../view_selamat_datang.php:329).
      // Print server baru juga happy karena ignore field yang tidak dia pakai.
      const payload = { no: data.nomor_antrian, ...data }
      const res = await fetch(PRINT_SERVER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
      if (!res.ok) {
        throw new Error(`Print server responded ${res.status}`)
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Print server tidak tersedia'
      setPrintError(msg)
      toast.error('Cetak tiket gagal — minta tiket ke petugas', { duration: 6000 })
    } finally {
      setIsPrinting(false)
    }
  }, [])

  return { printTicket, isPrinting, printError }
}

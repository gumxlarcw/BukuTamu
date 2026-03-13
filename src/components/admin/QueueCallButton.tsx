import { useState } from 'react'
import { toast } from 'sonner'
import { consultationsApi } from '@/api/consultations'
import { Button } from '@/components/ui/button'
import { Volume2 } from 'lucide-react'

interface QueueCallButtonProps {
  visitId: number
  nomor_antrian: string | null
}

export function QueueCallButton({ visitId, nomor_antrian }: QueueCallButtonProps) {
  const [loading, setLoading] = useState(false)
  const [called, setCalled] = useState(false)

  const handleCall = async () => {
    if (!nomor_antrian) return
    setLoading(true)
    try {
      await consultationsApi.call(visitId)
      setCalled(true)
      toast.success(`Nomor ${nomor_antrian} dipanggil`)
    } catch {
      toast.error('Gagal memanggil nomor antrian')
    } finally {
      setLoading(false)
    }
  }

  return (
    <Button
      size="sm"
      variant={called ? 'outline' : 'default'}
      className={called ? '' : 'bg-teal-600 hover:bg-teal-700 text-white'}
      onClick={handleCall}
      disabled={!nomor_antrian || loading}
    >
      <Volume2 className="w-3.5 h-3.5 mr-1" />
      {loading ? 'Memanggil...' : called ? 'Panggil Lagi' : 'Panggil'}
    </Button>
  )
}

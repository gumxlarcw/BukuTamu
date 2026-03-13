import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { toast } from 'sonner'
import { guestsApi } from '@/api/guests'
import { visitsApi } from '@/api/visits'
import { ManualEntryForm } from '@/components/admin/ManualEntryForm'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { ArrowLeft, PlusCircle } from 'lucide-react'

export default function ManualEntryPage() {
  const navigate = useNavigate()
  const [selectedGuestId, setSelectedGuestId] = useState<number | null>(null)
  const [selectedService, setSelectedService] = useState('')

  const { data: guestsData, isLoading: guestsLoading } = useQuery({
    queryKey: ['guests-all'],
    queryFn: () => guestsApi.list({ limit: 1000 }).then(r => r.data.data),
  })

  const guests = guestsData ?? []

  const createMutation = useMutation({
    mutationFn: () =>
      visitsApi.create({
        id_user: selectedGuestId!,
        jenis_layanan: selectedService,
      }),
    onSuccess: () => {
      toast.success('Kunjungan manual berhasil ditambahkan')
      navigate('/admin/visits')
    },
    onError: () => toast.error('Gagal menambahkan kunjungan'),
  })

  const isValid = selectedGuestId !== null && selectedService !== ''

  return (
    <div className="max-w-xl space-y-5">
      <div className="flex items-center gap-3">
        <Button
          variant="outline"
          size="sm"
          onClick={() => navigate('/admin/visits')}
        >
          <ArrowLeft className="w-4 h-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold">Tambah Kunjungan Manual</h1>
          <p className="text-muted-foreground text-sm">Daftarkan pengunjung secara manual</p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-base">Form Kunjungan</CardTitle>
        </CardHeader>
        <CardContent>
          {guestsLoading ? (
            <div className="space-y-3">
              <Skeleton className="h-10 rounded-md" />
              <Skeleton className="h-10 rounded-md" />
            </div>
          ) : (
            <ManualEntryForm
              guests={guests}
              selectedGuestId={selectedGuestId}
              selectedService={selectedService}
              onGuestChange={setSelectedGuestId}
              onServiceChange={setSelectedService}
            />
          )}

          <div className="mt-6 flex justify-end gap-3">
            <Button variant="outline" onClick={() => navigate('/admin/visits')}>
              Batal
            </Button>
            <Button
              className="bg-teal-600 hover:bg-teal-700 text-white"
              disabled={!isValid || createMutation.isPending}
              onClick={() => createMutation.mutate()}
            >
              <PlusCircle className="w-4 h-4 mr-2" />
              {createMutation.isPending ? 'Mendaftarkan...' : 'Daftarkan Kunjungan'}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

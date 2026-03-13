import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useQuery, useMutation } from '@tanstack/react-query'
import { toast } from 'sonner'
import { consultationsApi } from '@/api/consultations'
import { visitsApi } from '@/api/visits'
import { ConsultationDataForm } from '@/components/admin/ConsultationDataForm'
import type { ConsultationDataRow } from '@/types/visit'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/shared/StatusBadge'
import { ArrowLeft, Save } from 'lucide-react'

export default function ConsultationFormPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const visitId = Number(id)

  const [rows, setRows] = useState<ConsultationDataRow[]>([])
  const [hasilKonsultasi, setHasilKonsultasi] = useState('')

  // Fetch visit info
  const { data: visit, isLoading: visitLoading } = useQuery({
    queryKey: ['visit', visitId],
    queryFn: () => visitsApi.get(visitId).then(r => r.data.data),
    enabled: !!visitId,
  })

  // Fetch existing consultation data
  const { data: existingData, isLoading: dataLoading } = useQuery({
    queryKey: ['consultation-data', visitId],
    queryFn: () => consultationsApi.getData(visitId).then(r => r.data.data),
    enabled: !!visitId,
  })

  // Populate form with existing data
  useEffect(() => {
    if (existingData && existingData.length > 0) {
      setRows(existingData)
    }
  }, [existingData])

  const saveMutation = useMutation({
    mutationFn: () =>
      consultationsApi.saveData(visitId, {
        kebutuhan_data: rows,
        hasil_konsultasi: hasilKonsultasi || undefined,
      }),
    onSuccess: () => {
      toast.success('Data konsultasi berhasil disimpan')
      navigate('/admin/consultations')
    },
    onError: () => toast.error('Gagal menyimpan data konsultasi'),
  })

  const isLoading = visitLoading || dataLoading

  return (
    <div className="max-w-4xl space-y-5">
      <div className="flex items-center gap-3">
        <Button
          variant="outline"
          size="sm"
          onClick={() => navigate('/admin/consultations')}
        >
          <ArrowLeft className="w-4 h-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold">Form Konsultasi</h1>
          <p className="text-muted-foreground text-sm">Catat kebutuhan data pengunjung</p>
        </div>
      </div>

      {/* Visitor info */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Informasi Pengunjung</CardTitle>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="space-y-2">
              <Skeleton className="h-5 w-48" />
              <Skeleton className="h-4 w-64" />
            </div>
          ) : visit ? (
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
              <div>
                <p className="text-muted-foreground">Nama</p>
                <p className="font-semibold">{visit.nama}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Instansi</p>
                <p className="font-semibold">{visit.nama_instansi}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Layanan</p>
                <p className="font-semibold">{visit.jenis_layanan}</p>
              </div>
              <div>
                <p className="text-muted-foreground">No. Antrian</p>
                <p className="font-semibold">{visit.nomor_antrian ?? '-'}</p>
              </div>
              <div>
                <p className="text-muted-foreground">Status</p>
                <StatusBadge status={visit.status} />
              </div>
            </div>
          ) : (
            <p className="text-muted-foreground text-sm">Data pengunjung tidak ditemukan.</p>
          )}
        </CardContent>
      </Card>

      {/* Consultation data form */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Kebutuhan Data</CardTitle>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="space-y-3">
              <Skeleton className="h-32 rounded-xl" />
              <Skeleton className="h-32 rounded-xl" />
            </div>
          ) : (
            <ConsultationDataForm
              rows={rows}
              hasilKonsultasi={hasilKonsultasi}
              onChange={setRows}
              onHasilChange={setHasilKonsultasi}
            />
          )}
        </CardContent>
      </Card>

      {/* Save button */}
      <div className="flex justify-end gap-3 pb-6">
        <Button variant="outline" onClick={() => navigate('/admin/consultations')}>
          Batal
        </Button>
        <Button
          className="bg-teal-600 hover:bg-teal-700 text-white"
          onClick={() => saveMutation.mutate()}
          disabled={saveMutation.isPending}
        >
          <Save className="w-4 h-4 mr-2" />
          {saveMutation.isPending ? 'Menyimpan...' : 'Simpan Data'}
        </Button>
      </div>
    </div>
  )
}

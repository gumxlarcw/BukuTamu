import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useMutation } from '@tanstack/react-query'
import { toast } from 'sonner'
import { guestsApi } from '@/api/guests'
import { VisitorForm } from '@/components/kiosk/VisitorForm'
import type { GuestFormData } from '@/types/guest'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft } from 'lucide-react'

const EMPTY_FORM: GuestFormData = {
  tgldatang: '',
  nama: '',
  email: '',
  notel: '',
  jeniskelamin: 'Laki-laki',
  pendidikan: 0,
  pekerjaan: 0,
  kategori_instansi: 0,
  nama_instansi: '',
  pemanfaatan: 0,
  pengaduan: 'Tidak',
}

export default function GuestAddPage() {
  const navigate = useNavigate()
  const [formData, setFormData] = useState<GuestFormData>(EMPTY_FORM)

  const isValid =
    formData.nama.trim() !== '' &&
    formData.email.trim() !== '' &&
    formData.notel.trim() !== '' &&
    formData.pendidikan > 0 &&
    formData.pekerjaan > 0 &&
    formData.kategori_instansi > 0 &&
    formData.nama_instansi.trim() !== '' &&
    formData.pemanfaatan > 0

  const createMutation = useMutation({
    mutationFn: () => guestsApi.create(formData),
    onSuccess: () => {
      toast.success('Tamu berhasil ditambahkan')
      navigate('/admin/guests')
    },
    onError: () => toast.error('Gagal menambahkan tamu'),
  })

  return (
    <div className="max-w-2xl space-y-5">
      <div className="flex items-center gap-3">
        <Button
          variant="outline"
          size="sm"
          onClick={() => navigate('/admin/guests')}
        >
          <ArrowLeft className="w-4 h-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold">Tambah Tamu Baru</h1>
          <p className="text-muted-foreground text-sm">Isi data pengunjung secara manual</p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-base">Data Pengunjung</CardTitle>
        </CardHeader>
        <CardContent>
          {/* VisitorForm uses kiosk styles, we wrap it in a dark container to keep consistency */}
          <div className="bg-gray-800 rounded-xl p-6">
            <VisitorForm value={formData} onChange={setFormData} />
          </div>

          <div className="mt-6 flex justify-end gap-3">
            <Button variant="outline" onClick={() => navigate('/admin/guests')}>
              Batal
            </Button>
            <Button
              className="bg-teal-600 hover:bg-teal-700 text-white"
              disabled={!isValid || createMutation.isPending}
              onClick={() => createMutation.mutate()}
            >
              {createMutation.isPending ? 'Menyimpan...' : 'Simpan Tamu'}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'
import { guestsApi } from '@/api/guests'
import type { Guest } from '@/types/guest'
import {
  PENDIDIKAN_OPTIONS,
  PEKERJAAN_OPTIONS,
  KATEGORI_INSTANSI_OPTIONS,
} from '@/types/guest'
import { GuestTable } from '@/components/admin/GuestTable'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Skeleton } from '@/components/ui/skeleton'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog'
import { Search, UserPlus, ChevronLeft, ChevronRight } from 'lucide-react'

export default function GuestListPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const [searchInput, setSearchInput] = useState('')
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [limit, setLimit] = useState(10)

  // Debounce search
  useEffect(() => {
    const t = setTimeout(() => {
      setSearch(searchInput)
      setPage(1)
    }, 400)
    return () => clearTimeout(t)
  }, [searchInput])

  const { data, isLoading } = useQuery({
    queryKey: ['guests', { search, page, limit }],
    queryFn: () => guestsApi.list({ search, page, limit }).then(r => r.data),
  })

  const guests = data?.data ?? []
  const pagination = data?.pagination

  // Edit dialog state
  const [editGuest, setEditGuest] = useState<Guest | null>(null)
  const [editForm, setEditForm] = useState<Partial<Guest>>({})

  const openEdit = useCallback((guest: Guest) => {
    setEditGuest(guest)
    setEditForm({
      nama: guest.nama,
      email: guest.email,
      notel: guest.notel,
      jeniskelamin: guest.jeniskelamin,
      pendidikan: guest.pendidikan,
      pekerjaan: guest.pekerjaan,
      kategori_instansi: guest.kategori_instansi,
      nama_instansi: guest.nama_instansi,
    })
  }, [])

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Guest> }) =>
      guestsApi.update(id, data),
    onSuccess: () => {
      toast.success('Data tamu berhasil diperbarui')
      setEditGuest(null)
      queryClient.invalidateQueries({ queryKey: ['guests'] })
    },
    onError: () => toast.error('Gagal memperbarui data tamu'),
  })

  // Delete confirmation
  const [deleteId, setDeleteId] = useState<number | null>(null)

  const deleteMutation = useMutation({
    mutationFn: (id: number) => guestsApi.delete(id),
    onSuccess: () => {
      toast.success('Tamu berhasil dihapus')
      setDeleteId(null)
      queryClient.invalidateQueries({ queryKey: ['guests'] })
    },
    onError: () => toast.error('Gagal menghapus tamu'),
  })

  const handleSaveEdit = () => {
    if (!editGuest) return
    updateMutation.mutate({ id: editGuest.id_user, data: editForm })
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <h1 className="text-2xl font-bold">Daftar Tamu</h1>
          <p className="text-muted-foreground text-sm">Kelola data pengunjung PST</p>
        </div>
        <Button
          className="bg-teal-600 hover:bg-teal-700 text-white"
          onClick={() => navigate('/admin/guests/add')}
        >
          <UserPlus className="w-4 h-4 mr-2" />
          Tambah Tamu
        </Button>
      </div>

      {/* Search bar */}
      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <Input
          placeholder="Cari nama, email..."
          value={searchInput}
          onChange={e => setSearchInput(e.target.value)}
          className="pl-9"
        />
      </div>

      {/* Table */}
      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 5 }).map((_, i) => (
            <Skeleton key={i} className="h-10 rounded-md" />
          ))}
        </div>
      ) : (
        <GuestTable guests={guests} onEdit={openEdit} onDelete={setDeleteId} />
      )}

      {/* Pagination */}
      {pagination && (
        <div className="flex items-center justify-between gap-4 flex-wrap">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <span>Tampilkan</span>
            <select
              value={limit}
              onChange={e => { setLimit(Number(e.target.value)); setPage(1) }}
              className="border rounded px-2 py-1 text-sm bg-background"
            >
              {[10, 25, 50, 100].map(n => (
                <option key={n} value={n}>{n}</option>
              ))}
            </select>
            <span>per halaman</span>
            <span className="ml-2">
              Total: <strong>{pagination.total}</strong>
            </span>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              disabled={page <= 1}
              onClick={() => setPage(p => p - 1)}
            >
              <ChevronLeft className="w-4 h-4" />
            </Button>
            <span className="text-sm">
              {page} / {pagination.totalPages}
            </span>
            <Button
              variant="outline"
              size="sm"
              disabled={page >= pagination.totalPages}
              onClick={() => setPage(p => p + 1)}
            >
              <ChevronRight className="w-4 h-4" />
            </Button>
          </div>
        </div>
      )}

      {/* Edit dialog */}
      <Dialog open={!!editGuest} onOpenChange={open => !open && setEditGuest(null)}>
        <DialogContent className="sm:max-w-lg max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Edit Data Tamu</DialogTitle>
          </DialogHeader>
          <div className="space-y-3 py-2">
            <div className="space-y-1">
              <Label>Nama</Label>
              <Input
                value={editForm.nama ?? ''}
                onChange={e => setEditForm(f => ({ ...f, nama: e.target.value }))}
              />
            </div>
            <div className="space-y-1">
              <Label>Email</Label>
              <Input
                type="email"
                value={editForm.email ?? ''}
                onChange={e => setEditForm(f => ({ ...f, email: e.target.value }))}
              />
            </div>
            <div className="space-y-1">
              <Label>No. Telepon</Label>
              <Input
                value={editForm.notel ?? ''}
                onChange={e => setEditForm(f => ({ ...f, notel: e.target.value }))}
              />
            </div>
            <div className="space-y-1">
              <Label>Jenis Kelamin</Label>
              <select
                value={editForm.jeniskelamin ?? ''}
                onChange={e => setEditForm(f => ({ ...f, jeniskelamin: e.target.value as 'Laki-laki' | 'Perempuan' }))}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            <div className="space-y-1">
              <Label>Pendidikan</Label>
              <select
                value={editForm.pendidikan ?? ''}
                onChange={e => setEditForm(f => ({ ...f, pendidikan: Number(e.target.value) }))}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {PENDIDIKAN_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
            <div className="space-y-1">
              <Label>Pekerjaan</Label>
              <select
                value={editForm.pekerjaan ?? ''}
                onChange={e => setEditForm(f => ({ ...f, pekerjaan: Number(e.target.value) }))}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {PEKERJAAN_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
            <div className="space-y-1">
              <Label>Kategori Instansi</Label>
              <select
                value={editForm.kategori_instansi ?? ''}
                onChange={e => setEditForm(f => ({ ...f, kategori_instansi: Number(e.target.value) }))}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {KATEGORI_INSTANSI_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
            <div className="space-y-1">
              <Label>Nama Instansi</Label>
              <Input
                value={editForm.nama_instansi ?? ''}
                onChange={e => setEditForm(f => ({ ...f, nama_instansi: e.target.value }))}
              />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setEditGuest(null)}>
              Batal
            </Button>
            <Button
              className="bg-teal-600 hover:bg-teal-700 text-white"
              onClick={handleSaveEdit}
              disabled={updateMutation.isPending}
            >
              {updateMutation.isPending ? 'Menyimpan...' : 'Simpan'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete confirmation dialog */}
      <Dialog open={deleteId !== null} onOpenChange={open => !open && setDeleteId(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Konfirmasi Hapus</DialogTitle>
          </DialogHeader>
          <p className="text-sm text-muted-foreground py-2">
            Apakah Anda yakin ingin menghapus tamu ini? Tindakan ini tidak dapat dibatalkan.
          </p>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteId(null)}>
              Batal
            </Button>
            <Button
              variant="destructive"
              onClick={() => deleteId !== null && deleteMutation.mutate(deleteId)}
              disabled={deleteMutation.isPending}
            >
              {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}

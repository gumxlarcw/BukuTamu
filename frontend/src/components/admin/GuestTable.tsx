import type { Guest } from '@/types/guest'
import { PENDIDIKAN_OPTIONS } from '@/types/guest'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Eye, Pencil, Trash2 } from 'lucide-react'

interface GuestTableProps {
  guests: Guest[]
  onView: (guest: Guest) => void
  onEdit: (guest: Guest) => void
  onDelete: (id: number) => void
  canDelete?: boolean
}

function getPendidikanLabel(value: number): string {
  return PENDIDIKAN_OPTIONS.find(o => o.value === value)?.label ?? String(value)
}

export function GuestTable({ guests, onView, onEdit, onDelete, canDelete = true }: GuestTableProps) {
  if (guests.length === 0) {
    return (
      <div className="text-center py-12 text-muted-foreground">
        Tidak ada data tamu ditemukan.
      </div>
    )
  }

  return (
    <div className="rounded-md border overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead className="w-12">No</TableHead>
            <TableHead>Nama</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Telepon</TableHead>
            <TableHead>JK</TableHead>
            <TableHead>Pendidikan</TableHead>
            <TableHead>Instansi</TableHead>
            <TableHead>Tgl Daftar</TableHead>
            <TableHead className="text-right">Aksi</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {guests.map((guest, idx) => (
            <TableRow key={guest.id_user}>
              <TableCell className="text-muted-foreground">{idx + 1}</TableCell>
              <TableCell className="font-medium">{guest.nama}</TableCell>
              <TableCell className="text-sm text-muted-foreground">{guest.email}</TableCell>
              <TableCell className="text-sm text-muted-foreground">{guest.notel}</TableCell>
              <TableCell className="text-sm">{guest.jeniskelamin === 'Laki-laki' ? 'L' : 'P'}</TableCell>
              <TableCell className="text-sm">{getPendidikanLabel(guest.pendidikan)}</TableCell>
              <TableCell className="text-sm">{guest.nama_instansi}</TableCell>
              <TableCell className="text-sm text-muted-foreground">{guest.tgldatang}</TableCell>
              <TableCell className="text-right">
                <div className="flex items-center justify-end gap-1.5">
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onView(guest)}
                    title="Lihat Detail"
                  >
                    <Eye className="w-3.5 h-3.5" />
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onEdit(guest)}
                    title="Edit"
                  >
                    <Pencil className="w-3.5 h-3.5" />
                  </Button>
                  {canDelete && (
                    <Button
                      size="sm"
                      variant="outline"
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                      onClick={() => onDelete(guest.id_user)}
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </Button>
                  )}
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

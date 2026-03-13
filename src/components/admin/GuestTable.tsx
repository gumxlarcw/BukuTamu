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
import { Pencil, Trash2 } from 'lucide-react'

interface GuestTableProps {
  guests: Guest[]
  onEdit: (guest: Guest) => void
  onDelete: (id: number) => void
}

function getPendidikanLabel(value: number): string {
  return PENDIDIKAN_OPTIONS.find(o => o.value === value)?.label ?? String(value)
}

export function GuestTable({ guests, onEdit, onDelete }: GuestTableProps) {
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
            <TableHead>Jenis Kelamin</TableHead>
            <TableHead>Pendidikan</TableHead>
            <TableHead>Instansi</TableHead>
            <TableHead className="text-right">Aksi</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {guests.map((guest, idx) => (
            <TableRow key={guest.id_user}>
              <TableCell className="text-muted-foreground">{idx + 1}</TableCell>
              <TableCell className="font-medium">{guest.nama}</TableCell>
              <TableCell className="text-sm text-muted-foreground">{guest.email}</TableCell>
              <TableCell>{guest.jeniskelamin}</TableCell>
              <TableCell>{getPendidikanLabel(guest.pendidikan)}</TableCell>
              <TableCell className="max-w-[200px] truncate">{guest.nama_instansi}</TableCell>
              <TableCell className="text-right">
                <div className="flex items-center justify-end gap-2">
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onEdit(guest)}
                  >
                    <Pencil className="w-3.5 h-3.5" />
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    onClick={() => onDelete(guest.id_user)}
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

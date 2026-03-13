import type { ConsultationDataRow } from '@/types/visit'
import {
  LEVEL_DATA_OPTIONS,
  PERIODE_DATA_OPTIONS,
  STATUS_DATA_OPTIONS,
  JENIS_PUBLIKASI_OPTIONS,
} from '@/types/visit'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Plus, Trash2 } from 'lucide-react'

function emptyRow(): ConsultationDataRow {
  return {
    rincian_data: '',
    wilayah_data: '',
    tahun_awal: new Date().getFullYear(),
    tahun_akhir: new Date().getFullYear(),
    level_data: 1,
    periode_data: 4,
    status_data: 4,
    jenis_publikasi: null,
    judul_publikasi: null,
    tahun_publikasi: null,
    digunakan_nasional: null,
    kualitas: null,
  }
}

interface ConsultationDataFormProps {
  rows: ConsultationDataRow[]
  hasilKonsultasi: string
  onChange: (rows: ConsultationDataRow[]) => void
  onHasilChange: (val: string) => void
}

export function ConsultationDataForm({
  rows,
  hasilKonsultasi,
  onChange,
  onHasilChange,
}: ConsultationDataFormProps) {
  const updateRow = (index: number, patch: Partial<ConsultationDataRow>) => {
    const updated = rows.map((r, i) => (i === index ? { ...r, ...patch } : r))
    onChange(updated)
  }

  const removeRow = (index: number) => {
    onChange(rows.filter((_, i) => i !== index))
  }

  const addRow = () => {
    onChange([...rows, emptyRow()])
  }

  const showPublikasi = (status: number) => status === 1 || status === 2

  return (
    <div className="space-y-6">
      {rows.map((row, idx) => (
        <div key={idx} className="border rounded-xl p-4 space-y-3 bg-muted/20">
          <div className="flex items-center justify-between">
            <p className="font-semibold text-sm">Data #{idx + 1}</p>
            <Button
              size="sm"
              variant="ghost"
              className="text-red-600 hover:text-red-700 hover:bg-red-50"
              onClick={() => removeRow(idx)}
            >
              <Trash2 className="w-4 h-4" />
            </Button>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div className="space-y-1">
              <Label>Rincian Data</Label>
              <Input
                value={row.rincian_data}
                onChange={e => updateRow(idx, { rincian_data: e.target.value })}
                placeholder="Judul / rincian data yang diminta"
              />
            </div>
            <div className="space-y-1">
              <Label>Wilayah Data</Label>
              <Input
                value={row.wilayah_data}
                onChange={e => updateRow(idx, { wilayah_data: e.target.value })}
                placeholder="Provinsi / Kab / Kota"
              />
            </div>
            <div className="space-y-1">
              <Label>Tahun Awal</Label>
              <Input
                type="number"
                value={row.tahun_awal}
                onChange={e => updateRow(idx, { tahun_awal: Number(e.target.value) })}
              />
            </div>
            <div className="space-y-1">
              <Label>Tahun Akhir</Label>
              <Input
                type="number"
                value={row.tahun_akhir}
                onChange={e => updateRow(idx, { tahun_akhir: Number(e.target.value) })}
              />
            </div>
            <div className="space-y-1">
              <Label>Level Data</Label>
              <select
                value={row.level_data}
                onChange={e => updateRow(idx, { level_data: Number(e.target.value) })}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {LEVEL_DATA_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
            <div className="space-y-1">
              <Label>Periode Data</Label>
              <select
                value={row.periode_data}
                onChange={e => updateRow(idx, { periode_data: Number(e.target.value) })}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {PERIODE_DATA_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
            <div className="space-y-1 sm:col-span-2">
              <Label>Status Data</Label>
              <select
                value={row.status_data}
                onChange={e => updateRow(idx, { status_data: Number(e.target.value) })}
                className="w-full border rounded px-3 py-2 text-sm bg-background"
              >
                {STATUS_DATA_OPTIONS.map(o => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </div>
          </div>

          {/* Conditional fields when status_data is 1 or 2 (Ya sesuai / Ya tidak sesuai) */}
          {showPublikasi(row.status_data) && (
            <div className="border-t pt-3 space-y-3">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                Detail Publikasi
              </p>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div className="space-y-1">
                  <Label>Jenis Publikasi</Label>
                  <select
                    value={row.jenis_publikasi ?? ''}
                    onChange={e => updateRow(idx, { jenis_publikasi: e.target.value || null })}
                    className="w-full border rounded px-3 py-2 text-sm bg-background"
                  >
                    <option value="">-- Pilih --</option>
                    {JENIS_PUBLIKASI_OPTIONS.map(o => (
                      <option key={o} value={o}>{o}</option>
                    ))}
                  </select>
                </div>
                <div className="space-y-1">
                  <Label>Tahun Publikasi</Label>
                  <Input
                    type="number"
                    value={row.tahun_publikasi ?? ''}
                    onChange={e =>
                      updateRow(idx, {
                        tahun_publikasi: e.target.value ? Number(e.target.value) : null,
                      })
                    }
                  />
                </div>
                <div className="space-y-1 sm:col-span-2">
                  <Label>Judul Publikasi</Label>
                  <Input
                    value={row.judul_publikasi ?? ''}
                    onChange={e => updateRow(idx, { judul_publikasi: e.target.value || null })}
                    placeholder="Judul publikasi"
                  />
                </div>
                <div className="space-y-1">
                  <Label>Digunakan Nasional</Label>
                  <select
                    value={row.digunakan_nasional ?? ''}
                    onChange={e =>
                      updateRow(idx, {
                        digunakan_nasional: e.target.value ? Number(e.target.value) : null,
                      })
                    }
                    className="w-full border rounded px-3 py-2 text-sm bg-background"
                  >
                    <option value="">-- Pilih --</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                  </select>
                </div>
                <div className="space-y-1">
                  <Label>Kualitas (1-10)</Label>
                  <Input
                    type="number"
                    min={1}
                    max={10}
                    value={row.kualitas ?? ''}
                    onChange={e =>
                      updateRow(idx, {
                        kualitas: e.target.value ? Number(e.target.value) : null,
                      })
                    }
                  />
                </div>
              </div>
            </div>
          )}
        </div>
      ))}

      <Button
        variant="outline"
        onClick={addRow}
        className="w-full border-dashed"
      >
        <Plus className="w-4 h-4 mr-2" />
        Tambah Data
      </Button>

      {/* Hasil konsultasi */}
      <div className="space-y-1">
        <Label htmlFor="hasil_konsultasi" className="text-sm font-semibold">
          Hasil / Ringkasan Konsultasi
        </Label>
        <textarea
          id="hasil_konsultasi"
          rows={4}
          className="w-full border rounded-md px-3 py-2 text-sm bg-background resize-none focus:outline-none focus:ring-2 focus:ring-ring"
          placeholder="Catatan hasil konsultasi..."
          value={hasilKonsultasi}
          onChange={e => onHasilChange(e.target.value)}
        />
      </div>
    </div>
  )
}

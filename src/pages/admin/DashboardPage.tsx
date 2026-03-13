import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import { dashboardApi } from '@/api/dashboard'
import { StatsCard } from '@/components/admin/StatsCard'
import { Skeleton } from '@/components/ui/skeleton'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

function StatsSkeleton() {
  return (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      {Array.from({ length: 12 }).map((_, i) => (
        <Skeleton key={i} className="h-20 rounded-xl" />
      ))}
    </div>
  )
}

export default function DashboardPage() {
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')
  const [filterParams, setFilterParams] = useState<{ date_from?: string; date_to?: string }>({})

  const { data: stats, isLoading: statsLoading } = useQuery({
    queryKey: ['dashboard-stats', filterParams],
    queryFn: () => dashboardApi.stats(filterParams).then(r => r.data.data),
  })

  const { data: events, isLoading: eventsLoading } = useQuery({
    queryKey: ['dashboard-events'],
    queryFn: () => dashboardApi.events().then(r => r.data.data),
  })

  const handleFilter = () => {
    setFilterParams({
      date_from: dateFrom || undefined,
      date_to: dateTo || undefined,
    })
  }

  const handleReset = () => {
    setDateFrom('')
    setDateTo('')
    setFilterParams({})
  }

  const statsItems = stats
    ? [
        { label: 'Total Kunjungan', value: stats.total_kunjungan, icon: '👥' },
        { label: 'Tamu Unik', value: stats.tamu_unik, icon: '🧑' },
        { label: 'Jumlah Hari', value: stats.jumlah_hari, icon: '📅' },
        { label: 'Rata-rata/Hari', value: stats.rata_rata_per_hari, icon: '📊' },
        { label: 'Hari Tersibuk', value: stats.hari_tersibuk, icon: '🔥' },
        { label: 'Periode Aktif', value: stats.periode_aktif, icon: '📆' },
        { label: 'Selesai', value: stats.selesai, icon: '✅' },
        { label: 'Antri', value: stats.antri, icon: '⏳' },
        { label: 'Tingkat Selesai', value: `${stats.tingkat_selesai}%`, icon: '📈' },
        { label: 'Rata-rata Durasi', value: stats.rata_rata_durasi, icon: '⏱️' },
        { label: 'Layanan Terbanyak', value: stats.layanan_terbanyak, icon: '🏆' },
        { label: 'Instansi Terbanyak', value: stats.instansi_terbanyak, icon: '🏢' },
      ]
    : []

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Dashboard</h1>
        <p className="text-muted-foreground text-sm mt-1">Statistik Pelayanan Statistik Terpadu</p>
      </div>

      {/* Date filter */}
      <Card>
        <CardContent className="py-4">
          <div className="flex flex-wrap items-end gap-4">
            <div className="space-y-1">
              <Label htmlFor="date_from">Dari Tanggal</Label>
              <Input
                id="date_from"
                type="date"
                value={dateFrom}
                onChange={e => setDateFrom(e.target.value)}
                className="w-44"
              />
            </div>
            <div className="space-y-1">
              <Label htmlFor="date_to">Sampai Tanggal</Label>
              <Input
                id="date_to"
                type="date"
                value={dateTo}
                onChange={e => setDateTo(e.target.value)}
                className="w-44"
              />
            </div>
            <Button onClick={handleFilter} className="bg-teal-600 hover:bg-teal-700 text-white">
              Filter
            </Button>
            <Button variant="outline" onClick={handleReset}>
              Reset
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Stats grid */}
      {statsLoading ? (
        <StatsSkeleton />
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {statsItems.map((item) => (
            <StatsCard key={item.label} label={item.label} value={item.value} icon={item.icon} />
          ))}
        </div>
      )}

      {/* Calendar */}
      <Card>
        <CardHeader>
          <CardTitle>Kalender Kunjungan</CardTitle>
        </CardHeader>
        <CardContent>
          {eventsLoading ? (
            <Skeleton className="h-64 rounded-xl" />
          ) : (
            <FullCalendar
              plugins={[dayGridPlugin]}
              initialView="dayGridMonth"
              events={events ?? []}
              locale="id"
              headerToolbar={{
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek',
              }}
              height="auto"
            />
          )}
        </CardContent>
      </Card>
    </div>
  )
}

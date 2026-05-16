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
import { Users, User, CalendarDays, BarChart3, Flame, Calendar, CheckCircle, Hourglass, TrendingUp, Timer, Trophy, Building2 } from 'lucide-react'

function StatsSkeleton() {
  return (
    <div className="grid grid-cols-2 gap-3">
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
        { label: 'Total Kunjungan', value: stats.total_kunjungan, icon: <Users className="w-5 h-5" /> },
        { label: 'Tamu Unik', value: stats.tamu_unik, icon: <User className="w-5 h-5" /> },
        { label: 'Jumlah Hari', value: stats.jumlah_hari, icon: <CalendarDays className="w-5 h-5" /> },
        { label: 'Rata-rata/Hari', value: stats.rata_rata_per_hari, icon: <BarChart3 className="w-5 h-5" /> },
        { label: 'Hari Tersibuk', value: stats.hari_tersibuk, icon: <Flame className="w-5 h-5" /> },
        { label: 'Periode Aktif', value: stats.periode_aktif, icon: <Calendar className="w-5 h-5" /> },
        { label: 'Selesai', value: stats.selesai, icon: <CheckCircle className="w-5 h-5" /> },
        { label: 'Antri', value: stats.antri, icon: <Hourglass className="w-5 h-5" /> },
        { label: 'Tingkat Selesai', value: `${stats.tingkat_selesai}%`, icon: <TrendingUp className="w-5 h-5" /> },
        { label: 'Rata-rata Durasi', value: stats.rata_rata_durasi, icon: <Timer className="w-5 h-5" /> },
        { label: 'Layanan Terbanyak', value: stats.layanan_terbanyak, icon: <Trophy className="w-5 h-5" /> },
        { label: 'Instansi Terbanyak', value: stats.instansi_terbanyak, icon: <Building2 className="w-5 h-5" /> },
      ]
    : []

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <h1 className="admin-h1">Dashboard</h1>
          <p className="admin-subtitle">Ringkasan Data BPS Provinsi Maluku Utara</p>
        </div>
        {/* Date filter — compact inline */}
        <div className="flex flex-wrap items-end gap-3">
          <div className="space-y-1">
            <Label htmlFor="date_from">Dari</Label>
            <Input id="date_from" type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className="w-36 h-9" />
          </div>
          <div className="space-y-1">
            <Label htmlFor="date_to">Sampai</Label>
            <Input id="date_to" type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className="w-36 h-9" />
          </div>
          <Button size="sm" onClick={handleFilter} className="bg-orange-600 hover:bg-orange-700 text-white">Filter</Button>
          <Button size="sm" variant="outline" onClick={handleReset}>Reset</Button>
        </div>
      </div>

      {/* Two-column: stats left, calendar right */}
      <div className="grid grid-cols-1 xl:grid-cols-[minmax(0,480px)_1fr] gap-5">
        {/* Left — Stats cards */}
        <div>
          {statsLoading ? (
            <StatsSkeleton />
          ) : (
            <div className="grid grid-cols-2 gap-3">
              {statsItems.map((item, i) => (
                <StatsCard key={item.label} label={item.label} value={item.value} icon={item.icon} accent={i >= 6 ? 'secondary' : 'primary'} />
              ))}
            </div>
          )}
        </div>

        {/* Right — Calendar */}
        <div className="admin-card p-5">
          <h2 className="text-sm font-bold mb-3 text-[--admin-text]">Kalender Kunjungan</h2>
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
                right: '',
              }}
              height="auto"
            />
          )}
        </div>
      </div>
    </div>
  )
}

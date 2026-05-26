import { Info, GitBranch, Calendar, Server, Shield, Users, ExternalLink } from 'lucide-react'

const APP_DESCRIPTION =
  'Sistem pencatatan kunjungan Pelayanan Statistik Terpadu (PST) BPS Provinsi Maluku Utara yang terintegrasi dengan instrumen Survei Kebutuhan Data (SKD) dan modul evaluasi kepuasan layanan. Mengganti buku tamu fisik dan kuesioner kertas dengan alur digital tunggal — biodata pengunjung di-input satu kali lalu ter-link ke seluruh tahap kunjungan, konsultasi, dan evaluasi.'

const FEATURES = [
  'Pencatatan kunjungan dengan pemindaian wajah dan alur cadangan pencarian manual',
  'Antrian otomatis dengan prefix per jenis layanan (P, K, R, J untuk grup SKD; D untuk DTSEN)',
  'Soft-correct gate tiga lapis untuk layanan SKD yang wajib evaluasi',
  '16 indikator evaluasi IKM skala 1–10 dengan submit lewat tablet kiosk',
  'Whitelist sarana per grup layanan (SKD / DTSEN / Resepsionis)',
  'DELETE cascade dengan audit log untuk penghapusan kunjungan oleh admin',
  'Continuation token HMAC untuk endpoint kiosk publik (5 menit profile, 10 menit eval)',
  'Laporan SKD, IKM, rekap kunjungan, dan responden tahunan dengan export CSV',
]

const TECH = [
  { label: 'Frontend', value: 'React 19 + TypeScript + Vite 8 + Tailwind v4' },
  { label: 'Backend', value: 'CodeIgniter 3 (HMVC) — PHP 7.4 FPM' },
  { label: 'Database', value: 'MySQL (db_tamdes)' },
  { label: 'Print server', value: 'Node + escpos, dijalankan lokal di tiap kiosk PC pada port 5300' },
  { label: 'Auth', value: 'JWT cookie httpOnly + role-based access (operator, resepsionis, petugas_pst, admin, superadmin)' },
]

function formatBuildDate(iso: string): string {
  try {
    const d = new Date(iso)
    return d.toLocaleString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      timeZoneName: 'short',
    })
  } catch {
    return iso
  }
}

export default function AboutPage() {
  const version = typeof __APP_VERSION__ !== 'undefined' ? __APP_VERSION__ : '0.0.0'
  const buildDate = typeof __BUILD_DATE__ !== 'undefined' ? __BUILD_DATE__ : ''

  return (
    <div className="space-y-6 max-w-4xl">
      <div>
        <h1 className="admin-h1">Tentang Aplikasi</h1>
        <p className="admin-subtitle">Informasi versi, deskripsi, dan komponen sistem</p>
      </div>

      {/* Header card: app name + version */}
      <div className="rounded-xl border bg-card p-6">
        <div className="flex items-start gap-4">
          <div className="rounded-lg bg-orange-500/10 p-3 shrink-0">
            <Info className="w-6 h-6 text-orange-500" />
          </div>
          <div className="min-w-0 flex-1">
            <h2 className="text-xl font-bold">Buku Tamu Digital PST</h2>
            <p className="text-sm text-muted-foreground mt-0.5">BPS Provinsi Maluku Utara</p>
            <div className="flex flex-wrap gap-3 mt-3 text-xs">
              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-500/10 text-orange-600 font-medium">
                <GitBranch className="w-3 h-3" />
                v{version}
              </span>
              {buildDate && (
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-muted text-muted-foreground">
                  <Calendar className="w-3 h-3" />
                  Build: {formatBuildDate(buildDate)}
                </span>
              )}
            </div>
          </div>
        </div>
        <p className="mt-5 text-sm leading-relaxed text-muted-foreground">
          {APP_DESCRIPTION}
        </p>
      </div>

      {/* Features */}
      <section className="rounded-xl border bg-card p-6">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-4 flex items-center gap-2">
          <Shield className="w-4 h-4" />
          Fitur Utama
        </h3>
        <ul className="space-y-2.5">
          {FEATURES.map((f, i) => (
            <li key={i} className="flex items-start gap-2.5 text-sm">
              <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0" />
              <span>{f}</span>
            </li>
          ))}
        </ul>
      </section>

      {/* Tech stack */}
      <section className="rounded-xl border bg-card p-6">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-4 flex items-center gap-2">
          <Server className="w-4 h-4" />
          Komponen Sistem
        </h3>
        <dl className="space-y-3">
          {TECH.map(t => (
            <div key={t.label} className="grid grid-cols-1 sm:grid-cols-[140px_1fr] gap-1 sm:gap-3 text-sm">
              <dt className="font-medium text-muted-foreground">{t.label}</dt>
              <dd>{t.value}</dd>
            </div>
          ))}
        </dl>
      </section>

      {/* Penyusun & kontak */}
      <section className="rounded-xl border bg-card p-6">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-4 flex items-center gap-2">
          <Users className="w-4 h-4" />
          Pengembang dan Pemilik Sistem
        </h3>
        <dl className="space-y-3 text-sm">
          <div className="grid grid-cols-1 sm:grid-cols-[140px_1fr] gap-1 sm:gap-3">
            <dt className="font-medium text-muted-foreground">Pemilik</dt>
            <dd>Tim Diseminasi dan Layanan Statistik, BPS Provinsi Maluku Utara</dd>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-[140px_1fr] gap-1 sm:gap-3">
            <dt className="font-medium text-muted-foreground">Pengembang</dt>
            <dd>Wisnu Candra Gumelar — Pranata Komputer Ahli Pertama</dd>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-[140px_1fr] gap-1 sm:gap-3">
            <dt className="font-medium text-muted-foreground">Domain produksi</dt>
            <dd>
              <a
                href="https://bukutamu.bpsmalut.com"
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center gap-1 text-orange-600 hover:underline"
              >
                bukutamu.bpsmalut.com
                <ExternalLink className="w-3 h-3" />
              </a>
            </dd>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-[140px_1fr] gap-1 sm:gap-3">
            <dt className="font-medium text-muted-foreground">Arsip versi lama</dt>
            <dd>
              <a
                href="https://old-bukutamu.bpsmalut.com"
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center gap-1 text-muted-foreground hover:underline"
              >
                old-bukutamu.bpsmalut.com
                <ExternalLink className="w-3 h-3" />
              </a>
              <span className="text-xs text-muted-foreground ml-2">(read-only)</span>
            </dd>
          </div>
        </dl>
      </section>

      <p className="text-xs text-muted-foreground text-center pt-2">
        © {new Date().getFullYear()} BPS Provinsi Maluku Utara. Aplikasi internal untuk pelayanan PST.
      </p>
    </div>
  )
}

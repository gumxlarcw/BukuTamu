# Tamdes Web — React Frontend Redesign Spec

**Date:** 2026-03-12
**Status:** Approved
**Scope:** Full 1:1 rebuild of Tamdes guest management system as a React SPA

---

## 1. Overview

Rebuild the Tamdes (Tamu Desa / BPS guest book) management system frontend as a standalone React SPA. The existing CodeIgniter HMVC backend is preserved and extended with a REST API module. The old PHP frontend remains in production until the React app is production-ready (Strangler Fig pattern).

### Current State
- CodeIgniter 3 HMVC with server-rendered PHP views
- Bootstrap 5.3 + jQuery 2.2.4 + Paper Bootstrap Wizard
- face-api.js for facial recognition, print server at localhost:5000
- 36 view files across 5 modules (admin, selamat_datang, layanan, recognize, evaluasi)
- Overall frontend quality: 6.3/10 — functional but dated

### Target State
- Decoupled React SPA (separate repository)
- CodeIgniter backend serves REST API (JSON)
- Modern, warm & welcoming UI with consistent design system
- Same feature set (1:1 rebuild)

### Database Tables
- `tamdes_buku` — Guest registry (name, email, phone, gender, education, institution, face_descriptor)
- `tamdes_kunjungan` — Visit records (guest FK, service type, status, queue number, date)
- `konsultasi_pengunjung` — Consultation data needs (11 detail fields per consultation)
- `evaluasi_pengunjung` — Evaluation header (visit FK, overall score)
- `tamdes_evaluasi_detail` — Evaluation details (17 indicators, importance + satisfaction ratings)

---

## 2. Architecture

```
┌─────────────────────┐         ┌─────────────────────────┐
│   React SPA (Vite)  │  REST   │  CodeIgniter Backend     │
│                     │◄───────►│                          │
│  /kiosk/*  (public) │  JSON   │  /api/auth/*             │
│  /admin/*  (auth)   │         │  /api/guests/*           │
│  /login             │  JWT    │  /api/visits/*           │
│                     │ cookie  │  /api/consultations/*    │
└─────────────────────┘         │  /api/dashboard/*        │
        │                       │  /api/services/*         │
        │                       │  /api/evaluations/*      │
        │                       │  /api/kiosk/*            │
        ▼                       └─────────────────────────┘
┌─────────────────────┐                   │
│  Print Server       │              ┌────┴────┐
│  localhost:5000     │              │  MySQL  │
└─────────────────────┘              └─────────┘
        │
┌───────┴─────────────┐
│  External Dashboard  │
│  dashboard-pst.     │
│  bpsmalut.com       │
└─────────────────────┘
```

- React app is a fully separate Git repository
- JWT stored in httpOnly cookie (not localStorage) with SameSite=Strict for CSRF protection
- Kiosk routes are public, admin routes require valid JWT
- CodeIgniter reuses existing models (M_admin, M_user, M_selamat_datang) + refactored query logic into API service layer
- CORS configured for React dev server
- Print via HTTP POST to localhost:5000/print (existing print server)
- Queue calling integrates with external dashboard at dashboard-pst.bpsmalut.com

---

## 3. Tech Stack

| Layer | Choice |
|-------|--------|
| Frontend | Vite + React + TypeScript |
| Styling | Tailwind CSS + Shadcn/ui |
| Routing | React Router v7 |
| Data fetching | TanStack Query (React Query) |
| Client state | React Context API |
| Auth | JWT (httpOnly cookie, SameSite=Strict) |
| Backend API | CodeIgniter 3 (existing) + new API module |
| Face detection | face-api.js |
| Printing | HTTP POST to localhost:5000/print |

---

## 4. React App Structure

```
tamdes-frontend/
├── public/
│   ├── video/                    # Background videos for kiosk
│   └── models/                   # face-api.js ML models
├── src/
│   ├── api/                      # API client & endpoint definitions
│   │   ├── client.ts             # Axios instance with JWT interceptor
│   │   ├── auth.ts               # login, logout, checkSession
│   │   ├── guests.ts             # CRUD guests
│   │   ├── visits.ts             # CRUD visits, service edit, summary edit
│   │   ├── consultations.ts      # Queue management + consultation data
│   │   ├── evaluations.ts        # Evaluation submit + retrieve
│   │   ├── dashboard.ts          # Stats, calendar events
│   │   ├── services.ts           # Service list
│   │   └── kiosk.ts              # Kiosk registration, face matching, tickets
│   ├── components/
│   │   ├── ui/                   # Shadcn/ui components
│   │   ├── kiosk/                # Kiosk-specific components
│   │   │   ├── ServiceBubble.tsx
│   │   │   ├── VisitorForm.tsx
│   │   │   ├── FaceCapture.tsx
│   │   │   ├── FaceRecognize.tsx
│   │   │   ├── StepWizard.tsx
│   │   │   ├── QueueTicket.tsx
│   │   │   ├── PhotoDisclaimer.tsx
│   │   │   └── EvaluationForm.tsx
│   │   ├── admin/                # Admin-specific components
│   │   │   ├── Sidebar.tsx
│   │   │   ├── StatsCard.tsx
│   │   │   ├── GuestTable.tsx
│   │   │   ├── QueueList.tsx
│   │   │   ├── QueueCallButton.tsx
│   │   │   ├── VisitFilters.tsx
│   │   │   ├── VisitDetailPanel.tsx
│   │   │   ├── ConsultationDataForm.tsx
│   │   │   ├── ManualEntryForm.tsx
│   │   │   └── EvaluationResults.tsx
│   │   └── shared/               # Shared across kiosk & admin
│   │       ├── ThemeToggle.tsx
│   │       ├── LoadingSpinner.tsx
│   │       ├── SkeletonCard.tsx
│   │       ├── EmptyState.tsx
│   │       ├── ErrorAlert.tsx
│   │       └── StatusBadge.tsx
│   ├── hooks/                    # Custom React hooks
│   │   ├── useAuth.ts
│   │   ├── useCamera.ts
│   │   ├── usePrint.ts
│   │   └── useInactivityTimeout.ts
│   ├── layouts/
│   │   ├── KioskLayout.tsx       # Full-screen, video background
│   │   └── AdminLayout.tsx       # Sidebar + content area
│   ├── pages/
│   │   ├── kiosk/
│   │   │   ├── WelcomePage.tsx
│   │   │   ├── StatusSelectPage.tsx
│   │   │   ├── ServiceSelectPage.tsx
│   │   │   ├── VisitorFormPage.tsx
│   │   │   ├── FaceCapturePage.tsx
│   │   │   ├── FaceRecognizePage.tsx
│   │   │   ├── EvaluationStandbyPage.tsx
│   │   │   ├── EvaluationPage.tsx
│   │   │   └── TicketPage.tsx
│   │   ├── admin/
│   │   │   ├── LoginPage.tsx
│   │   │   ├── DashboardPage.tsx
│   │   │   ├── GuestListPage.tsx
│   │   │   ├── GuestAddPage.tsx
│   │   │   ├── ConsultationQueuePage.tsx
│   │   │   ├── ConsultationFormPage.tsx
│   │   │   ├── VisitLogPage.tsx
│   │   │   └── ManualEntryPage.tsx
│   │   └── NotFoundPage.tsx
│   ├── lib/
│   │   ├── utils.ts              # Shadcn/ui utility (cn function)
│   │   └── face-detection.ts     # face-api.js wrapper
│   ├── providers/
│   │   ├── AuthProvider.tsx      # Auth context + JWT management
│   │   ├── ThemeProvider.tsx     # Dark/light theme
│   │   └── QueryProvider.tsx     # TanStack Query setup
│   ├── types/
│   │   ├── guest.ts              # Guest type definitions
│   │   ├── visit.ts              # Visit + consultation types
│   │   ├── evaluation.ts         # Evaluation types
│   │   └── api.ts                # API response types
│   ├── styles/
│   │   └── globals.css           # Tailwind base + custom design tokens
│   ├── App.tsx                   # Router setup
│   └── main.tsx                  # Entry point
├── .env                          # API_URL config
├── tailwind.config.ts
├── tsconfig.json
├── vite.config.ts
└── package.json
```

---

## 5. REST API Endpoints

All endpoints return JSON: `{ success: bool, data: ..., message: ... }`

Added as `application/modules/api/controllers/` in existing CodeIgniter app.

### Auth
```
GET    /api/auth/check            → Check if JWT is valid, return user info
POST   /api/auth/login            → Login, returns JWT httpOnly cookie
POST   /api/auth/logout           → Clear JWT cookie
```

### Dashboard
```
GET    /api/dashboard/stats       → All KPI metrics (see Section 7 for full list)
       ?date_from=&date_to=        (optional date range filter)
GET    /api/dashboard/events      → Calendar events for FullCalendar
```

### Guests
```
GET    /api/guests                → List guests (?search=&page=&limit=)
GET    /api/guests/:id            → Single guest detail
PUT    /api/guests/:id            → Update guest
DELETE /api/guests/:id            → Delete guest
```
Note: Guest creation is primarily through the kiosk. Admin can also add guests (face_descriptor optional).

```
POST   /api/guests                → Create guest (admin, face_descriptor optional)
```

### Visits
```
GET    /api/visits                → List visits (?q=&layanan=&tahun=&bulan=&page=&limit=)
GET    /api/visits/:id            → Single visit detail (includes consultation data + evaluation results)
POST   /api/visits                → Create visit (manual entry by admin)
PUT    /api/visits/:id/status     → Update visit status (antri→proses→menunggu_evaluasi→selesai)
PUT    /api/visits/:id/service    → Edit jenis_layanan
PUT    /api/visits/:id/summary    → Add/edit ringkasan
```

### Consultations
```
GET    /api/consultations         → Today's consultation queue
PUT    /api/consultations/:id     → Update consultation status
POST   /api/consultations/:id/call → Call visitor (POST to external dashboard-pst.bpsmalut.com)
POST   /api/consultations/:id/data → Save consultation data needs (array of kebutuhan_data[])
GET    /api/consultations/:id/data → Get consultation data needs (returns array of rows)
```

### Evaluations
```
GET    /api/evaluations/pending    → Get next visit awaiting evaluation (for tablet polling)
GET    /api/evaluations/:id       → Get evaluation form (17 indicators)
POST   /api/evaluations/:id       → Submit evaluation (importance + satisfaction per indicator + overall)
GET    /api/evaluations/:id/results → Get evaluation results for admin view
```

### Services
```
GET    /api/services              → List available services (currently 6, hardcoded on backend)
```
Services: Perpustakaan, Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Penjualan Produk Statistik, Keperluan Pimpinan, Lainnya.

### Kiosk
```
GET    /api/kiosk/face-data       → Get all face descriptors for client-side matching
POST   /api/kiosk/register        → New visitor: form data + base64 photo + face descriptor
POST   /api/kiosk/visit           → Returning visitor: matched guest ID + selected service
GET    /api/kiosk/ticket/:id      → Get ticket data for printing
```

---

## 6. Design System

### Color Palette (Warm & Welcoming)

```
Primary:      #0D9488  (Teal 600)     — Main actions, active states
Primary Dark: #0F766E  (Teal 700)     — Hover states
Accent:       #F59E0B  (Amber 500)    — Highlights, notifications
Success:      #22C55E  (Green 500)    — Completed (selesai)
Warning:      #F59E0B  (Amber 500)    — Pending (menunggu_evaluasi)
Danger:       #EF4444  (Red 500)      — Errors, delete actions
Info:         #3B82F6  (Blue 500)     — In progress (proses)

Light mode:
  Background:   #F9FAFB  (Gray 50)
  Surface:      #FFFFFF
  Text:         #1F2937  (Gray 800)
  Text Muted:   #6B7280  (Gray 500)

Dark mode:
  Background:   #111827  (Gray 900)
  Surface:      #1F2937  (Gray 800)
  Text:         #F3F4F6  (Gray 100)
```

### Typography
- **Font**: Inter (Google Fonts)
- **Kiosk headings**: 2rem-3rem, semibold
- **Admin body**: 0.875rem, regular
- **Admin headings**: 1.25rem-1.5rem, semibold

### Component Styling
- **Border radius**: 0.75rem (rounded-xl)
- **Shadows**: shadow-sm on cards, shadow-md on modals
- **Spacing**: Generous p-8 on kiosk, compact p-4 on admin
- **Transitions**: 150ms ease for hover/focus
- **Touch targets**: minimum 48x48px, 64px+ on kiosk

### Status Badges
| Status | Color | Label |
|--------|-------|-------|
| `antri` | Gray/Dark | Antri |
| `proses` | Blue/Info | Proses |
| `menunggu_evaluasi` | Amber/Warning | Menunggu Evaluasi |
| `selesai` | Green/Success | Selesai |

---

## 7. Page Specifications

### Kiosk Pages (Public — no auth)

**WelcomePage** (`/kiosk`)
- Full-screen video background with dark overlay
- Centered welcome message in large friendly text
- "Mulai / Start" button — large, teal, prominent
- Inactivity timeout: returns to this page after 2 minutes of no interaction

**StatusSelectPage** (`/kiosk/status`)
- Two large cards: "Sudah Pernah Daftar" (returning) vs "Belum Pernah Daftar" (new)
- Returning → FaceRecognizePage
- New → ServiceSelectPage

**ServiceSelectPage** (`/kiosk/service`)
- 6 service bubbles in a 2x3 grid
- Each bubble: icon + service name + short description
- Selected state: teal border + checkmark + subtle scale animation
- "Lanjut / Next" enabled only after selection

**VisitorFormPage** (`/kiosk/form`)
- All 11 fields (large, touch-friendly inputs):
  - `tgldatang` — date/time (auto-filled, datetime-local)
  - `nama` — text, required
  - `email` — email, required
  - `notel` — text (phone number)
  - `jeniskelamin` — radio: Laki-laki / Perempuan (stored as full string)
  - `pendidikan` — select: 1=<=SLTA, 2=D1/D2/D3, 3=D4/S1, 4=S2, 5=S3
  - `pekerjaan` — select: 1=Pelajar/Mahasiswa, 2=Peneliti/Dosen, 3=ASN/TNI/Polri, 4=Pegawai BUMN/BUMD, 5=Pegawai Swasta, 6=Wiraswasta, 7=Lainnya
  - `kategori_instansi` — select: 1=Lembaga Negara, 2=Kementerian & Lembaga Pemerintah, 3=TNI/POLRI/BIN/Kejaksaan, 4=Pemerintah Daerah, 5=Lembaga Internasional, 6=Lembaga Penelitian & Pendidikan, 7=BUMN/BUMD, 8=Swasta, 9=Lainnya
  - `nama_instansi` — text (institution name)
  - `pemanfaatan` — select: 1=Tugas Sekolah/Kuliah, 2=Pemerintah, 3=Komersial, 4=Penelitian, 5=Lainnya
  - `pengaduan` — radio: Ya / Tidak
- Inline validation with friendly error messages
- Auto-save to localStorage as user types, restore on page reload
- Back/Next navigation at bottom

**FaceCapturePage** (`/kiosk/capture`)
- Privacy disclaimer modal shown first (must accept before camera access)
- Live camera preview with face detection overlay
- Face frame guide (oval outline)
- "Ambil Foto / Capture" enabled when face detected
- Photo preview with retake/confirm options
- On confirm → submit all data → redirect to ticket

**FaceRecognizePage** (`/kiosk/recognize`)
- For returning visitors (from StatusSelectPage)
- Loads all face descriptors via `GET /api/kiosk/face-data`
- Live camera with real-time face matching against existing descriptors
- On match: show matched visitor name, confirm identity
- On confirm → select service → POST /api/kiosk/visit → ticket
- No match → redirect to new registration flow

**TicketPage** (`/kiosk/ticket/:id`)
- Queue number (large), service, name, date/time
- Queue number format: service prefix + daily counter (e.g., KS-001)
- Note: Keperluan Pimpinan and Lainnya do not get queue numbers
- Auto-print via POST to localhost:5000/print
- Manual "Cetak Ulang / Print Again" button
- "Selesai / Done" button → returns to WelcomePage

**EvaluationStandbyPage** (`/kiosk/evaluasi`)
- Dedicated tablet standby page
- Polls `GET /api/evaluations/pending` every 5 seconds
- When a visit enters `menunggu_evaluasi` status, auto-redirects to EvaluationPage
- Shows "Menunggu..." message while idle

**EvaluationPage** (`/kiosk/evaluasi/:id`)
- Accessed from EvaluationStandbyPage or via direct link
- 17 evaluation indicators
- Each indicator: importance rating (1-10 stars) + satisfaction rating (1-10 stars)
- Overall satisfaction score
- Submit → status changes to selesai → redirect back to EvaluationStandbyPage

### Admin Pages (Protected — JWT required)

**LoginPage** (`/login`)
- Centered card with logo, username, password
- Teal login button

**DashboardPage** (`/admin`)
- Date range filter at top
- 12 stats cards in responsive grid:
  - Total Kunjungan (total visits)
  - Tamu Unik (unique guests)
  - Jumlah Hari (number of days)
  - Rata-rata/Hari (average per day)
  - Hari Tersibuk (busiest day)
  - Periode Aktif (active period)
  - Selesai (completed)
  - Antri (in queue)
  - Tingkat Selesai (completion rate %)
  - Rata-rata Durasi (average duration)
  - Layanan Terbanyak (most popular service)
  - Instansi Terbanyak (most common institution)
- FullCalendar showing visit events by date with color coding

**GuestListPage** (`/admin/guests`)
- Search bar + data table
- Columns: No, Nama, Email, Jenis Kelamin, Pendidikan, Instansi, Aksi
- Pagination (configurable 10-1000 rows per page)
- Edit opens modal, delete shows confirmation dialog
- "Tambah Tamu" button → navigates to GuestAddPage

**ConsultationQueuePage** (`/admin/consultations`)
- Today's queue as card list
- Each card: queue number, name, service, status badge, time
- Actions per card:
  - "Panggil" (call) — POST to external dashboard-pst.bpsmalut.com/update-antrian
  - "Tes Suara ke TV" — test sound on external display
  - "Mulai Konsultasi" → navigates to ConsultationFormPage
  - "Selesai" — mark complete

**ConsultationFormPage** (`/admin/consultations/:id/form`)
- Multi-row data needs table: admin can add **multiple** data-needs entries per consultation
- Each row has 11 fields:
  - `rincian_data` — text (data details)
  - `wilayah_data` — text (geographic area)
  - `tahun_awal` — number (start year)
  - `tahun_akhir` — number (end year)
  - `level_data` — select: Nasional, Provinsi, Kabupaten/Kota, Kecamatan, Desa/Kelulahan, Individu, Lainnya
  - `periode_data` — select: Sepuluh Tahunan, Lima Tahunan, Tahunan, Semesteran, Triwulanan, Bulanan, Mingguan, Harian, Lainnya, Tidak Berkala
  - `status_data` — select: 1=Ya sesuai, 2=Ya tidak sesuai, 3=Tidak diperoleh, 4=Belum Diperoleh
  - `jenis_publikasi` — select (conditional, shown when status_data=1 or 2): Publikasi, Data Mikro, Peta, Tabulasi Data, Tabel di Website
  - `judul_publikasi` — text (conditional, shown when status_data=1 or 2)
  - `tahun_publikasi` — number (conditional, shown when status_data=1 or 2)
  - `digunakan_nasional` — select: 1=Ya, 2=Tidak (conditional, shown when status_data=1 or 2)
- `kualitas` — star rating (overall data quality assessment, per consultation not per row)
- UI: modal to add/edit a row, table displays all rows, inline delete
- Save → POST /api/consultations/:id/data (sends array of kebutuhan_data[])

**VisitLogPage** (`/admin/visits`)
- Filters: text search (q), layanan dropdown, tahun dropdown, bulan dropdown
- Data table with all visits
- Columns: No, Nama, Layanan, Tanggal, Status, Aksi
- Status badges (antri, proses, menunggu_evaluasi, selesai)
- Click row → expandable detail panel showing:
  - Visitor info
  - Consultation data needs (if any)
  - Evaluation results (if any)
  - Ringkasan (summary)

**ManualEntryPage** (`/admin/manual-entry`)
- Select from existing guest dropdown ("Pilih Tamu") — does NOT create new guests
- Select service type
- Submit creates visit record + generates queue number
- For walk-in visitors who are already registered in the system

**GuestAddPage** (`/admin/guests/add`)
- Admin guest creation form (same fields as kiosk visitor form)
- Used when a guest needs to be added without going through the kiosk
- Note: existing system has this page (`tambah.php`) but blocks it requiring face scan. In React rebuild, allow admin to add guests without face scan (face_descriptor optional).

---

## 8. Technical Integration

### Face Detection (face-api.js)
- Wrapped in `useCamera` hook
- Models loaded once on mount from `/public/models/` (~25MB, 14 files)
- New visitors: detect face → extract descriptor → Float32Array → JSON to API
- Returning visitors: load all descriptors, match client-side using Euclidean distance
- Graceful fallback: file upload if camera denied
- Privacy disclaimer modal before camera access

### Printing (localhost:5000)
- Wrapped in `usePrint` hook
- POST JSON to localhost:5000/print with ticket data
- Auto-print on ticket page load
- Manual "Cetak Ulang / Print Again" button as backup
- Graceful error if print server unavailable

### External Dashboard Integration
- Queue calling POSTs to `https://dashboard-pst.bpsmalut.com/update-antrian`
- "Tes Suara ke TV" tests audio on external display
- Current behavior: browser calls external dashboard directly
- React redesign: proxy through backend API (`POST /api/consultations/:id/call`) for better CORS/security handling. This is a deliberate change from current behavior.

### Authentication Flow
- Login → POST /api/auth/login → JWT set as httpOnly cookie (SameSite=Strict)
- AuthProvider stores user info in React Context
- React Router checks auth before admin routes
- 401 response from any API call → redirect to /login
- Logout → POST /api/auth/logout → clear cookie
- CSRF protection via SameSite=Strict cookie attribute

### Theme Persistence
- ThemeProvider reads/writes localStorage('theme')
- Applies `dark` class to `<html>` (Tailwind dark mode)
- Toggle in admin sidebar footer
- Admin only — kiosk always uses light theme

### Kiosk Flow State
- Step wizard state managed with local useState
- Data flows forward through steps
- Form data auto-saved to localStorage on each input change
- Restored on page reload (handles accidental refresh)
- Final submit: single POST to /api/kiosk/register (new) or /api/kiosk/visit (returning)
- On success → redirect to ticket page
- Inactivity timeout: 2 minutes → reset to WelcomePage

### Queue Number Generation
- Format: service prefix + daily counter (e.g., KS-001, PP-002)
- Counter resets daily
- Keperluan Pimpinan and Lainnya services do not generate queue numbers

---

## 9. Error Handling & UX States

### API Error Handling
- Global error interceptor in Axios client
- 400 → show validation errors inline on form fields
- 401 → redirect to login (session expired)
- 403 → show "Access denied" toast
- 404 → show "Not found" with back button
- 500 → show "Server error, please try again" toast with retry button
- Network error → show "Connection lost" banner with auto-retry

### Loading States
- Skeleton cards for dashboard stats while loading
- Skeleton rows for data tables
- Spinner overlay for form submissions
- Progress indicator for face model loading (~25MB)

### Empty States
- "Belum ada data tamu" — GuestListPage with no results
- "Belum ada kunjungan hari ini" — ConsultationQueuePage with empty queue
- "Tidak ada kunjungan ditemukan" — VisitLogPage with no matching filters
- Each empty state includes an icon + message + suggested action

---

## 10. Deployment Strategy

- **During development**: Old PHP frontend stays live in production
- **React dev**: Runs on localhost:5173, hits CodeIgniter API via VITE_API_URL
- **When ready**: Build React app → deploy static files → point domain to React app
- **API**: Same CodeIgniter backend, API module always available alongside PHP views
- **Rollback**: Switch DNS/nginx back to old PHP frontend if issues arise

# Tamdes React Frontend Redesign — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the Tamdes guest management system as a React SPA with a CodeIgniter REST API backend.

**Architecture:** Decoupled SPA (Vite + React + TypeScript) communicating with CodeIgniter 3 via REST API (JSON + JWT httpOnly cookies). Two independent codebases: React frontend in a separate repo, API controllers added to the existing CodeIgniter app.

**Tech Stack:** Vite, React 18, TypeScript, Tailwind CSS, Shadcn/ui, React Router v7, TanStack Query, Axios, face-api.js, FullCalendar

**Spec:** `docs/superpowers/specs/2026-03-12-tamdes-react-frontend-redesign.md`

---

## Chunk 1: Project Setup & Foundation

### Task 1: Initialize React Project

**Files:**
- Create: `tamdes-frontend/` (new repo, separate from tamdes-web)
- Create: `tamdes-frontend/package.json`
- Create: `tamdes-frontend/vite.config.ts`
- Create: `tamdes-frontend/tsconfig.json`
- Create: `tamdes-frontend/tailwind.config.ts`
- Create: `tamdes-frontend/.env`
- Create: `tamdes-frontend/src/main.tsx`
- Create: `tamdes-frontend/src/App.tsx`
- Create: `tamdes-frontend/index.html`

- [ ] **Step 1: Scaffold Vite + React + TypeScript project**

```bash
cd /var/www/html
npm create vite@latest tamdes-frontend -- --template react-ts
cd tamdes-frontend
npm install
```

- [ ] **Step 2: Install core dependencies**

```bash
npm install tailwindcss @tailwindcss/vite axios @tanstack/react-query react-router-dom
npm install -D @types/node
```

- [ ] **Step 3: Configure Tailwind CSS**

In `vite.config.ts`:
```ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080', // CodeIgniter dev server
        changeOrigin: true,
      },
    },
  },
})
```

- [ ] **Step 4: Create `.env` file**

```
VITE_API_URL=http://localhost:8080
```

- [ ] **Step 5: Verify project runs**

```bash
npm run dev
```
Expected: Vite dev server starts on localhost:5173

- [ ] **Step 6: Initialize git repo and commit**

```bash
cd /var/www/html/tamdes-frontend
git init
git add .
git commit -m "feat: scaffold Vite + React + TypeScript project"
```

---

### Task 2: Design System & Tailwind Configuration

**Files:**
- Modify: `tamdes-frontend/tailwind.config.ts`
- Create: `tamdes-frontend/src/styles/globals.css`
- Create: `tamdes-frontend/src/lib/utils.ts`

- [ ] **Step 1: Configure Tailwind with design tokens**

`tailwind.config.ts`:
```ts
import type { Config } from 'tailwindcss'

const config: Config = {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0D9488',
          dark: '#0F766E',
          light: '#14B8A6',
        },
        accent: '#F59E0B',
        surface: {
          DEFAULT: '#FFFFFF',
          dark: '#1F2937',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        xl: '0.75rem',
      },
    },
  },
  plugins: [],
}

export default config
```

- [ ] **Step 2: Create global CSS**

`src/styles/globals.css`:
```css
@import 'tailwindcss';
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
```

- [ ] **Step 3: Create utility function for Shadcn/ui**

`src/lib/utils.ts`:
```ts
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
```

```bash
npm install clsx tailwind-merge
```

- [ ] **Step 4: Update `src/main.tsx` to import globals**

```tsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './styles/globals.css'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
```

- [ ] **Step 5: Commit**

```bash
git add . && git commit -m "feat: configure Tailwind design system with warm teal palette"
```

---

### Task 3: Install & Configure Shadcn/ui

**Files:**
- Create: `tamdes-frontend/components.json`
- Create: `tamdes-frontend/src/components/ui/` (multiple files)

- [ ] **Step 1: Initialize Shadcn/ui**

```bash
npx shadcn@latest init
```
Select: TypeScript, default style, CSS variables, `@/` alias, `src/components/ui`

- [ ] **Step 2: Install core UI components**

```bash
npx shadcn@latest add button card input label select dialog table badge alert toast dropdown-menu tabs separator skeleton
```

- [ ] **Step 3: Customize theme colors in CSS variables**

Update `src/styles/globals.css` to include Shadcn/ui CSS variables using teal as primary:
```css
@layer base {
  :root {
    --primary: 168 80% 29%;
    --primary-foreground: 0 0% 100%;
    --accent: 38 92% 50%;
    --accent-foreground: 0 0% 0%;
    /* ... shadcn defaults for the rest */
  }
  .dark {
    --background: 222 47% 11%;
    --foreground: 210 20% 96%;
    --primary: 168 80% 29%;
    --primary-foreground: 0 0% 100%;
    /* ... dark mode overrides */
  }
}
```

- [ ] **Step 4: Verify a Button renders with teal styling**

Quick smoke test in `App.tsx`:
```tsx
import { Button } from '@/components/ui/button'

function App() {
  return (
    <div className="flex items-center justify-center h-screen">
      <Button>Test Button</Button>
    </div>
  )
}
export default App
```

- [ ] **Step 5: Commit**

```bash
git add . && git commit -m "feat: install and configure Shadcn/ui components with teal theme"
```

---

### Task 4: TypeScript Type Definitions

**Files:**
- Create: `tamdes-frontend/src/types/api.ts`
- Create: `tamdes-frontend/src/types/guest.ts`
- Create: `tamdes-frontend/src/types/visit.ts`
- Create: `tamdes-frontend/src/types/evaluation.ts`

- [ ] **Step 1: Create API response types**

`src/types/api.ts`:
```ts
export interface ApiResponse<T> {
  success: boolean
  data: T
  message: string
}

export interface PaginatedResponse<T> {
  success: boolean
  data: T[]
  message: string
  pagination: {
    page: number
    limit: number
    total: number
    totalPages: number
  }
}
```

- [ ] **Step 2: Create Guest types**

`src/types/guest.ts`:
```ts
export interface Guest {
  id: number
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  pendidikan: number // 1-5
  pekerjaan: number // 1-7
  kategori_instansi: number // 1-9
  nama_instansi: string
  face_descriptor: number[] | null
  created_at: string
}

export interface GuestFormData {
  tgldatang: string
  nama: string
  email: string
  notel: string
  jeniskelamin: 'Laki-laki' | 'Perempuan'
  pendidikan: number
  pekerjaan: number
  kategori_instansi: number
  nama_instansi: string
  pemanfaatan: number // 1-5
  pengaduan: 'Ya' | 'Tidak'
}

export const PENDIDIKAN_OPTIONS = [
  { value: 1, label: '<=SLTA' },
  { value: 2, label: 'D1/D2/D3' },
  { value: 3, label: 'D4/S1' },
  { value: 4, label: 'S2' },
  { value: 5, label: 'S3' },
] as const

export const PEKERJAAN_OPTIONS = [
  { value: 1, label: 'Pelajar/Mahasiswa' },
  { value: 2, label: 'Peneliti/Dosen' },
  { value: 3, label: 'ASN/TNI/Polri' },
  { value: 4, label: 'Pegawai BUMN/BUMD' },
  { value: 5, label: 'Pegawai Swasta' },
  { value: 6, label: 'Wiraswasta' },
  { value: 7, label: 'Lainnya' },
] as const

export const KATEGORI_INSTANSI_OPTIONS = [
  { value: 1, label: 'Lembaga Negara' },
  { value: 2, label: 'Kementerian & Lembaga Pemerintah' },
  { value: 3, label: 'TNI/POLRI/BIN/Kejaksaan' },
  { value: 4, label: 'Pemerintah Daerah' },
  { value: 5, label: 'Lembaga Internasional' },
  { value: 6, label: 'Lembaga Penelitian & Pendidikan' },
  { value: 7, label: 'BUMN/BUMD' },
  { value: 8, label: 'Swasta' },
  { value: 9, label: 'Lainnya' },
] as const

export const PEMANFAATAN_OPTIONS = [
  { value: 1, label: 'Tugas Sekolah/Kuliah' },
  { value: 2, label: 'Pemerintah' },
  { value: 3, label: 'Komersial' },
  { value: 4, label: 'Penelitian' },
  { value: 5, label: 'Lainnya' },
] as const
```

- [ ] **Step 3: Create Visit & Consultation types**

`src/types/visit.ts`:
```ts
export type VisitStatus = 'antri' | 'proses' | 'menunggu_evaluasi' | 'selesai'

export interface Visit {
  id: number
  guest_id: number
  guest_nama: string
  guest_instansi: string
  jenis_layanan: string
  no_antrian: string | null
  status: VisitStatus
  ringkasan: string | null
  tgldatang: string
  created_at: string
}

export interface ConsultationDataRow {
  id?: number
  rincian_data: string
  wilayah_data: string
  tahun_awal: number
  tahun_akhir: number
  level_data: number // 1-7
  periode_data: number // 1-10
  status_data: number // 1-4
  jenis_publikasi: string | null // conditional: status_data 1 or 2
  judul_publikasi: string | null
  tahun_publikasi: number | null
  digunakan_nasional: number | null // 1=Ya, 2=Tidak
  kualitas: number | null // star rating 1-5
}

export const LEVEL_DATA_OPTIONS = [
  { value: 1, label: 'Nasional' },
  { value: 2, label: 'Provinsi' },
  { value: 3, label: 'Kabupaten/Kota' },
  { value: 4, label: 'Kecamatan' },
  { value: 5, label: 'Desa/Kelurahan' },
  { value: 6, label: 'Individu' },
  { value: 7, label: 'Lainnya' },
] as const

export const PERIODE_DATA_OPTIONS = [
  { value: 1, label: 'Sepuluh Tahunan' },
  { value: 2, label: 'Lima Tahunan' },
  { value: 3, label: 'Tiga Tahunan' },
  { value: 4, label: 'Tahunan' },
  { value: 5, label: 'Semesteran' },
  { value: 6, label: 'Triwulanan' },
  { value: 7, label: 'Bulanan' },
  { value: 8, label: 'Mingguan' },
  { value: 9, label: 'Harian' },
  { value: 10, label: 'Lainnya' },
] as const

export const STATUS_DATA_OPTIONS = [
  { value: 1, label: 'Ya sesuai' },
  { value: 2, label: 'Ya tidak sesuai' },
  { value: 3, label: 'Tidak diperoleh' },
  { value: 4, label: 'Belum Diperoleh' },
] as const

export const JENIS_PUBLIKASI_OPTIONS = [
  'Publikasi', 'Data Mikro', 'Peta', 'Tabulasi Data', 'Tabel di Website',
] as const

export const SERVICE_OPTIONS = [
  'Perpustakaan',
  'Konsultasi Statistik',
  'Rekomendasi Kegiatan Statistik',
  'Penjualan Produk Statistik',
  'Keperluan Pimpinan',
  'Lainnya',
] as const

export interface DashboardStats {
  total_kunjungan: number
  tamu_unik: number
  jumlah_hari: number
  rata_rata_per_hari: number
  hari_tersibuk: string
  periode_aktif: string
  selesai: number
  antri: number
  tingkat_selesai: number
  rata_rata_durasi: string
  layanan_terbanyak: string
  instansi_terbanyak: string
}

export interface CalendarEvent {
  id: string
  title: string
  start: string
  end?: string
  color: string
}
```

- [ ] **Step 4: Create Evaluation types**

`src/types/evaluation.ts`:
```ts
export interface EvaluationIndicator {
  id: number
  label: string
  importance: number // 1-10
  satisfaction: number // 1-10
}

export interface EvaluationSubmission {
  indicators: { id: number; importance: number; satisfaction: number }[]
  overall_score: number
}

export interface EvaluationResult {
  visit_id: number
  guest_nama: string
  indicators: EvaluationIndicator[]
  overall_score: number
  submitted_at: string
}
```

- [ ] **Step 5: Commit**

```bash
git add . && git commit -m "feat: add TypeScript type definitions for all domain models"
```

---

### Task 5: API Client & Endpoint Modules

**Files:**
- Create: `tamdes-frontend/src/api/client.ts`
- Create: `tamdes-frontend/src/api/auth.ts`
- Create: `tamdes-frontend/src/api/guests.ts`
- Create: `tamdes-frontend/src/api/visits.ts`
- Create: `tamdes-frontend/src/api/consultations.ts`
- Create: `tamdes-frontend/src/api/evaluations.ts`
- Create: `tamdes-frontend/src/api/dashboard.ts`
- Create: `tamdes-frontend/src/api/services.ts`
- Create: `tamdes-frontend/src/api/kiosk.ts`

- [ ] **Step 1: Create Axios client with interceptors**

`src/api/client.ts`:
```ts
import axios from 'axios'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '',
  withCredentials: true, // send httpOnly cookies
  headers: { 'Content-Type': 'application/json' },
})

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  },
)

export default apiClient
```

- [ ] **Step 2: Create auth API module**

`src/api/auth.ts`:
```ts
import apiClient from './client'
import type { ApiResponse } from '@/types/api'

export interface AuthUser {
  id: number
  username: string
  nama: string
}

export const authApi = {
  check: () => apiClient.get<ApiResponse<AuthUser>>('/api/auth/check'),
  login: (username: string, password: string) =>
    apiClient.post<ApiResponse<AuthUser>>('/api/auth/login', { username, password }),
  logout: () => apiClient.post<ApiResponse<null>>('/api/auth/logout'),
}
```

- [ ] **Step 3: Create remaining API modules**

Create `src/api/guests.ts`, `src/api/visits.ts`, `src/api/consultations.ts`, `src/api/evaluations.ts`, `src/api/dashboard.ts`, `src/api/services.ts`, `src/api/kiosk.ts` — each wrapping the corresponding API endpoints from the spec (Section 5). All use `apiClient` and return typed responses.

Key patterns:
- List endpoints accept filter params
- Mutations use POST/PUT/DELETE
- All return `ApiResponse<T>` or `PaginatedResponse<T>`

See spec Section 5 for all endpoint signatures.

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: add API client with all endpoint modules"
```

---

### Task 6: Providers (Auth, Theme, Query)

**Files:**
- Create: `tamdes-frontend/src/providers/AuthProvider.tsx`
- Create: `tamdes-frontend/src/providers/ThemeProvider.tsx`
- Create: `tamdes-frontend/src/providers/QueryProvider.tsx`

- [ ] **Step 1: Create AuthProvider**

`src/providers/AuthProvider.tsx`: Context provider that checks JWT on mount via `authApi.check()`, stores user state, exposes `login()`, `logout()`, `user`, `isLoading`. Custom `useAuth()` hook.

- [ ] **Step 2: Create ThemeProvider**

`src/providers/ThemeProvider.tsx`: Reads/writes `localStorage('theme')`, toggles `dark` class on `<html>`. Custom `useTheme()` hook.

- [ ] **Step 3: Create QueryProvider**

`src/providers/QueryProvider.tsx`: Wraps `QueryClientProvider` with configured `QueryClient` (30s staleTime, 1 retry).

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: add Auth, Theme, and Query providers"
```

---

### Task 7: Shared Components

**Files:**
- Create: `tamdes-frontend/src/components/shared/LoadingSpinner.tsx`
- Create: `tamdes-frontend/src/components/shared/SkeletonCard.tsx`
- Create: `tamdes-frontend/src/components/shared/EmptyState.tsx`
- Create: `tamdes-frontend/src/components/shared/ErrorAlert.tsx`
- Create: `tamdes-frontend/src/components/shared/StatusBadge.tsx`
- Create: `tamdes-frontend/src/components/shared/ThemeToggle.tsx`

- [ ] **Step 1: Create all shared components**

- `LoadingSpinner`: Animated spinner with configurable size
- `SkeletonCard`: Card-shaped skeleton using Shadcn Skeleton
- `EmptyState`: Icon + message + optional action text
- `ErrorAlert`: Destructive alert with optional retry button
- `StatusBadge`: Maps VisitStatus to colored Badge (antri=gray, proses=blue, menunggu_evaluasi=amber, selesai=green)
- `ThemeToggle`: Button that calls `useTheme().toggleTheme()`

- [ ] **Step 2: Commit**

```bash
git add . && git commit -m "feat: add shared UI components (spinner, skeleton, empty, error, badge, theme)"
```

---

### Task 8: Layouts & Router Setup

**Files:**
- Create: `tamdes-frontend/src/layouts/KioskLayout.tsx`
- Create: `tamdes-frontend/src/layouts/AdminLayout.tsx`
- Create: `tamdes-frontend/src/components/admin/Sidebar.tsx`
- Modify: `tamdes-frontend/src/App.tsx`
- Create: `tamdes-frontend/src/pages/NotFoundPage.tsx`
- Create: all placeholder page files

- [ ] **Step 1: Create KioskLayout**

Full-screen video background with dark overlay. `<Outlet />` centered. Video from `/video/bg-video.mp4`.

- [ ] **Step 2: Create Admin Sidebar**

Fixed sidebar (w-64) with: brand title "Tamdes Admin", 5 NavLinks (Dashboard, Daftar Tamu, Antrian Konsultasi, Daftar Kunjungan, Tambah Kunjungan Manual), ThemeToggle + Logout at bottom. Active link shows teal highlight.

- [ ] **Step 3: Create AdminLayout**

Checks auth via `useAuth()`. If loading → spinner. If no user → redirect to `/login`. Otherwise renders Sidebar + `<Outlet />`.

- [ ] **Step 4: Create NotFoundPage**

Simple 404 page with "Kembali" button.

- [ ] **Step 5: Set up router in App.tsx**

Configure all routes per spec:
- `/kiosk/*` under KioskLayout (public)
- `/kiosk/evaluasi` and `/kiosk/evaluasi/:id` outside KioskLayout (no video bg)
- `/login` standalone
- `/admin/*` under AdminLayout (protected)
- `/` → WelcomePage
- `*` → NotFoundPage

Use `lazy()` imports for all pages.

- [ ] **Step 6: Create placeholder pages**

Create minimal default-export component for each of the 17 pages so the router compiles.

- [ ] **Step 7: Verify app compiles**

```bash
npm run build
```
Expected: Build succeeds with no TypeScript errors.

- [ ] **Step 8: Commit**

```bash
git add . && git commit -m "feat: add layouts, router, sidebar, and placeholder pages"
```

---

## Chunk 2: Backend API — Auth, Guests, Services, Dashboard

### Task 9: CodeIgniter API Module Setup

**Context:** All backend work happens in the existing tamdes-web repo at `/var/www/html/tamdes-web/`.

**Files:**
- Create: `application/modules/api/controllers/Api_base.php`
- Create: `application/libraries/JWT_Helper.php`
- Modify: `application/config/routes.php`

- [ ] **Step 1: Create API base controller**

`Api_base.php`: Extends MX_Controller. Sets CORS headers (allowing localhost:5173 + production origin from env). Handles OPTIONS preflight. Provides `require_auth()` (reads JWT from cookie, decodes, sets `$this->current_user`), `json_response()`, `get_json_input()` helpers.

- [ ] **Step 2: Create JWT helper library**

`JWT_Helper.php`: Simple HS256 JWT implementation. `encode($payload)` creates token with iat/exp. `decode($token)` verifies signature and expiry. Secret from `JWT_SECRET` env var.

- [ ] **Step 3: Add API routes**

Add routes for all API endpoints to `application/config/routes.php`:
- `api/auth/*`, `api/guests/*`, `api/visits/*`, `api/consultations/*`
- `api/evaluations/*`, `api/dashboard/*`, `api/services/*`, `api/kiosk/*`

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: add API base controller, JWT helper, and API routes"
```

---

### Task 10: Auth API Controller

**Files:**
- Create: `application/modules/api/controllers/Auth.php`

- [ ] **Step 1: Create Auth controller**

Extends Api_base. Three methods:
- `check()`: GET, requires auth, returns current user info
- `login()`: POST, validates username/password via M_admin, creates JWT, sets httpOnly cookie (SameSite=Strict)
- `logout()`: POST, clears JWT cookie

- [ ] **Step 2: Verify M_admin login method compatibility**

Check that `M_admin->login()` returns user object with id, username, nama. Adapt if needed.

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: add Auth API controller with JWT login/logout/check"
```

---

### Task 11: Guests API Controller

**Files:**
- Create: `application/modules/api/controllers/Guests.php`

- [ ] **Step 1: Create Guests controller**

Extends Api_base. All methods require auth.
- `index()`: GET → paginated list with search filter. POST → create guest (face_descriptor optional).
- `detail($id)`: GET → single guest. PUT → update guest. DELETE → delete guest.

Queries `tamdes_buku` table directly.

- [ ] **Step 2: Verify table/column names match actual database**

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: add Guests API controller with CRUD endpoints"
```

---

### Task 12: Services & Dashboard API Controllers

**Files:**
- Create: `application/modules/api/controllers/Services.php`
- Create: `application/modules/api/controllers/Dashboard.php`

- [ ] **Step 1: Create Services controller**

Returns hardcoded array of 6 services with id, name, icon, description.

- [ ] **Step 2: Create Dashboard controller**

- `stats()`: GET with optional date_from/date_to. Computes all 12 KPI metrics from tamdes_kunjungan + tamdes_buku tables.
- `events()`: GET, returns calendar events grouped by date + service type with color coding.

- [ ] **Step 3: Verify database column names**

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: add Services and Dashboard API controllers"
```

---

## Chunk 3: Backend API — Visits, Consultations, Evaluations, Kiosk

### Task 13: Visits API Controller

**Files:**
- Create: `application/modules/api/controllers/Visits.php`

- [ ] **Step 1: Create Visits controller**

- `index()`: GET → paginated list with filters (q, layanan, tahun, bulan). POST → create visit with queue number generation.
- `detail($id)`: GET → visit with guest info + consultation data + evaluation results.
- `status($id)`: PUT → update visit status.
- `service($id)`: PUT → update jenis_layanan.
- `summary($id)`: PUT → update ringkasan.
- Private `generate_queue_number()`: service prefix + daily counter. Keperluan Pimpinan and Lainnya return null.

- [ ] **Step 2: Verify FK column names in tamdes_kunjungan**

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: add Visits API controller with CRUD, status, service, summary, queue number"
```

---

### Task 14: Consultations API Controller

**Files:**
- Create: `application/modules/api/controllers/Consultations.php`

- [ ] **Step 1: Create Consultations controller**

- `index()`: GET → today's queue (all visits for today with guest info).
- `detail($id)`: PUT → update consultation status.
- `call($id)`: POST → proxy call to external dashboard-pst.bpsmalut.com/update-antrian via cURL.
- `data($id)`: GET → get consultation data rows. POST → save array of kebutuhan_data[] (delete existing + re-insert).

- [ ] **Step 2: Commit**

```bash
git add . && git commit -m "feat: add Consultations API controller with queue, call, data endpoints"
```

---

### Task 15: Evaluations API Controller

**Files:**
- Create: `application/modules/api/controllers/Evaluations.php`

- [ ] **Step 1: Create Evaluations controller**

- `pending()`: GET (no auth) → returns first visit with status=menunggu_evaluasi, or null.
- `detail($id)`: GET → returns 17 evaluation indicators. POST → submits evaluation (creates evaluasi_pengunjung + tamdes_evaluasi_detail rows, updates visit status to selesai).
- `results($id)`: GET (requires auth) → returns evaluation results for admin view.

- [ ] **Step 2: Verify evaluation table schema**

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: add Evaluations API controller with pending, form, submit, results"
```

---

### Task 16: Kiosk API Controller

**Files:**
- Create: `application/modules/api/controllers/Kiosk.php`

- [ ] **Step 1: Create Kiosk controller**

- `face_data()`: GET → returns all guests with face descriptors (id, nama, face_descriptor JSON decoded).
- `register()`: POST → creates guest + visit for new visitors. Includes form data, base64 photo, face descriptor, service.
- `visit()`: POST → creates visit for returning visitors (guest_id + jenis_layanan).
- `ticket($id)`: GET → returns ticket data (no_antrian, nama, jenis_layanan, tgldatang).
- Private `generate_queue_number()` same as Visits controller.

- [ ] **Step 2: Add kiosk-specific routes**

Ensure routes for `face-data` (hyphenated) map to `face_data` method.

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: add Kiosk API controller with register, visit, face-data, ticket"
```

---

## Chunk 4: Frontend — Kiosk Pages

### Task 17: Custom Hooks

**Context:** Back to the tamdes-frontend repo.

**Files:**
- Create: `tamdes-frontend/src/hooks/useCamera.ts`
- Create: `tamdes-frontend/src/hooks/usePrint.ts`
- Create: `tamdes-frontend/src/hooks/useInactivityTimeout.ts`
- Create: `tamdes-frontend/src/lib/face-detection.ts`

- [ ] **Step 1: Create face-detection wrapper**

`src/lib/face-detection.ts`: Wraps face-api.js. Functions:
- `loadFaceModels()`: loads tinyFaceDetector, faceLandmark68Net, faceRecognitionNet from `/models`
- `detectFace(video)`: returns detection with descriptor or null
- `matchFace(descriptor, knownDescriptors, threshold=0.6)`: returns best match by Euclidean distance

```bash
npm install face-api.js
```

- [ ] **Step 2: Create useCamera hook**

Manages video stream, face model loading, continuous face detection loop (500ms interval), photo capture with descriptor extraction. Returns: `videoRef`, `isModelLoading`, `isCameraActive`, `faceDetected`, `error`, `startCamera()`, `stopCamera()`, `capturePhoto()`.

- [ ] **Step 3: Create usePrint hook**

POST to `http://localhost:5000/print` with ticket data. Returns: `printTicket()`, `isPrinting`, `printError`. Graceful error handling.

- [ ] **Step 4: Create useInactivityTimeout hook**

Listens for mousedown/touchstart/keydown/scroll events. Resets timer on any activity. Calls `onTimeout` callback after `timeoutMs` (default 120000ms). Cleanup on unmount.

- [ ] **Step 5: Commit**

```bash
git add . && git commit -m "feat: add useCamera, usePrint, useInactivityTimeout hooks and face-detection lib"
```

---

### Task 18: Kiosk Pages — Welcome, Status, Service, Form

**Files:**
- Modify: `tamdes-frontend/src/pages/kiosk/WelcomePage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/StatusSelectPage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/ServiceSelectPage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/VisitorFormPage.tsx`
- Create: `tamdes-frontend/src/components/kiosk/ServiceBubble.tsx`
- Create: `tamdes-frontend/src/components/kiosk/VisitorForm.tsx`
- Create: `tamdes-frontend/src/components/kiosk/StepWizard.tsx`

- [ ] **Step 1: Implement WelcomePage**

Full-screen centered content on video background. Large welcome text, "Mulai" teal button → `/kiosk/status`. Uses `useInactivityTimeout` to reset.

- [ ] **Step 2: Implement StatusSelectPage**

Two large cards: "Sudah Pernah Daftar" → `/kiosk/recognize`, "Belum Pernah Daftar" → `/kiosk/service`.

- [ ] **Step 3: Create ServiceBubble component**

Card with icon, name, description. Selected state: teal border + checkmark + scale animation.

- [ ] **Step 4: Implement ServiceSelectPage**

Fetches services via TanStack Query. 2x3 grid of ServiceBubble. "Lanjut" enabled on selection. Passes service via location state.

- [ ] **Step 5: Create VisitorForm component**

All 11 fields using constants from `types/guest.ts`. Auto-save to localStorage. Restore on mount.

- [ ] **Step 6: Implement VisitorFormPage**

Wraps VisitorForm. Back → service page. Next → capture page. Passes form data via location state.

- [ ] **Step 7: Commit**

```bash
git add . && git commit -m "feat: implement kiosk Welcome, StatusSelect, ServiceSelect, VisitorForm pages"
```

---

### Task 19: Kiosk Pages — Face Capture, Recognize, Ticket, Evaluation

**Files:**
- Modify: `tamdes-frontend/src/pages/kiosk/FaceCapturePage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/FaceRecognizePage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/TicketPage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/EvaluationStandbyPage.tsx`
- Modify: `tamdes-frontend/src/pages/kiosk/EvaluationPage.tsx`
- Create: `tamdes-frontend/src/components/kiosk/PhotoDisclaimer.tsx`
- Create: `tamdes-frontend/src/components/kiosk/FaceCapture.tsx`
- Create: `tamdes-frontend/src/components/kiosk/FaceRecognize.tsx`
- Create: `tamdes-frontend/src/components/kiosk/QueueTicket.tsx`
- Create: `tamdes-frontend/src/components/kiosk/EvaluationForm.tsx`

- [ ] **Step 1: Create PhotoDisclaimer component**

Dialog modal. Text about photo storage. Accept/Decline buttons. Must accept to proceed.

- [ ] **Step 2: Implement FaceCapturePage**

PhotoDisclaimer → camera via useCamera → live video with oval guide → "Ambil Foto" (enabled when faceDetected) → preview with Retake/Confirm → submit all data via `kioskApi.register()` → navigate to `/kiosk/ticket/:id`. Clear localStorage.

- [ ] **Step 3: Implement FaceRecognizePage**

Load face descriptors via `kioskApi.getFaceData()`. Start camera. Continuous matching via `matchFace()`. On match: show name + Confirm/Bukan Saya. On confirm: inline service selection → `kioskApi.visit()` → ticket. No match → redirect to new flow.

- [ ] **Step 4: Implement TicketPage**

Fetch ticket via `kioskApi.getTicket(:id)`. Display queue number (large), service, name, datetime. Auto-print on mount via `usePrint`. "Cetak Ulang" + "Selesai" buttons.

- [ ] **Step 5: Implement EvaluationStandbyPage**

Poll `evaluationsApi.getPending()` with `refetchInterval: 5000`. Show "Menunggu..." message. On data → navigate to `/kiosk/evaluasi/:id`.

- [ ] **Step 6: Implement EvaluationPage**

Fetch indicators via `evaluationsApi.getForm(:id)`. 17 indicators with dual star ratings (importance + satisfaction, 1-10). Overall score. Submit → `evaluationsApi.submit()` → redirect to standby.

- [ ] **Step 7: Commit**

```bash
git add . && git commit -m "feat: implement kiosk FaceCapture, FaceRecognize, Ticket, Evaluation pages"
```

---

## Chunk 5: Frontend — Admin Pages

### Task 20: Login Page

**Files:**
- Modify: `tamdes-frontend/src/pages/admin/LoginPage.tsx`

- [ ] **Step 1: Implement LoginPage**

Centered card. Logo + "Tamdes Admin" title. Username/password inputs. Teal "Masuk" button. Uses `useAuth().login()`. Error toast on failure. Redirect to `/admin` if already authenticated.

- [ ] **Step 2: Commit**

```bash
git add . && git commit -m "feat: implement admin LoginPage"
```

---

### Task 21: Dashboard Page

**Files:**
- Modify: `tamdes-frontend/src/pages/admin/DashboardPage.tsx`
- Create: `tamdes-frontend/src/components/admin/StatsCard.tsx`

- [ ] **Step 1: Create StatsCard component**

Props: label, value, icon. Rounded card with large value and label.

- [ ] **Step 2: Implement DashboardPage**

Date range filter. 12 StatsCards in responsive grid. FullCalendar with events. Skeleton loading.

```bash
npm install @fullcalendar/core @fullcalendar/react @fullcalendar/daygrid
```

- [ ] **Step 3: Commit**

```bash
git add . && git commit -m "feat: implement admin DashboardPage with 12 stats cards and calendar"
```

---

### Task 22: Guest List & Guest Add Pages

**Files:**
- Modify: `tamdes-frontend/src/pages/admin/GuestListPage.tsx`
- Modify: `tamdes-frontend/src/pages/admin/GuestAddPage.tsx`
- Create: `tamdes-frontend/src/components/admin/GuestTable.tsx`

- [ ] **Step 1: Create GuestTable component**

Shadcn Table. Columns: No, Nama, Email, Jenis Kelamin, Pendidikan, Instansi, Aksi. Edit via Dialog. Delete via AlertDialog.

- [ ] **Step 2: Implement GuestListPage**

Search input. GuestTable. Pagination. "Tambah Tamu" button.

- [ ] **Step 3: Implement GuestAddPage**

Same fields as kiosk form but admin styling. face_descriptor optional. Submit → `guestsApi.create()` → redirect to guest list.

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: implement admin GuestListPage and GuestAddPage"
```

---

### Task 23: Consultation Queue & Form Pages

**Files:**
- Modify: `tamdes-frontend/src/pages/admin/ConsultationQueuePage.tsx`
- Modify: `tamdes-frontend/src/pages/admin/ConsultationFormPage.tsx`
- Create: `tamdes-frontend/src/components/admin/QueueList.tsx`
- Create: `tamdes-frontend/src/components/admin/QueueCallButton.tsx`
- Create: `tamdes-frontend/src/components/admin/ConsultationDataForm.tsx`

- [ ] **Step 1: Create queue components**

QueueCallButton: calls `consultationsApi.call()` with loading/error states.
QueueList: card list with queue number, name, service, status, action buttons.

- [ ] **Step 2: Implement ConsultationQueuePage**

Today's queue via `consultationsApi.list()`. QueueList component. EmptyState. Auto-refetch 30s.

- [ ] **Step 3: Create ConsultationDataForm component**

Multi-row table. "Tambah Data" opens Dialog with 11 fields. Conditional fields shown when status_data=1 or 2. Edit/delete per row.

- [ ] **Step 4: Implement ConsultationFormPage**

Fetch existing data. ConsultationDataForm. Save → `consultationsApi.saveData()`.

- [ ] **Step 5: Commit**

```bash
git add . && git commit -m "feat: implement admin ConsultationQueue and ConsultationForm pages"
```

---

### Task 24: Visit Log & Manual Entry Pages

**Files:**
- Modify: `tamdes-frontend/src/pages/admin/VisitLogPage.tsx`
- Modify: `tamdes-frontend/src/pages/admin/ManualEntryPage.tsx`
- Create: `tamdes-frontend/src/components/admin/VisitFilters.tsx`
- Create: `tamdes-frontend/src/components/admin/VisitDetailPanel.tsx`
- Create: `tamdes-frontend/src/components/admin/EvaluationResults.tsx`
- Create: `tamdes-frontend/src/components/admin/ManualEntryForm.tsx`

- [ ] **Step 1: Create VisitFilters**

Text search, layanan dropdown, tahun dropdown, bulan dropdown.

- [ ] **Step 2: Create VisitDetailPanel**

Expandable panel: visitor info, consultation data table, evaluation results, editable ringkasan.

- [ ] **Step 3: Create EvaluationResults**

Read-only view of 17 indicators with importance/satisfaction bars + overall score.

- [ ] **Step 4: Implement VisitLogPage**

Filters + table + expandable detail + pagination.

- [ ] **Step 5: Create ManualEntryForm & Implement ManualEntryPage**

Guest dropdown (searchable, fetches from guestsApi). Service select. Submit creates visit.

- [ ] **Step 6: Commit**

```bash
git add . && git commit -m "feat: implement admin VisitLog and ManualEntry pages"
```

---

## Chunk 6: Integration, Polish & Deployment

### Task 25: Copy Assets & Production Build

**Files:**
- Copy: face-api.js models to `tamdes-frontend/public/models/`
- Copy: background video to `tamdes-frontend/public/video/`

- [ ] **Step 1: Copy face-api.js models**

```bash
cp -r /var/www/html/tamdes-web/assets/models/ /var/www/html/tamdes-frontend/public/models/
```

- [ ] **Step 2: Copy background video**

```bash
mkdir -p /var/www/html/tamdes-frontend/public/video/
cp /var/www/html/tamdes-web/assets/form/video/bg-video.mp4 /var/www/html/tamdes-frontend/public/video/
```

- [ ] **Step 3: Test production build**

```bash
cd /var/www/html/tamdes-frontend
npm run build
npm run preview
```

- [ ] **Step 4: Commit**

```bash
git add . && git commit -m "feat: add face-api models, video assets, verify production build"
```

---

### Task 26: CORS Configuration for Production

**Files:**
- Modify: `application/modules/api/controllers/Api_base.php`

- [ ] **Step 1: Update CORS to support dynamic origins**

Read allowed origins from config/environment. Support both dev (localhost:5173) and production.

- [ ] **Step 2: Commit**

```bash
git add . && git commit -m "feat: configure CORS for dev and production origins"
```

---

### Task 27: End-to-End Smoke Test

- [ ] **Step 1: Start CodeIgniter backend**

```bash
cd /var/www/html/tamdes-web && php -S localhost:8080
```

- [ ] **Step 2: Start React frontend**

```bash
cd /var/www/html/tamdes-frontend && npm run dev
```

- [ ] **Step 3: Test kiosk flow**

Verify: Welcome → Status → Service → Form → Face Capture → Ticket

- [ ] **Step 4: Test admin flow**

Verify: Login → Dashboard (12 stats) → Guest List → Consultation Queue → Visit Log → Theme Toggle

- [ ] **Step 5: Fix any issues found**

- [ ] **Step 6: Final commit**

```bash
git add . && git commit -m "chore: smoke test fixes and polish"
```

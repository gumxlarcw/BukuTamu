# Tamdes Web — React Frontend Redesign Spec

**Date:** 2026-03-12
**Status:** Approved
**Scope:** Full 1:1 rebuild of Tamdes guest management system as a React SPA

---

## 1. Overview

Rebuild the Tamdes (Tamu Desa) guest book management system frontend as a standalone React SPA. The existing CodeIgniter HMVC backend is preserved and extended with a REST API module. The old PHP frontend remains in production until the React app is production-ready (Strangler Fig pattern).

### Current State
- CodeIgniter 3 HMVC with server-rendered PHP views
- Bootstrap 5.3 + jQuery 2.2.4 + Paper Bootstrap Wizard
- face-api.js for facial recognition, QZ Tray for ticket printing
- 36 view files across 5 modules (admin, selamat_datang, layanan, recognize, evaluasi)
- Overall frontend quality: 6.3/10 — functional but dated

### Target State
- Decoupled React SPA (separate repository)
- CodeIgniter backend serves REST API (JSON)
- Modern, warm & welcoming UI with consistent design system
- Same feature set (1:1 rebuild)

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
                                │  /api/services/*         │
                                └─────────────────────────┘
                                          │
                                     ┌────┴────┐
                                     │  MySQL  │
                                     └─────────┘
```

- React app is a fully separate Git repository
- JWT stored in httpOnly cookie (not localStorage)
- Kiosk routes are public, admin routes require valid JWT
- CodeIgniter reuses existing models (M_admin, M_user) — only new API controllers
- CORS configured for React dev server

---

## 3. Tech Stack

| Layer | Choice |
|-------|--------|
| Frontend | Vite + React + TypeScript |
| Styling | Tailwind CSS + Shadcn/ui |
| Routing | React Router v7 |
| Data fetching | TanStack Query (React Query) |
| Client state | React Context API |
| Auth | JWT (httpOnly cookie) |
| Backend API | CodeIgniter 3 (existing) + new API module |
| Face detection | face-api.js |
| Printing | QZ Tray |

---

## 4. React App Structure

```
tamdes-frontend/
├── public/
│   └── video/                    # Background videos for kiosk
├── src/
│   ├── api/                      # API client & endpoint definitions
│   │   ├── client.ts             # Axios instance with JWT interceptor
│   │   ├── auth.ts               # login, logout, checkSession
│   │   ├── guests.ts             # CRUD guests
│   │   ├── visits.ts             # CRUD visits
│   │   ├── consultations.ts      # Queue management
│   │   ├── dashboard.ts          # Stats, calendar events
│   │   └── services.ts           # Service list
│   ├── components/
│   │   ├── ui/                   # Shadcn/ui components
│   │   ├── kiosk/                # Kiosk-specific components
│   │   │   ├── ServiceBubble.tsx
│   │   │   ├── VisitorForm.tsx
│   │   │   ├── FaceCapture.tsx
│   │   │   ├── StepWizard.tsx
│   │   │   └── QueueTicket.tsx
│   │   ├── admin/                # Admin-specific components
│   │   │   ├── Sidebar.tsx
│   │   │   ├── StatsCard.tsx
│   │   │   ├── GuestTable.tsx
│   │   │   ├── QueueList.tsx
│   │   │   ├── VisitFilters.tsx
│   │   │   └── ManualEntryForm.tsx
│   │   └── shared/               # Shared across kiosk & admin
│   │       ├── ThemeToggle.tsx
│   │       ├── LoadingSpinner.tsx
│   │       └── StatusBadge.tsx
│   ├── hooks/                    # Custom React hooks
│   │   ├── useAuth.ts
│   │   ├── useCamera.ts
│   │   └── usePrint.ts
│   ├── layouts/
│   │   ├── KioskLayout.tsx       # Full-screen, video background
│   │   └── AdminLayout.tsx       # Sidebar + content area
│   ├── pages/
│   │   ├── kiosk/
│   │   │   ├── WelcomePage.tsx
│   │   │   ├── ServiceSelectPage.tsx
│   │   │   ├── VisitorFormPage.tsx
│   │   │   └── FaceCapturePage.tsx
│   │   ├── admin/
│   │   │   ├── LoginPage.tsx
│   │   │   ├── DashboardPage.tsx
│   │   │   ├── GuestListPage.tsx
│   │   │   ├── ConsultationQueuePage.tsx
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
GET    /api/auth/check          → Check if JWT is valid
POST   /api/auth/login          → Login, returns JWT cookie
POST   /api/auth/logout         → Clear JWT cookie
```

### Dashboard
```
GET    /api/dashboard/stats     → KPI counts (today's guests, queue, etc.)
GET    /api/dashboard/events    → Calendar events for FullCalendar
```

### Guests
```
GET    /api/guests              → List guests (?search=&page=&limit=)
GET    /api/guests/:id          → Single guest detail
POST   /api/guests              → Create guest
PUT    /api/guests/:id          → Update guest
DELETE /api/guests/:id          → Delete guest
```

### Visits
```
GET    /api/visits              → List visits (?date_from=&date_to=&status=)
GET    /api/visits/:id          → Single visit detail
POST   /api/visits              → Create visit (manual entry)
PUT    /api/visits/:id          → Update visit status
```

### Consultations
```
GET    /api/consultations       → Today's consultation queue
PUT    /api/consultations/:id   → Update consultation status
```

### Services
```
GET    /api/services            → List available services
```

### Kiosk
```
POST   /api/kiosk/register      → Submit visitor form + photo + face descriptor
GET    /api/kiosk/ticket/:id    → Get ticket data for printing
```

---

## 6. Design System

### Color Palette (Warm & Welcoming)

```
Primary:      #0D9488  (Teal 600)     — Main actions, active states
Primary Dark: #0F766E  (Teal 700)     — Hover states
Accent:       #F59E0B  (Amber 500)    — Highlights, notifications
Success:      #22C55E  (Green 500)    — Completed, approved
Warning:      #F59E0B  (Amber 500)    — Pending, attention
Danger:       #EF4444  (Red 500)      — Errors, delete actions

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

---

## 7. Page Specifications

### Kiosk Pages (Public — no auth)

**WelcomePage** (`/kiosk`)
- Full-screen video background with dark overlay
- Centered welcome message in large friendly text
- "Mulai / Start" button — large, teal, prominent

**ServiceSelectPage** (`/kiosk/service`)
- 6 service bubbles in a 2x3 grid
- Each bubble: icon + service name + short description
- Selected state: teal border + checkmark + subtle scale animation
- "Lanjut / Next" enabled only after selection

**VisitorFormPage** (`/kiosk/form`)
- Clean card form: name, institution, phone, email, purpose
- Large input fields (touch-friendly)
- Inline validation with friendly error messages
- Back/Next navigation at bottom

**FaceCapturePage** (`/kiosk/capture`)
- Live camera preview with face detection overlay
- Face frame guide (oval outline)
- "Ambil Foto / Capture" enabled when face detected
- Photo preview with retake/confirm options
- On confirm → submit all data → redirect to ticket

**QueueTicket** (`/kiosk/ticket/:id`)
- Queue number (large), service, name, date/time
- Auto-print via QZ Tray
- "Selesai / Done" button → returns to WelcomePage

### Admin Pages (Protected — JWT required)

**LoginPage** (`/login`)
- Centered card with logo, username, password
- Teal login button

**DashboardPage** (`/admin`)
- 4 stats cards (today's visitors, in queue, completed, pending)
- FullCalendar showing visit events
- Responsive: cards stack on mobile

**GuestListPage** (`/admin/guests`)
- Search bar + data table
- Columns: name, institution, phone, visit count, actions
- Pagination, edit modal, delete confirmation

**ConsultationQueuePage** (`/admin/consultations`)
- Today's queue as card list
- Each card: queue number, name, service, status badge
- Actions: call next, mark complete, skip

**VisitLogPage** (`/admin/visits`)
- Date range + status filters
- Data table with all visits
- Status badges (waiting, in progress, completed, cancelled)
- Expandable row detail

**ManualEntryPage** (`/admin/manual-entry`)
- Form matching kiosk fields in admin styling
- Submit creates visit + generates queue number

---

## 8. Technical Integration

### Face Detection (face-api.js)
- Wrapped in `useCamera` hook
- Models loaded once on FaceCapturePage mount from `/public/models/`
- Detect face → extract descriptor → Float32Array → JSON to API
- Fallback: file upload if camera denied

### Printing (QZ Tray)
- Wrapped in `usePrint` hook
- QZ Tray signing via existing `/assets/qz/sign.php`
- Auto-print on ticket page, manual "Print Again" backup

### Authentication Flow
- Login → POST /api/auth/login → JWT set as httpOnly cookie
- AuthProvider stores user info in React Context
- React Router checks auth before admin routes
- 401 response → redirect to /login
- Logout → POST /api/auth/logout → clear cookie

### Theme Persistence
- ThemeProvider reads/writes localStorage('theme')
- Applies `dark` class to `<html>` (Tailwind dark mode)
- Toggle in admin sidebar footer

### Kiosk Flow State
- Step wizard state managed with local useState
- Data flows forward through steps
- Final submit: single POST to /api/kiosk/register
- On success → redirect to ticket page

### CORS & Development
- CodeIgniter CORS headers for React dev server (localhost:5173)
- Production: React builds to static files, same domain or subdomain
- VITE_API_URL environment variable points to CodeIgniter API

---

## 9. Deployment Strategy

- **During development**: Old PHP frontend stays live in production
- **React dev**: Runs on localhost:5173, hits CodeIgniter API
- **When ready**: Build React app → deploy static files → point domain to React app
- **API**: Same CodeIgniter backend, API module always available
- **Rollback**: Switch back to old PHP frontend if issues arise

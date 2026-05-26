# Bukutamu — Claude Code Project Instructions

## ALWAYS FOLLOW

The files below are imported on every session start. Read them as if they were
inlined here — they are the source of truth for style, testing, API shape, and
deploy procedure.

@.claude/rules/code-style.md
@.claude/rules/testing.md
@.claude/rules/api-conventions.md
@.claude/skills/deploy/SKILL.md

In addition, this global rule from `~/.claude/CLAUDE.md` is **mandatory** for
every file edit (it predates this config and the project's `.gitignore` is
built around it):

1. Read the file before editing.
2. `cp {file} {file}.backup` immediately before the first edit.
3. Make targeted, minimal changes — keep existing style.
4. `diff {file}.backup {file}` to verify.

`*.backup` is in `.gitignore`, so the backup never reaches git.

**Commit discipline:** Do NOT add a `Co-Authored-By: Claude ...` trailer to
commits in this repo. This is a permanent project rule (see auto-memory
`feedback_no_co_authored`) and overrides the default Claude Code template.

## Project Overview

Bukutamu is the BPS Maluku Utara visitor-book / PST (Pelayanan Statistik
Terpadu) queueing system. It replaces both a paper-based logbook **and** a
pre-consolidation PHP app (`bukutamu-legacy/`). A single visit flows:
kiosk check-in (face recognition) → service selection → queue → consultation
(SKD / DTSEN / Resepsionis) → optional tablet evaluation → ticket print.

The monorepo bundles three components that were previously separate repos
(`tamdes-web`, `tamdes-frontend`, `tamdes-print`). Their histories are
preserved via `git subtree`; `git log -- backend/` shows pre-merge commits.

## Tech Stack

| Layer | Tech | Notes |
| --- | --- | --- |
| Backend | CodeIgniter 3 HMVC, PHP | JSON-API only — no server-side views. Apache vhost `bukutamu.bpsmalut.com` :60/:460. |
| Backend formatting | Prettier + `@prettier/plugin-php` | `backend/package.json` |
| Frontend | Vite 8 + React 19 + TypeScript 5.9 | Path alias `@/* → src/*`. ESLint flat config. |
| Styling | Tailwind 4 (`@tailwindcss/vite`) + tw-animate-css | No CSS modules. Use `clsx` + `tailwind-merge`. |
| Data | `@tanstack/react-query` v5 + axios | All API access goes through `src/api/*.ts`. |
| Router | `react-router-dom` 7 | |
| UI primitives | `@base-ui/react`, `lucide-react`, `sonner` (toast), FullCalendar | |
| Print kiosk | Node + Express + `escpos` / `escpos-usb` | Runs on **kiosk PCs** at `localhost:5300`, NOT on the server. |
| Database | MySQL `db_tamdes` | Name preserved deliberately — see README "Note on DB naming". |
| Process mgmt | PM2 (frontend), Apache (backend), manual (print kiosks) | `ecosystem.config.cjs` at repo root. |

## Commands

### Frontend (`cd frontend/`)
```bash
npm install              # install deps
npm run dev              # Vite dev server (default port 5173)
npm run build            # tsc -b && vite build → dist/
npm run lint             # eslint .
npm run preview          # serve built dist for smoke-testing
```

### Backend (`cd backend/`)
```bash
npm install              # installs Prettier + PHP plugin only
npx prettier --write .   # format PHP (use sparingly — review the diff)
# No PHPUnit. No phpcs. No migrations CLI — schema is hand-managed.
```

### Print server (`cd print/`)
```bash
npm install
npm start                # node server.js → :5300 (USB printer required)
```

### Process management
```bash
pm2 status                          # check bukutamu-frontend
pm2 restart bukutamu-frontend       # after build
pm2 logs bukutamu-frontend          # tail
sudo apachectl -k graceful          # reload Apache after backend changes
```

## Architecture / Folder Map

```
bukutamu/
├── backend/                          # CodeIgniter 3 — JSON API
│   ├── application/
│   │   ├── config/                   # database.php, routes.php (api/* only)
│   │   ├── modules/api/controllers/  # Api_base.php + one file per resource
│   │   ├── modules/api/models/
│   │   └── libraries/                # HMAC token, audit helpers
│   ├── assets/                       # uploads, face descriptors
│   └── index.php
├── frontend/                         # Vite + React 19 + TS
│   └── src/
│       ├── api/                      # axios wrappers — one file per resource
│       ├── components/{admin,kiosk,shared}/
│       ├── pages/{admin,kiosk}/
│       ├── hooks/, layouts/, providers/, lib/, types/, styles/
│       ├── App.tsx, main.tsx
│       └── global.d.ts
├── print/                            # Node thermal-print server (reference; runs on kiosks)
├── docs/                             # AUDIT, FLOW_PENGUNJUNG, CHANGELOG, migrations/
├── .claude/                          # Claude Code config (this directory)
├── CLAUDE.md                         # ← this file
├── ecosystem.config.cjs              # PM2
├── README.md
└── CHANGELOG.md
```

### Domain taxonomy (3 tiers — mirror in BE and FE)

- **SKD** — 4 inti services. Requires tablet evaluation. Gate prevents
  `selesai` without evaluation.
- **DTSEN** — Konsultasi DTSEN. Skip evaluation. Queue prefix `D`. Separate
  table `dtsen_konsultasi`, separate queue/form/list from SKD.
- **Resepsionis** — front-office, no evaluation.

Any change to service rules must touch **both** sides in the same session
(see auto-memory `feedback_backend_parity.md`).

## Do / Don't (project-specific)

- **DO** add new API endpoints in `backend/application/modules/api/controllers/`
  and register routes in `backend/application/config/routes.php`. Mirror
  the axios wrapper in `frontend/src/api/<resource>.ts`.
- **DO** keep face-recognition tuning constants at the top of their file
  (warmup 600 ms, 5-sample averaging, threshold 0.55, margin 0.08).
- **DO** add child cascades when adding a table that references `kunjungan` —
  see auto-memory `admin_delete_visit.md`.
- **DON'T** mediate print traffic through the backend. The kiosk browser
  fetches `localhost:5300` directly. Payload fields are `no` AND
  `nomor_antrian` (alias kept for compat).
- **DON'T** allow SKD visits to reach `selesai` without going through
  `menunggu_evaluasi` — the 3-layer gate is a security invariant
  (`status_finalization_gates.md`).
- **DON'T** rename the MySQL database `db_tamdes` — see README.
- **DON'T** add a role to the `admin_users.role` whitelist without an
  `ALTER TABLE` migration — column is an ENUM
  (`admin_users_role_enum.md`).
- **DON'T** use port 5000 — it belongs to `pds-backend`. Our ports: backend
  :60, dashboard-pst :5001, print :5300, frontend dev :5173, PM2 serve :3060.

## Using this Claude config

- For repeatable tasks, prefer the slash commands in `.claude/commands/`:
  `/review` (rule-aware diff against main), `/fix-issue` (issue → patch + tests
  outline).
- For code review or security work, dispatch a subagent from `.claude/agents/`:
  `code-reviewer.md` for correctness/style, `security-auditor.md` for secrets/
  injection/auth/dep risk.
- The deploy workflow lives in `.claude/skills/deploy/SKILL.md`. Trigger on
  "deploy", "release", "ship", or "push to production".
- Personal notes go in `CLAUDE.local.md` (git-ignored).

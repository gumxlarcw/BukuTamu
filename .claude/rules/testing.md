# Testing — bukutamu

## Reality check

**This repo has no automated test suite.** No PHPUnit, no Vitest/Jest, no
Playwright. The only `*.test.*` files in the tree live inside `node_modules/`.
Don't pretend otherwise. Don't invent `npm test` scripts.

That is a real risk on a production system — flag it if a change you're
making would clearly benefit from a regression test, and offer to scaffold
the first one. Until then, "testing" here means **manual verification** of
the items below.

## What to verify on every change

### Frontend
1. `npm run lint` — must be clean. ESLint catches the bulk of real issues.
2. `npm run build` — type-checks (`tsc -b`) AND bundles. A passing build is
   the closest thing to a test suite we have. Run it before saying "done".
3. Smoke test the affected page in the browser at `localhost:5173` (dev) or
   `localhost:3060` (PM2 serve). Don't claim a UI fix works without seeing
   the pixels.

### Backend
1. There is no linter or PHP type-checker wired up. Read the changed file
   end-to-end before saving.
2. Hit the affected endpoint with `curl` or via the frontend. Authenticated
   endpoints go through `Api_base.php` — check the JSON shape matches the
   axios wrapper in `frontend/src/api/<resource>.ts`.
3. For status-changing endpoints, verify the **3-layer finalization gate**
   isn't bypassed (`docs/AUDIT_2026-05-16.md`, auto-memory
   `status_finalization_gates.md`).

### Print server
- Cannot be tested from this machine — needs a USB-attached POS-58 printer.
  Verify the JSON contract by hand and ask the user to test on a kiosk.

## Cross-component regression checklist

When a change touches the **service taxonomy** (SKD / DTSEN / Resepsionis),
the **queue**, or the **status state machine**, manually walk this path
once:

1. Kiosk check-in → face recognition warm-up (600ms) → sampling 5 →
   match (threshold 0.55, margin 0.08).
2. Pick service → ticket prints (`no` + `nomor_antrian` payload).
3. PST dashboard calls the ticket → strict-mode TV call (abort DB update
   if dashboard offline).
4. SKD only: tablet evaluation flow → status moves `dipanggil` →
   `menunggu_evaluasi` → `selesai`. **SKD must not skip
   `menunggu_evaluasi`.**
5. DTSEN: skip evaluation, finish straight to `selesai`. Queue prefix `D`.
6. Admin → visit log → DELETE: cascades to 3 child tables + writes audit log.

## When introducing tests

If you do scaffold a first test, propose the framework explicitly:
- Frontend: **Vitest** (matches Vite). Co-locate as `*.test.ts(x)` next to
  the unit, or `__tests__/` for component trees.
- Backend: PHPUnit 9 (last CI3-compatible). Add `composer.json` if you go
  this route — currently there's only `backend/package.json` for Prettier.

Get user sign-off before adding either — they'd be the first tests in the
repo and the choice locks in tooling.

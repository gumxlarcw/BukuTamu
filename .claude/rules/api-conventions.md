# API Conventions — bukutamu

Backend is **JSON-only** since the 2026-05-17 cleanup. Apache vhost routes
`/api/*` → CodeIgniter; everything else → the React SPA at 127.0.0.1:3060.

## URL shape

- Prefix: every endpoint lives under `api/`.
- Resource → noun, plural: `api/visits`, `api/consultations`, `api/dtsen`,
  `api/evaluations`, `api/queue-stats`.
- Nested sub-resources: `api/visits/:id/status`, `api/visits/:id/summary`,
  `api/consultations/:id/call`.
- IDs are numeric (`(:num)` in CI3 routes). Use slug-style only for static
  collections (`api/auth/check`).
- Routes are explicitly listed in `backend/application/config/routes.php` —
  CI3 does **not** auto-route to controller methods in this project. Always
  add the route entry when you add a controller method.

## HTTP verbs

| Verb | Use for |
| --- | --- |
| `GET` | List + detail (`api/visits`, `api/visits/:id`). |
| `POST` | Create (`api/visits`) and stateful actions (`api/consultations/:id/call`, `api/evaluations`). |
| `PUT` | Partial update of a single field, esp. status (`api/visits/:id/status`). |
| `DELETE` | Soft / cascade delete (`api/visits/:id` — see `admin_delete_visit.md`). |

CI3 doesn't ship a router-level HTTP verb guard, so the controller method
must check `$this->input->method()` and 405 on mismatch — `Api_base.php`
has helpers; use them.

## Auth

- `Api_base.php` checks an `admin_users` session token on every request,
  except routes whitelisted as public.
- Kiosk-public endpoints (no admin login) use an **HMAC continuation token**
  passed in the request — anti-replay, short TTL. See
  `backend/application/libraries/` for the implementation. Don't roll a new
  auth scheme; reuse this one.

## Response shape

```jsonc
// Success
{ "ok": true, "data": { ... } }

// Success — list
{ "ok": true, "data": [...], "meta": { "total": 123 } }

// Failure
{ "ok": false, "error": "human-readable string", "code": "OPTIONAL_MACHINE_CODE" }
```

- HTTP status reflects the result: 200 success, 400 client error, 401 auth,
  403 forbidden, 404 not found, 409 conflict (e.g. duplicate queue), 422
  validation, 500 server. **Don't return 200 with `ok: false`** — the
  frontend axios interceptors expect status to match.

## Validation

- Validate at the controller boundary, not in the model. Reject early.
- Use CI3 `form_validation` for trivial cases, or hand-rolled checks for
  domain rules (e.g. role ENUM whitelist, service taxonomy bucket).
- Numeric IDs come from `(:num)` routes — still cast to `int` in the
  controller for safety.

## Frontend mirror

Every backend endpoint must have a wrapper in `frontend/src/api/<resource>.ts`:

```ts
// frontend/src/api/visits.ts (shape; adapt to existing file)
import { http } from '@/lib/http'
import type { Visit } from '@/types/visit'

export const listVisits = (params?) => http.get<Visit[]>('/api/visits', { params })
export const getVisit   = (id: number) => http.get<Visit>(`/api/visits/${id}`)
export const setStatus  = (id: number, status: string) =>
  http.put(`/api/visits/${id}/status`, { status })
```

The `http` axios instance normalizes the `{ ok, data, error }` envelope —
components should never see the envelope, just the unwrapped `data` or a
thrown error.

## Special endpoints

- **Print** is NOT a backend endpoint. The kiosk browser posts directly to
  `http://localhost:5300/print` on its own machine. Don't add a `/api/print`
  proxy.
- **Strict-mode TV call** (`api/consultations/:id/call`) aborts the DB
  update if the PST dashboard at `:5001` does not respond — this is
  intentional, do not "fix" it to a fire-and-forget pattern.

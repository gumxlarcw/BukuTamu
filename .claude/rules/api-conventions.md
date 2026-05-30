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

- `Api_base.php::require_auth()` validates a JWT carried in the `jwt_token`
  cookie (set at login by `Auth.php`). It is **opt-in per controller method** —
  call it explicitly at the top of each protected method; it is NOT applied
  automatically, so a method that forgets the call is public.
- Kiosk-public endpoints (no admin login) use an **HMAC continuation token**
  (`Api_base::mint_kiosk_token` / `require_kiosk_token`): HMAC-signed,
  namespaced by `purpose`, bound to a resource id, short TTL. Note it is
  **not** single-use — the same token is replayable within its TTL by design
  (e.g. evaluation re-submit for correction). The underlying JWT signing lives
  in `libraries/JWT_Helper.php`. Reuse this scheme; don't roll a new one.

## Response shape

```jsonc
// Success
{ "success": true, "data": { ... }, "message": "OK" }

// Success — list (paginated)
{ "success": true, "data": [...], "message": "OK",
  "pagination": { "page": 1, "limit": 20, "total": 123, "totalPages": 7 } }

// Failure
{ "success": false, "message": "human-readable string" }
```

- Keys are `success` / `data` / `message` — see `frontend/src/types/api.ts`
  (`ApiResponse<T>`, `PaginatedResponse<T>`). There is no `ok` / `error` /
  `code` field; don't add one without changing the type and every caller.
- HTTP status reflects the result: 200 success, 400 client error, 401 auth,
  403 forbidden, 404 not found, 409 conflict (e.g. duplicate queue), 422
  validation, 500 server. **Don't return 200 with `success: false`** —
  axios + react-query treat only non-2xx as errors, so a 200 with
  `success: false` slips through as a (wrong) success.

## Validation

- Validate at the controller boundary, not in the model. Reject early.
- Use CI3 `form_validation` for trivial cases, or hand-rolled checks for
  domain rules (e.g. role ENUM whitelist, service taxonomy bucket).
- Numeric IDs come from `(:num)` routes — still cast to `int` in the
  controller for safety.

## Frontend mirror

Every backend endpoint must have a wrapper in `frontend/src/api/<resource>.ts`:

```ts
// frontend/src/api/visits.ts (real shape)
import apiClient from './client'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import type { Visit, VisitStatus } from '@/types/visit'

export const visitsApi = {
  list: (params) => apiClient.get<PaginatedResponse<Visit>>('/api/visits', { params }),
  get:  (id: number) => apiClient.get<ApiResponse<Visit>>(`/api/visits/${id}`),
  updateStatus: (id: number, status: VisitStatus) =>
    apiClient.put<ApiResponse<Visit>>(`/api/visits/${id}/status`, { status }),
}
```

`apiClient` (`src/api/client.ts`) does **not** unwrap the envelope. Its only
response interceptor redirects to `/login` on a 401 from an `/admin` page,
otherwise it rethrows. Callers read the payload at `res.data.data` and the
message at `res.data.message`; type every call with `ApiResponse<T>` or
`PaginatedResponse<T>` so that the double `.data` is type-checked.

## Special endpoints

- **Print** is NOT a backend endpoint. The kiosk browser posts directly to
  `http://localhost:5300/print` on its own machine. Don't add a `/api/print`
  proxy.
- **Strict-mode TV call** (`api/consultations/:id/call`) aborts the DB
  update if the PST dashboard at `:5001` does not respond — this is
  intentional, do not "fix" it to a fire-and-forget pattern.

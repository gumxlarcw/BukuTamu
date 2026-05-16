# Phase B — Remaining items (require your decision/coordination)

Generated 2026-05-17 after closing Phase A audit findings. Phase A closed C1, C2, C3, M1, M2, M3, M4, M5, M6, M7, M8, N1 — all unilateral fixes that don't touch shared infra or require new credentials from you. Items below need your call.

---

## 1. MySQL `root` password rotation

### Why this is still pending

During the missing-`.env` investigation on 2026-05-16, the production DB credentials were echoed onto the audit terminal. The bukutamu backend has since been moved to a dedicated `bukutamu_app` MySQL user with `db_tamdes`-only grants, so **the leak no longer grants access to bukutamu's data via the backend**. But the `root` MySQL account is still active with the original (now-known) password, and these services on this server connect as `root`:

| Service | Database | Where credential lives |
|---------|----------|------------------------|
| `metabase_db` | metabase_db | Metabase internal config |
| `email_notify` | email_notify | (need to locate) |
| `data_bps` | data_bps | (need to locate) |
| `agenda_work_db` | agenda_work_db | (need to locate) |

A patient attacker with the leaked `root` password and localhost MySQL access can still touch all DBs (`mysql -uroot -p`). MySQL likely binds to `127.0.0.1:3306` only, so this requires shell access — but it's still a real path.

### Two strategies

#### Strategy A — Rotate root password (cheap, one-time)

Single maintenance window. Updates every service's config in lockstep.

```bash
# 1. Generate new password
NEW_ROOT=$(openssl rand -base64 32 | tr -d '/+=' | head -c 40)

# 2. Apply to MySQL (this disconnects nothing yet — existing sessions keep working)
sudo mysql -uroot -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$NEW_ROOT';"
sudo mysql -uroot -e "FLUSH PRIVILEGES;"  # optional

# 3. Update each service's config (find these locations first!)
#    Then restart each service so the next connection uses the new password.

# 4. Update the root mysql client default (so `mysql -uroot ...` keeps working)
sudo mysql_config_editor remove --login-path=client  # or update ~/.my.cnf

# 5. Test each service can still connect
```

**Risk:** If you miss a service, its next reconnect fails. Use `SELECT USER FROM information_schema.processlist GROUP BY USER` to discover every connecting client first.

#### Strategy B — Dedicated user per service (clean, durable)

Same pattern we used for bukutamu_app. For each service:

```sql
CREATE USER 'metabase_app'@'localhost' IDENTIFIED BY '<random>';
GRANT SELECT, INSERT, UPDATE, DELETE, LOCK TABLES, CREATE TEMPORARY TABLES
  ON metabase_db.* TO 'metabase_app'@'localhost';
```

Update each service's config to use its dedicated user. Leave `root` alone (the leak still technically reaches `root` but no service is using it, so any unauthorized attempt now stands out in the audit log).

**Eventually**: rotate `root` password too once nothing depends on it. That can happen at your leisure without coordinating with multiple services.

### My recommendation

**Strategy B**. Higher one-time cost (~2-3 hours across all 4+ services) but eliminates this entire class of risk permanently. Each future service rotation is independent. The `root` user stays available for true admin tasks but stops being a backdoor for every app.

### How to find each service's config

```bash
# Locate where each app's DB password lives:
sudo find /var/www /opt /etc -name "*.env" -o -name "settings.py" -o -name "database.php" 2>/dev/null \
  | xargs sudo grep -lE "DB_PASSWORD|DATABASE_URL|password.*root" 2>/dev/null

# Service definitions (systemd unit files often contain DB env vars):
sudo systemctl cat metabase 2>/dev/null
ls /etc/systemd/system/ | xargs -I{} sudo grep -l "mysql\|MYSQL" /etc/systemd/system/{} 2>/dev/null
```

---

## 2. `ADMIN_PASSWORD_HASH` rotation

### Why pending

The admin password hash was readable when the missing-`.env` was investigated. It's stored in `backend/.env` as a bcrypt hash, used as the fallback when `admin_users` table doesn't match (per `Auth::login`). Real admin users in `admin_users` (admin, nayla, irma, nita) each have their own `password_hash` column — those are independent and unaffected.

### Two options

#### Option A — You provide a new admin password

Tell me a new password. I'll:
1. Generate the bcrypt hash via `php -r "echo password_hash('YOUR_NEW_PASSWORD', PASSWORD_DEFAULT);"`
2. Replace `ADMIN_PASSWORD_HASH=` in `backend/.env`
3. `.env.backup` will reflect the new value (no leftover old hash)
4. Smoke-test login with new password

#### Option B — I generate a random password

I'll generate, write it to `/tmp/new-admin-password.txt`, you read it once, then we shred the file. The hash goes into `.env` the same way. Useful if you don't have a password manager handy.

### Note

Both options ONLY rotate the `.env` fallback hash. The `admin_users` table users (admin, nayla, irma, nita) keep their own passwords. If you want to rotate those too, they each go through `/api/users/change-password` (already implemented).

---

## 3. Strategic followups (low-priority)

### Nits N2–N8 from audit §7

| Nit | What | Effort |
|---|---|---|
| N2 | `evaluations.ts` references `EvaluationSummary` type before declaration | 5 min cosmetic |
| N3 | (already gone with C2 cleanup) | — |
| N4 | `Responden::_skd_clause` uses `addslashes` instead of CI3 escape | 5 min, swap for `$this->db->escape` |
| N5 | `Dashboard::stats` rebuilds queries instead of one aggregate | 30 min performance opt |
| N6 | `usePrint.ts` has no auto-retry on USB unplug | Acceptable — Reprint button exists |
| N7 | Hardcoded service list in PHP — covered by FE-BE parity rule | Working as designed |
| N8 | `print/server.js` allows `Access-Control-Allow-Origin: *` | Add `127.0.0.1` bind |

### Future hardening (not in original audit)

- **Apache IP allowlist on `/api/kiosk/*`** — proper fix for M4 (we did soft rate-limit). Requires you to enumerate kiosk PC IPs.
- **`/api/kiosk/face-data` paging** — right now returns the entire `tamdes_buku` set every page-load. As the guest table grows, this gets expensive. Eventually should be `?since=<timestamp>` incremental + client-side merge.
- **CI3 → modern framework migration** — CodeIgniter 3 EOL is approaching. Plan for Laravel/Symfony or a Node rewrite in next year's cycle.

---

## State of the system as of this writing

All Phase A fixes deployed live and pushed to `origin/main`:

```
6c8467d  docs(audit): mark M2 + M4 + M7 as fixed in findings status
46f7a5c  feat(api): soft rate-limit for kiosk enumeration endpoints (mitigates M4)
4fccce6  refactor(frontend): typed API clients for admin endpoints (closes M7)
8d8624d  chore: delete legacy CI3 modules (closes audit C2 + M2)
4aed38c  fix(api): surface FK / insert failures in Kiosk::register and Kiosk::visit
6da0e7c  docs(audit): mark C3 + M1 as fixed in findings status table
2b9d17d  feat(kiosk): signed continuation tokens for profile-update + eval submit
2c2ebfd  chore: root .gitignore for *.backup files
1564b9f  docs: comprehensive bukutamu audit + missing .env.example keys
9a85ee4  feat(frontend): RequireRole component gates /admin/audit and /admin/users
ff98258  feat(api): kiosk visit dedup + guests safety (LOCK + cascade-guard)
632c95d  fix(api): role-gate /api/audit endpoint to admin+
```

Production endpoints sampled: all healthy.

Frontend bundle: rebuilt and PM2-restarted with all M7 typed API clients live.

Database: `bukutamu_app` MySQL user active with minimum grants; `tamdes_rate_limit` table created and exercised in smoke tests.

Working tree clean, `main...origin/main` in sync.

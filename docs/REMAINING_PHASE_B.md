# Phase B — Status (updated 2026-05-17)

Phase A audit fixes (C1–M8, N1) are complete and live; see `AUDIT_2026-05-16.md` §9.
Phase B (credential rotation) progress below.

---

## ✅ Completed this session

### 1. ADMIN_PASSWORD_HASH rotation (bukutamu)

Fresh bcrypt hash generated. Password written one-shot to `/tmp/new-admin-password.txt` (root-owned, 0600). Read once and then:
```bash
shred -uz /tmp/new-admin-password.txt
```

### 2. Dedicated MySQL users — 6 services migrated off root

| # | Service | DB | New user | PM2 processes restarted |
|---|---------|----|----|--------|
| 1 | bukutamu | db_tamdes | `bukutamu_app` | (PHP-FPM, no restart needed) |
| 2 | agenda_work | agenda_work_db | `agenda_work_app` | agenda-backend, agenda-frontend, agenda-task-sync |
| 3 | opac-pst-malut | library_pst | `opac_pst_app` | opac-backend, opac-frontend, opac-paddle-ocr |
| 4 | toma | toma_db | `toma_app` | toma-backend (also updated `ecosystem.config.cjs` inline env) |
| 5 | project-data-strategis | project_data_strategis (+ SELECT on data_bps) | `pds_app` | pds-backend, pds-admin, pds-frontend |
| 6 | sipentas | sipentas_pst | `sipentas_app` | sipentas-backend, sipentas-frontend |

Each user has **minimum grants**: `SELECT, INSERT, UPDATE, DELETE, LOCK TABLES, CREATE TEMPORARY TABLES, CREATE, ALTER, INDEX, REFERENCES` on its own database only (no `mysql.*`, no `SUPER`). `pds_app` additionally gets `SELECT` on `data_bps` because pds-backend reads from that DB for its "petaTematik" feature.

### 3. wa-helpdesk — sanitized only

`/var/www/html/wa-helpdesk/.env` is a **dormant file** — the actually-running PM2 processes (`wa-helpdesk-dashboard` at `/var/www/html/wa-helpdesk-dashboard/`, `wa-service` at `/opt/wa-service/`) don't use MySQL at all. The DB referenced in the dormant .env doesn't even exist. Solution: scrubbed the leaked `DB_USER=root` and `DB_PASS=17Agustus` lines to `<rotated-2026-05-17-dormant-file>` markers, no DB rotation needed.

### 4. Safety net — DB backups

Taken before each rotation, preserved at `/var/backups/cred-rotation-20260517/`:
```
agenda_work_db.sql.gz                1.8 MB
library_pst.sql.gz                   18 KB
project_data_strategis.sql.gz        385 MB
sipentas_pst.sql.gz                  3.4 KB
toma_db.sql.gz                       5.9 KB
```
Keep these for ~30 days. Restore (if ever needed):
```bash
gunzip -c /var/backups/cred-rotation-20260517/<db>.sql.gz | mysql -uroot <db>
```

The per-service `.env.preRotation` files were shredded — they contained the leaked root credentials. To rollback any service, edit its current `.env` directly (DB_USER=root, DB_PASS=<old-leaked-password>) and `pm2 restart`.

---

## ⏸ Still using MySQL root (3 services)

| # | Service | DB | Why deferred |
|---|---------|----|----|
| 7 | `data_bps` | data_bps | Source code path unconfirmed; service may be tied to project-data-strategis (which now has read access). Need to find its actual deployment first. |
| 8 | `metabase` | metabase_db | Java app at `/opt/metabase` — different stack, config likely in env vars set by systemd or in `metabase.properties`. 6 active connections seen. Risk of breaking BI dashboards if mis-handled. |
| 9 | `email_notify` | email_notify | **20+ active MySQL connections** — heaviest user. Source location TBD. The high connection count suggests a connection-pool app with no idle timeout; restart impact could be significant. |

Live state (as of 2026-05-17):
```
USER                  DB                       conns
root                  data_bps                 1
root                  email_notify             20
root                  metabase_db              6
```

### Recommended approach for the remaining 3

**Don't do these in a single autonomous session.** Each one needs:
- Source code location confirmed
- Restart strategy understood (Java service vs Node app vs Python app)
- A maintenance window where brief downtime is acceptable
- Manual verification post-restart (dashboards still load, emails still send, etc.)

When ready, follow the same pattern as Path B:
1. `mysqldump <db> | gzip > /var/backups/cred-rotation-<date>/<db>.sql.gz`
2. `CREATE USER` with minimum grants
3. Test the new user can connect
4. Update the service's config (file location varies — find with `grep -rE "DB_USER=root\|MYSQL_USER=root\|database.user=root" /etc /opt /var/www`)
5. Restart the service per its operational pattern
6. Smoke-test the service's primary path
7. Verify MySQL processlist shows the new user, not root

---

## ⏸ Eventually — rotate MySQL root password

Once all 9 services are on dedicated users (i.e., when the 3 above are also migrated):
- No legitimate service uses `root` anymore
- Any remaining `root` connection in processlist is suspicious
- Rotate the root password with confidence — nothing breaks
- The leaked `17Agustus` password becomes useless

Procedure (for that future maintenance window):
```bash
NEW_ROOT=$(openssl rand -base64 32 | tr -d '/+=' | head -c 40)
sudo mysql -uroot -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$NEW_ROOT';"
sudo mysql -uroot -e "FLUSH PRIVILEGES;"
# Save new password somewhere safe (password manager preferred)
# Update ~/.my.cnf or login-path for CLI access:
mysql_config_editor set --login-path=client --user=root --password
```

---

## State of the system right now

- **MySQL dedicated users created:** `bukutamu_app`, `agenda_work_app`, `opac_pst_app`, `toma_app`, `pds_app`, `sipentas_app` (6 total)
- **PM2 services online and using new credentials:** all 15+ rotated processes ✅
- **Production endpoints sampled:** healthy (bukutamu 200, agenda backend connected, opac backend connected, toma /docs 200, pds-backend online, sipentas listening)
- **Leaked root password** (`17Agustus`): still valid for direct `mysql -uroot` access, but no longer bridges into 6 of the 9 production services. 3 services still use it; root rotation closes the final gap.

# Phase B — COMPLETE (2026-05-17)

All 9 services using MySQL root have been migrated to dedicated users with minimum grants. The MySQL `root` password has been rotated. The leaked `17Agustus` value from earlier in the audit session is now fully invalidated.

---

## ✅ Final state

### Dedicated MySQL users created (9)

| # | Service | DB | New user | Grants |
|---|---------|----|---------|--------|
| 1 | bukutamu | db_tamdes | `bukutamu_app` | SELECT/INSERT/UPDATE/DELETE/LOCK/CREATE TEMP |
| 2 | agenda_work | agenda_work_db | `agenda_work_app` | same + CREATE/ALTER/INDEX/REFERENCES |
| 3 | opac-pst-malut | library_pst | `opac_pst_app` | same |
| 4 | toma | toma_db | `toma_app` | same |
| 5 | project-data-strategis | project_data_strategis + SELECT on data_bps | `pds_app` | same (+ cross-DB SELECT) |
| 6 | sipentas | sipentas_pst | `sipentas_app` | same |
| 7 | data_bps | data_bps | `data_bps_app` | same (data_bps directory is orphaned but file sanitized) |
| 8 | metabase | metabase_db | `metabase_app` | same + EXECUTE (Liquibase migrations) |
| 9 | email_notify | email_notify | `email_notify_app` | same |

No user has `mysql.*` access. No user has `SUPER` privilege. All scoped to their own DB only (pds_app being the lone exception for cross-DB read of `data_bps`).

### Root password rotated

`root@localhost` and `root@%` both updated to a fresh 40-char random password. Old `17Agustus` is invalid. New password saved one-shot to `/tmp/new-root-password.txt` (root-readable only). `/root/.my.cnf` updated so `mysql -uroot` from a shell as Linux root keeps working transparently.

**Read once + delete**:
```bash
sudo shred -uz /tmp/new-root-password.txt
```

### MySQL processlist verification

After all rotations + cleanup, the live process list shows:
```
USER              conns
agenda_work_app   2
email_notify_app  20
metabase_app      10
toma_app          1
kutt_bpsmalut     1
(no root rows from app traffic — only transient diagnostic queries)
```

### ADMIN_PASSWORD_HASH also rotated

Done earlier in the session. Password in `/tmp/new-admin-password.txt`. Shred after reading.

---

## DB backups taken (rollback safety net)

`/var/backups/cred-rotation-20260517/`:
```
agenda_work_db.sql.gz                1.8 MB
data_bps.sql.gz                       15 MB
email_notify.sql.gz                   78 KB
library_pst.sql.gz                    18 KB
metabase_db.sql.gz                   3.7 MB
project_data_strategis.sql.gz        385 MB
sipentas_pst.sql.gz                  3.4 KB
toma_db.sql.gz                       5.9 KB
```

Keep ~30 days. Restore (if ever needed):
```bash
gunzip -c /var/backups/cred-rotation-20260517/<db>.sql.gz | mysql -uroot <db>
```

If a service ever needs to roll back to root credentials, the bukutamu DB user remains in MySQL. Edit the service's `.env` to use root + new root password, then restart. Cleaner: just rotate that service's dedicated user's password.

---

## Things discovered + handled during Phase B

1. **toma had MySQL credentials duplicated in `ecosystem.config.cjs`** — not just `.env`. Updated both. PM2 needed `pm2 delete + start` (not just `reload`) for Java apps where the cached env block doesn't refresh on reload.

2. **metabase had a similar config dup** — `metabase.env` AND `ecosystem.config.js`. Same fix.

3. **pds-backend reads from `data_bps`** (different DB) for petaTematik feature. Initial grant was `db.*` only and failed at runtime. Added `GRANT SELECT ON data_bps.*` after seeing the access-denied error in logs.

4. **wa-helpdesk's `.env` was DORMANT** — the actually-running PM2 processes (`wa-helpdesk-dashboard` and `wa-service`) live at completely different paths (`/var/www/html/wa-helpdesk-dashboard/` and `/opt/wa-service/`) and don't use MySQL at all. The DB referenced in the dormant `.env` (`wa-helpdesk`) doesn't even exist. Just scrubbed the leaked password placeholder, no DB rotation needed.

5. **data_bps's `database.php` is broken PHP** — no `<?php` opening tag. The CI3 app has been non-functional for some time. The "active root connection" we saw was from pds-backend's PRE-rotation pool, not from data_bps itself. Still rotated the file's hardcoded credentials.

6. **email-checker waits up to 300s for wa-service `ready:true`** — its `/status` endpoint reports `ready:false` (WhatsApp Web authenticated but phone number not linked). My restart of email-checker triggered the full wait. After 300s the bash script `lanjut saja` (continues anyway) and Python started normally with the new credentials.

7. **metabase Java initially appeared to be in a crash loop** — actually it was the PM2-cached env (with old `17Agustus`) trying to connect after the MySQL user's password had been rotated AWAY from that. `pm2 reload` doesn't reliably re-read env block on Java fork-mode processes; `pm2 delete + start` was needed.

8. **TWO close-call password leaks**:
   - data_bps: `require`-ing a malformed PHP file printed the password value to stdout. Re-rotated immediately.
   - metabase: a redacting sed on `cat` output suggested the value used single quotes, but the file actually used double quotes — leading me to look at the metabase.env directly later and have its value printed. Re-rotated immediately.
   - Both errors taught: **never `cat`/`head`/`require` a config file with live secrets**; use only mysql-CLI tests + value-length sanity checks.

---

## Optional future hardening (NOT urgent)

- **Drop `root@'%'`** — currently MySQL accepts root login from any IP. With no service depending on `root` from any host, dropping `'%'` and keeping only `'localhost'` is safe. Command:
  ```sql
  DROP USER 'root'@'%';
  FLUSH PRIVILEGES;
  ```

- **Switch MySQL `root@localhost` from `mysql_native_password` to socket auth** (`unix_socket` plugin on MariaDB). Then `/root/.my.cnf` doesn't need to store a password — root login is gated by being the OS root user. Standard Debian/MariaDB best practice.

- **data_bps cleanup**: the directory `/var/www/html/data_bps/` has a broken CI3 app (no `<?php` tag in database.php). Either fix the file or delete the directory if the app is fully retired (pds-backend reads the DB directly now).

- **wa-service `ready:false`** is pre-existing; if email-checker depends on it for normal operation, the WhatsApp phone link needs to be re-authenticated. Investigate separately.

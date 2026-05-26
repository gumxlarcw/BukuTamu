---
description: Review the current branch's diff against main against this project's rules
allowed-tools: Bash(git diff:*), Bash(git log:*), Bash(git status:*), Bash(npm run lint:*), Bash(npm run build:*), Read, Grep, Glob
---

You are reviewing the diff between the current branch and `main` for the
bukutamu repo. Be terse and concrete — every finding must cite a file:line.

## Steps

1. Run `git status` and `git log main..HEAD --oneline` to see scope.
2. Run `git diff main...HEAD` and read the full diff. If it's large
   (>500 lines), summarize per-file first, then drill into hotspots.
3. Open the imported rule files and check the diff against each:
   - `.claude/rules/code-style.md` — formatting, naming, imports, React patterns.
   - `.claude/rules/testing.md` — does this change need a regression check the
     user should run? Flag explicitly.
   - `.claude/rules/api-conventions.md` — route shape, envelope, validation,
     auth, FE↔BE mirror.
4. Specifically watch for **project invariants** (these are non-negotiable):
   - SKD visits MUST go through `menunggu_evaluasi` before `selesai`.
   - DTSEN flow stays separate from SKD (table, queue prefix `D`, no eval).
   - Print payload includes BOTH `no` and `nomor_antrian`.
   - No `Co-Authored-By: Claude` trailers in commits.
   - `admin_users.role` ENUM — adding a role requires `ALTER TABLE`.
   - DELETE on visits must cascade + write audit log.
   - Strict-mode TV call must abort DB update on dashboard timeout.
   - No port 5000 usage anywhere new (collides with pds-backend).
5. Run `cd frontend && npm run lint` if frontend files changed.
   Run `cd frontend && npm run build` if TS/types changed — fail the review
   loudly if either breaks.
6. Output a structured report:

```
## Review of <branch> vs main

### Blockers
- file:line — what's wrong + which rule/invariant it violates

### Suggestions
- file:line — improvement (not a blocker)

### Verified
- lint: pass/fail
- build: pass/fail/skipped
- invariants checked: <list>
```

Do not fix anything — only report. If the user wants fixes, they'll ask.

$ARGUMENTS

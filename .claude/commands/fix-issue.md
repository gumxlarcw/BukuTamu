---
description: Implement a fix for a described issue, with a manual-test plan since the repo has no automated tests
allowed-tools: Bash(git status:*), Bash(git diff:*), Bash(git log:*), Bash(git checkout:*), Bash(npm run lint:*), Bash(npm run build:*), Read, Edit, Write, Grep, Glob
---

You will fix the issue described in `$ARGUMENTS`. The argument may be a free-form
description, a GitHub issue number (e.g. `#42`), or a one-line summary.

## Steps

1. **Understand**:
   - Restate the issue in your own words so the user can correct any
     misreading before code changes.
   - If it's an issue number, surface what you know from `git log --grep`
     or commit messages mentioning that number (this repo doesn't have
     a `gh` integration).
   - Identify whether the fix is frontend, backend, print, or cross-cutting.

2. **Locate**:
   - Grep for relevant symbols/files. List the files you'll touch and why,
     BEFORE editing.
   - For backend changes, identify the controller + route + frontend axios
     wrapper that mirror each other (api-conventions.md rule).

3. **Confirm scope** — pause here and ask the user:
   - "I plan to edit: [list]. Anything off-limits or higher-priority?"
   - Wait for "go" before writing code.

4. **Implement**:
   - Follow the global backup rule: `cp <file> <file>.backup` before each
     edit, then `diff` after.
   - Make minimal targeted changes. No drive-by refactors.
   - If the fix touches domain rules (service taxonomy, queue, status
     gate), edit BOTH frontend and backend in the same session.

5. **Manual test plan** — output a numbered list of verifications the user
   should run, since there's no automated suite. Be specific (URL, click
   path, expected JSON). Mark which ones you ran yourself
   (`npm run lint`, `npm run build`) and which ones only the user can run
   (browser interactions, kiosk + thermal printer).

6. **Do NOT commit** unless the user explicitly asks. If you do commit:
   - No `Co-Authored-By: Claude` trailer.
   - Use the project's existing commit-message style
     (`git log --oneline -20` to sample).

$ARGUMENTS

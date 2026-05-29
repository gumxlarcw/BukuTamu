#!/usr/bin/env bash
# PreToolUse hook for the `Bash` tool — second layer of defense behind
# the deny patterns in .claude/settings.json. Reads the proposed tool input
# from stdin (JSON), inspects the command, and exits non-zero to block.
#
# Exit codes:
#   0  — allow
#   2  — block (stderr message is shown to the model and the user)
#
# This script is intentionally minimal. It catches gross mistakes, not a
# determined attacker. The real safety net is human review of the diff.

set -euo pipefail

# Read the hook payload — Claude Code provides a JSON object on stdin.
# We only need .tool_input.command for Bash. If jq isn't available, fall
# back to a grep-based extractor.
payload=$(cat || true)

if command -v jq >/dev/null 2>&1; then
  cmd=$(printf '%s' "$payload" | jq -r '.tool_input.command // empty')
else
  cmd=$(printf '%s' "$payload" | grep -oE '"command"[[:space:]]*:[[:space:]]*"[^"]*"' | head -1 | sed -E 's/.*"command"[[:space:]]*:[[:space:]]*"([^"]*)".*/\1/')
fi

# Nothing to validate — let it through.
if [[ -z "${cmd:-}" ]]; then
  exit 0
fi

block() {
  # stderr is surfaced to the model; exit 2 is the "block" signal.
  printf 'BLOCKED by .claude/hooks/validate-bash.sh: %s\n' "$1" >&2
  printf 'Command: %s\n' "$cmd" >&2
  exit 2
}

# --- destructive filesystem ---
[[ "$cmd" =~ (^|[[:space:]\;\&\|])rm[[:space:]]+(-[a-zA-Z]*[rf]+[a-zA-Z]*|--recursive|--force) ]] && \
  block "destructive rm (recursive or force). Use 'mv to a trash dir' or ask the user."

[[ "$cmd" =~ (^|[[:space:]\;\&\|])sudo[[:space:]]+rm ]] && \
  block "sudo rm. Refuse — get explicit user confirmation outside Claude."

[[ "$cmd" =~ (^|[[:space:]\;\&\|])mv[[:space:]]+[^[:space:]]+[[:space:]]+/dev/null ]] && \
  block "mv to /dev/null is destructive."

# The '>' below must live in a variable: a bare '>' inside [[ =~ ]] is parsed
# by bash as a redirection operator (syntax error). Expanding an unquoted
# variable on the RHS of =~ stops bash re-tokenizing it, so '>' stays literal.
re_blockdev_redirect='(^|[[:space:]\;\&\|])>[[:space:]]*/dev/sd[a-z]+'
[[ "$cmd" =~ $re_blockdev_redirect ]] && \
  block "redirect to raw block device."

# --- git foot-guns ---
[[ "$cmd" =~ git[[:space:]]+push[[:space:]]+(.*[[:space:]])?(--force|-f)([[:space:]]|$) ]] && \
  block "git push --force. Use --force-with-lease and confirm with user."

[[ "$cmd" =~ git[[:space:]]+reset[[:space:]]+(.*[[:space:]])?--hard ]] && \
  block "git reset --hard wipes local changes. Ask the user."

[[ "$cmd" =~ git[[:space:]]+clean[[:space:]]+(.*[[:space:]])?(-fd|-fdx|-df|-xdf) ]] && \
  block "git clean -fd removes untracked files irrecoverably."

[[ "$cmd" =~ git[[:space:]]+branch[[:space:]]+(.*[[:space:]])?-D ]] && \
  block "git branch -D force-deletes a branch."

[[ "$cmd" =~ git[[:space:]]+commit[[:space:]]+(.*[[:space:]])?--no-verify ]] && \
  block "git commit --no-verify skips hooks. User must approve explicitly."

[[ "$cmd" =~ Co-Authored-By:[[:space:]]+Claude ]] && \
  block "Co-Authored-By: Claude trailer is forbidden in this repo (auto-memory: feedback_no_co_authored)."

# --- shell-piped installers ---
[[ "$cmd" =~ (curl|wget)[[:space:]]+.*[[:space:]]*\|[[:space:]]*(sh|bash|zsh)([[:space:]]|$) ]] && \
  block "curl|sh / wget|sh pattern. Download to a file, review, then run."

# --- MySQL writes / DDL ---
if [[ "$cmd" =~ ^[[:space:]]*mysql ]]; then
  upper_cmd="${cmd^^}"
  for danger in "DROP DATABASE" "DROP TABLE" "TRUNCATE" "DELETE FROM" "UPDATE " "ALTER TABLE" "GRANT ALL"; do
    if [[ "$upper_cmd" == *"$danger"* ]]; then
      block "MySQL command contains '$danger'. Refuse without explicit user confirmation."
    fi
  done
fi

# --- production process kills ---
[[ "$cmd" =~ pm2[[:space:]]+(delete|kill|unstartup) ]] && \
  block "pm2 delete/kill/unstartup affects the running production process."

[[ "$cmd" =~ apachectl[[:space:]]+(stop|-k[[:space:]]+stop) ]] && \
  block "apachectl stop takes the backend offline. Use 'graceful' for reload."

# --- port-5000 collision (pds-backend) ---
[[ "$cmd" =~ (^|[^0-9])5000([^0-9]|$) ]] && [[ "$cmd" =~ (listen|bind|--port|PORT=|-p[[:space:]]) ]] && \
  block "port 5000 is owned by pds-backend (auto-memory: port_assignments)."

# All checks passed — allow.
exit 0

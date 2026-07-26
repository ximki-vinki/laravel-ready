#!/usr/bin/env bash
# Release smoke against fixtures (CLI contract exit codes + stable footers).
# Usage:
#   scripts/smoke-fixtures.sh php build/laravel-ready.phar
#   scripts/smoke-fixtures.sh ./build/laravel-ready.exe
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX="$ROOT/tests/Fixtures"
CONFIG_ROOT="$FX/Use"

if [[ $# -lt 1 ]]; then
  echo "usage: $0 <binary> [args...]" >&2
  echo "  e.g. $0 php build/laravel-ready.phar" >&2
  exit 2
fi

BIN=("$@")
for i in "${!BIN[@]}"; do
  if [[ "${BIN[$i]}" == */* && "${BIN[$i]}" != /* ]]; then
    BIN[$i]="$ROOT/${BIN[$i]#./}"
  fi
done

run_case() {
  local label="$1" expect="$2" needle="$3" file="$4"
  local out code

  set +e
  out="$(cd "$CONFIG_ROOT" && "${BIN[@]}" "$file" --no-ansi 2>&1)"
  code=$?
  set -e

  out="$(printf '%s' "$out" | tr -d '\r')"

  if [[ "$code" -ne "$expect" ]]; then
    echo "FAIL [$label]: exit=$code expected=$expect" >&2
    echo "$out" >&2
    exit 1
  fi

  if [[ -n "$needle" ]] && ! grep -Fq "$needle" <<<"$out"; then
    echo "FAIL [$label]: missing: $needle" >&2
    echo "$out" >&2
    exit 1
  fi

  echo "ok [$label] exit=$code"
}

# Pass — each readiness tag
run_case "pass @laravel-ready" 0 "" \
  "$FX/Tags/laravel-ready/class.php"

run_case "pass @laravel-adapter" 0 "" \
  "$FX/Tags/laravel-adapter/class.php"

run_case "pass @legacy-adapter @allows" 0 "" \
  "$FX/Tags/legacy-adapter/with-allows.php"

run_case "pass @legacy-perfect" 0 "" \
  "$FX/Tags/legacy-perfect/class.php"

run_case "pass @legacy-code with findings" 0 "" \
  "$FX/Tags/Mixed/tag-and-blocker.php"

run_case "pass @skipCheck with blockers" 0 \
  "Skipped: @skipCheck." \
  "$FX/Tags/laravel-adapter/skip-check-with-blocker.php"

# Fail — stable Guard failed / MultiTag / Not guarded footers
run_case "fail @laravel-ready blocker" 1 \
  "Guard failed: @laravel-ready file must stay LaravelReady." \
  "$FX/Tags/laravel-ready/with-blocker.php"

run_case "fail @laravel-adapter blocker" 1 \
  "Guard failed: @laravel-adapter file must stay LaravelAdapter." \
  "$FX/Tags/laravel-adapter/with-blocker.php"

run_case "fail @legacy-adapter blocker" 1 \
  "Guard failed: @legacy-adapter file must stay in legacy contour." \
  "$FX/Tags/legacy-adapter/with-blocker.php"

run_case "fail @legacy-perfect blocker" 1 \
  "Guard failed: @legacy-perfect file must stay cleaned in legacy contour." \
  "$FX/Tags/legacy-perfect/with-blocker.php"

run_case "fail MultiTag" 1 \
  "MultiTag failed: file must have only one tag." \
  "$FX/Tags/Mixed/multi-tag.php"

run_case "fail Untagged" 1 \
  "Not guarded: file has no tag." \
  "$FX/Legacy/Clean/empty.php"

# Dependency guard (use)
run_case "fail @laravel-ready bad use" 1 \
  "Guard failed: @laravel-ready file must stay LaravelReady." \
  "$FX/Use/project/app/Domain/Invoice.php"

echo "smoke-fixtures: all passed"

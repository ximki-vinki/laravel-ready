# Public CLI contract 0.x (beta)

This document is the **compatibility promise for beta consumers**: what is safe
to rely on in git hooks and CI, and what remains an internal implementation
detail.

Internal architecture lives in `ARCHITECTURE.md`, `READINESS_MODEL.md`, and
`RESOLUTION_AND_OUTPUT.md`.

**Versioning:** before `1.0.0`, semver may include breaking changes, but items
marked **stable** below are the intended compatibility floor for beta. Changes
to the stable surface require an entry in `CHANGELOG.md`.

---

## Command (stable)

```text
laravel-ready <path> [<path>...]
```

| Element               | Contract                                                |
|-----------------------|---------------------------------------------------------|
| Binary / command name | `laravel-ready`                                         |
| Working directory     | Repository root containing `laravel-ready.json`         |
| `<path>`              | One or more paths to a `.php` file or directory         |
| No arguments          | Command help, exit `0`                                  |

**Beta delivery:** binaries for Windows, Linux, and macOS (see Releases).
Running from source via `php bin/laravel-ready` is for package development, not
the primary install path for consumers.

---

## Project configuration (stable, beta)

Before beta, class resolution and project paths are configured via an **explicit
JSON file** in the analysed repository root, not hard-coded prefixes inside the
package.

**File:** `laravel-ready.json` in the current working directory (name and
location are stable for 0.x beta). Resolver paths are relative to this file.

Minimal shape (extends as implementation lands):

```json
{
  "vendor-dir": "libs",
  "resolvers": [
    {
      "prefix": "App\\",
      "path": "project/app/",
      "extensions": [
        ".php",
        ".class.php"
      ]
    },
    {
      "prefix": "Wf\\",
      "type": "wf-pear",
      "libs-path": "libs/",
      "project-map": {
        "Wf/Tools": "project/tools"
      }
    }
  ]
}
```

The CLI does not walk parent directories. Missing config in the current working
directory is a CLI error (exit `≠ 0`).

---

## PHPDoc tags and modifiers (stable)

Tag names are **exact matches**, not prefix matches.

### Readiness tags (exactly one per file)

| Tag                | Meaning                                                       |
|--------------------|---------------------------------------------------------------|
| `@laravel-ready`   | File guarded in the Laravel contour                           |
| `@laravel-adapter` | Bridge to legacy for the Laravel contour                      |
| `@legacy-adapter`  | Bridge only inside legacy; AST limited by `@allows` whitelist |
| `@legacy-perfect`  | Cleaned up, still in the legacy contour                       |
| `@legacy-code`     | Explicit legacy; findings do not fail the guard               |

### Modifiers

| Tag          | Meaning                                                                   |
|--------------|---------------------------------------------------------------------------|
| `@allows`    | AST whitelist for `@legacy-adapter` (comma-separated tokens)              |
| `@skipCheck` | With blockers on a readiness-tagged file — exit `0`, findings still shown. `until=YYYY-MM-DD` — temporary skip with a deadline: the package materializes the date into the file (default from config), after the deadline guard fails again (exit `1`). Package writes, hook reads |

Full semantics: `READINESS_MODEL.md`.

---

## Exit codes (stable)

Hooks and CI must rely on the **exit code**. Semantics:

| Situation                                                                       | Exit                  |
|---------------------------------------------------------------------------------|-----------------------|
| File with no tag or multiple tags                                               | `1`                   |
| `@legacy-code` (with or without findings)                                       | `0`                   |
| `@legacy-adapter` without blockers                                              | `0`                   |
| `@legacy-adapter` with blockers                                                 | `1`                   |
| `@legacy-perfect` without blockers                                              | `0`                   |
| `@legacy-perfect` with blockers                                                 | `1`                   |
| `@laravel-ready` / `@laravel-adapter` without blockers                          | `0`                   |
| `@laravel-ready` / `@laravel-adapter` with blockers                             | `1`                   |
| Readiness tag + blockers + `@skipCheck`                                         | `0`                   |
| `@skipCheck` on `Untagged` / `MultiTag`                                         | `1` (does not rescue) |
| Readiness tag + blockers + `@skipCheck(until=...)` with deadline passed          | `1`                   |
| CLI error (file not found, not `.php`, missing config, …)                      | `≠ 0`                 |

When analyzing multiple files: **any** exit `1` (or CLI error) → overall exit
`≠ 0`.

---

## Stable output strings (stable)

Besides the exit code, 0.x beta **does not change without a changelog** the
exact footer literals (ANSI codes stripped):

| String                                                                    | When                                      |
|---------------------------------------------------------------------------|-------------------------------------------|
| `Guard failed: @laravel-ready file must stay LaravelReady.`               | blockers on `@laravel-ready`              |
| `Guard failed: @laravel-adapter file must stay LaravelAdapter.`           | blockers on `@laravel-adapter`            |
| `Guard failed: @legacy-adapter file must stay in legacy contour.`         | blockers on `@legacy-adapter`             |
| `Guard failed: @legacy-perfect file must stay cleaned in legacy contour.` | blockers on `@legacy-perfect`             |
| `MultiTag failed: file must have only one tag.`                           | multiple readiness tags                   |
| `Not guarded: file has no tag.`                                           | no readiness tag                          |
| `Skipped: @skipCheck.`                                                    | blockers + `@skipCheck` on a guarded file |

Wrapping footers in terminal colour markup is allowed; the **literal text** in
the table is part of the contract.

**Unstable** (may improve without a breaking change): finding lines (`var:`,
`func:`, `use:`, `tag:`), finding order, indentation, header `{path} : {Level}`,
colors, `--verbose`.

---

## Dependency guard (stable — boundary)

For guarded files, only **direct imports** from the AST are checked:

- `use`
- `group use`

**Out of 0.x contract** (not a beta priority):

- `extends`, `implements`, `new`, `instanceof`
- `require` / `include`
- indirect dependencies via calls

A depended-on class must be tagged `@laravel-ready` or `@laravel-adapter` (or
follow the level policy for legacy tags — see `READINESS_MODEL.md`).

---

## Not part of the public API

| Area                                         | Note                                       |
|----------------------------------------------|--------------------------------------------|
| PHP namespace `LaravelReady\*`               | Internal implementation; not a library API |
| Finding format and order                     | May change                                 |
| `--verbose` flag                             | Planned; not part of the beta contract     |
| Exact stdout layout beyond the footers above | Not contracted                             |
| Smoke/fixtures in this repository            | For package development, not consumers     |

---

## Recommended CI / pre-commit integration

```bash
cd "$PROJECT_ROOT"
laravel-ready "$file"
code=$?
if [ "$code" -ne 0 ]; then
  exit "$code"
fi
```

Optionally search for a footer in the log (not required if exit is checked):

```bash
laravel-ready ... 2>&1 | grep -F 'Guard failed:'
```

### Tag-only diff (hook-level)

**Tag-only** — the **only** change in a file is the tag itself (PHPDoc marker); no code touched:

```diff
  /**
   * @laravel-ready
+  * @skipCheck(until=2025-06-01)
   */
```

Tag-only is **hook logic**, not package logic: it is decided by the pre-commit / CI
script (diff vs `merge-base`), and the package CLI stays **read-only** at commit
time — it never rewrites files for the hook.

| Diff in a file                          | Hook behaviour                                            |
|-----------------------------------------|-----------------------------------------------------------|
| Only tag lines changed                  | skip re-check (commit allowed; the file already passed)   |
| Code changed (tag edited or not)        | full check, normal exit codes                             |
| Cannot prove it is tag-only (ambiguous, rebase, whitespace besides tag) | re-check — conservative default |

The purpose: adding a tag to a depended-on file must not trigger a re-check of
that file (no cascade of forced tags). Deadlines (`until=`, `skip:set`) are
**package** semantics — `READINESS_MODEL.md`; the hook only reads them.

Reference detection (bash):

```bash
for file in "$@"; do
  diff=$(git diff --cached -U0 -- "$file" | grep -E '^[+-]' | grep -vE '^(\+\+\+|---)')
  total=$(printf '%s\n' "$diff" | grep -cE '^[+-]')
  tags=$(printf '%s\n' "$diff" | grep -cE '^[+-]\s*\*\s*@(laravel-ready|laravel-adapter|legacy-adapter|legacy-perfect|legacy-code|allows|skipCheck)')
  if [ "$total" -gt 0 ] && [ "$tags" -eq "$total" ]; then
    continue # tag-only: nothing new to verify
  fi
  laravel-ready "$file"
done
```

---

## Related documents

- `README.md` — quickstart
- `RELEASE_TIERS.md` — beta / release criteria

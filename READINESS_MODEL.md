# Readiness Model

Internal architecture document. Public contract surface lives in
`CLI_CONTRACT_0x.md`; output/resolution behaviour in `RESOLUTION_AND_OUTPUT.md`.

This document defines the **skip model**: how `@skipCheck` works, why every skip
is temporary, and how expiry is enforced. The driving principle:

> **A skip without an expiry date is not a skip — it is a permanent excuse.
> There is no infinite `@skipCheck`.**

---

## 1. Core principle

Every `@skipCheck` must eventually expire. Skipping is a way to say *"I know
this fails today, and I will deal with it by a known date"* — never *"silence
this forever"*. If a skip has no date, or its date is in the past, the check it
hides is reported again.

This makes the report an honest backlog: nothing disappears silently, and
nothing is postponed indefinitely.

---

## 2. Syntax

Only **absolute dates** are allowed. No `until=` keyword, no relative periods
(`+30d`, `2w`).

```php
/** @skipCheck(2026-03-15) */
public function foo(): void { /* known failing */ }
```

| Form                          | Meaning                                     |
|-------------------------------|---------------------------------------------|
| `@skipCheck(YYYY-MM-DD)`      | Valid. Check is hidden until that date.     |
| `@skipCheck`                  | **Violation.** Missing date. Red flag.      |
| `@skipCheck(2026-13-99)`      | **Violation.** Malformed date. Red flag.    |
| `@skipCheck(2025-01-01)` past | **Violation.** Expired. Red flag.           |

There is **no reason field**: keeping the annotation minimal was a deliberate
decision. If context is needed, it belongs in a regular comment next to the tag.

---

## 3. Expiry semantics (variant A)

When a skip's date passes, the skip simply **stops applying**. It is not
annotated, not auto-converted, not rewritten. The check it was hiding becomes
active again and fails exactly like any other failure. The user fixes it by
hand: either updates the date (because the task is genuinely still open) or
fixes the underlying issue.

The analyser **never mutates source files**. It is strictly read-only. All
date-writing is explicit and happens in a dedicated command (see §5).

---

## 4. Bare skip without a date: red flag (V2)

A `@skipCheck` without a date is a contract violation in the main flow:

- reported as a red flag / violation;
- the analysis run fails with a non-zero exit code;
- the user must either add a date by hand, or run `skips:fill`.

Rationale: a yellow "warning" with virtual auto-fill would make the date roll
forward on every run — the skip would never expire. That is the infinite skip
we forbid. The only honest alternative is to materialize a **real** date into
the file, which requires an explicit write command.

---

## 5. Commands

| Command                    | Mode       | Behaviour                                                       |
|----------------------------|------------|------------------------------------------------------------------|
| `laravel-ready analyse`    | read-only  | Main flow. Red flag on bare/expired/malformed skips.             |
| `laravel-ready skips:fill` | write      | Finds bare `@skipCheck` tags, materializes real dates into them. |

`analyse` is the compatibility floor and stays pure. `skips:fill` is the only
place where the package writes into the user's source code, and it must be an
explicit, visible command.

### `skips:fill` behaviour

1. Scans the repository for bare `@skipCheck` (no date).
2. Assigns each a concrete `YYYY-MM-DD` date.
3. Default period comes from configuration (see §6), default **10 days**.
4. May run interactively (confirm each date) or as a batch with the default.
5. Reports how many files were changed.

Typical flow: `laravel-ready skips:fill` → `laravel-ready analyse`.

---

## 6. Configuration

The default fill period lives in `laravel-ready.json` so each project can tune
it. If absent, the built-in default is **10 days**.

```json
{
  "skip-fill-days": 10
}
```

This key is not part of the stable public contract yet — it is an internal
design decision pending the 0.x beta review.

---

## 7. Out of scope (deferred)

Deliberately not part of this iteration:

- **Maximum skip duration** (e.g. hard 90-day cap) — deferred.
- **Observer / dashboard command** (list of active and expiring skips,
  pre-expiry warnings) — deferred as a management tool, not a replacement.
- **Orchestrator command** (e.g. `check` = `skips:fill` → `analyse`) — optional
  convenience on top, only if the two-step flow proves annoying.
- **Reason field** — rejected, keeps the annotation minimal.

---

## 8. Decision log

| # | Decision                                                       |
|---|----------------------------------------------------------------|
| 1 | No infinite skips; expiry is mandatory.                        |
| 2 | Absolute date only; no `until=`, no relative periods.          |
| 3 | Bare `@skipCheck` = red flag / violation in the main flow.     |
| 4 | Expired skip = variant A: silently stops applying, check fails again. |
| 5 | Default fill period from settings, built-in default 10 days.   |
| 6 | `skips:fill` is a separate write command; `analyse` stays pure read-only. |
| 7 | Analyser never mutates source files.                           |
| 8 | No reason field.                                               |

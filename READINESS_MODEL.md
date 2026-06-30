# Модель готовности

## Уровни

**`Legacy`** — плохо: блокеры или метка `legacy-code`. Нужна работа.

Из `Legacy` файл может прийти в **два** разных исхода:

```
                         → LaravelReady → LaravelPerfect
Legacy ──┤
                         → LegacyPerfect
```

- **`LaravelReady`** — переписали, можно в Laravel
- **`LegacyPerfect`** — переписали / почистили, но **остаётся с легаси** (мост, хелпер)
- **`LaravelPerfect`** — уже `LaravelReady` + идиомы Laravel

`LegacyPerfect` и `LaravelReady` — **равные выходы из `Legacy`**, не ступени друг друга.

---

## Порядок проверки

Где файл **сейчас**:

1. Метка **legacy-code** или **блокер** → `Legacy`
2. Блокеров нет + **legacy-perfect** / **adapter** / **for-legacy** → `LegacyPerfect`
3. Иначе deps ок → `LaravelReady`
4. На шаге 3 + идиомы → `LaravelPerfect`

**Серая зона** — шаг 3 не прошёл.

---

## Метки

| Метка | Где |
|-------|-----|
| **legacy-code** | файл → `Legacy` |
| **legacy-perfect** | файл → без блокеров `LegacyPerfect` |
| **adapter** | файл / метод → без блокеров `LegacyPerfect` |
| **for-legacy** | файл → без блокеров `LegacyPerfect` |

---

## Legacy

Блокер или метка **legacy-code**. Цель — `LaravelReady` **или** `LegacyPerfect`.

### Блокеры

**Общие** — валидный PHP 8.2, но не Laravel-way; простой AST:

| Паттерн | PHP 8.2 | Laravel-ready |
|---------|---------|---------------|
| Суперглобали (`$GLOBALS`, `$_GET`, `$_POST`, …) | Работают | не Laravel-way |
| `define()` | Работает | не Laravel-way |
| `global $x` | Работает | не Laravel-way |
| `eval()`, `extract()` | Работают | не Laravel-way |
| `mysqli_*` | Работает | не Laravel-way (процедурная БД) |

**Дополнительно** (project-specific или сложнее AST):

- `container('legacy')`
- `legacy_config()`, `db_legacy_query()`
- `mysql_connect`, сырой SQL с конкатенацией

Slim, свой фреймворк — метка **legacy-code**.

---

## LegacyPerfect

Был `Legacy`, убрали блокеры, поставили **adapter** / **for-legacy**. Код ок, с легаси остаётся.

---

## LaravelReady

Был `Legacy`, убрали блокеры и метку **legacy-code**, deps ок. В Laravel.

Зависимость от **adapter** — ок. От `Legacy` / **for-legacy** — нет.

---

## LaravelPerfect

`LaravelReady` + `Arr::get`, `collect`, `Str::of`, `now()` / Carbon.

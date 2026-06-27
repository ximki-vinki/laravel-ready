# Модель готовности

## Уровни

**`Legacy`** — плохо: блокеры или метка `legacy`. Нужна работа.

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

1. Метка **legacy** или **блокер** → `Legacy`
2. Блокеров нет + **adapter** / **for-legacy** → `LegacyPerfect`
3. Иначе deps ок → `LaravelReady`
4. На шаге 3 + идиомы → `LaravelPerfect`

**Серая зона** — шаг 3 не прошёл.

---

## Метки

| Метка | Где |
|-------|-----|
| **legacy** | файл → `Legacy` |
| **adapter** | файл / метод → без блокеров `LegacyPerfect` |
| **for-legacy** | файл → без блокеров `LegacyPerfect` |

---

## Legacy

Блокер или метка **legacy**. Цель — `LaravelReady` **или** `LegacyPerfect`.

### Блокеры

- `$GLOBALS`, `Registry::get()`, `container('legacy')`
- `legacy_config()`, `db_legacy_query()`
- `mysqli_*`, `mysql_connect`, сырой SQL с конкатенацией

Slim, свой фреймворк — метка **legacy**.

---

## LegacyPerfect

Был `Legacy`, убрали блокеры, поставили **adapter** / **for-legacy**. Код ок, с легаси остаётся.

---

## LaravelReady

Был `Legacy`, убрали блокеры и метку **legacy**, deps ок. В Laravel.

Зависимость от **adapter** — ок. От `Legacy` / **for-legacy** — нет.

---

## LaravelPerfect

`LaravelReady` + `Arr::get`, `collect`, `Str::of`, `now()` / Carbon.

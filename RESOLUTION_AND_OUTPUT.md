# Разрешение уровня и вывод

Смысл уровней и меток — `READINESS_MODEL.md`. Pipeline и фазы — `ARCHITECTURE.md`.

## Зачем отдельный слой

Между детектором и CLI — три разные задачи:

1. **Разрешить уровень** — что файл означает сейчас (`ReadinessResolver`).
2. **Решить, провален ли guard** — можно ли коммитить (`hasBlockers`; позже — `pledged` / `guardFailed`).
3. **Показать результат** — строка, findings, footer, exit code (`ReadinessPresenter` + formatters).

`AnalyseCommand` только оркестрирует; домен и форматирование не смешиваются.

## Три слоя данных

| Слой | Кто задаёт | Смысл |
|------|------------|-------|
| **Finding** | Detector, UseDependencyChecker | Факт в коде или политике |
| **Tag** | разработчик в PHPDoc | Намерение / режим файла |
| **ReadinessLevel** | ReadinessResolver | Итоговый уровень |

Finding ≠ tag: метка файла не дублируется в каждом finding.

## Границы компонентов

| Компонент | Ответственность | Не делает |
|-----------|-----------------|-----------|
| **Detector** | Факты AST (суперглобали, `global`, tag в PHPDoc, сырые `use`, `@skipCheck` → `SkipCheckParseResult`, `@allows` → `AllowsParseResult`) | Политику допустимости `use`, решение skip / guard |
| **UseDependencyChecker** | Политика `use` для guarded-файлов → `UseFinding` | Вывод в консоль |
| **ReadinessResolver** | `actual` из меток; `hasBlockers` через GuardEvaluator; проброс `skipCheck` | Exit code, форматирование |
| **SkipCheckEvaluator** | `SkipCheckVerdict` из даты (`Absent` / `Active` / `Expired` / `Bare` / `Malformed`); `applies()` ≡ `Active` | Вывод в консоль |
| **PresentationPlanBuilder** | План вывода (Active skip + blockers → footer SkipCheck, exit `0`) → formatters | Детект `@skipCheck`, текст секции `skip:` |
| **FindingSectionBuilder** | Секции тела: findings + `allows.unknowns` + `SkipCheckNote` | Решение exit / footer |

Детали — в коде и тестах (`ReadinessResolver`, `PresentationPlanBuilder`).

## actual vs hasBlockers

**`actual`** — уровень файла по метке (`Untagged`, `Legacy`, `LaravelReady`, …).

**`hasBlockers`** — упрощённый guard до появления `pledged`:

- `Untagged`, `MultiTag` — всегда blockers (проблема конфигурации метки).
- `@laravel-adapter` — blockers, если есть `LegacyFinding` (AST); `UseFinding` не блокер.
- `@laravel-ready` — blockers, если есть `LegacyFinding` или `UseFinding`.
- `@legacy-adapter` — blockers, если есть `UseFinding` **или** непрощённый `LegacyFinding` (см. `@allows` ниже).
- `@legacy-perfect` — blockers, если есть `LegacyFinding` или `UseFinding`.
- `@legacy-code` — blockers нет: findings информативны, exit `0`.

### `@allows` (для `@legacy-adapter`)

Модификатор контракта адаптера: какие AST-примитивы разрешены в файле.

```php
/**
 * @legacy-adapter
 * @allows $_COOKIE, setcookie
 */
```

| Ситуация | Blocker? |
|----------|----------|
| нет `LegacyFinding`, deps ок | нет — `@allows` не обязателен |
| есть `LegacyFinding`, нет `@allows` | да (`null` ≡ ничего не разрешено) |
| `LegacyFinding` ∈ списка `@allows` | нет |
| `LegacyFinding` ∉ списка | да |
| `global` и прочее без токена в allowlist | да |
| неизвестный токен в `@allows` (`UnknownAllowToken`) | нет (информативен, exit не валит) |

Токены — явные, 1:1 с детектором: `$_COOKIE`, `setcookie` (не пресеты вроде `cookie`).

Guard — не синоним «exit 1». Файл с `@legacy-code` и `$_GET` — `Legacy`, exit `0`: метка осознанная, не нарушение обещания.

**`@skipCheck`** — отдельно от `hasBlockers`: не меняет уровень и не убирает blockers. Вердикт считает `SkipCheckEvaluator`.

| Вердикт | Смысл | Exit при blockers на readiness-метке | Тело | Футер |
|---------|-------|--------------------------------------|------|-------|
| `Active` | дата ≥ сегодня | `0` | findings, **без** `skip:` | `Skipped: @skipCheck.` |
| `Expired` | дата в прошлом | `1` | findings + `skip: expired` | обычный `Guard failed` |
| `Bare` | `@skipCheck` без даты | `1` | findings + `skip: missing date` | обычный `Guard failed` |
| `Malformed` | дата не парсится | `1` | findings + `skip: malformed` | обычный `Guard failed` |
| `Absent` | тега нет | как без скипа | — | — |

На `Untagged` / `MultiTag` скип не действует (exit `1`, footer про метку). `SkipCheckNote` — не Finding: рисуется через `Displayable` в секции `skip:`, по тому же принципу что `UnknownAllowToken` в `allows:`. В `GuardEvaluator` не участвует.

Позже: `guardFailed = pledged !== null && actual хуже pledged` — общий механизм для всех меток с обещанием, не только `@laravel-ready`.

## Презентация

Exit code, наличие findings и «успех для hook'а» — **три независимые оси**. Деление на `SuccessOutput` / `UnSuccessOutput` или `WithFindings` / `WithoutFindings` не работает: `Legacy` — exit `0` с findings; `Untagged` — exit `1` с footer про отсутствие метки; Active `@skipCheck` + blockers — exit `0` с findings и Warning.

Слои вывода:

- **хедер** — путь и уровень;
- **тело** — `var` / `global` / `func` / `use` / `allows` / `skip` (модификаторы — не Finding);
- **футер** — один вердикт (`Guard failed`, `Skipped`, `Not guarded`, …).

План (exit, footer, показывать ли findings) строит `PresentationPlanBuilder`; секции тела — `FindingSectionBuilder` / `FindingsOutput`. Formatters только рисуют — без бизнес-логики скипа (кроме вызова evaluator для `SkipCheckNote`).

При успехе `@legacy-adapter` findings сейчас **скрыты** (в т.ч. разрешённый `$_COOKIE` и опечатки в `@allows`).

## Контракт exit code

Public promise for beta consumers: `CLI_CONTRACT_0x.md`. The table below is the internal spec (must match the contract).

| Ситуация | Exit |
|----------|------|
| Файл без метки или с несколькими метками | `1` |
| `@legacy-code` (с findings или без) | `0` |
| `@legacy-adapter` без blockers | `0` |
| `@legacy-adapter` с `UseFinding` или непрощённым AST | `1` |
| `@legacy-perfect` без blockers | `0` |
| `@legacy-perfect` с AST или UseFinding | `1` |
| `@laravel-ready` / `@laravel-adapter` без blockers | `0` |
| `@laravel-ready` / `@laravel-adapter` с blockers | `1` |
| readiness-метка + blockers + Active `@skipCheck` | `0` (footer SkipCheck; `Untagged`/`MultiTag` — нет) |
| readiness-метка + blockers + Expired / Bare / Malformed `@skipCheck` | `1` (секция `skip:`; футер Guard failed) |
| Ошибка CLI (файл не найден, не `.php`) | `≠ 0` |

## Планируется

- **`pledged` / `guardFailed`** в `ReadinessResult` вместо частных правил в `hasBlockers`.
- **`LaravelPerfect`**, подсказки по идиомам без guard.
- **`UseImportFinding`** в Detector как сырой `use` (политика остаётся в checker).

## Антипаттерны

| Не делать | Почему |
|-----------|--------|
| Tag в каждом finding | Метка файла ≠ факт в AST |
| `@skipCheck` как `Tag` / поле на `TagFinding` | Модификатор политики презентации, не readiness-уровень |
| `SkipCheckNote` / `UnknownAllowToken` в `findings` | Улика про модификатор; секция тела, не Finding и не blocker |
| Второй красный футер для expired/bare | Футер — один вердикт; причина скипа — в `skip:` |
| `ReadinessLevel` для guard-нарушения | Level — состояние; guard — нарушение обещания |
| `kind` enum у finding | Дублирует смысл класса; лучше `LegacyFinding` |
| Выбор вывода в `AnalyseCommand` | Разрастётся с новыми метками |
| Бизнес-логика в output-классах | Только форматирование по плану |
| Политика `use` в Detector или resolver | Detector — факты; checker — политика; resolver — вердикт по findings |

## Связанные документы

- `READINESS_MODEL.md` — уровни, метки, политика зависимостей
- `ARCHITECTURE.md` — фазы, контракт CLI
- `TDD.md` — как дробить работу тестами

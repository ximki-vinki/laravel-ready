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
| **Detector** | Факты AST (суперглобали, `global`, tag в PHPDoc, сырые `use`) | Политику допустимости `use` |
| **UseDependencyChecker** | Политика `use` для guarded-файлов → `UseFinding` | Вывод в консоль |
| **ReadinessResolver** | `actual` из меток; `hasBlockers` через GuardEvaluator (`LegacyFinding` и/или `UseFinding` по уровню) | Exit code, форматирование |
| **ReadinessPresenter** | План вывода → formatters | Доменные правила guard |

Детали — в коде и тестах (`ReadinessResolver`, `PresentationPlanBuilder`).

## actual vs hasBlockers

**`actual`** — уровень файла по метке (`Untagged`, `Legacy`, `LaravelReady`, …).

**`hasBlockers`** — упрощённый guard до появления `pledged`:

- `Untagged`, `MultiTag` — всегда blockers (проблема конфигурации метки).
- `@laravel-adapter` — blockers, если есть `LegacyFinding` (AST); `UseFinding` не блокер.
- `@laravel-ready` — blockers, если есть `LegacyFinding` или `UseFinding`.
- `@legacy-adapter` — blockers, если есть `UseFinding`; AST (`LegacyFinding`) не блокер.
- `@legacy-code` и др. — blockers нет: findings информативны, exit `0`.

Guard — не синоним «exit 1». Файл с `@legacy-code` и `$_GET` — `Legacy`, exit `0`: метка осознанная, не нарушение обещания.

Позже: `guardFailed = pledged !== null && actual хуже pledged` — общий механизм для всех меток с обещанием, не только `@laravel-ready`.

## Презентация

Exit code, наличие findings и «успех для hook'а» — **три независимые оси**. Деление на `SuccessOutput` / `UnSuccessOutput` или `WithFindings` / `WithoutFindings` не работает: `Legacy` — exit `0` с findings; `Untagged` — exit `1` с footer про отсутствие метки.

План вывода строит `PresentationPlanBuilder`; formatters только рисуют переданные части — без бизнес-логики.

## Контракт exit code

| Ситуация | Exit |
|----------|------|
| Файл без метки или с несколькими метками | `1` |
| `@legacy-code` (с findings или без) | `0` |
| `@legacy-adapter` без blockers (AST ок, deps ок) | `0` |
| `@legacy-adapter` с UseFinding | `1` |
| `@laravel-ready` / `@laravel-adapter` без blockers | `0` |
| `@laravel-ready` / `@laravel-adapter` с blockers | `1` |
| Ошибка CLI (файл не найден, не `.php`) | `≠ 0` |

## Планируется

- **`pledged` / `guardFailed`** в `ReadinessResult` вместо частных правил в `hasBlockers`.
- **`LegacyPerfect`**, **`LaravelPerfect`**, подсказки по идиомам без guard.
- **`UseImportFinding`** в Detector как сырой `use` (политика остаётся в checker).

## Антипаттерны

| Не делать | Почему |
|-----------|--------|
| Tag в каждом finding | Метка файла ≠ факт в AST |
| `ReadinessLevel` для guard-нарушения | Level — состояние; guard — нарушение обещания |
| `kind` enum у finding | Дублирует смысл класса; лучше `LegacyFinding` |
| Выбор вывода в `AnalyseCommand` | Разрастётся с новыми метками |
| Бизнес-логика в output-классах | Только форматирование по плану |
| Политика `use` в Detector или resolver | Detector — факты; checker — политика; resolver — вердикт по findings |

## Связанные документы

- `READINESS_MODEL.md` — уровни, метки, политика зависимостей
- `ARCHITECTURE.md` — фазы, контракт CLI
- `TDD.md` — как дробить работу тестами

# Разрешение уровня и вывод

> **Статус: на уточнении**  
> Документ фиксирует направление обсуждения. Детали API, имена классов и границы фаз могут измениться.

Смысл уровней и меток — `READINESS_MODEL.md`. Технический контекст пакета — `ARCHITECTURE.md`.

---

## Зачем отдельный документ

В пакете появляется слой между детектором и CLI: не только «нашли блокер», но и **уровень файла**, **обещание разработчика (метка)** и **сценарий вывода** (инфо vs guard failed).

Цель — не размазывать эту логику по `AnalyseCommand` и не смешивать домен с форматированием строк.

---

## Три слоя данных

| Слой | Кто задаёт | Пример | Вопрос |
|------|------------|--------|--------|
| **Finding** | детектор (AST, `use`, …) | `$_GET`, `global $x` | Что нашли в коде? |
| **Tag** (метка файла) | разработчик в PHPDoc | `@laravel-ready`, `@legacy-code` | Какое обещание / режим у файла? |
| **ReadinessLevel** | resolver | `Legacy`, `LaravelReady`, … | Каков итоговый уровень? |

**Правило:** finding хранит **факт**, tag — **намерение**, level — **вердикт** resolver'а.

---

## Типы finding (маркерные интерфейсы)

Сейчас:

| Интерфейс / класс | Влияние на уровень |
|-------------------|-------------------|
| `Finding` | базовый: `display()` |
| `LegacyFinding` | тянет в `Legacy` |
| `SuperglobalFinding`, `GlobalFinding`, `FunctionCallFinding` | реализуют `LegacyFinding` |

План:

| Интерфейс / класс | Фаза | Влияние |
|-------------------|------|---------|
| `TagFinding` | 1 | `@legacy-code` как причина `Legacy` (синтез в resolver) |
| `UseFinding` | 2 | недопустимый `use` → `Legacy` |
| `LegacyPerfectFinding` | позже | находки для контура `LegacyPerfect` |
| `LaravelPerfectFinding` | позже | подсказки по идиомам Laravel; **не ломает guard** |

Resolver смотрит на **тип finding** (`instanceof LegacyFinding` и т.д.), а не на `findings->isNotEmpty()`.

---

## ReadinessResolver

**Роль:** `AnalysisResult` → `ReadinessResult`. Только домен, без вывода в консоль.

### Вход

```text
AnalysisResult
├── findings: Collection<Finding>
└── tag: ?Tag
```

### Выход (на уточнении)

```text
ReadinessResult
├── actual: ReadinessLevel      // фактический уровень по findings + tag (позже)
├── pledged: ?ReadinessLevel    // минимальный уровень из метки файла (обещание)
├── guardFailed: bool           // actual хуже pledged
└── findings: Collection<Finding>
```

### Логика actual (MVP, есть в коде)

1. Есть хотя бы один `LegacyFinding` → `ReadinessLevel::Legacy`
2. Иначе → `ReadinessLevel::LaravelReady`

### Логика pledged (план)

Метка файла задаёт **обещание** — файл не должен опуститься ниже этого уровня:

| Метка | pledged (обещание) | guardFailed когда |
|-------|-------------------|-------------------|
| `@laravel-ready` | `LaravelReady` | `actual === Legacy` |
| `@legacy-perfect` | `LegacyPerfect` | `actual === Legacy` |
| `@laravel-perfect` | `LaravelPerfect` | `actual` ниже `LaravelPerfect` |
| нет метки / без guard | `null` | никогда (exit `0`) |

`@legacy-code` — отдельный случай: явный `Legacy` (через `TagFinding` в reasons, не через pledged).

### Что не делает resolver

- не печатает в консоль;
- не знает про exit code;
- не содержит флаги вроде `notLaravelReadyIsLegacy` — это сценарий презентации, не домен.

---

## Guard — общий механизм, не один флаг

Guard — не только `@laravel-ready`. Это сравнение **обещания** (pledged) и **факта** (actual):

```text
guardFailed = pledged !== null && actual хуже pledged
```

Для MVP охраны важен первый кейс: `@laravel-ready` + `Legacy` → exit `1`.  
Позже те же правила распространяются на `@legacy-perfect`, `@laravel-perfect`.

---

## Презентация: ReportMode и Presenter

### Проблема

Один и тот же `ReadinessLevel::Legacy` ведёт себя по-разному:

| actual | pledged | Поведение |
|--------|---------|-----------|
| `Legacy` | `null` | показать `Legacy` + блокеры, exit `0` (инфо) |
| `Legacy` | `LaravelReady` | показать `Legacy` + блокеры + **Guard failed**, exit `1` |

Составной флаг `notLaravelReadyIsLegacy` понятен человеку, но смешивает две оси. Лучше явный **сценарий вывода**.

### ReportMode (на уточнении)

```text
enum ReportMode
├── Success           // actual ok, guard ok
├── LegacyInfo        // Legacy, без обещания — только информирование
├── GuardFailed       // pledged есть, actual хуже — warning + exit 1
└── …                 // позже: подсказки LaravelPerfect и т.д.
```

### ReadinessPresenter / AnalysisPresenter (план)

**Роль:** `ReadinessResult` + путь к файлу → какой `ReportMode` и какие output-классы вызвать.

```text
present(ReadinessResult, relativePath) → Report
├── mode: ReportMode
├── exitCode: int
└── writers: какие *Output дернуть
```

### *Output — только форматирование

| Класс | Роль |
|-------|------|
| `LegacyOutput` | строка `path : Legacy` + группы findings |
| `LaravelReadyOutput` | строка `path : LaravelReady` |
| `GuardFailedOutput` | строка `Guard failed: …` (план) |

Output **не решает**, печатать ли guard-сообщение — только рисует переданный сценарий.

---

## AnalyseCommand — только оркестрация

```text
для каждого файла:
  analysis  = Detector.analyse(path)
  readiness = ReadinessResolver.resolve(analysis)
  report    = Presenter.present(readiness, relativePath)
  report.write(console)
  учесть report.exitCode
вернуть итоговый exit code
```

Команда **не содержит** `if (level === Legacy && tag === …)`.

---

## Порядок внедрения (на уточнении)

| # | Что | Статус |
|---|-----|--------|
| 1 | `LegacyFinding`, `ReadinessResolver` (только `LegacyFinding` → Legacy) | есть |
| 2 | `pledged` / `guardFailed` в `ReadinessResult` | план |
| 3 | `ReadinessPresenter` + `ReportMode` | план |
| 4 | `GuardFailedOutput`, exit `1` в команде | план |
| 5 | `TagFinding`, `@legacy-code` в resolver | план |
| 6 | `UseFinding`, зависимости | фаза 2 |
| 7 | `LegacyPerfectFinding`, `LaravelPerfectFinding` | позже |

---

## Антипаттерны

| Не делать | Почему |
|-----------|--------|
| `Tag::Legacy` в поле каждого finding | tag файла ≠ факт в AST |
| `ReadinessLevel` для guard-сценария (`GuardViolation`) | level — состояние файла, guard — нарушение обещания |
| `kind` enum в каждом finding | дублирует смысл класса; лучше `LegacyFinding extends Finding` |
| Логика выбора вывода в `AnalyseCommand` | разрастётся при `@legacy-perfect`, `@laravel-perfect` |
| Логика guard в `LegacyOutput` | output должен оставаться тупым форматтером |

---

## Связанные документы

- `READINESS_MODEL.md` — уровни, метки, порядок проверки
- `ARCHITECTURE.md` — фазы, компоненты, контракт CLI
- `TDD.md` — как дробить работу тестами

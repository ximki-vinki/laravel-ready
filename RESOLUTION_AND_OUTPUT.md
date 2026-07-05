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
| `UseFinding` | 2 | недопустимый `use`; создаёт **UseDependencyChecker**, не Detector |
| `UseImportFinding` (план) | 2 | сырой `use` из AST; **не** `LegacyFinding` |
| `LegacyPerfectFinding` | позже | находки для контура `LegacyPerfect` |
| `LaravelPerfectFinding` | позже | подсказки по идиомам Laravel; **не ломает guard** |

Resolver смотрит на **тип finding** (`instanceof LegacyFinding` и т.д.), а не на `findings->isNotEmpty()`.

Политика зависимостей (`use Wf\`, резолв `App\`) — **UseDependencyChecker** (между Detector и Resolver). Resolver только учитывает уже готовые `UseFinding`.

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
- не содержит флаги вроде `notLaravelReadyIsLegacy` — это сценарий презентации, не домен;
- **не применяет политику `use`** — только реагирует на `UseFinding` как на `LegacyFinding`.

---

## Презентация: сценарии и Presenter

### Проблема: три независимые оси

Один output-класс или пара `Success` / `UnSuccess` **не описывают** поведение CLI. Нужно различать три оси:

| Ось | Вопрос | Примеры |
|-----|--------|---------|
| **exit / guard** | ломать ли коммит? | чистый `LaravelReady` → 0; с блокером → 1 |
| **уровень (смысл)** | какой статус у файла? | `Legacy` — не провал, а осознанная метка |
| **тело вывода** | что печатать? | одна строка; findings; footer; комбинация |

Из-за этого ломаются наивные деления:

**`SuccessOutput` / `UnSuccessOutput`**

| Кейс | exit | Это Success? |
|------|------|--------------|
| `Legacy` (`@legacy-code`) + `$_GET` | 0 | да для хука, но не «чистый» success |
| `LaravelAdapter` чистый | 0 | да |
| `Untagged` | 1 | нет |

`Legacy` — **успех для exit**, но не тот же сценарий, что `LaravelReady`.

**`WithFindingsOutput` / `WithoutFindingsOutput`**

| Кейс | findings? | цвет |
|------|-----------|------|
| `LaravelReady` чистый | нет | зелёный |
| `LaravelAdapter` чистый | нет | cyan |
| `Legacy` | **да** (инфо) | жёлтый, exit 0 |
| `LaravelReady` + блокер | **да** | красный |
| `Untagged` без паттернов | нет | красный |
| `Untagged` + `$_GET` | опционально | красный |

Наличие findings не совпадает ни с exit, ни с уровнем.

### Текущее состояние кода (временно)

`ReadinessPresenter` делит вывод грубо:

```text
LaravelReady && !hasBlockers  →  LaravelReadyOutput   (одна строка)
всё остальное               →  LegacyOutput           (header + findings)
```

Имя **`LegacyOutput` вводит в заблуждение**: класс используется не только для `ReadinessLevel::Legacy`, но и для `LaravelAdapter`, `Untagged`, `MultiTag`, `LaravelReady` с блокерами. По смыслу это **«подробный вывод с findings»**, а не «легаси с ошибкой».

Чистый `LaravelAdapter` сейчас попадает в `LegacyOutput`, хотя по модели должен идти в ту же ветку, что чистый `LaravelReady` — **одна строка без findings**.

### Решение: ReportScenario + PresentationPlan

**Resolver** отдаёт факты: `actual`, `hasBlockers`, `findings`.  
**Presenter** выбирает **сценарий** и план рендера.  
**Output-классы** только рисуют переданные части — без бизнес-логики.

```text
ReadinessPresenter
  → resolveScenario(ReadinessResult): ReportScenario
  → buildPlan(...): PresentationPlan
  → HeaderWriter + ?FindingsWriter + ?FooterWriter
```

#### ReportScenario

```text
enum ReportScenario
├── Clean          // pledged-уровень без blockers: одна строка
├── LegacyInfo     // @legacy-code: информирование, exit 0, с findings
├── GuardFailed    // обещание нарушено: findings + footer, exit 1
└── TagInvalid     // Untagged / MultiTag: footer обязателен, exit 1
```

Позже: `LaravelPerfectHints` — подсказки по идиомам без guard.

#### PresentationPlan

```text
PresentationPlan
├── scenario:     ReportScenario
├── headerStyle:  Clean | Warning | Error   // цвет строки path : Level
├── showFindings: bool
├── footer:       ?ReadinessFooter
└── exitCode:     0 | 1
```

`headerStyle` берёт цвет из палитры уровня (`ReadinessHeader` / `TagPalette`), если нет blockers; при `hasBlockers` — `<error>`.

#### Маппинг (целевой)

| actual | hasBlockers | Сценарий | findings | footer | exit |
|--------|-------------|----------|----------|--------|------|
| `LaravelReady` | false | **Clean** | нет | — | 0 |
| `LaravelAdapter` | false | **Clean** | нет | — | 0 |
| `LaravelPerfect` | false | **Clean** | нет* | — | 0 |
| `Legacy` | false | **LegacyInfo** | да | — | 0 |
| `LaravelReady` | true | **GuardFailed** | да | `GuardFailed` | 1 |
| `LaravelAdapter` | true | **GuardFailed** | да | `AdapterFailed`* | 1 |
| `Untagged` | true | **TagInvalid** | опционально** | `NotGuarded` | 1 |
| `MultiTag` | true | **TagInvalid** | нет / теги | `MultiTagFailed` | 1 |

\* `LaravelPerfect` с hints — отдельный сценарий позже.  
\* `AdapterFailed` — добавить в `ReadinessFooter`.  
\** для `Untagged`: если есть legacy findings — показывать их (помогает понять файл), но главное сообщение — про отсутствие/конфликт метки.

#### Writers (тупые форматтеры)

| Класс | Сейчас | Целевое имя | Роль |
|-------|--------|-------------|------|
| header | `ReadinessHeader` | без изменений | `path : Level` + цвет |
| findings | `LegacyOutput` | **`FindingsOutput`** | секции findings через `FindingSectionBuilder` |
| footer | `ReadinessFooterOutput` | без изменений | `Guard failed: …` и т.д. |
| clean body | `LaravelReadyOutput` | убрать* | только header; заменить сценарием **Clean** |

\* `LaravelReadyOutput` и будущий `LaravelAdapterOutput` схлопываются: сценарий **Clean** + `ReadinessHeader`.

Output **не решает** сценарий — только рисует переданный план.

### Guard vs сценарий

Guard — не синоним `UnSuccess`. Это сравнение **обещания** (pledged) и **факта** (actual):

```text
guardFailed = pledged !== null && actual хуже pledged
```

Сейчас в коде вместо `pledged` / `guardFailed` — упрощение через `hasBlockers`. Сценарий **GuardFailed** соответствует файлам с меткой (`@laravel-ready`, `@laravel-adapter`, …), у которых появились blockers.

Сценарий **TagInvalid** — проблема **конфигурации метки**, не нарушение периметра внутри уровня.

Сценарий **LegacyInfo** — exit 0, findings для информации; guard не применяется.

### Пример потока

```text
@laravel-adapter, без $_GET
  actual=LaravelAdapter, hasBlockers=false
  → Clean → "class.php : LaravelAdapter" (cyan), exit 0

@laravel-adapter, есть $_GET
  actual=LaravelAdapter, hasBlockers=true
  → GuardFailed → error header + var: $_GET + AdapterFailed footer, exit 1

@legacy-code, есть $_GET
  actual=Legacy, hasBlockers=false
  → LegacyInfo → yellow header + findings, exit 0

без метки
  actual=Untagged, hasBlockers=true
  → TagInvalid → error header + Not guarded footer, exit 1
```

### ReadinessPresenter (план)

**Роль:** `ReadinessResult` + путь к файлу → `PresentationPlan` → вызов writers.

```text
present(ReadinessResult, relativePath) → int exitCode
```

Команда **не содержит** `if (level === Legacy && tag === …)`.

### Порядок внедрения презентации

| # | Что |
|---|-----|
| 1 | `ReportScenario` + `resolveScenario()` в presenter |
| 2 | Чистый `LaravelAdapter` → сценарий **Clean** (тест в `AnalyseCommandTest`) |
| 3 | Переименовать `LegacyOutput` → `FindingsOutput` |
| 4 | `PresentationPlan`, убрать `LaravelReadyOutput` в пользу **Clean** |
| 5 | `ReadinessFooter::AdapterFailed`, сценарий **GuardFailed** для adapter |
| 6 | Явный **TagInvalid** (отделить от GuardFailed в presenter) |

---

## Guard — общий механизм, не один флаг

Guard — не только `@laravel-ready`. Это сравнение **обещания** (pledged) и **факта** (actual):

```text
guardFailed = pledged !== null && actual хуже pledged
```

Для MVP охраны важен первый кейс: `@laravel-ready` + blockers → exit `1`.  
Позже те же правила распространяются на `@laravel-adapter`, `@legacy-perfect`, `@laravel-perfect`.

---

## Цветовая палитра меток

> **Статус: принято**  
> Только **6 PHPDoc-меток**. Ошибки конфигурации (`Untagged`, `multi`, `Not guarded: …`) — отдельный документ, здесь не описываются.

### Принципы

- **6 PHPDoc-меток** — у каждой свой цвет или оттенок внутри семейства.
- **Симметрия контуров:** legacy- и laravel-метки зеркальны по смыслу; адаптеры и «готово в полосе» делят одну гамму на два оттенка.
- **Отдельные цвета:** `@legacy-code` и `@laravel-perfect` не смешиваются с парами выше.

### Семейства

```text
@legacy-code              ── отдельно (тёплый «легаси / стоп»)

@legacy-adapter    ╲
                    ╳── адаптеры (синяя гамма, два оттенка)
@laravel-adapter   ╱

@legacy-perfect    ╲
                    ╳── «готово в своей полосе» (зелёная гамма, два оттенка)
@laravel-ready     ╱

@laravel-perfect          ── отдельно (верхний уровень Laravel)
```

### Таблица цветов

| Метка | Семейство | Symfony Console | Примечание |
|-------|-----------|-----------------|------------|
| `@legacy-code` | легаси-проблема | `<fg=yellow>` | Явное легаси; не путать с адаптерами |
| `@legacy-adapter` | адаптеры | `<fg=blue>` | Бывш. `@for-legacy`; мост в легаси-контуре |
| `@laravel-adapter` | адаптеры | `<fg=cyan>` | Допустимая зависимость для `@laravel-ready` |
| `@legacy-perfect` | готово в полосе | `<fg=green>` | Чистый код в легаси-контуре |
| `@laravel-ready` | готово в полосе | `<fg=bright-green>` | Целевой уровень, guard |
| `@laravel-perfect` | идеал Laravel | `<fg=bright-cyan>` | Отдельно от `@laravel-ready` |

### Имена меток (согласовано)

| В PHPDoc | Было в черновиках |
|----------|-------------------|
| `@legacy-adapter` | `@for-legacy` |
| `@laravel-adapter` | `@adapter` |

Полный набор: `@legacy-code`, `@legacy-adapter`, `@legacy-perfect`, `@laravel-adapter`, `@laravel-ready`, `@laravel-perfect`.

### Реализация (план)

- Один источник правды в коде (например `TagPalette` / метод на `Tag`) — и `ReadinessHeader`, и `TagStatus` берут цвета оттуда.
- Сейчас в `ReadinessHeader` частично: `LaravelReady` → green, `Legacy` → yellow; остальные уровни и метки — по мере внедрения.

---

## AnalyseCommand — только оркестрация

```text
для каждого файла:
  analysis  = Detector.analyse(path)
  analysis  = UseDependencyChecker.check(analysis, path, projectRoot)   // фаза 2
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
| 3 | `ReportScenario`, `PresentationPlan`, рефакторинг `ReadinessPresenter` | план |
| 4 | `FindingsOutput` (переименование `LegacyOutput`), сценарий **Clean** для adapter | план |
| 5 | `TagFinding`, `@legacy-code` в resolver | план |
| 6 | `UseImportFinding` в Detector, `UseDependencyChecker`, `UseFinding` | фаза 2 |
| 7 | `LegacyPerfectFinding`, `LaravelPerfectFinding` | позже |

---

## Антипаттерны

| Не делать | Почему |
|-----------|--------|
| `Tag::Legacy` в поле каждого finding | tag файла ≠ факт в AST |
| `ReadinessLevel` для guard-сценария (`GuardViolation`) | level — состояние файла, guard — нарушение обещания |
| `kind` enum в каждом finding | дублирует смысл класса; лучше `LegacyFinding extends Finding` |
| Логика выбора вывода в `AnalyseCommand` | разрастётся при `@legacy-perfect`, `@laravel-perfect` |
| Логика guard в `FindingsOutput` / `LegacyOutput` | output должен оставаться тупым форматтером |
| `SuccessOutput` / `UnSuccessOutput` как единственное деление | смешивает exit, уровень и тело вывода; нужен `ReportScenario` |
| `WithFindings` / `WithoutFindings` как единственное деление | `Legacy` exit 0 с findings; `Untagged` exit 1 без findings — разные сценарии |
| Политика `use` в `Detector` | Detector — факты AST; вердикт — UseDependencyChecker |
| Политика `use` в `ReadinessResolver` | resolver только `hasBlockers` по готовым `LegacyFinding` |

---

## Связанные документы

- `READINESS_MODEL.md` — уровни, метки, порядок проверки
- `ARCHITECTURE.md` — фазы, компоненты, контракт CLI
- `TDD.md` — как дробить работу тестами

# Архитектура

Технические решения по пакету. Смысл уровней и меток — `READINESS_MODEL.md`, цель продукта — `MANIFEST.md`.

## Сценарий

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│  правка     │────▶│  laravel-ready   │────▶│  exit 0/1   │
│  одного     │     │  один .php       │     │             │
│  .php       │     └──────────────────┘     └──────┬──────┘
└─────────────┘                                      │
                                                     ▼
                                            ┌─────────────────┐
                                            │  pre-commit     │
                                            │  staged .php    │
                                            └─────────────────┘
```

**Guard** — exit `1`, когда нарушено обещание метки или файл без метки. Детали — `RESOLUTION_AND_OUTPUT.md`. Блокировка коммита — на стороне **git hook** в целевом проекте.

---

## Фазы

| Фаза | Что | Статус |
|------|-----|--------|
| **0** | Блокеры легаси (AST), CLI на файл/каталог, вывод уровня | есть |
| **1** | `@laravel-ready`, `@laravel-adapter`, `ReadinessResolver`, guard + exit code | частично |
| **2** | Зависимости по `use`, `UseFinding`, autoload/base path | частично |
| **3** | `@legacy-adapter`, pre-commit-скрипт для легаси-проекта | план |
| **4** | `extends` / `new` / `require`, manifest, `LaravelPerfect` | позже |

Разработка пакета и использование на реальных файлах идут **параллельно**: новая фича в пакете — только когда без неё нельзя честно пометить следующий файл.

---

## Стек и доставка

**Сейчас:**

- **PHP 8.5** — пишем и запускаем CLI
- **Composer** — пакет с `bin/laravel-ready`
- **symfony/console**, **illuminate/support**, **nikic/php-parser**

Версия PHP **анализируемого** проекта не важна: CLI читает файлы с диска.

**Docker** (образ из `Dockerfile`, без Composer 8.5 на хосте):

```bash
docker build -t laravel-ready .

docker run --rm \
  -e FORCE_COLOR=1 \
  -v /path/to/KDL.Site:/project \
  laravel-ready \
  --app-root=/project/project/app \
  /project/project/app/Infrastructure/Cache
```

Без `-e FORCE_COLOR=1` stdout в контейнере не TTY — Symfony Console отключает ANSI, вывод без цветов. Для логов без escape-кодов: `-e NO_COLOR=1` или `--no-ansi`.

**Доставка:** static binary (`laravel-ready.exe`) через GitHub Release; в чужой проект — тонкий пакет `laravel-readiness/cli` (см. `RELEASE_TIERS.md`).

Подход к разработке — `DEVELOPMENT.md`, `TDD.md`.

---

## Контракт анализа

Результат — **уровень + причины** (`ReadinessLevel` + `Finding`), не `bool`.

**Компоненты:**

| Компонент | Роль |
|-----------|------|
| `LegacyDetector` | AST, сбор `findings` и `tag` |
| `ReadinessResolver` | `findings` + `tag` + deps → `ReadinessLevel` |
| `AnalyseCommand` | CLI, guard, exit code |
| `*Output` | Форматирование для человека |

Подробнее про resolver, guard и presenter — `RESOLUTION_AND_OUTPUT.md`.

Уровень считает **resolver**, не отдельные правила.

**Правила** — в коде пакета (`*Visitor`, per-rule enum). Инфраструктура: `nikic/php-parser`. Внешний rule-pack не подключаем — см. обзор ниже.

### Findings (расширяемый список)

| Finding | Фаза | Смысл |
|---------|------|-------|
| `SuperglobalFinding` | 0 | суперглобали |
| `GlobalFinding` | 0 | `global $x` |
| `FunctionCallFinding` | 0 | `define()`, `eval()`, … |
| `TagFinding` | 1 | метка в PHPDoc |
| `UseFinding` | 2 | недопустимый `use`; создаёт **UseDependencyChecker**, не Detector |

---

## Guard и exit code

| Условие | Exit |
|---------|------|
| `@laravel-ready` / `@laravel-adapter` без blockers | `0` |
| `@laravel-ready` / `@laravel-adapter` с blockers | `1` |
| `@legacy-code` | `0` |
| без метки / несколько меток | `1` |
| файл не найден, не `.php` | `≠ 0` (ошибка CLI) |

Полная таблица — `RESOLUTION_AND_OUTPUT.md`.

Флаг `--verbose` (план): показывать уровень и для неguarded-файлов. По умолчанию — тишина или краткая строка только для guarded.

### Git hook (в целевом проекте)

Pre-commit: `git diff-index --cached` → для каждого staged `.php` → `laravel-ready "$file"`. Любой exit `1` → коммит отменён. Паттерн как у `lint-php.sh` в этом репозитории.

Пакет **не ставит** хук в чужой проект автоматически — только документирует контракт.

Хуки **этого** репозитория (lint, Pint, PHPStan, тесты) — `GIT_HOOKS.md`.

---

## Контракт вывода CLI

### Успех (guarded, `LaravelReady`) — exit 0

```
src/Domain/Invoice.php : LaravelReady
```

### Провал (guarded, `Legacy`) — exit 1

```
src/Domain/Invoice.php : Legacy
  var: $_GET (line 12)
  func: define() (line 45)

Guard failed: @laravel-ready file must stay LaravelReady.
```

Только метка:

```
src/Domain/Invoice.php : Legacy
  tag: @legacy-code
```

Зависимость (фаза 2):

```
src/Domain/Invoice.php : Legacy
  use: App\Legacy\OldRepo (not LaravelReady)
```

### Не guarded — exit 0

По умолчанию без вывода. С `--verbose` — уровень + `(not guarded)`.

---

## Зависимости (`use`) — фаза 2

Реальный легаси (KDL.Site: `Wf\`, `.class.php`, `vendor-dir: libs`) — `LEGACY_PROJECTS.md`.

### Разделение слоёв

**Detector** — только факты из AST. Не решает, допустим ли `use`.

| Компонент | Роль |
|-----------|------|
| `UseVisitor` (в Detector) | `use` → сырой finding: FQCN + строка (`UseImportFinding` или аналог) |
| `UseDependencyChecker` | политика: `Wf\` → блок; `App\` → резолв + метка; vendor → пропуск → `UseFinding` |
| `ReadinessResolver` | `hasBlockers` по `LegacyFinding` (включая `UseFinding`); политику `use` **не применяет** |

```text
Detector.analyse(path)
    → AnalysisResult (теги, блокеры AST, сырые use-импорты)

UseDependencyChecker.check(result, path, projectRoot)   // только если @laravel-ready
    → дополняет findings UseFinding при нарушении

ReadinessResolver.resolve(result)
    → hasBlockers по LegacyFinding
```

Checker вызывается из resolver; политику `use` **не** кладём в Detector.

### Политика (принято для KDL и по умолчанию)

1. `use Wf\...` в guarded-файле → `UseFinding` (без резолва пути).
2. `use App\...` → резолв через `composer.json` целевого проекта (+ `.class.php` для KDL).
3. Остальное → если файл не в проекте (vendor) → пропуск.

**Вход checker'а:** guarded-файл, `projectRoot` / `composer.json` целевого проекта (не `tests/Fixtures/Use/composer.json` на проде).

Транзитивные deps (A → B → C) — позже; MVP — **только прямые `use`**.

---

## Сторонние пакеты с правилами

Готового набора под нашу модель **нет**. Правила зашиты в пакет; идеи можно заимствовать из PHPStan/PHPCS.

| Пакет | Почему не как источник правил |
|-------|-------------------------------|
| PHPStan | типы/баги, не шкала готовности |
| spaze/phpstan-disallowed-calls | PHPStan-экосистема, конфиг |
| PHPCompatibility | версии PHP, не легаси-паттерны |
| Rector | меняет код |
| Laravel Preflight | уже Laravel-проект |

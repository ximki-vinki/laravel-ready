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

**Guard** — режим, в котором exit `1` только если файл с `@laravel-ready` перестал быть `LaravelReady`. Блокировка коммита — на стороне **git hook** в целевом проекте, не внутри пакета.

---

## Фазы

| Фаза | Что | Статус |
|------|-----|--------|
| **0** | Блокеры легаси (AST), CLI на файл/каталог, вывод уровня | есть |
| **1** | `@laravel-ready`, `ReadinessResolver`, guard + exit code | план |
| **2** | Зависимости по `use`, `UseFinding`, autoload/base path | план |
| **3** | `@adapter`, `@for-legacy`, pre-commit-скрипт для легаси-проекта | план |
| **4** | `extends` / `new` / `require`, manifest, `LaravelPerfect` | позже |

Разработка пакета и использование на реальных файлах идут **параллельно**: новая фича в пакете — только когда без неё нельзя честно пометить следующий файл.

---

## Стек и доставка

**Сейчас:**

- **PHP 8.5** — пишем и запускаем CLI
- **Composer** — пакет с `bin/laravel-ready`
- **symfony/console**, **illuminate/support**, **nikic/php-parser**

Версия PHP **анализируемого** проекта не важна: CLI читает файлы с диска.

**Будущее (доставка в легаси без Composer 8.5):** Docker → phar → static binary — по необходимости.

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

Подробнее про resolver, guard, pledged/actual и presenter — `RESOLUTION_AND_OUTPUT.md` (**на уточнении**).

Уровень считает **resolver**, не отдельные правила.

**Правила** — в коде пакета (`*Visitor`, per-rule enum). Инфраструктура: `nikic/php-parser`. Внешний rule-pack не подключаем — см. обзор ниже.

### Findings (расширяемый список)

| Finding | Фаза | Смысл |
|---------|------|-------|
| `SuperglobalFinding` | 0 | суперглобали |
| `GlobalFinding` | 0 | `global $x` |
| `FunctionCallFinding` | 0 | `define()`, `eval()`, … |
| `TagFinding` | 1 | `@legacy-code` как причина `Legacy` |
| `UseFinding` | 2 | недопустимый `use` |

---

## Guard и exit code

| Условие | Exit |
|---------|------|
| `@laravel-ready` + `LaravelReady` | `0` |
| `@laravel-ready` + `Legacy` | `1` |
| без `@laravel-ready` | `0` (не guarded) |
| файл не найден, не `.php` | `≠ 0` (ошибка CLI) |

Флаг `--verbose` (план): показывать уровень и для неguarded-файлов. По умолчанию — тишина или краткая строка только для guarded.

### Git hook (в целевом проекте)

Pre-commit: `git diff-index --cached` → для каждого staged `.php` → `laravel-ready "$file"`. Любой exit `1` → коммит отменён. Паттерн как у `lint-php.sh` в этом репозитории.

Пакет **не ставит** хук в чужой проект автоматически — только документирует контракт.

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

**Вход:** guarded-файл, base path / composer autoload целевого проекта.

**Шаги:**

1. `UseVisitor` — список FQCN из `use`.
2. Резолв FQCN → путь к `.php` (PSR-4).
3. Для каждого проектного файла — тот же pipeline (tag + findings), без рекурсии по всему графу.
4. Нарушение → `UseFinding` → resolver → `Legacy`.

**Игнор:** `vendor/`, встроенные и сторонние namespace без файла в проекте.

Транзитивные deps (A → B → C) — предупреждение позже; MVP — **только прямые `use`**.

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

---

## Что уже есть в коде (фаза 0)

- `LegacyDetector` — суперглобали, `global`, blocked functions
- `TagVisitor` — `@legacy-code`, `@legacy-perfect` (чтение; resolver ещё не подключён)
- `AnalyseCommand` — один файл / каталог; **всегда exit 0** при найденном Legacy (нужна фаза 1)
- `ReadinessLevel` enum — без resolver

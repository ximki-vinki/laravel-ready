# Архитектура

Технические решения по пакету. Смысл уровней и меток — в `READINESS_MODEL.md`.

## Сейчас (MVP)

- **PHP 8.5** — пишем и запускаем CLI на этой версии
- **Composer** — пакет с `bin/laravel-ready`
- **symfony/console** — CLI; **illuminate/support**, **nikic/php-parser** — прод-зависимости
- Запуск там, где установлен PHP **8.5+**

Подход к разработке и TDD — `DEVELOPMENT.md`.

Версия PHP **анализируемого** проекта не важна: CLI читает файлы с диска. Легаси на 8.1 можно сканировать с машины на 8.5.

В `composer.json` легаси-проекта на PHP ниже 8.5 пакет **не поставится** — для таких случаев CLI запускают снаружи (глобально, отдельная установка, см. будущее ниже).

## Будущее (не сейчас)

Другие способы доставки того же CLI:

| Способ | Суть |
|--------|------|
| **Docker** | PHP 8.5 в образе; проект монтируется в контейнер |
| **Phar** ([box](https://github.com/box-project/box)) | один файл; на хосте нужен PHP 8.5+ |
| **Static binary** ([static-php-cli](https://github.com/static-php-cli/static-php-cli)) | бинарник со встроенным PHP; на хосте PHP не нужен |

Порядок: MVP (Composer) → Docker → phar → static binary — по необходимости.

## Контракт анализа (зафиксировано)

Результат — **шкала + причины**, не `bool` (см. `MANIFEST.md`, `READINESS_MODEL.md`).

**Рассмотренные варианты:**

| Подход | Суть | Вердикт |
|--------|------|---------|
| Ассоциативный массив | `['level' => 'Legacy', 'blockers' => [...]]` | Быстрый прототип; слабо для PHPStan 10 |
| DTO + строки | `AnalysisResult` + `list<string>` | Шаг от `bool`, но magic strings остаются |
| Один `enum Blocker` | все блокеры в одном enum | Простой CLI; enum раздувается, теряются детали правил |
| **Enum на правило + `Finding`** | `SuperglobalName`, своё enum у каждого правила | **Выбрано** — масштабируется, правила без правки ядра |

**Минимальный контракт (MVP):**

- `ReadinessLevel` — enum уровней модели
- `Finding` — marker interface; первое правило: `SuperglobalFinding(SuperglobalName $name)`
- `AnalysisResult` — `level`, `list<Finding>`, `list<Tag>` (tags позже)
- Уровень считает resolver из findings + tags, не каждое правило
- Guidance («→ LaravelReady: убрать …») — в CLI/formatter, не в DTO

**Правила блокеров** — свои, в коде пакета (не внешний rule-pack). Инфраструктура: `nikic/php-parser`. См. обзор сторонних пакетов ниже.

## Сторонние пакеты с правилами

Готового набора под нашу модель (`Legacy` → `LaravelReady` / `LegacyPerfect`, блокеры вроде суперглобалов и `define()`) **нет**.

| Пакет | Что даёт | Почему не подходит как источник правил |
|-------|----------|----------------------------------------|
| [nikic/php-parser](https://github.com/nikic/PHP-Parser) | AST, обход | Инфраструктура — уже используем |
| [PHPStan](https://phpstan.org/developing-extensions/rules) + кастомные rules | API правил на AST | Другая цель (типы/баги); тянет PHPStan; не шкала готовности |
| [spaze/phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls) | `$GLOBALS`, функции, superglobals через neon-конфиг | PHPStan-экосистема; конфиг, не наша модель уровней |
| [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility) | deprecated/removed PHP по версии | Совместимость версий PHP, не легаси-паттерны проекта |
| [Rector](https://github.com/rectorphp/rector) | авто-рефакторинг по правилам | Меняет код; не «готовность к Laravel» |
| Laravel Preflight / Upgrade Pilot | breaking changes Laravel 8→13 | Уже Laravel-проект; не миграция с кастомного легаси |
| PhpCodeArcheology | метрики качества, generic smells | Не project-specific blockers |

**Итог:** правила **зашиты в пакет** (свои `*BlockerRule` + per-rule enum). Можно заимствовать *идеи* detection из PHPStan/PHPCS, но не подключать их как зависимость для rule-pack.

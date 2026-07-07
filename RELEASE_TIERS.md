# Release Tiers

Практичный план публикации `laravel-ready` как инструмента для других проектов.

**Основной путь установки для пользователя:** `composer require-dev laravel-ready/cli` → `vendor/bin/laravel-ready`.  
**GitHub Release с `.exe`:** хранилище бинарника; тонкий пакет `cli` подтягивает его при `install` — не ручное скачивание.

Фокус старта: **Windows-first static binary**.

**Один репозиторий, два Composer-пакета** (monorepo, общие git-теги):

| Пакет | Назначение |
|-------|------------|
| `laravel-ready/tool` | Исходники и разработка: `src/`, тесты, полный PHP-стек (`php ^8.5`, illuminate, symfony, …). |
| `laravel-ready/cli` | Доставка пользователю: обёртка `bin/laravel-ready` + скачивание `.exe` из Release. Без `src/` и без runtime-зависимостей приложения. |

Пользователь **не** ставит `laravel-ready/tool` — только `laravel-ready/cli`.

### Термины

| Термин | Что это |
|--------|---------|
| **Static binary** | Один файл `laravel-ready.exe`. PHP 8.5 на машине пользователя **не нужен** для запуска анализа. Внутри exe — свой PHP-рантайм. |
| **GitHub Release** | Хранилище `.exe` для каждой версии (`v0.1.0`). В git и на Packagist exe **не коммитится** — только на Release. |
| **Сборка в CI** | PHP и Composer на раннере GitHub нужны **только** чтобы собрать бинарник. |
| **Тонкий пакет `cli`** | Мало PHP-кода: `bin/`, `composer.json`, плагин (например `pact-foundation/composer-downloads-plugin`). При install качает exe той же версии с Release. |

Внутренние шаги сборки (PHAR, Box) — детали пайплайна, не формат доставки. Пользователь их не видит.

### Потоки

**Разработчик (ты):**

```
код → git push → git tag v0.1.0 → git push --tags
                    │
                    ├─ CI → laravel-ready.exe → GitHub Release v0.1.0
                    └─ Packagist → версии 0.1.0 пакетов tool и cli
```

Руками exe в git **не пушишь** — только код и теги.

**Пользователь (чужой проект):**

```
composer require-dev laravel-ready/cli
        │
        ├─ Packagist → тонкий пакет cli (без exe, без illuminate/symfony)
        │
        └─ плагин → github.com/.../releases/download/v0.1.0/laravel-ready.exe
                    → vendor/laravel-ready/cli/bin/laravel-ready.exe

vendor/bin/laravel-ready файл.php  →  laravel-ready.exe файл.php
```

Composer при `require` качает **исходники PHP-пакета** с Packagist, не assets из Release. Release и Packagist — разные каналы; связь версий и скачивание exe — задача пакета `cli`.

**PHP у пользователя:** для `composer install` нужен PHP (сам Composer на PHP). Для **запуска** инструмента через `.exe` — PHP 8.5 и зависимости приложения **не нужны**. Требование `php` в `cli` — минимальное (только чтобы отработал Composer и плагин), не `^8.5` как у `tool`.

---

## 1) Minimum

Цель: в чужом проекте одна команда Composer и рабочий CLI через static binary.

### Шаги (по порядку)

| # | Шаг | Статус |
|---|-----|--------|
| 1 | `laravel-ready/tool`: `composer.json` как пакет разработки (`type: library`, `src/`, тесты) | готово |
| 2 | GitHub Actions: сборка `laravel-ready.exe` по тегу → GitHub Release | готово |
| 3 | Пакет `laravel-ready/cli`: тонкий dist + плагин скачивания exe + обёртка `bin/laravel-ready` | готово |
| 4 | Packagist: оба пакета из одного репо, общие теги `v*` | — |
| 5 | Проверка в другом проекте: `composer require-dev laravel-ready/cli` + запуск | — |

### Шаг 2: что делает workflow (`.github/workflows/release-windows.yml`)

Собирает **static binary** — один `.exe` без зависимости от PHP у пользователя при запуске.

| Элемент | Что делает |
|---------|------------|
| `env.BOX_URL` | URL Box PHAR для упаковки приложения. |
| `env.PHP_MICRO_URL` | URL static PHP micro.sfx (pin версии рантайма). |
| `on.push.tags: v*` | Запуск только при пуше тега (`v0.1.0`, `v0.1.1`, …). |
| `permissions: contents: write` | Право создать GitHub Release и загрузить файл. |
| `runs-on: windows-latest` | Сборка на Windows-раннере GitHub. |
| `checkout` | Клонирует репозиторий на коммит тега. |
| `setup-php` | PHP 8.5 + Composer — **только на CI-раннере**, для процесса сборки. |
| `composer install --no-dev` | Prod-зависимости попадут внутрь бинарника. |
| `Bundle application` | Box: упаковка `src` + `vendor` → PHAR (`main`: `bin/laravel-ready`). |
| `Download micro.sfx` | Статический PHP-рантайм — ядро будущего `.exe`. |
| `Assemble laravel-ready.exe` | Склейка рантайма и PHAR → **static binary**. |
| `Smoke test` | Запуск `.exe` без аргументов, ожидается exit 0. |
| `action-gh-release` | Публикует `laravel-ready.exe` в Release с именем тега. |

Файл `box.json` — конфиг сборки PHAR, не артефакт для пользователя.

### Шаг 3: пакет `laravel-ready/cli`

Тонкий пакет для установки в чужой проект. **Не дублирует** `src/` и **не тянет** illuminate/symfony/php-parser в `vendor` пользователя — всё это уже внутри `.exe`.

Планируемая структура (monorepo):

```
laravel-ready/
  composer.json              ← laravel-ready/tool (разработка)
  src/ …
  packages/cli/
    composer.json            ← laravel-ready/cli (пользователи)
    bin/laravel-ready        ← обёртка: exe на Windows, иначе сообщение / fallback
    bin/laravel-ready.bat    ← опционально: прямой вызов exe без php-shim
```

| Элемент | Что делает |
|---------|------------|
| `name: laravel-ready/cli` | Имя на Packagist для `composer require-dev`. |
| `require` плагина | Например `pact-foundation/composer-downloads-plugin` — скачивание при install/update. |
| `extra.downloads` | URL: `…/releases/download/{$version}/laravel-ready.exe`, путь: `bin/laravel-ready.exe`. |
| `bin/laravel-ready` | Если рядом есть `.exe` → запуск exe с аргументами; иначе — подсказка или dev-fallback. |
| Общий тег `v0.1.0` | Версия `cli@0.1.0` → exe с Release `v0.1.0`. |

`.exe` в git **нет** — только на GitHub Release; плагин качает при установке нужной версии.

### Как проверить шаг 2

1. Закоммитить и запушить изменения в `master`.
2. Создать и запушить тег:
   ```bash
   git tag v0.1.0
   git push origin v0.1.0
   ```
3. GitHub → **Actions** → workflow **Release Windows Binary** → дождаться зелёной галочки.
4. GitHub → **Releases** → релиз `v0.1.0` → скачать `laravel-ready.exe`.
5. На Windows (без отдельно установленного PHP 8.5):
   ```cmd
   laravel-ready.exe
   laravel-ready.exe --app-root=C:\path\to\app C:\path\to\file.php
   ```

Если workflow красный — открыть упавший step в Actions и смотреть лог (часто: нет прав `contents: write`, ошибка упаковки приложения, неверный URL micro.sfx).

### Что обязательно для Minimum

- Репозиторий на GitHub с тегами версий (`v0.1.0`, `v0.1.1`, …).
- `laravel-ready/tool` — пакет разработки (исходники, CI, Box).
- `laravel-ready/cli` — тонкий пакет доставки (без runtime-зависимостей приложения).
- GitHub Actions: `tag push` → сборка exe → артефакт в Release.
- Плагин / post-install в `cli`: скачивание exe по версии пакета.
- Packagist: оба пакета, webhook на теги.
- README: `composer require-dev laravel-ready/cli`, пример с `--app-root`, fallback «скачать `.exe` вручную с Releases».

### Критерий готовности Minimum

- В чужом проекте: `composer require-dev laravel-ready/cli` → `vendor/bin/laravel-ready` работает на Windows.
- Запуск анализа идёт через **static binary**; PHP 8.5 и стек `tool` на машине пользователя **не требуются**.
- По тегу `vX.Y.Z` в Release лежит `laravel-ready.exe`; версия `cli@X.Y.Z` качает именно его.
- `laravel-ready/tool` по-прежнему используется для разработки в этом репозитории, не как основной пакет для потребителей.

---

## 2) Желательно (уже не стыдно показывать)

Цель: предсказуемые релизы и удобство для других команд.

### Что добавить

- Smoke-тест собранного `.exe` на fixtures в CI.
- Checksums (`SHA256`) для артефактов в Release; проверка в плагине `cli`.
- Release notes (изменения, breaking changes, ограничения).
- Пример интеграции: pre-commit и CI job с fail по exit code `1`.
- `composer global require laravel-ready/cli` как альтернатива project-local установке.
- CI: версия в теге совпадает с `Application::VERSION`.

### Критерий готовности «Желательно»

- Каждый релиз проходит smoke-проверку до публикации.
- Можно показать коллеге: одна команда `composer require-dev laravel-ready/cli` + готовый пример хука.

---

## 3) Maximum (production-grade)

Цель: зрелый release-процесс на все платформы.

### Что добавить

- Matrix-сборка: `windows`, `linux`, `macos`.
- Platform-пакеты (`laravel-ready/cli-windows`, `-linux`, `-darwin`) или единый `cli` с выбором артефакта по ОС.
- Подпись артефактов, provenance.
- Semver + changelog policy.
- Расширенные тесты артефактов (smoke, негативные кейсы, exit codes, формат вывода).
- Документация внедрения: pre-commit, CI templates, стратегия обновлений.
- Мониторинг качества релизов (фейлы сборки, time-to-fix).

### Критерий готовности Maximum

- Релизы воспроизводимы и безопасны.
- Инструмент ставится и работает на разных ОС без ручных правок.

---

## Рекомендуемый порядок

1. Закрыть шаги **Minimum** (1 → 5): `cli`, Packagist, проверка в чужом проекте.
2. Добавить блок **Желательно**.
3. Расширять до **Maximum** по необходимости.

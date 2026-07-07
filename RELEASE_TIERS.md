# Release Tiers

Практичный план публикации `laravel-ready` как инструмента для других проектов.

**Основной путь установки:** Composer (`require-dev` → `vendor/bin/laravel-ready`).  
**GitHub Release с `.exe`:** хранилище бинарника, который Composer подтягивает при `install` — не ручное скачивание.

Фокус старта: **Windows-first static binary**.

Один репозиторий, один Composer-пакет (`laravel-ready/tool`).

### Термины

| Термин | Что это |
|--------|---------|
| **Static binary** | Один файл `laravel-ready.exe`. PHP на машине пользователя **не нужен**. Это то, что мы публикуем и ставим через Composer. |
| **GitHub Release** | Хранилище `.exe` для каждой версии (`v0.1.0`). Composer-установщик (шаг 3) скачивает отсюда. |
| **Сборка в CI** | PHP и Composer на раннере GitHub нужны **только** чтобы собрать бинарник. Пользователь их не ставит. |

Внутренние шаги сборки (PHAR, Box) — детали пайплайна, не формат доставки. Пользователь их не видит.

---

## 1) Minimum (уже работает)

Цель: в чужом проекте добавить зависимость в `composer.json` и запустить CLI.

### Шаги (по порядку)

| # | Шаг | Статус |
|---|-----|--------|
| 1 | `composer.json`: `type: library` (пакет можно ставить в другой проект) | готово |
| 2 | GitHub Actions: сборка static binary `laravel-ready.exe` по тегу → GitHub Release | готово |
| 3 | Post-install в пакете: скачать `.exe` из Release, обёртка `bin/laravel-ready` | — |
| 4 | Проверка в другом проекте: `composer require-dev` + запуск | — |

### Шаг 2: что делает workflow (`.github/workflows/release-windows.yml`)

Собирает **static binary** — один `.exe` без зависимости от PHP у пользователя.

| Элемент | Что делает |
|---------|------------|
| `on.push.tags: v*` | Запуск только при пуше тега (`v0.1.0`, `v0.1.1`, …). |
| `permissions: contents: write` | Право создать GitHub Release и загрузить файл. |
| `runs-on: windows-latest` | Сборка на Windows-раннере GitHub. |
| `checkout` | Клонирует репозиторий на коммит тега. |
| `setup-php` | PHP 8.5 + Composer — **только на CI-раннере**, для процесса сборки. |
| `composer install --no-dev` | Prod-зависимости попадут внутрь бинарника. |
| `Bundle application` | Упаковка `src` + `vendor` (внутренний шаг сборки). |
| `Download micro.sfx` | Статический PHP-рантайм — ядро будущего `.exe`. |
| `Assemble laravel-ready.exe` | Склейка рантайма и приложения → **static binary**. |
| `Smoke test` | Запуск `.exe` без аргументов, ожидается exit 0. |
| `action-gh-release` | Публикует `laravel-ready.exe` в Release с именем тега. |

Файл `box.json` — конфиг сборки PHAR, не артефакт для пользователя.

### Как проверить шаг 2

1. Закоммитить и запушить изменения в `master`.
2. Создать и запушить тег:
   ```bash
   git tag v0.1.0
   git push origin v0.1.0
   ```
3. GitHub → **Actions** → workflow **Release Windows Binary** → дождаться зелёной галочки.
4. GitHub → **Releases** → релиз `v0.1.0` → скачать `laravel-ready.exe`.
5. На Windows (без PHP):
   ```cmd
   laravel-ready.exe
   laravel-ready.exe --app-root=C:\path\to\app C:\path\to\file.php
   ```

Если workflow красный — открыть упавший step в Actions и смотреть лог (часто: нет прав `contents: write`, ошибка упаковки приложения, неверный URL micro.sfx).

### Что обязательно

- Репозиторий на GitHub с тегами версий (`v0.1.0`, `v0.1.1`, …).
- `composer.json` как устанавливаемого пакета (`type: library`, `bin`, без dev-мусора в dist).
- GitHub Actions workflow на `tag push` → сборка static binary → артефакт в Release.
- Скрипт установки бинарника при `composer install` / `update`.
- README: `composer require-dev`, пример с `--app-root`, fallback «скачать `.exe` вручную».

### Критерий готовности Minimum

- В чужом проекте: `composer require-dev laravel-ready/tool` → `vendor/bin/laravel-ready` работает.
- Бинарник не требует PHP на машине пользователя (static binary).
- По тегу `vX.Y.Z` в Release лежит `laravel-ready.exe` (источник для Composer-установщика).

---

## 2) Желательно (уже не стыдно показывать)

Цель: предсказуемые релизы и удобство для других команд.

### Что добавить

- Smoke-тест собранного `.exe` на fixtures в CI.
- Checksums (`SHA256`) для артефактов в Release.
- Release notes (изменения, breaking changes, ограничения).
- Пример интеграции: pre-commit и CI job с fail по exit code `1`.
- `composer global require` как альтернатива project-local установке.

### Критерий готовности «Желательно»

- Каждый релиз проходит smoke-проверку до публикации.
- Можно показать коллеге: одна команда Composer + готовый пример хука.

---

## 3) Maximum (production-grade)

Цель: зрелый release-процесс на все платформы.

### Что добавить

- Matrix-сборка: `windows`, `linux`, `macos`.
- Platform-пакеты или единый installer с выбором ОС.
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

1. Закрыть все шаги блока **Minimum** (1 → 4).
2. Добавить блок **Желательно**.
3. Расширять до **Maximum** по необходимости.

# Git hooks

Как устроены хуки в этом репозитории и как они связаны с продуктовым сценарием `laravel-ready` в легаси-проекте.

Смысл продукта и guard — `MANIFEST.md`, `ARCHITECTURE.md`. Команды разработки — `DEVELOPMENT.md`.

---

## Два разных «мира» хуков

| | Хуки **этого** репозитория | Хуки **целевого** легаси-проекта |
|---|---|---|
| Где | `.git_hooks/` | Пока **не реализованы** (фаза 3 в `ARCHITECTURE.md`) |
| Зачем | Качество кода пакета (lint, стиль, тесты) | Блокировать коммит, если `@laravel-ready` файл деградировал |
| Установка | `composer hooks` | Будет документированный скрипт для чужого проекта |

Ниже — хуки, которые **реально работают сейчас** в `.git_hooks/`.

---

## Установка

```bash
composer hooks
```

Команда запускает `.git_hooks/install.sh`. Скрипт создаёт в `.git/hooks/` два runner-скрипта: `pre-commit` и `pre-push`.

Runner переходит в корень репозитория и **по порядку имени файла** выполняет всё из `.git_hooks/<hook-name>/`. Если любой скрипт завершается с ненулевым кодом — операция git отменяется (`set -e`).

Каждый файл в `pre-commit/` и `pre-push/` — тонкая обёртка; логика лежит в `.git_hooks/scripts/`.

### Схема

```
git commit  →  .git/hooks/pre-commit  →  01-lint-php.sh  →  scripts/lint-php.sh
                                         →  02-pint.sh      →  scripts/pint.sh

git push    →  .git/hooks/pre-push     →  01-composer-validate.sh  →  scripts/composer-validate.sh
                                         →  02-phpstan.sh            →  scripts/phpstan.sh
                                         →  03-test-code.sh          →  scripts/test-code.sh
```

---

## pre-commit (перед коммитом)

Быстрые проверки только по **staged** файлам.

### 1. `php-linter` — `01-lint-php.sh`

| | |
|---|---|
| **Когда** | каждый `git commit` |
| **Область** | staged `.php` (`git diff-index --cached`) |
| **Как** | `php -l` на каждый файл |
| **При провале** | коммит блокируется |
| **Меняет файлы** | нет |

### 2. `pint` — `02-pint.sh`

| | |
|---|---|
| **Когда** | каждый `git commit` |
| **Область** | staged `.php`, кроме `tests/Fixtures/` |
| **Как** | `vendor/bin/pint` форматирует файлы, затем `git add` возвращает их в stage |
| **При провале** | коммит блокируется, если `pint` не найден (нужен `composer install`) |
| **Меняет файлы** | да |

---

## pre-push (перед push)

Тяжёлые проверки по **всему** проекту.

### 1. `composer-validate` — `01-composer-validate.sh`

- `composer validate --strict --no-check-publish --no-check-all`
- Невалидный `composer.json` → push блокируется

### 2. `phpstan` — `02-phpstan.sh`

- `composer phpstan`
- Ошибки статического анализа → push блокируется

### 3. `test-code` — `03-test-code.sh`

- `composer test` (Pest)
- Падающие тесты → push блокируется

---

## Разделение ответственности

| Событие | Скорость | Область | Что проверяет |
|---|---|---|---|
| **pre-commit** | быстро | только staged | синтаксис PHP, форматирование Pint |
| **pre-push** | медленно | весь репозиторий | composer.json, PHPStan, тесты |

pre-commit не гоняет PHPStan и тесты — чтобы коммит не тормозил. pre-push ловит регрессии перед отправкой в remote.

---

## Продуктовый хук `laravel-ready` (план)

Для **легаси-проекта**, куда подключают пакет, описан отдельный сценарий — не путать с хуками разработки этого репозитория.

```
правка файла → laravel-ready path/to/File.php → exit 0 / 1
коммит       → pre-commit на staged .php      → тот же CLI
```

Паттерн — как у `lint-php.sh`: `git diff-index --cached` → для каждого staged `.php` → `laravel-ready "$file"`. Любой exit `1` → коммит отменён.

### Контракт exit code (guard)

| Условие | Exit |
|---------|------|
| `@laravel-ready` + `LaravelReady` | `0` |
| `@laravel-ready` + `Legacy` | `1` |
| без `@laravel-ready` | `0` (не guarded) |
| файл не найден, не `.php` | `≠ 0` (ошибка CLI) |

Пакет **не ставит** хук в чужой проект автоматически — только CLI с предсказуемым exit code и документация.

### Статус

- Скрипт pre-commit для легаси — **фаза 3** (`ARCHITECTURE.md`)
- Guard в `AnalyseCommand` — **фаза 1**: CLI уже анализирует файлы, но пока **всегда** возвращает `0`, даже для `Legacy`

---

## Краткое резюме

В репозитории пакета стоят git-хуки качества кода: **pre-commit** проверяет синтаксис и форматирует staged PHP через Pint; **pre-push** валидирует `composer.json`, гоняет PHPStan и тесты.

Отдельный pre-commit для `laravel-ready` в целевом легаси-проекте запланирован, но ещё не реализован.

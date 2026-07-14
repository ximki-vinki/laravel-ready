# TDD

Как дробим работу. Смысл уровней — `READINESS_MODEL.md`, стек — `DEVELOPMENT.md`.

## Цикл

```
Красный → Зелёный → Рефакторинг → …
```

| Фаза | Суть |
|------|------|
| **Красный** | Новый тест. Код не умеет — тест падает. |
| **Зелёный** | Минимум, чтобы **этот** тест прошёл. `return true` — нормально. |
| **Рефакторинг** | Меняем форму, не поведение. Тесты зелёные. Stan + Pint. |

Один тест — один шаг. Не прыгаем сразу к парсеру, resolver и guard.

## Пример: guard (фаза 1)

Цель продукта — `MANIFEST.md`. Тест фиксирует **exit code**, не только вывод.

| # | Тест | Ожидание |
|---|------|----------|
| 1 | `@laravel-ready`, без блокеров | exit `0`, `LaravelReady` |
| 2 | `@laravel-ready`, `$_GET` | exit `1`, `Legacy` + причины |
| 3 | без метки, `$_GET` | exit `0` (не guarded) |
| 4 | `@laravel-ready`, `@legacy-code` | exit `1`, `tag: @legacy-code` |
| 5 | `@laravel-adapter @skipCheck` + блокер | exit `0`, footer `Skipped: @skipCheck.` |
| 6 | без метки + `@skipCheck` + блокер | exit `1` (skip не спасает Untagged) |

## Пример: `$GLOBALS`

Фикстура: `tests/Fixtures/Legacy/globals.php`. Пока достаточно `LegacyDetector::isLegacy(string $path): bool`.

| # | Фаза | Тест | Код |
|---|------|------|-----|
| 1 | Красный | `globals.php` → `true` | `return false` |
| 2 | Зелёный | тот же | `return true` (заглушка) |
| 3 | Красный | чистый файл → `false` | заглушка ломается |
| 4 | Зелёный | оба | ищем `$GLOBALS` в AST |
| 5 | Рефакторинг | оба без изменений | `BlockerRule`, walker, парсер отдельно |

**Шаг 1 — тест:**

```php
expect((new LegacyDetector)->isLegacy(fixture('Legacy/globals.php')))->toBeTrue();
```

**Шаг 5 — рефакторинг:** правило `$GLOBALS` → `GlobalsBlockerRule`, обход AST → `AstWalker`. Заглушку убирает шаг 3, не рефакторинг.

Дальше по той же схеме: resolver, guard, `use` (фаза 2), следующий блокер.

## Источник правды

Тесты и фикстуры в `tests/Fixtures/`. Модель — *что*; этот файл — *как* дробим.

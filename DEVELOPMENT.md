# Разработка

Как пишем код. Смысл уровней — `READINESS_MODEL.md`, доставка — `ARCHITECTURE.md`.

## Стек

**Прод:** PHP 8.5, `symfony/console`, `illuminate/support`, `nikic/php-parser`.

**Dev:** PHPUnit 13, PHPStan (level 10), Laravel Pint.

CLI — **Symfony Console** (без `illuminate/console`): меньше bootstrap, достаточно для команд.

## Команды

```bash
composer install
vendor/bin/phpunit          # тесты
vendor/bin/phpstan analyse  # статический анализ
vendor/bin/pint             # стиль (Laravel preset)
vendor/bin/pint --test      # проверка без правок
php bin/laravel-ready       # CLI
```

`composer.lock` коммитим — воспроизводимые сборки.

## TDD

1. **Красный** — тест из `READINESS_MODEL.md` (файл → уровень, блокер, метка).
2. **Зелёный** — минимум кода, чтобы тест прошёл (заглушка — нормально).
3. **Рефакторинг** — улучшаем структуру при зелёных тестах; Stan и Pint перед коммитом.

Подробный пример с лестницей шагов — `TDD.md`.

Тесты — источник правды для правил анализа; документация не дублирует детали реализации.

## Перед коммитом

```bash
vendor/bin/phpstan analyse
vendor/bin/phpunit
vendor/bin/pint --test
```

## Порядок фич

Согласован с `ARCHITECTURE.md` (фазы 0–4). Сначала охрана периметра, не полная миграция.

**Фаза 1 (guard):**

1. Тест: `@laravel-ready` + блокер → exit `1`
2. `Tag::LaravelReady`, `ReadinessResolver`
3. Guard в `AnalyseCommand`, exit code
4. Stan + Pint

**Фаза 2 (use):**

1. Тест: guarded-файл + `use` на легаси → exit `1`
2. `UseVisitor`, резолв FQCN, `UseFinding`
3. Прогон на реальном файле в легаси-проекте

**Параллельно с легаси:** один файл → CLI → метка → хук. Пакет дописываем только под реальную боль.

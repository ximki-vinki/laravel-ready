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

1. Тест-кейс из модели готовности
2. Правило / парсер / команда
3. Stan + Pint
4. Прогон на своём легаси

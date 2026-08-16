# laravel-ready

CLI для охраны PHP-файлов при миграции в Laravel: помечаешь файл меткой — при правке проверка не даёт скатиться в легаси.

## Запуск

```bash
# PHP 8.5+
composer install
cd /path/to/project
vendor/bin/laravel-ready path/to/File.php
```

**Windows:** скачать `laravel-ready.exe` с [Releases](https://github.com/ximki-vinki/laravel-ready/releases) (PHP на машине не нужен).

```cmd
cd C:\path\to\project
laravel-ready.exe path\to\File.php
```

В текущей рабочей директории должен находиться `laravel-ready.json`. Пути
резолверов задаются относительно этого файла:

```json
{
  "resolvers": [
    {
      "prefix": "App\\",
      "path": "app/"
    }
  ]
}
```

**Docker:**

```bash
docker build -t laravel-ready .
docker run --rm -e FORCE_COLOR=1 \
  -v /path/to/project:/project \
  -w /project \
  laravel-ready \
  app/SomeFile.php
```

Exit `0` — ок для хука/CI, `1` — guard провален (или нет/несколько меток).

## Метки (PHPDoc)

| Метка | Смысл |
|-------|--------|
| `@laravel-ready` | Под охраной: без AST-блокеров, deps ок |
| `@laravel-adapter` | Мост к легаси для Laravel-контура |
| `@legacy-adapter` | Мост только внутри легаси; AST только по `@allows` |
| `@legacy-perfect` | Почищен, но остаётся в легаси-контуре |
| `@legacy-code` | Явный легаси (findings не валят exit) |
| `@allows` | Модификатор для `@legacy-adapter`: `$_COOKIE`, `setcookie`, … |
| `@skipCheck` | Модификатор: при blockers не валить guard (exit `0`, findings видны); на файл без/с несколькими метками не действует. С `until=YYYY-MM-DD` — временный скип с дедлайном: пакет материализует дату в файл (дефолт из `laravel-ready.json`), после дедлайна guard снова валит (exit `1`). Пакет пишет, хук читает |

Public CLI contract (hooks/CI): `CLI_CONTRACT_0x.md`.

Архитектура и модель: `ARCHITECTURE.md`, `READINESS_MODEL.md`, `RESOLUTION_AND_OUTPUT.md`, `MANIFEST.md`.


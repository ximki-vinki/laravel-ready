# laravel-ready

CLI для охраны PHP-файлов при миграции в Laravel: помечаешь файл меткой — при правке проверка не даёт скатиться в легаси.

## Запуск

```bash
# PHP 8.5+
composer install
php bin/laravel-ready --app-root=/path/to/app path/to/File.php
```

**Windows:** скачать `laravel-ready.exe` с [Releases](https://github.com/ximki-vinki/laravel-ready/releases) (PHP на машине не нужен).

```cmd
laravel-ready.exe --app-root=C:\path\to\app C:\path\to\File.php
```

**Docker:**

```bash
docker build -t laravel-ready .
docker run --rm -e FORCE_COLOR=1 \
  -v /path/to/project:/project \
  laravel-ready \
  --app-root=/project/project/app \
  /project/project/app/SomeFile.php
```

Exit `0` — ок для хука/CI, `1` — guard провален (или нет/несколько меток).

## Метки (PHPDoc)

| Метка | Смысл |
|-------|--------|
| `@laravel-ready` | Под охраной: без AST-блокеров, deps ок |
| `@laravel-adapter` | Мост к легаси для Laravel-контура |
| `@legacy-adapter` | Мост только внутри легаси |
| `@legacy-perfect` | Почищен, но остаётся в легаси-контуре |
| `@legacy-code` | Явный легаси (findings не валят exit) |


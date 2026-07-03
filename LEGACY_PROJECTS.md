# Целевые легаси-проекты

Описание реальных проектов, на которых проверяем `laravel-ready`, и ограничения для фазы 2 (`use`).

Смысл уровней и guard — `READINESS_MODEL.md`. Технический pipeline — `ARCHITECTURE.md`.

---

## Зачем этот файл

Unit-фикстуры (`tests/Fixtures/Use/`) — **только для TDD** в репозитории `laravel-ready`. На реальном проекте читается **`composer.json` целевого репозитория** (KDL.Site), не фикстура.

`tests/Fixtures/Use/composer.json` опционален: можно обойтись конвенцией `App\` → `src/` в тестах. На проде всегда — composer целевого проекта + политика `Wf\`.

Обновлять при изменении структуры целевого проекта или при выборе реализации в пакете.

---

## KDL.Site (kdl/site)

Первый реальный проект для охраны периметра. Slim + кастомный фреймворк `Wf`, постепенная миграция в namespace `App\`.

### Bootstrap

```
index.php / api.php / cronjobs
    → core/bootstrap.php
        → libs/Wf/Project.class.php
            → Wf::bootstrap()
                → spl_autoload_register('wfAutoLoad')   // легаси
                → include libs/autoload.php             // Composer
```

Корневая константа: `WWW_DIR` (корень репозитория).

### Два мира кода

| Зона | Namespace | Где лежит | Роль |
|------|-----------|-----------|------|
| **Новый код** | `App\` | `project/app/` | Целевой периметр для `@laravel-ready` |
| **Фреймворк Wf** | `Wf\` | `libs/Wf/`, частично `project/*` | Легаси-движок, БД, контроллеры, шаблоны |
| **PEAR / Zend** | `Text_*`, `Zend_*`, … | `libs/PEAR/`, `libs/Zend/` | Старые библиотеки через `include_path` |
| **Composer-пакеты** | `Illuminate\`, `GuzzleHttp\`, … | `libs/` (`vendor-dir`) | Внешние зависимости |
| **Точки входа** | — | `index.php`, `admin/`, `api/` | Без namespace, `include`, суперглобали |

### `project/` вне `app/`

Папки с кодом под namespace `Wf\`, загружаемым через `wfAutoLoad` (не через Composer PSR-4):

| Папка | Пример namespace |
|-------|------------------|
| `project/publicCtrls/` | `Wf\Controller\Publicly\Index` |
| `project/adminCtrls/` | `Wf\Controller\Admin\...` |
| `project/db/` | `Wf\Db\Table\...` |
| `project/entity/` | `Wf\Entity\Table\...` |
| `project/tools/` | `Wf\Tools\YandexCaptcha` |
| `project/components/`, `api/`, `restApi/`, … | `Wf\Controller\...` |

### Composer autoload (узкий)

Из `composer.json` целевого проекта:

```json
"config": { "vendor-dir": "libs" },
"autoload": {
  "psr-4": { "App\\": "project/app/" },
  "files": ["project/app/helpers.php"]
}
```

Только `App\` → `project/app/`. Namespace `Wf\` в autoload **не описан**.

### `wfAutoLoad` (источник правды для `Wf\`)

Файл: `libs/functions/main.php`, функция `wfAutoLoad($className)`.

Принцип PEAR: `\Wf\Db\Table` → путь `Wf/Db/Table` с вариантами:

1. `libs/{path}.class.php`
2. `libs/{path}/{LastSegment}.class.php`
3. `libs/{path}.php` (Zend)
4. `libs/PEAR/{path}.php`

Дополнительно — префиксный ремап в `project/` (фрагмент таблицы `quick`):

| Префикс пути | Папка в `project/` |
|--------------|-------------------|
| `Wf/Controller/Publicly/` | `publicCtrls` |
| `Wf/Controller/Admin/` | `adminCtrls` |
| `Wf/Db/Table/` | `db` |
| `Wf/Entity/Table/` | `entity` |
| `Wf/Tools` | `tools` |
| `Wf/Profile` | `profile` |
| `App` | `app` |
| … | см. `libs/functions/main.php` |

### Смешанные расширения в `project/app/`

Порядка ~520 PHP-файлов в `project/app/`:

- `.php` — новый стиль (`OfficeListService.php`, `OfficeRepository.php`);
- `.class.php` — легаси-именование (`UserDto.class.php`, `BaseRepository.class.php`).

Стандартный PSR-4 Composer ищет только `ClassName.php` → **`UserDto.class.php` не резолвится**.

### Типичные `use` в новом коде

`App\`-файлы часто импортируют и своё, и легаси:

```php
// App\EntryPoint\...\AuthenticationByPhoneController.php
use App\Domain\Dto\Authentication\ByPhone\UserDto;   // .class.php
use Wf\Tools\YandexCaptcha;                          // project/tools/

// App\Infrastructure\Repository\Office\OfficeRepository.php
use Wf\Db\Table;
use Wf\Db\Exception;
```

`UserDto` лежит в `Domain/Dto/.../UserDto.class.php`.

---

## Что не покроет наивный резолв (только `composer.json`)

Сценарий: читаем `autoload.psr-4`, ищем `Prefix\Class.php`, vendor = `libs/`.

| `use` в guarded-файле | Наивный резолв | Проблема |
|-----------------------|----------------|----------|
| `App\...\UserDto` | не найден | файл `UserDto.class.php`, не `.php` |
| `App\...\OrderRepository` | не найден | часто `.class.php` |
| `Wf\Db\Table` | пропуск (внешний) | это **легаси в проекте**, ложный «ок» |
| `Wf\Tools\YandexCaptcha` | пропуск | файл в `project/tools/` |
| `Illuminate\Support\Str` | пропуск | корректно (composer в `libs/`) |
| `Psr\Http\Message\...` | пропуск | корректно |

Итог: guard на KDL.Site будет **дырявым** без доработок резолвера.

### Что достаточно для unit-тестов пакета

`tests/Fixtures/Use/composer.json` (`App\` → `src/`, только `.php`) — **достаточно для TDD** базовой логики `UseFinding`. **Недостаточно** как модель KDL.Site.

---

## Принятое решение (фаза 2)

### Denylist `Wf\` + резолв `App\`

| Слой | Что делает |
|------|------------|
| **Detector** | `UseVisitor` — факт: FQCN и строка. **Без вердикта.** |
| **UseDependencyChecker** | `use Wf\...` в `@laravel-ready` → сразу `UseFinding`; `use App\...` → резолв + метка |
| **ReadinessResolver** | `hasBlockers` по `instanceof LegacyFinding` (включая `UseFinding`). Политику не применяет |

Резолвер `wfAutoLoad` для guard **не нужен**: префикс `Wf\` в `use` — достаточное условие нарушения.

### Как писать код в KDL.Site

| Метка | `use Wf\...` | `use App\...` |
|-------|--------------|---------------|
| `@laravel-ready` | запрещено | только на `@laravel-ready` / `@adapter` |
| `@adapter` | разрешено | по необходимости |
| без метки | не guard'ится | не guard'ится |

```php
// ❌ не пройдёт guard
/** @laravel-ready */
class OfficeRepository {
    use Wf\Db\Table;
}

// ✅ адаптер — Wf остаётся здесь
/** @adapter */
class WfOfficeTableGateway { /* use Wf\... */ }

// ✅ периметр — только App + vendor
/** @laravel-ready */
class OfficeListService {
    public function __construct(private OfficeRepository $repo) {}
}
```

---

## Альтернативы (не выбраны для MVP)

### A. Слойный резолвер с `wfAutoLoad`

Несколько стратегий по префиксу namespace; первая, нашедшая файл, побеждает.

| Префикс | Стратегия |
|---------|-----------|
| `App\` | PSR-4 из `composer.json` + расширения `.php`, `.class.php` |
| `Wf\` | Повтор логики `wfAutoLoad` (или общий модуль, вызываемый из пакета) |
| Остальное | если файл только под `libs/{package}/` и это composer-vendor → пропуск |

`vendor-dir` читать из `composer.json` (`libs`, не обязательно `vendor/`).

### B. Узкий MVP только для `App\`

Проверять **только** `use App\...`; `use Wf\...` не проверять.

Отклонено: не ловит главный риск KDL — прямой `Wf\` в новом коде.

### C. Политика для `Wf\` через метки зон

Не резолвить каждый `Wf\`-класс, а задать правило:

- всё под `libs/Wf/` → зона `@legacy-code` или слой `@adapter` целиком;
- `use Wf\...` в `@laravel-ready` → допустимо только если зависимость помечена `@adapter` (или в allowlist).

Меньше точного резолва, больше договорённостей в метках.

### D. Конфиг проекта (`laravel-ready.json` в корне KDL.Site)

Явное описание для пакета:

```json
{
  "vendor-dir": "libs",
  "resolvers": [
    {
      "prefix": "App\\",
      "path": "project/app/",
      "extensions": [".php", ".class.php"]
    },
    {
      "prefix": "Wf\\",
      "type": "wf-pear",
      "libs-path": "libs/",
      "project-map": { "Wf/Tools": "project/tools" }
    }
  ]
}
```

Плюс: не хардкодить KDL в пакете. Минус: поддержка конфига.

---

## Рекомендуемый порядок внедрения

1. **TDD** — фикстура `tests/Fixtures/Use/` (`App\` без метки → `UseFinding`).
2. **Detector** — `UseVisitor`, сырой импорт (не `UseFinding`).
3. **UseDependencyChecker** — denylist `Wf\` + резолв `App\` через composer целевого проекта.
4. **ReadinessResolver** — без изменений логики: `UseFinding instanceof LegacyFinding`.
5. **KDL** — расширения `.class.php` для `App\`.
6. **CLI** — `--project-root`, поиск `composer.json` вверх от файла.
7. Позже — `extends` / `new` / `require` (фаза 4).

---

## Проверочный чеклист на реальном файле

Перед тем как пометить файл `@laravel-ready` в KDL.Site:

- [ ] Нет `use Wf\...` (в `@laravel-ready` — всегда блок)
- [ ] Все `use App\...` ведут на `@laravel-ready` или `@adapter`
- [ ] Легаси из Wf вынесено в `@adapter`-классы
- [ ] Нет блокеров AST внутри файла
- [ ] Зависимости на `.class.php` резолвятся резолвером пакета

Пример для прогона (когда фаза 2 готова):

```bash
laravel-ready --project-root=/path/to/KDL.Site \
  project/app/Domain/Service/Office/OfficeListService.php
```

---

## Связанные файлы

| Файл | Содержание |
|------|------------|
| `ARCHITECTURE.md` | фаза 2, pipeline `use` |
| `READINESS_MODEL.md` | допустимые метки зависимостей |
| `tests/Fixtures/Use/` | минимальная фикстура для TDD |
| KDL: `libs/functions/main.php` | `wfAutoLoad` — эталон для резолва `Wf\` |
| KDL: `composer.json` | PSR-4 только для `App\` |

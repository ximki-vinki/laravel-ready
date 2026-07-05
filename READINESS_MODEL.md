# Модель готовности

## Зачем эта модель

Модель описывает **уровень файла**, а не задачу «перевести всё в Laravel».

Для **охраны периметра** важны два уровня:

- **`LaravelReady`** — файл можно считать готовым; с `@laravel-ready` — под охраной.
- **`Legacy`** — есть блокеры, `@legacy-code` или недопустимая зависимость; guarded-файл **не должен** сюда попадать.

Остальные уровни (`LegacyPerfect`, `LaravelPerfect`) — для классификации и будущей полировки, не для первого релиза охраны.

---

## Уровни

**`Legacy`** — блокеры, метка `@legacy-code` или недопустимый `use`.

Из `Legacy` файл может прийти в **два** разных исхода (при целенаправленной работе, не при охране):

```
                         → LaravelReady → LaravelPerfect
Legacy ──┤
                         → LegacyPerfect
```

- **`LaravelReady`** — без блокеров, без `@legacy-code`, deps ок
- **`LegacyPerfect`** — без блокеров, но осознанно остаётся мостом к легаси (`@laravel-adapter`, `@legacy-adapter`, …)
- **`LaravelPerfect`** — `LaravelReady` + идиомы Laravel (не для MVP охраны)

`LegacyPerfect` и `LaravelReady` — **равные выходы из `Legacy`**, не ступени друг друга.

---

## Порядок проверки

Где файл **сейчас** (resolver):

1. Метка **`@legacy-code`** или **блокер** → `Legacy`
2. У guarded-файла (**`@laravel-ready`**): недопустимый **`use`** → `Legacy`
3. Блокеров нет + **`@legacy-perfect`** / **`@laravel-adapter`** / **`@legacy-adapter`** → `LegacyPerfect`
4. Иначе → `LaravelReady`
5. На шаге 4 + идиомы Laravel → `LaravelPerfect` (позже)

Файл **без метки** или с **несколькими метками** — exit `1` (нужна ровно одна метка).  
Guard **периметра** (`@laravel-ready` + blockers) — отдельный кейс; `@legacy-code` с findings — exit `0`.

---

## Метки

| Метка | Назначение |
|-------|------------|
| **`laravel-ready`** | Файл под охраной; при правке не должен стать `Legacy` |
| **`legacy-code`** | Явно легаси → всегда `Legacy` |
| **`legacy-perfect`** | Почищен, но остаётся в легаси-контуре → `LegacyPerfect` |
| **`laravel-adapter`** | Мост к легаси; для guarded-файла **допустимая** зависимость |
| **`legacy-adapter`** | Код только для легаси-контура; **недопустимая** зависимость для `@laravel-ready` |

Метки в PHPDoc: `@laravel-ready`, `@legacy-code`, и т.д.

---

## Охрана (`@laravel-ready`)

Файл с меткой проверяется **строже**:

| Проверка | Провал → `Legacy` |
|----------|-------------------|
| Блокеры в AST | да |
| `@legacy-code` | да |
| `use` на класс без `@laravel-ready` / `@laravel-adapter` | да |
| `use Wf\...` в `@laravel-ready` | да (denylist по префиксу) |
| `use` на `@legacy-adapter` / явный легаси | да |

`vendor/`, стандартная библиотека PHP, фреймворк — **вне** проверки зависимостей.

Без метки файл — `Untagged`, exit `1`. С `@legacy-code` blockers в AST **не ломают** exit (информирование).

---

## Зависимости (`use`)

Минимальный контракт (фаза 2):

- Собрать все `use` в файле (факт в AST — **Detector**).
- Применить **политику** допустимости (отдельный слой, **не Detector**).
- Для guarded-файла (`@laravel-ready`) нарушение → `UseFinding` → `ReadinessResolver` → guard.

### Политика (принято)

| `use` | Правило |
|-------|---------|
| `Wf\...` | **сразу недопустимо** в `@laravel-ready` (denylist по префиксу; резолв `wfAutoLoad` не нужен) |
| `App\...` | резолв в файл проекта → метка `@laravel-ready` или `@laravel-adapter` |
| без метки, `@legacy-code`, `@legacy-adapter` | недопустимо |
| `Illuminate\...`, `Psr\...`, прочий vendor | пропуск (вне периметра) |

Легаси из `Wf\` — только в файлах с `@laravel-adapter`; guarded-файл зависит от адаптера через `App\`, не через `use Wf\`.

### Как писать код

```php
/** @laravel-adapter */
class WfOfficeTableGateway
{
    use Wf\Db\Table;  // ok здесь
}

/** @laravel-ready */
class OfficeRepository
{
    public function __construct(
        private WfOfficeTableGateway $gateway,  // use App\... — ok
    ) {}
}
```

Позже: `extends`, `new`, `require` — не в первой итерации.

Подробнее про KDL.Site — `LEGACY_PROJECTS.md`.

---

## Legacy

Блокер или метка **`@legacy-code`**.

### Блокеры

**Общие** — валидный PHP, но не Laravel-way; AST:

| Паттерн | PHP | Laravel-ready |
|---------|-----|---------------|
| Суперглобали (`$GLOBALS`, `$_GET`, `$_POST`, …) | работают | не Laravel-way |
| `define()` | работает | не Laravel-way |
| `global $x` | работает | не Laravel-way |
| `eval()`, `extract()` | работают | не Laravel-way |
| `mysqli_*` | работает | не Laravel-way |

**Дополнительно** (project-specific, позже):

- `container('legacy')`, `legacy_config()`, `db_legacy_query()`
- Slim, свой фреймворк — метка **`@legacy-code`**

---

## LegacyPerfect

Без блокеров + метка **`@laravel-adapter`** / **`@legacy-adapter`** / **`@legacy-perfect`**. Код ок, с легаси остаётся намеренно.

---

## LaravelReady

Без блокеров, без `@legacy-code`, deps ок. Для переноса в Laravel.

Зависимость от **`@laravel-adapter`** — ок. От `Legacy` / **`@legacy-adapter`** / неразмеченного проектного класса — нет (для guarded-файла).

---

## LaravelPerfect

`LaravelReady` + `Arr::get`, `collect`, `Str::of`, `now()` / Carbon. Не блокирует MVP.

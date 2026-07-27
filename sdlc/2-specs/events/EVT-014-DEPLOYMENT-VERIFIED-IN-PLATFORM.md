# EVT-014-DEPLOYMENT-VERIFIED-IN-PLATFORM

## Смысл

Контейнерный контур запущен и проверен либо признан неготовым.

## Источники

- [PRD NFR-07](../../0-vibes/prd/prd.md)
- [PT-011](../../1-business-tasks/planning/PT-011.md)

## Инициатор и сущность

- актор: [ACTOR-007](../actors/ACTOR-007-DEPLOYMENT-OPERATOR-IN-PLATFORM.md);
- сущность: [ENT-008](../entities/ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM.md).

## Предусловия

Есть Docker Compose-конфигурация, шаблон окружения и собранные образы.

## Проверки

1. Запуск `app`, `nginx`, `db`, `redis`.
2. Health каждого компонента.
3. Доступность миграций и тестов.
4. REST через HTTPS.
5. MCP Streamable HTTP через HTTPS.
6. TLS termination на Nginx.
7. Отсутствие секретов в образах и репозитории.

## Полезная нагрузка

`release_id`, версии компонентов, статусы health-check, migration version,
endpoint checks, `result`, `verified_at`, `correlation_id`.

## Постусловия

При успехе окружение допускается к evaluation. При ошибке создаётся
диагностируемый сигнал с конкретной зависимостью.

## Use case

[UC-014](../use-cases/UC-014-ACTOR-007-EVT-014-ENT-008-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)

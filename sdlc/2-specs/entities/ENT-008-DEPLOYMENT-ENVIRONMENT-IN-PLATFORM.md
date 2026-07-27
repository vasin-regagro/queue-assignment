# ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM

## Назначение

Описанный и проверяемый контейнерный контур MVP.

## Источники

- [PRD NFR-07](../../0-vibes/prd/prd.md)
- [PT-011](../../1-business-tasks/planning/PT-011.md)

## Состав

| Компонент | Обязанность | Готовность |
|---|---|---|
| `app` | Laravel REST и MCP | App health проходит |
| `nginx` | HTTPS/TLS termination и proxy | HTTPS отвечает |
| `db` | MariaDB/MySQL | Соединение и миграции доступны |
| `redis` | Идемпотентность/блокировки | Соединение доступно |

## Атрибуты спецификации

`release_id`, версии образов, непубличные имена переменных, health statuses,
migration version, REST endpoint, MCP Streamable HTTP endpoint, `verified_at`.

## Инварианты

- запуск после настройки окружения: `docker compose up -d`;
- секретные значения отсутствуют в репозитории и образах;
- TLS завершается на Nginx;
- health-check отличает app и зависимости;
- миграции и тесты имеют документированные команды;
- целевая проверка включает 20 rps, 100 Queue и 1000 активных QueueEntry.

## Связанные события и use cases

- [EVT-014](../events/EVT-014-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)
- [UC-014](../use-cases/UC-014-ACTOR-007-EVT-014-ENT-008-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)

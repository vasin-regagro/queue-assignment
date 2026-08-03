# EVT-016-PUBLIC-QUEUE-BOARD-VIEWED-IN-QUEUE

## Смысл

Публичному наблюдателю сформировано актуальное read-only представление всех
очередей и активных участников.

## Источники

- [PRD 0.6, FR-14](../../0-vibes/prd/prd.md)
- [PT-014](../../1-business-tasks/planning/PT-014.md)

## Инициатор и сущности

- актор: [ACTOR-009](../actors/ACTOR-009-PUBLIC-VIEWER-IN-QUEUE.md);
- очередь: [ENT-002](../entities/ENT-002-QUEUE-IN-QUEUE.md);
- активная запись: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- HTTP endpoint доступен;
- авторизация отсутствует и не требуется;
- чтение выполняется из текущего подтверждённого состояния базы.

## Полезная нагрузка представления

- время формирования;
- для Queue: display name, `OPEN|CLOSED`, число активных записей;
- для QueueEntry: ticket number, display name, `WAITING|CALLED|SERVING`,
  `joined_at`, доступные `called_at` и `service_started_at`.

## Порядок и фильтрация

- Queue сортируются по `id`;
- QueueEntry фильтруются по активным состояниям;
- QueueEntry сортируются по `joined_at`, затем `id`;
- `COMPLETED` и `CANCELLED` не входят в представление.

## Постусловия

Состояние Queue и QueueEntry не меняется. Представление автоматически
запрашивается повторно через 15 секунд.

## Ошибки

Внутренняя ошибка чтения приводит к стандартному HTTP 5xx без раскрытия SQL,
секретов и stack trace.

## Use case

[UC-016](../use-cases/UC-016-ACTOR-009-EVT-016-ENT-004-BOARD-RETURNED-IN-QUEUE.md)

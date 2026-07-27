# EVT-012-RETENTION-APPLIED-IN-PLATFORM

## Смысл

Планировщик удалил или архивировал данные, срок хранения которых закончился,
не нарушив активные операции и ссылочную целостность.

## Источники

- [PRD NFR-05, DEC-011](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)

## Инициатор и сущности

- актор: [ACTOR-005](../actors/ACTOR-005-PLATFORM-SCHEDULER-IN-PLATFORM.md);
- QueueEntry: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md);
- IdempotencyRecord: [ENT-005](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md);
- McpToolCall: [ENT-006](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md).

## Сроки

- QueueEntry — 90 дней;
- IdempotencyRecord — 24 часа;
- McpToolCall — 30 дней.

## Предусловия

- запись старше соответствующего срока;
- QueueEntry не активна;
- обработка не удаляет данные, требуемые выполняющейся операцией.

## Полезная нагрузка

`retention_run_id`, `entity_type`, `cutoff_at`, `processed_count`,
`archived_count`, `deleted_count`, `failed_count`, `occurred_at`.

## Постусловия

Просроченные данные обработаны; активные записи и новые ключи сохранены; сбои
наблюдаемы и могут быть повторены.

## Use case

[UC-012](../use-cases/UC-012-ACTOR-005-EVT-012-ENT-005-RETENTION-APPLIED-IN-PLATFORM.md)

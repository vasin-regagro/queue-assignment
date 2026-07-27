# EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE

## Смысл

Пользователь успешно занял место в открытой очереди.

## Источники

- [PRD FR-02](../../0-vibes/prd/prd.md)
- [PT-003](../../1-business-tasks/planning/PT-003.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- действующий JWT;
- Queue существует и имеет `OPEN`;
- у User нет активной записи в этой Queue;
- передан ключ идемпотентности.

## Атомарная операция

1. Заблокировать выдачу следующего талона Queue.
2. Повторно проверить отсутствие активной записи.
3. Выдать следующий монотонный `ticket_number`.
4. Создать QueueEntry в `WAITING` с `joined_at`.
5. Сохранить результат идемпотентности.

## Полезная нагрузка

`queue_entry_id`, `queue_id`, `user_id`, `ticket_number`, `joined_at`,
`initial_position`, `correlation_id`.

## Постусловия

Создана ровно одна активная запись; талон уникален; FIFO-порядок определён
`joined_at`, затем `id`.

## Ошибки

Queue not found, Queue closed, active entry exists, authorization error,
idempotency conflict. Частичного эффекта нет.

## Use case

[UC-004](../use-cases/UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE.md)

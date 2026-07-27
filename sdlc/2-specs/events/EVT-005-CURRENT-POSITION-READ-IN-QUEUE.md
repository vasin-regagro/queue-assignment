# EVT-005-CURRENT-POSITION-READ-IN-QUEUE

## Смысл

Пользователь получил актуальное состояние собственной записи и, если она
ожидает, позицию в FIFO.

## Источники

- [PRD FR-03](../../0-vibes/prd/prd.md)
- [PT-004](../../1-business-tasks/planning/PT-004.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- JWT действителен;
- запись принадлежит пользователю либо найдена по User + Queue;
- чужой `user_id` не принимается как область поиска.

## Расчёт

Для `WAITING` позиция равна единице плюс число записей той же Queue со статусом
`WAITING`, которые имеют меньший `joined_at` либо тот же `joined_at` и меньший
`id`.

## Полезная нагрузка

`queue_entry_id`, `queue_id`, `ticket_number`, `status`, `position|null`,
`calculated_at`, `correlation_id`.

## Постусловия

Состояние не меняется. Для `CALLED`, `SERVING`, `COMPLETED`, `CANCELLED`
возвращается `position = null`.

## Ошибки

Неавторизован, запись не найдена, попытка доступа к чужой записи.

## Use case

[UC-005](../use-cases/UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE.md)

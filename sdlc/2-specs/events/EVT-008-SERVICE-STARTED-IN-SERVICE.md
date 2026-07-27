# EVT-008-SERVICE-STARTED-IN-SERVICE

## Смысл

Назначенный оператор начал обслуживание вызванной записи.

## Источники

- [PRD FR-06, DEC-004](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-006](../../1-business-tasks/planning/PT-006.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-002](../actors/ACTOR-002-OPERATOR-IN-SERVICE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- оператор назначен на Queue;
- запись находится в `CALLED`;
- у оператора нет другой записи `SERVING`;
- передан ключ идемпотентности.

## Переход

Атомарно выполнить `CALLED → SERVING`, записать `operator_user_id` и
`service_started_at`.

## Полезная нагрузка

`queue_entry_id`, `queue_id`, `operator_user_id`, `ticket_number`,
`service_started_at`, `correlation_id`.

## Постусловия

У оператора ровно одна или менее запись `SERVING`. Другой оператор той же
очереди может обслуживать другую запись.

## Ошибки

Нет назначения, неверное состояние, оператор уже занят, запись отменена,
конфликт ключа.

## Use case

[UC-008](../use-cases/UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE.md)

# EVT-009-SERVICE-COMPLETED-IN-SERVICE

## Смысл

Оператор завершил обслуживаемую им запись.

## Источники

- [PRD FR-07](../../0-vibes/prd/prd.md)
- [PT-006](../../1-business-tasks/planning/PT-006.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-002](../actors/ACTOR-002-OPERATOR-IN-SERVICE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- запись в `SERVING`;
- `operator_user_id` совпадает с текущим оператором;
- назначение на Queue действует;
- передан ключ идемпотентности.

## Переход

Атомарно выполнить `SERVING → COMPLETED` и установить `completed_at`.

## Полезная нагрузка

`queue_entry_id`, `queue_id`, `operator_user_id`, `ticket_number`,
`completed_at`, `correlation_id`.

## Постусловия

Запись терминальна; оператор может начать обслуживание другой вызванной
записи; история хранится 90 дней.

## Ошибки

Неверный оператор, отсутствие назначения, неверное состояние, конфликт ключа.

## Use case

[UC-009](../use-cases/UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE.md)

# EVT-007-NEXT-USER-CALLED-IN-SERVICE

## Смысл

Назначенный оператор атомарно вызвал первую ожидающую запись по FIFO.

## Источники

- [PRD FR-05](../../0-vibes/prd/prd.md)
- [PT-006](../../1-business-tasks/planning/PT-006.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-002](../actors/ACTOR-002-OPERATOR-IN-SERVICE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- оператор назначен на Queue;
- существует хотя бы одна QueueEntry в `WAITING`;
- передан ключ идемпотентности.

## Атомарная операция

Выбрать первую `WAITING` по `joined_at, id` с блокировкой от конкурирующих
операторов и выполнить `WAITING → CALLED`, установив `called_at`.

## Полезная нагрузка

Только разрешённые оператору поля: `queue_entry_id`, `ticket_number`, `status`,
`joined_at`, `display_name`, `called_at`, `correlation_id`.

## Постусловия

Запись больше не участвует в позиции ожидания и не может быть выбрана повторно.

## Ошибки

Оператор не назначен, Queue not found, очередь пуста, конфликт ключа. Закрытый
статус Queue не мешает вызвать ранее созданную активную запись.

## Use case

[UC-007](../use-cases/UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE.md)

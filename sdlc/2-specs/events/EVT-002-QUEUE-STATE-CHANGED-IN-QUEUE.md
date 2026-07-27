# EVT-002-QUEUE-STATE-CHANGED-IN-QUEUE

## Смысл

Администратор создал очередь либо изменил её состояние между `CLOSED` и
`OPEN`.

## Источники

- [PRD FR-01, FR-08](../../0-vibes/prd/prd.md)
- [PT-002](../../1-business-tasks/planning/PT-002.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Инициатор и сущность

- актор: [ACTOR-003](../actors/ACTOR-003-ADMINISTRATOR-IN-QUEUE.md);
- сущность: [ENT-002](../entities/ENT-002-QUEUE-IN-QUEUE.md).

## Типы

- `QUEUE_CREATED`: новая Queue в `CLOSED`;
- `QUEUE_OPENED`: `CLOSED → OPEN`;
- `QUEUE_CLOSED`: `OPEN → CLOSED`.

## Предусловия

- действующий JWT с ролью администратора;
- вызов выполнен через REST API;
- Queue существует для открытия/закрытия;
- передан ключ идемпотентности.

## Полезная нагрузка

`queue_id`, `previous_status`, `new_status`, `administrator_id`,
`occurred_at`, `correlation_id`.

## Постусловия

- новое состояние сохранено атомарно;
- `CLOSED` запрещает новые QueueEntry;
- активные записи после закрытия не меняются;
- счётчик талонов не сбрасывается.

## Ошибки

Неавторизованный доступ, запрет роли, Queue not found, конфликт
идемпотентности. Эквивалентный повтор не создаёт второе изменение.

## Use case

[UC-002](../use-cases/UC-002-ACTOR-003-EVT-002-ENT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)

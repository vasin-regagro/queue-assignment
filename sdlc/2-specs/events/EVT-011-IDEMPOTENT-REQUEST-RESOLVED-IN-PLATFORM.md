# EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM

## Смысл

Изменяющий запрос классифицирован как новый, эквивалентный повтор,
конфликтующий повтор или выполняющийся параллельно запрос.

## Источники

- [PRD FR-12](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициаторы и сущность

Инициатором может быть любой авторизованный
[User](../actors/ACTOR-001-USER-IN-QUEUE.md),
[Operator](../actors/ACTOR-002-OPERATOR-IN-SERVICE.md) или
[Administrator](../actors/ACTOR-003-ADMINISTRATOR-IN-QUEUE.md).
Сущность: [ENT-005](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md).

## Варианты результата

- `NEW`: зарезервирован ключ, операция выполняется;
- `REPLAY`: fingerprint совпал, возвращён сохранённый результат;
- `CONFLICT`: параметры отличаются;
- `IN_PROGRESS`: исходная операция ещё выполняется.

## Полезная нагрузка

`user_id`, `operation`, `idempotency_key`, `request_fingerprint`,
`resolution`, `record_id`, `correlation_id`.

## Инварианты

- ключ не глобален: он ограничен User и operation;
- запись создаётся атомарно до бизнес-эффекта;
- успешный результат сохраняется 24 часа;
- повтор не испускает повторное предметное событие;
- конфликт не изменяет предметные сущности.

## Use case

[UC-011](../use-cases/UC-011-ACTOR-001-EVT-011-ENT-005-IDEMPOTENT-RESULT-RETURNED-IN-PLATFORM.md)

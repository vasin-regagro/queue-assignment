# ACTOR-002-OPERATOR-IN-SERVICE

## Назначение

Пользователь с ролью оператора, назначенный администратором на одну или
несколько очередей. Последовательно вызывает участников и управляет циклом
обслуживания.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), разделы 7.2, FR-05–FR-07
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-006](../../1-business-tasks/planning/PT-006.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Права и ограничения

- работает только в назначенных очередях;
- вызывает первую запись `WAITING` по FIFO;
- переводит `CALLED → SERVING → COMPLETED`;
- одновременно обслуживает не более одной записи во всех назначенных очередях;
- может работать параллельно с другими операторами той же очереди;
- видит только талон, статус, время записи и отображаемое имя пользователя;
- не управляет очередями, если отдельно не имеет роль администратора.

## Связи через сущности

- [ENT-001-USER-IN-AUTH](../entities/ENT-001-USER-IN-AUTH.md) — профиль и роль.
- [ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE](../entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md) —
  область разрешённых очередей.
- [ENT-002-QUEUE-IN-QUEUE](../entities/ENT-002-QUEUE-IN-QUEUE.md) — назначенная
  очередь.
- [ENT-004-QUEUE-ENTRY-IN-QUEUE](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md) —
  вызываемая и обслуживаемая запись.
- [ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md) —
  защита изменяющих команд.

## События

- [EVT-007-NEXT-USER-CALLED-IN-SERVICE](../events/EVT-007-NEXT-USER-CALLED-IN-SERVICE.md)
- [EVT-008-SERVICE-STARTED-IN-SERVICE](../events/EVT-008-SERVICE-STARTED-IN-SERVICE.md)
- [EVT-009-SERVICE-COMPLETED-IN-SERVICE](../events/EVT-009-SERVICE-COMPLETED-IN-SERVICE.md)
- [EVT-010-MCP-TOOL-CALLED-IN-MCP](../events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM](../events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)

## Use cases

- [UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE](../use-cases/UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE.md)
- [UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE](../use-cases/UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE.md)
- [UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE](../use-cases/UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE.md)

## Критерии корректности актора

1. Назначение проверяется при каждой операторской операции.
2. Параллельный вызов не выбирает одну запись дважды.
3. Вторая запись не переходит в `SERVING`, пока у оператора есть обслуживаемая.
4. Ответ не раскрывает оператору запрещённые пользовательские поля.

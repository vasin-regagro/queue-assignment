# ACTOR-004-AI-AGENT-IN-MCP

## Назначение

Идентифицированный MCP-клиент, который вызывает инструменты через Streamable
HTTP от имени пользователя. Это канал взаимодействия, а не самостоятельный
источник полномочий.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), раздел 7.4 и FR-10–FR-13
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)

## Права и ограничения

- использует доверенный JWT-контекст пользователя;
- не принимает `user_id` как источник личности;
- вызывает только семь инструментов MVP;
- не имеет административных инструментов;
- не имеет прямого доступа к базе данных;
- наследует роль и область доступа пользователя;
- передаёт `idempotencyKey` во все изменяющие инструменты;
- каждый вызов, включая ошибочный, журналируется.

## Связи через сущности

- [ENT-001-USER-IN-AUTH](../entities/ENT-001-USER-IN-AUTH.md) — делегирующий
  пользователь.
- [ENT-002-QUEUE-IN-QUEUE](../entities/ENT-002-QUEUE-IN-QUEUE.md) — ресурс tools.
- [ENT-004-QUEUE-ENTRY-IN-QUEUE](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md) —
  ресурс пользовательских и операторских tools.
- [ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md) —
  повторяемость команд.
- [ENT-006-MCP-TOOL-CALL-IN-MCP](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md) —
  обязательный аудит.

## События

- [EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE](../events/EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE.md)
- [EVT-005-CURRENT-POSITION-READ-IN-QUEUE](../events/EVT-005-CURRENT-POSITION-READ-IN-QUEUE.md)
- [EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE](../events/EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE.md)
- [EVT-007-NEXT-USER-CALLED-IN-SERVICE](../events/EVT-007-NEXT-USER-CALLED-IN-SERVICE.md)
- [EVT-008-SERVICE-STARTED-IN-SERVICE](../events/EVT-008-SERVICE-STARTED-IN-SERVICE.md)
- [EVT-009-SERVICE-COMPLETED-IN-SERVICE](../events/EVT-009-SERVICE-COMPLETED-IN-SERVICE.md)
- [EVT-010-MCP-TOOL-CALLED-IN-MCP](../events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM](../events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)

## Use cases

- [UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE](../use-cases/UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE.md)
- [UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE](../use-cases/UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE.md)
- [UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE](../use-cases/UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE.md)
- [UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE](../use-cases/UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE.md)
- [UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE](../use-cases/UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE.md)
- [UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE](../use-cases/UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE.md)
- [UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP](../use-cases/UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP.md)

## Критерии корректности актора

1. MCP доступен по Streamable HTTP через HTTPS.
2. Контекст полномочий совпадает с REST-контекстом пользователя.
3. Инструмент не способен выполнить административную операцию.
4. Аудит сохраняется для успеха и ошибки без JWT и секретов.

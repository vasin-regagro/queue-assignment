# ACTOR-001-USER-IN-QUEUE

## Назначение

Постоянный или гостевой пользователь, который получает доступ к сервису,
выбирает очередь, создаёт собственную запись, проверяет позицию и отменяет
ожидание. Пользователь является владельцем своих записей независимо от того,
обращается он через REST или через AI-агента.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), разделы 7.1, 8 и 9
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-003](../../1-business-tasks/planning/PT-003.md)
- [PT-004](../../1-business-tasks/planning/PT-004.md)
- [PT-005](../../1-business-tasks/planning/PT-005.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Варианты

- **Постоянный пользователь:** регистрируется и аутентифицируется сервисом.
- **Гость:** создаётся сервисом без регистрации и получает JWT на 24 часа.
- **Пользователь с дополнительной ролью:** может также быть оператором или
  администратором, но в пользовательских сценариях подчиняется тем же правилам
  владения записью.

## Права и ограничения

- видит список и состояние очередей;
- создаёт не более одной активной записи в каждой очереди;
- может иметь активные записи одновременно в разных очередях;
- видит только собственную запись и позицию;
- отменяет собственную запись в `WAITING` или `CALLED`;
- не передаёт доверенный `user_id`: личность определяется по JWT;
- не получает данные других участников.

## Связи через сущности

- [ENT-001-USER-IN-AUTH](../entities/ENT-001-USER-IN-AUTH.md) — профиль,
  гостевой признак, роли и отображаемое имя.
- [ENT-002-QUEUE-IN-QUEUE](../entities/ENT-002-QUEUE-IN-QUEUE.md) — очередь,
  выбранная для участия.
- [ENT-004-QUEUE-ENTRY-IN-QUEUE](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md) —
  принадлежащая пользователю запись и талон.
- [ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md) —
  результат изменяющего запроса.
- [ENT-006-MCP-TOOL-CALL-IN-MCP](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md) —
  аудит действия через AI-агента.

## События

- [EVT-001-IDENTITY-ISSUED-IN-AUTH](../events/EVT-001-IDENTITY-ISSUED-IN-AUTH.md)
- [EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE](../events/EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE.md)
- [EVT-005-CURRENT-POSITION-READ-IN-QUEUE](../events/EVT-005-CURRENT-POSITION-READ-IN-QUEUE.md)
- [EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE](../events/EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE.md)
- [EVT-010-MCP-TOOL-CALLED-IN-MCP](../events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM](../events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)

## Use cases

- [UC-001-ACTOR-001-EVT-001-ENT-001-IDENTITY-ISSUED-IN-AUTH](../use-cases/UC-001-ACTOR-001-EVT-001-ENT-001-IDENTITY-ISSUED-IN-AUTH.md)
- [UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE](../use-cases/UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE.md)
- [UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE](../use-cases/UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE.md)
- [UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE](../use-cases/UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE.md)

## Критерии корректности актора

1. Любая защищённая операция связана с действующим JWT.
2. Владение записью невозможно подменить входным параметром.
3. Гость имеет те же предметные права пользователя в течение срока JWT.
4. Истечение JWT запрещает новые операции, но не удаляет доменные записи.

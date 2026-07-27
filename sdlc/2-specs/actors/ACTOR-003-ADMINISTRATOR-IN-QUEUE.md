# ACTOR-003-ADMINISTRATOR-IN-QUEUE

## Назначение

Пользователь с ролью администратора, управляющий жизненным циклом очередей и
назначениями операторов исключительно через REST API.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), разделы 7.3, FR-01 и DEC-012
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-002](../../1-business-tasks/planning/PT-002.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Права и ограничения

- создаёт очередь;
- открывает и закрывает очередь;
- просматривает состояние;
- назначает и снимает операторов;
- не получает административных MCP-инструментов в MVP;
- не выполняет пользовательские или операторские действия без соответствующей
  дополнительной роли.

## Связи через сущности

- [ENT-001-USER-IN-AUTH](../entities/ENT-001-USER-IN-AUTH.md) — профиль и роль.
- [ENT-002-QUEUE-IN-QUEUE](../entities/ENT-002-QUEUE-IN-QUEUE.md) — управляемая
  очередь.
- [ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE](../entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md) —
  назначение оператора.
- [ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md) —
  идемпотентность административной команды.

## События

- [EVT-002-QUEUE-STATE-CHANGED-IN-QUEUE](../events/EVT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)
- [EVT-003-OPERATOR-ASSIGNMENT-CHANGED-IN-QUEUE](../events/EVT-003-OPERATOR-ASSIGNMENT-CHANGED-IN-QUEUE.md)
- [EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM](../events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)

## Use cases

- [UC-002-ACTOR-003-EVT-002-ENT-002-QUEUE-STATE-CHANGED-IN-QUEUE](../use-cases/UC-002-ACTOR-003-EVT-002-ENT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)
- [UC-003-ACTOR-003-EVT-003-ENT-003-ASSIGNMENT-CHANGED-IN-QUEUE](../use-cases/UC-003-ACTOR-003-EVT-003-ENT-003-ASSIGNMENT-CHANGED-IN-QUEUE.md)

## Критерии корректности актора

1. Роль администратора проверяется по JWT.
2. Административный маршрут отсутствует среди MCP tools.
3. Закрытие запрещает новые записи, но не уничтожает активные.
4. Назначение допускает одного оператора в нескольких очередях.

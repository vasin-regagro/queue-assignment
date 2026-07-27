# TASK-013 — Реализовать завершение обслуживания

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-004, TASK-005, TASK-012
- **Результат:** CompleteService action

## Источники

- [PT-006](../1-business-tasks/planning/PT-006.md)
- [EVT-009](../2-specs/events/EVT-009-SERVICE-COMPLETED-IN-SERVICE.md)
- [UC-009](../2-specs/use-cases/UC-009-ACTOR-002-EVT-009-ENT-004-SERVICE-COMPLETED-IN-SERVICE.md)

## Цель

Завершить обслуживаемую текущим оператором QueueEntry.

## Объём

- общий complete action;
- REST endpoint;
- проверка обслуживающего Operator и назначения;
- `SERVING → COMPLETED`;
- `completed_at`;
- освобождение operator occupancy;
- событие/idempotency.

## Критерии приёмки

1. Завершает только оператор, указанный в QueueEntry.
2. Только `SERVING` допускает переход.
3. После завершения Operator начинает другую запись.
4. Повтор не меняет время завершения.
5. История сохраняется для retention 90 дней.

## Проверки

Ownership by operator, state matrix, replay и occupancy release tests.

## Definition of Done

UC-009 реализован через REST и готов к MCP adapter.

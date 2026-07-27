# TASK-012 — Реализовать начало обслуживания

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-004, TASK-005, TASK-007, TASK-011
- **Результат:** StartService action с лимитом на оператора

## Источники

- [PT-001](../1-business-tasks/planning/PT-001.md)
- [PT-006](../1-business-tasks/planning/PT-006.md)
- [EVT-008](../2-specs/events/EVT-008-SERVICE-STARTED-IN-SERVICE.md)
- [UC-008](../2-specs/use-cases/UC-008-ACTOR-002-EVT-008-ENT-004-SERVICE-STARTED-IN-SERVICE.md)

## Цель

Перевести вызванную запись в обслуживание, гарантируя не более одной
`SERVING` на Operator.

## Объём

- общий start action;
- REST endpoint;
- проверка назначения;
- блокировка QueueEntry и занятости Operator;
- `CALLED → SERVING`;
- operator ID и start timestamp;
- событие/idempotency.

## Требования реализации

Разные Operator одной Queue могут иметь отдельные `SERVING`. Ограничение
применяется к Operator во всех его Queue.

## Критерии приёмки

1. Валидная `CALLED` становится `SERVING`.
2. Занятый Operator не начинает вторую запись.
3. Другой Operator может обслуживать параллельно.
4. Неверное состояние и снятое назначение отклоняются.
5. Гонка двух start-команд сохраняет инвариант.

## Проверки

State, policy, idempotency и parallel operator occupancy tests.

## Definition of Done

UC-008 реализован через REST и готов к MCP adapter.

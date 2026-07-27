# TASK-009 — Реализовать получение текущей позиции

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-004, TASK-008
- **Результат:** GetCurrentPosition action

## Источники

- [PT-004](../1-business-tasks/planning/PT-004.md)
- [EVT-005](../2-specs/events/EVT-005-CURRENT-POSITION-READ-IN-QUEUE.md)
- [UC-005](../2-specs/use-cases/UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE.md)

## Цель

Вернуть состояние собственной QueueEntry и позицию среди `WAITING`.

## Объём

- общий query action;
- REST endpoint;
- поиск только в области текущего User;
- расчёт по `joined_at, id`;
- `position = null` вне `WAITING`;
- оптимизированный запрос по индексам TASK-002.

## Требования реализации

1. Позиция начинается с 1.
2. Учитываются только `WAITING`.
3. Чужая запись не раскрывается.
4. Query не меняет состояние.
5. Результат отражает согласованный snapshot чтения.

## Критерии приёмки

1. FIFO-позиция корректна после join/call/cancel.
2. Tie-breaker использует `id`.
3. Для `CALLED`, `SERVING`, `COMPLETED`, `CANCELLED` позиция отсутствует.
4. Нет активной записи — однозначный not found.

## Проверки

Набор таблиц состояний, тест пересчёта и query-count/performance test.

## Definition of Done

UC-005 реализован через REST и доступен для MCP adapter.

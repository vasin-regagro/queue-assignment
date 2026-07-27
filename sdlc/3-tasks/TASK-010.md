# TASK-010 — Реализовать отмену записи

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-004, TASK-005, TASK-008
- **Результат:** CancelQueueEntry action

## Источники

- [PT-001](../1-business-tasks/planning/PT-001.md)
- [PT-005](../1-business-tasks/planning/PT-005.md)
- [EVT-006](../2-specs/events/EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE.md)
- [UC-006](../2-specs/use-cases/UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE.md)

## Цель

Атомарно отменять собственную запись из `WAITING` или `CALLED`.

## Объём

- общий cancel action;
- REST endpoint;
- проверка владельца;
- переходы `WAITING|CALLED → CANCELLED`;
- `cancelled_at`;
- идемпотентный результат и событие.

## Требования реализации

Обновлённые PRD/PT-001 имеют приоритет над старой формулировкой PT-005:
отмена из `CALLED` обязательна.

## Критерии приёмки

1. Оба разрешённых исходных состояния отменяются.
2. `SERVING`, `COMPLETED`, `CANCELLED` отклоняются.
3. Чужая запись неизменна.
4. Отменённая запись не участвует в позиции и call-next.
5. Повтор не меняет `cancelled_at`.

## Проверки

State matrix, ownership, replay и гонка cancel/call.

## Definition of Done

UC-006 реализован через REST и готов к MCP adapter.

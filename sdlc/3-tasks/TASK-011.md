# TASK-011 — Реализовать вызов следующего пользователя

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-004, TASK-005, TASK-007, TASK-008
- **Результат:** конкурентно-безопасный CallNextUser action

## Источники

- [PT-006](../1-business-tasks/planning/PT-006.md)
- [PT-009](../1-business-tasks/planning/PT-009.md)
- [EVT-007](../2-specs/events/EVT-007-NEXT-USER-CALLED-IN-SERVICE.md)
- [UC-007](../2-specs/use-cases/UC-007-ACTOR-002-EVT-007-ENT-004-NEXT-USER-CALLED-IN-SERVICE.md)

## Цель

Выбрать и вызвать первую `WAITING` запись без двойного выбора конкурентами.

## Объём

- общий call-next action;
- REST endpoint;
- назначение Operator;
- row locking/эквивалентная стратегия;
- `WAITING → CALLED`, `called_at`;
- безопасная operator projection;
- результат пустой очереди.

## Требования реализации

1. Порядок: `joined_at`, затем `id`.
2. Closed Queue не мешает обслуживать активные записи.
3. Отменённые записи не выбираются.
4. Требуется idempotency key.
5. Ответ содержит только разрешённые поля.

## Критерии приёмки

1. FIFO подтверждён тестом.
2. Два параллельных оператора не получают одну QueueEntry.
3. Неназначенный Operator получает forbidden.
4. Пустая очередь возвращает согласованный результат.
5. Повтор возвращает исходно вызванную запись.

## Проверки

Feature, projection, parallel transaction и empty queue tests.

## Definition of Done

UC-007 реализован через REST и готов к MCP adapter.

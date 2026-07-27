# TASK-008 — Реализовать постановку в очередь

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-004, TASK-005, TASK-006
- **Результат:** атомарный JoinQueue action

## Источники

- [PT-003](../1-business-tasks/planning/PT-003.md)
- [ENT-004](../2-specs/entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)
- [EVT-004](../2-specs/events/EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE.md)
- [UC-004](../2-specs/use-cases/UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE.md)

## Цель

Создать одну активную QueueEntry пользователя с уникальным непрерывным талоном.

## Объём

- общий JoinQueue action;
- REST endpoint;
- проверка `OPEN`;
- ограничение User + Queue;
- атомарная выдача талона;
- начальная позиция;
- доменное событие.

## Требования реализации

1. User определяется по JWT.
2. Активные записи в разных Queue разрешены.
3. Счётчик не сбрасывается.
4. Проверки повторяются под блокировкой в транзакции.
5. Требуется idempotency key.

## Критерии приёмки

1. Валидный запрос возвращает QueueEntry, талон и позицию.
2. Closed Queue отклоняется.
3. Дубликат активной записи невозможен при гонке.
4. Два User не получают один талон.
5. Ошибка не оставляет частичный эффект.

## Проверки

Feature, transaction rollback и конкурентные tests.

## Definition of Done

UC-004 реализован через REST и готов к вызову из MCP adapter.

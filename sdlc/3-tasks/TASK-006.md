# TASK-006 — Реализовать REST-управление очередями

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-004, TASK-005
- **Результат:** административный жизненный цикл Queue и публичный список

## Источники

- [PT-002](../1-business-tasks/planning/PT-002.md)
- [ENT-002](../2-specs/entities/ENT-002-QUEUE-IN-QUEUE.md)
- [EVT-002](../2-specs/events/EVT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)
- [UC-002](../2-specs/use-cases/UC-002-ACTOR-003-EVT-002-ENT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)

## Цель

Реализовать REST actions создания, просмотра, открытия и закрытия Queue, а
также пользовательский список очередей.

## Объём

- create Queue с исходным `CLOSED`;
- list/show Queue;
- `CLOSED → OPEN`;
- `OPEN → CLOSED`;
- запрет новых записей в закрытую Queue;
- сохранение активных QueueEntry и счётчика талонов.

## Требования реализации

1. Изменения доступны только Administrator через REST.
2. Изменения требуют `Idempotency-Key`.
3. Повтор целевого состояния не создаёт событие повторно.
4. List возвращает минимальные безопасные поля.
5. Удаление Queue отсутствует.

## Критерии приёмки

1. Admin проходит полный жизненный цикл.
2. Другие роли получают forbidden.
3. Административного MCP route/tool нет.
4. Закрытие не меняет активные записи.
5. После повторного открытия нумерация продолжается.

## Проверки

REST feature tests, authorization matrix и idempotency replay.

## Definition of Done

UC-002 реализован; list action готов для REST и будущего MCP `list_queues`.

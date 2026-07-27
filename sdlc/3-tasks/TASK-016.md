# TASK-016 — Реализовать политику хранения данных

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-002, TASK-005, TASK-015
- **Результат:** безопасные retention jobs

## Источники

- [PT-001](../1-business-tasks/planning/PT-001.md)
- [PT-010](../1-business-tasks/planning/PT-010.md)
- [ACTOR-005](../2-specs/actors/ACTOR-005-PLATFORM-SCHEDULER-IN-PLATFORM.md)
- [EVT-012](../2-specs/events/EVT-012-RETENTION-APPLIED-IN-PLATFORM.md)
- [UC-012](../2-specs/use-cases/UC-012-ACTOR-005-EVT-012-ENT-005-RETENTION-APPLIED-IN-PLATFORM.md)

## Цель

Применять сроки 90 дней / 24 часа / 30 дней без повреждения активных операций.

## Объём

- scheduled jobs с пакетной обработкой;
- QueueEntry старше 90 дней и только неактивные;
- IdempotencyRecord старше 24 часов и не `IN_PROGRESS`;
- McpToolCall старше 30 дней;
- итог запуска и ошибки;
- безопасный повтор.

## Критерии приёмки

1. Активные `WAITING`, `CALLED`, `SERVING` сохраняются независимо от возраста.
2. Выполняющийся idempotency record не удаляется.
3. Каждый тип использует собственный cutoff.
4. Повторный job безопасен.
5. Частичная ошибка наблюдаема и повторяема.

## Проверки

Time-travel retention tests, active-record protection и batch retry tests.

## Definition of Done

UC-012 реализован и запускается scheduler-командой.

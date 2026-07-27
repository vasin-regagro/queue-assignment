# ACTOR-005-PLATFORM-SCHEDULER-IN-PLATFORM

## Назначение

Внутренний планировщик платформы, применяющий сроки хранения и запускающий
безопасную очистку или архивирование просроченных данных.

## Источники

- [PRD NFR-05](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)

## Права и ограничения

- работает с технической сервисной идентичностью;
- обрабатывает только типы данных с заданным сроком хранения;
- не удаляет активные QueueEntry;
- не изменяет бизнес-состояния очередей;
- сохраняет наблюдаемый итог каждого запуска;
- повторный запуск должен быть безопасен.

## Связи через сущности

- [ENT-004-QUEUE-ENTRY-IN-QUEUE](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md) —
  терминальные записи старше 90 дней.
- [ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM](../entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md) —
  результаты старше 24 часов.
- [ENT-006-MCP-TOOL-CALL-IN-MCP](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md) —
  аудит старше 30 дней.
- [ENT-007-OBSERVATION-IN-OBSERVABILITY](../entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md) —
  сигнал о сбое очистки.

## События

- [EVT-012-RETENTION-APPLIED-IN-PLATFORM](../events/EVT-012-RETENTION-APPLIED-IN-PLATFORM.md)
- [EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY](../events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)

## Use cases

- [UC-012-ACTOR-005-EVT-012-ENT-005-RETENTION-APPLIED-IN-PLATFORM](../use-cases/UC-012-ACTOR-005-EVT-012-ENT-005-RETENTION-APPLIED-IN-PLATFORM.md)

## Критерии корректности актора

1. Активные записи никогда не удаляются по возрасту.
2. Ошибка одного пакета не скрывает результат запуска.
3. Повтор не повреждает уже обработанные данные.
4. Итоги доступны наблюдаемости без раскрытия содержимого данных.

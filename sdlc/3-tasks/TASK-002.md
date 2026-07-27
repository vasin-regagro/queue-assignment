# TASK-002 — Реализовать доменную модель и миграции

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-001
- **Результат:** схема данных с ограничениями целостности

## Источники

- [PT-003](../1-business-tasks/planning/PT-003.md)
- [PT-006](../1-business-tasks/planning/PT-006.md)
- [ENT-001 User](../2-specs/entities/ENT-001-USER-IN-AUTH.md)
- [ENT-002 Queue](../2-specs/entities/ENT-002-QUEUE-IN-QUEUE.md)
- [ENT-003 OperatorAssignment](../2-specs/entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md)
- [ENT-004 QueueEntry](../2-specs/entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)
- [ENT-005 IdempotencyRecord](../2-specs/entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md)
- [ENT-006 McpToolCall](../2-specs/entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md)

## Цель

Создать миграции, модели, enum и database constraints для предметных и
инфраструктурных сущностей MVP.

## Объём

- `users`, `queues`, `operator_assignments`, `queue_entries`;
- `idempotency_records`, `mcp_tool_calls`;
- enum состояний Queue и QueueEntry;
- timestamps и индексы для FIFO, поиска позиции и retention;
- уникальные ограничения активного назначения и талона;
- механизм запрета более одной активной записи User в Queue.

## Требования реализации

1. `ticket_number` уникален в пределах Queue.
2. FIFO индекс поддерживает порядок `queue_id, status, joined_at, id`.
3. Ограничение одного `SERVING` на оператора защищено от гонок.
4. Схема позволяет несколько `SERVING` разных операторов одной Queue.
5. Все внешние ключи и правила удаления определены явно.
6. Миграции обратимы без удаления чужих таблиц.

## Критерии приёмки

1. Миграции применяются к чистой БД.
2. Недопустимые дубли отклоняются хранилищем.
3. Допустимые переходы поддерживаются типами модели.
4. Индексы покрывают основные конкурентные запросы.
5. Фабрики создают валидные состояния для тестов.

## Проверки

Migration tests, constraint tests и тесты enum/state transition matrix.

## Definition of Done

Схема соответствует ENT-001–ENT-006 и готова для прикладных сценариев.

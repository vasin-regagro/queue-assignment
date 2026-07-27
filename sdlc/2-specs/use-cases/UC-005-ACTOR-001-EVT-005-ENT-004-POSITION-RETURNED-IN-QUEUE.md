# UC-005-ACTOR-001-EVT-005-ENT-004-POSITION-RETURNED-IN-QUEUE

## Цель

Вернуть пользователю актуальное состояние и позицию собственной записи.

## Трассировка

- [PRD FR-03](../../0-vibes/prd/prd.md)
- [PT-004](../../1-business-tasks/planning/PT-004.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md)
- [EVT-005](../events/EVT-005-CURRENT-POSITION-READ-IN-QUEUE.md)
- [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)

## Интерфейсы

REST read operation или MCP `get_current_position`. Ключ идемпотентности не
требуется, так как состояние не меняется.

## Основной поток

1. Проверить JWT и определить User.
2. Найти собственную запись по идентификатору либо User + Queue.
3. Если статус `WAITING`, посчитать более ранние ожидающие записи по
   `joined_at, id`.
4. Вернуть позицию от 1, талон и статус.
5. Для MCP записать безопасный аудит.

## Альтернативы и ошибки

- запись вне `WAITING` — вернуть статус и `position = null`;
- запись отсутствует — not found;
- запись чужая — forbidden без раскрытия её содержимого;
- некорректный JWT — unauthorized.

## Результат

Ответ отражает состояние на момент расчёта и не меняет QueueEntry.

## Критерии приёмки

1. `CANCELLED` и `COMPLETED` не входят в расчёт.
2. Tie-breaker при равном времени — `id`.
3. После вызова/отмены позиции следующих записей актуальны.
4. Другие пользователи и их данные не раскрываются.

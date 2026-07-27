# UC-004-ACTOR-001-EVT-004-ENT-004-ENTRY-JOINED-IN-QUEUE

## Цель

Создать пользователю единственное активное место в выбранной открытой очереди.

## Трассировка

- [PRD FR-02](../../0-vibes/prd/prd.md)
- [PT-003](../../1-business-tasks/planning/PT-003.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)
- [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md)
- [EVT-004](../events/EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE.md)
- [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)

## Интерфейсы

REST изменяющая операция или MCP `join_queue`; оба вызывают один прикладной
сценарий. Требуется idempotency key.

## Предусловия

JWT действителен; Queue существует и открыта; у User нет активной записи в этой
Queue.

## Основной поток

1. Определить User из JWT.
2. Разрешить идемпотентный запрос.
3. В транзакции заблокировать ограничения User + Queue и счётчик талонов.
4. Повторно проверить `OPEN` и отсутствие активной записи.
5. Увеличить непрерывный счётчик Queue.
6. Создать QueueEntry в `WAITING`.
7. Рассчитать начальную позицию.
8. Сохранить результат и EVT-004.
9. Для MCP сохранить McpToolCall.

## Альтернативы и ошибки

Queue closed/not found; active entry exists; idempotency conflict; ошибка
авторизации. Транзакция полностью откатывается.

## Результат

Возвращены `queue_entry_id`, талон, `WAITING`, `joined_at` и позиция.

## Критерии приёмки

1. В одной Queue у User не более одной активной записи.
2. В разных Queue активные записи разрешены.
3. Конкурентные запросы не создают дубль или одинаковый талон.
4. REST и MCP дают эквивалентный результат.

# UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE

## Цель

Отменить собственную запись пользователя до начала обслуживания.

## Трассировка

- [PRD FR-04, DEC-005](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-005](../../1-business-tasks/planning/PT-005.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)
- [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md)
- [EVT-006](../events/EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE.md)
- [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)

## Интерфейсы

REST изменяющая операция или MCP `cancel_queue_entry`; обязателен idempotency
key.

## Предусловия

JWT действителен; User владеет QueueEntry; состояние `WAITING` или `CALLED`.

## Основной поток

1. Определить User из JWT.
2. Разрешить идемпотентный запрос.
3. Заблокировать QueueEntry.
4. Повторно проверить владельца и состояние.
5. Выполнить переход в `CANCELLED`, установить `cancelled_at`.
6. Сохранить результат и EVT-006.
7. Для MCP сохранить аудит.

## Альтернативы и ошибки

Чужая/отсутствующая запись; `SERVING` или терминальное состояние; конфликт
ключа. Повтор исходной успешной команды возвращает исходный ответ.

## Результат

Запись отменена, сохраняется в истории и больше не участвует в FIFO.

## Критерии приёмки

1. Поддерживаются оба перехода: из `WAITING` и `CALLED`.
2. Отмена из `SERVING` запрещена.
3. Чужая запись неизменна.
4. Повтор не испускает второй бизнес-эффект.

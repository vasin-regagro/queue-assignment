# ENT-002-QUEUE-IN-QUEUE

## Назначение

Управляемая электронная очередь, определяющая доступность новых записей,
непрерывную последовательность талонов и область работы операторов.

## Источники

- [PRD 0.6](../../0-vibes/prd/prd.md), FR-01, FR-08, FR-14 и раздел 10.1
- [PT-002](../../1-business-tasks/planning/PT-002.md)
- [PT-003](../../1-business-tasks/planning/PT-003.md)
- [PT-014](../../1-business-tasks/planning/PT-014.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Неизменяемый |
| `name` | string | Обязательное отображаемое имя |
| `status` | enum | `OPEN` или `CLOSED` |
| `next_ticket_number` | integer | Монотонный счётчик, не сбрасывается |
| `created_at` | datetime | Время создания |
| `updated_at` | datetime | Время последнего изменения |

## Инварианты

- новая запись создаётся только при `OPEN`;
- закрытие не отменяет активные QueueEntry;
- `next_ticket_number` увеличивается атомарно;
- удаление очереди не входит в MVP;
- система поддерживает до 100 очередей.

## Переходы

- создание приводит к `CLOSED`;
- `CLOSED → OPEN`;
- `OPEN → CLOSED`;
- повторное открытие или закрытие не создаёт побочного эффекта при том же ключе.

## Связи

- Queue содержит до 1000 активных
  [QueueEntry](ENT-004-QUEUE-ENTRY-IN-QUEUE.md);
- Queue имеет много
  [OperatorAssignment](ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md).
- Queue отображается публичному
  [ACTOR-009](../actors/ACTOR-009-PUBLIC-VIEWER-IN-QUEUE.md) независимо от
  статуса вместе со счётчиком активных записей.

## Связанные события и use cases

- [EVT-002](../events/EVT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)
- [UC-002](../use-cases/UC-002-ACTOR-003-EVT-002-ENT-002-QUEUE-STATE-CHANGED-IN-QUEUE.md)
- [EVT-016](../events/EVT-016-PUBLIC-QUEUE-BOARD-VIEWED-IN-QUEUE.md)
- [UC-016](../use-cases/UC-016-ACTOR-009-EVT-016-ENT-004-BOARD-RETURNED-IN-QUEUE.md)

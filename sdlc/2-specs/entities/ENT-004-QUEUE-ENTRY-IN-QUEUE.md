# ENT-004-QUEUE-ENTRY-IN-QUEUE

## Назначение

Запись пользователя в конкретной очереди, содержащая талон, состояние и
временные отметки полного цикла ожидания и обслуживания.

## Источники

- [PRD 0.5](../../0-vibes/prd/prd.md), FR-02–FR-07, FR-14 и раздел 10.2
- [PT-003](../../1-business-tasks/planning/PT-003.md)
- [PT-004](../../1-business-tasks/planning/PT-004.md)
- [PT-005](../../1-business-tasks/planning/PT-005.md)
- [PT-006](../../1-business-tasks/planning/PT-006.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)
- [PT-014](../../1-business-tasks/planning/PT-014.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Стабильный tie-breaker FIFO |
| `queue_id` | FK | Родительская Queue |
| `user_id` | FK | Владелец записи |
| `operator_user_id` | FK, nullable | Оператор после начала обслуживания |
| `ticket_number` | integer | Уникален в Queue |
| `status` | enum | `WAITING`, `CALLED`, `SERVING`, `COMPLETED`, `CANCELLED` |
| `joined_at` | datetime | Основной ключ FIFO |
| `called_at` | datetime, nullable | Время вызова |
| `service_started_at` | datetime, nullable | Начало обслуживания |
| `completed_at` | datetime, nullable | Завершение |
| `cancelled_at` | datetime, nullable | Отмена |

## Инварианты

- у User не более одной активной записи в одной Queue;
- активные состояния: `WAITING`, `CALLED`, `SERVING`;
- `ticket_number` уникален и монотонен в Queue;
- FIFO: `joined_at`, затем `id`;
- один оператор связан не более чем с одной записью `SERVING`;
- разные операторы могут иметь `SERVING` в одной Queue;
- запись хранится 90 дней.

## Переходы

```text
WAITING -> CALLED -> SERVING -> COMPLETED
   |          |
   +----------+----> CANCELLED
```

Любой другой переход отклоняется атомарно.

## Раскрытие данных

- владельцу: собственная запись и позиция;
- оператору: талон, статус, `joined_at`, `User.display_name`;
- публичному наблюдателю на табло: только активная запись, талон, статус,
  временные отметки и `User.display_name`;
- email, внутренний `user_id`, роли, секреты и терминальная история публично не
  раскрываются.

## Связанные события

[EVT-004](../events/EVT-004-QUEUE-ENTRY-JOINED-IN-QUEUE.md),
[EVT-005](../events/EVT-005-CURRENT-POSITION-READ-IN-QUEUE.md),
[EVT-006](../events/EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE.md),
[EVT-007](../events/EVT-007-NEXT-USER-CALLED-IN-SERVICE.md),
[EVT-008](../events/EVT-008-SERVICE-STARTED-IN-SERVICE.md),
[EVT-009](../events/EVT-009-SERVICE-COMPLETED-IN-SERVICE.md),
[EVT-016](../events/EVT-016-PUBLIC-QUEUE-BOARD-VIEWED-IN-QUEUE.md).

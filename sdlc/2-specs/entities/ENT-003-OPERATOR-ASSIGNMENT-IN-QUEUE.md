# ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE

## Назначение

Связь пользователя с ролью оператора и очередью, определяющая область
операторских полномочий.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), DEC-003 и бизнес-правило 8
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Идентификатор назначения |
| `operator_user_id` | FK | User с ролью `OPERATOR` |
| `queue_id` | FK | Назначенная Queue |
| `assigned_by` | FK | User с ролью `ADMINISTRATOR` |
| `assigned_at` | datetime | Начало назначения |
| `revoked_at` | datetime, nullable | Завершение назначения |

## Инварианты

- активная пара `operator_user_id + queue_id` уникальна;
- назначает и снимает только администратор через REST;
- один оператор может иметь несколько активных назначений;
- одна очередь может иметь несколько операторов;
- снятие назначения запрещает новые действия, но не изменяет историю записей.

## Проверка доступа

Перед `call_next_user`, `start_service` и `complete_service` должна
существовать активная связь для оператора и очереди.

## Связи

- принадлежит [User](ENT-001-USER-IN-AUTH.md);
- принадлежит [Queue](ENT-002-QUEUE-IN-QUEUE.md).

## Связанные события и use cases

- [EVT-003](../events/EVT-003-OPERATOR-ASSIGNMENT-CHANGED-IN-QUEUE.md)
- [UC-003](../use-cases/UC-003-ACTOR-003-EVT-003-ENT-003-ASSIGNMENT-CHANGED-IN-QUEUE.md)

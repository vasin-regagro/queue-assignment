# EVT-006-QUEUE-ENTRY-CANCELLED-IN-QUEUE

## Смысл

Владелец отменил запись до начала обслуживания.

## Источники

- [PRD FR-04, DEC-005](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-005](../../1-business-tasks/planning/PT-005.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Инициатор и сущность

- авторизующий актор: [ACTOR-001](../actors/ACTOR-001-USER-IN-QUEUE.md);
- возможный канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- сущность: [ENT-004](../entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md).

## Предусловия

- запись принадлежит текущему User;
- статус `WAITING` или `CALLED`;
- передан ключ идемпотентности.

## Переход

`WAITING → CANCELLED` или `CALLED → CANCELLED`, с атомарной установкой
`cancelled_at`.

## Полезная нагрузка

`queue_entry_id`, `queue_id`, `user_id`, `previous_status`, `cancelled_at`,
`correlation_id`.

## Постусловия

- запись не учитывается в позиции;
- запись не может быть вызвана или обслужена;
- история сохраняется 90 дней;
- повтор возвращает первоначальный результат.

## Ошибки

Чужая/отсутствующая запись, `SERVING`, `COMPLETED` или `CANCELLED`, конфликт
ключа. Ошибка не меняет запись.

## Use case

[UC-006](../use-cases/UC-006-ACTOR-001-EVT-006-ENT-004-ENTRY-CANCELLED-IN-QUEUE.md)

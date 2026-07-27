# EVT-003-OPERATOR-ASSIGNMENT-CHANGED-IN-QUEUE

## Смысл

Администратор назначил оператора на очередь либо снял активное назначение.

## Источники

- [PRD DEC-003](../../0-vibes/prd/prd.md)
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Инициатор и сущность

- актор: [ACTOR-003](../actors/ACTOR-003-ADMINISTRATOR-IN-QUEUE.md);
- сущность: [ENT-003](../entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md).

## Типы

- `OPERATOR_ASSIGNED`;
- `OPERATOR_UNASSIGNED`.

## Предусловия

- вызов через REST от администратора;
- Queue и User существуют;
- назначаемый User имеет роль оператора;
- передан ключ идемпотентности.

## Полезная нагрузка

`assignment_id`, `queue_id`, `operator_user_id`, `administrator_id`, `action`,
`occurred_at`, `correlation_id`.

## Постусловия

- активная связь создана либо помечена завершённой;
- повторное назначение той же пары не создаёт дубль;
- снятый оператор не может начать новую операцию в очереди;
- исторические записи не изменяются.

## Ошибки

Запрет роли, отсутствующий User/Queue, конфликт состояния назначения или ключа
идемпотентности.

## Use case

[UC-003](../use-cases/UC-003-ACTOR-003-EVT-003-ENT-003-ASSIGNMENT-CHANGED-IN-QUEUE.md)

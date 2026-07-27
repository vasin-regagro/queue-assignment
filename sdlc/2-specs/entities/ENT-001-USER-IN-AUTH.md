# ENT-001-USER-IN-AUTH

## Назначение

Идентичность человека, используемая для аутентификации, ролей, владения
записями и назначения оператором.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), FR-00 и раздел 10.4
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Неизменяемый идентификатор |
| `display_name` | string | Единственное персональное поле, доступное оператору |
| `credential` | secret/hash, nullable | Только для постоянного пользователя |
| `is_guest` | boolean | Определяет временный профиль |
| `roles` | set | `USER`, `OPERATOR`, `ADMINISTRATOR` |
| `created_at` | datetime | Время создания |
| `guest_expires_at` | datetime, nullable | Для гостя — через 24 часа после выдачи JWT |

## Инварианты

- каждый пользователь имеет базовые пользовательские полномочия;
- гостевой пользователь не требует постоянных credentials;
- истёкший гостевой JWT не удаляет пользователя или его QueueEntry;
- роль не выводится из параметров запроса;
- один профиль может совмещать несколько ролей.

## Связи

- один User имеет много [QueueEntry](ENT-004-QUEUE-ENTRY-IN-QUEUE.md);
- User с ролью оператора имеет много
  [OperatorAssignment](ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md);
- User связан с [McpToolCall](ENT-006-MCP-TOOL-CALL-IN-MCP.md);
- User входит в область ключа [IdempotencyRecord](ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md).

## Безопасность

Credentials и JWT не выдаются в предметных ответах и не журналируются.
Оператору раскрывается только `display_name`.

## Связанные события и use cases

- [EVT-001](../events/EVT-001-IDENTITY-ISSUED-IN-AUTH.md)
- [UC-001](../use-cases/UC-001-ACTOR-001-EVT-001-ENT-001-IDENTITY-ISSUED-IN-AUTH.md)

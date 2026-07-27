# ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM

## Назначение

Сохранённый результат изменяющей операции, предотвращающий повторный
бизнес-эффект при сетевом повторе REST- или MCP-запроса.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), FR-12 и NFR-05
- [PT-001](../../1-business-tasks/planning/PT-001.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Идентификатор записи |
| `user_id` | FK | Авторизованный инициатор |
| `operation` | string | REST operation или MCP tool |
| `idempotency_key` | string | Переданный клиентом ключ |
| `request_fingerprint` | hash | Канонический отпечаток параметров |
| `status` | enum | `IN_PROGRESS`, `SUCCEEDED`, `FAILED` |
| `response_snapshot` | json | Безопасный исходный результат |
| `created_at` | datetime | Начало срока хранения |
| `expires_at` | datetime | `created_at + 24h` |

## Инварианты

- область уникальности: User + operation + idempotency_key;
- совпавший fingerprint возвращает сохранённый результат;
- иной fingerprint для того же ключа даёт конфликт;
- `IN_PROGRESS` не позволяет параллельно повторить эффект;
- запись хранится 24 часа;
- удаление не затрагивает уже созданные доменные сущности.

## Связи

- принадлежит [User](ENT-001-USER-IN-AUTH.md);
- может быть связана с [McpToolCall](ENT-006-MCP-TOOL-CALL-IN-MCP.md).

## Связанные события и use cases

- [EVT-011](../events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)
- [UC-011](../use-cases/UC-011-ACTOR-001-EVT-011-ENT-005-IDEMPOTENT-RESULT-RETURNED-IN-PLATFORM.md)

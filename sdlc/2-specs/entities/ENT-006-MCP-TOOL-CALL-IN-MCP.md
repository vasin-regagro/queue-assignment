# ENT-006-MCP-TOOL-CALL-IN-MCP

## Назначение

Аудит одного MCP-вызова, достаточный для расследования результата без
сохранения JWT, секретов или полного диалога.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), FR-13 и NFR-05
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | UUID/ID | Идентификатор вызова |
| `correlation_id` | string | Поиск сквозного запроса |
| `tool_name` | enum/string | Только tool из allowlist MVP |
| `user_id` | FK | Делегирующий пользователь |
| `ai_agent_id` | string, nullable | Идентификатор клиента |
| `idempotency_key` | string, nullable | Только для изменяющих tools |
| `request_payload` | json | Поля из белого списка |
| `response_payload` | json | Безопасное представление результата |
| `status` | enum | `SUCCEEDED` или `FAILED` |
| `error_code` | string, nullable | Категория ошибки |
| `started_at` | datetime | Начало |
| `finished_at` | datetime | Завершение |

## Инварианты

- создаётся для каждого успешного и ошибочного MCP-вызова;
- JWT, credentials и секреты отсутствуют;
- хранится 30 дней;
- tool_name не может обозначать административную операцию;
- изменяющий tool связан с idempotency key;
- доступ к журналу ограничен уполномоченным персоналом.

## Связи

- принадлежит [User](ENT-001-USER-IN-AUTH.md);
- может ссылаться на
  [IdempotencyRecord](ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md).

## Связанные события и use cases

- [EVT-010](../events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [UC-010](../use-cases/UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP.md)

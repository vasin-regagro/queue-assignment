# EVT-010-MCP-TOOL-CALLED-IN-MCP

## Смысл

AI-агент вызвал разрешённый MCP-инструмент по Streamable HTTP; вызов был
авторизован, выполнен или отклонён и записан в аудит.

## Источники

- [PRD FR-10, FR-13](../../0-vibes/prd/prd.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)

## Инициатор и сущность

- канал: [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md);
- авторизующий User: [ENT-001](../entities/ENT-001-USER-IN-AUTH.md);
- аудит: [ENT-006](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md).

## Allowlist

`list_queues`, `join_queue`, `get_current_position`, `cancel_queue_entry`,
`call_next_user`, `start_service`, `complete_service`.

## Обработка

1. Проверить HTTPS/MCP-сессию и JWT.
2. Проверить tool allowlist и схему параметров.
3. Получить права из User, не из аргументов.
4. Для изменения проверить `idempotencyKey`.
5. Вызвать тот же прикладной сценарий, что REST.
6. Записать безопасный аудит успеха или ошибки.

## Полезная нагрузка события

`tool_call_id`, `tool_name`, `user_id`, `ai_agent_id`, `status`, `error_code`,
`started_at`, `finished_at`, `correlation_id`.

JWT и секреты исключаются.

## Постусловия

Вызов имеет McpToolCall, а бизнес-эффект соответствует REST. Административный
tool отсутствует.

## Use case

[UC-010](../use-cases/UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP.md)

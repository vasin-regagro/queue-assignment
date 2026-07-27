# UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP

## Цель

Безопасно выполнить разрешённый MCP tool от имени пользователя и сохранить
полный минимальный аудит вызова.

## Трассировка

- [PRD FR-10–FR-13](../../0-vibes/prd/prd.md)
- [PT-007](../../1-business-tasks/planning/PT-007.md)
- [PT-008](../../1-business-tasks/planning/PT-008.md)
- [PT-009](../../1-business-tasks/planning/PT-009.md)
- [PT-010](../../1-business-tasks/planning/PT-010.md)
- [ACTOR-004](../actors/ACTOR-004-AI-AGENT-IN-MCP.md)
- [EVT-010](../events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [ENT-006](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md)

## Предусловия

Streamable HTTP доступен через HTTPS/Nginx; JWT присутствует; tool входит в
allowlist.

## Основной поток

1. Создать correlation id и начать McpToolCall.
2. Проверить JWT и получить User/roles.
3. Проверить имя tool и схему параметров.
4. Для изменяющего tool потребовать `idempotencyKey`.
5. Вызвать общий с REST прикладной use case.
6. Маскировать вход и результат по белому списку.
7. Завершить McpToolCall статусом `SUCCEEDED`.
8. Вернуть структурированный результат агенту.

## Ошибочный поток

При любой ошибке McpToolCall завершается `FAILED` с категорией error code.
JWT, секрет, SQL и stack trace не сохраняются и не возвращаются.

## Allowlist

`list_queues`, `join_queue`, `get_current_position`, `cancel_queue_entry`,
`call_next_user`, `start_service`, `complete_service`.

## Результат

Предметный результат эквивалентен REST; каждый вызов прослеживается 30 дней.

## Критерии приёмки

1. Успех и ошибка имеют аудит.
2. Административных tools нет.
3. `user_id` из args не подменяет JWT.
4. Изменяющий tool без ключа не выполняется.
5. Производительность проверяется в общей цели 20 rps.

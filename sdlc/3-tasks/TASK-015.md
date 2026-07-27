# TASK-015 — Реализовать аудит MCP-вызовов

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-002, TASK-014
- **Результат:** полный безопасный McpToolCall audit

## Источники

- [PT-010](../1-business-tasks/planning/PT-010.md)
- [ENT-006](../2-specs/entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md)
- [EVT-010](../2-specs/events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [UC-010](../2-specs/use-cases/UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP.md)

## Цель

Журналировать каждый успешный и ошибочный MCP-вызов без секретов.

## Объём

- начало/завершение audit record;
- tool, User, Agent, correlation, timestamps;
- idempotency key для изменений;
- allowlist payload fields и redaction;
- success/error category;
- доступ для расследования;
- retention marker 30 дней.

## Требования реализации

JWT, credentials, SQL и stack trace никогда не сохраняются. Сбой аудита
изменяющей команды должен иметь явно выбранную fail-safe политику и тест.

## Критерии приёмки

1. 100% tool calls имеют запись.
2. Ошибочный вызов тоже завершает audit.
3. По correlation id восстанавливается цепочка.
4. Secret scanning fixtures не обнаруживают токен.
5. Запись доступна retention job после 30 дней.

## Проверки

Success/failure audit, redaction, correlation и audit storage failure tests.

## Definition of Done

MCP gateway не имеет пути выполнения в обход аудита.

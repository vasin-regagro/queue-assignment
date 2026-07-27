# TASK-014 — Реализовать MCP Streamable HTTP gateway

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-003–TASK-013
- **Результат:** MCP endpoint и allowlist из семи tools

## Источники

- [PT-007](../1-business-tasks/planning/PT-007.md)
- [ACTOR-004](../2-specs/actors/ACTOR-004-AI-AGENT-IN-MCP.md)
- [EVT-010](../2-specs/events/EVT-010-MCP-TOOL-CALLED-IN-MCP.md)
- [UC-010](../2-specs/use-cases/UC-010-ACTOR-004-EVT-010-ENT-006-TOOL-CALL-RECORDED-IN-MCP.md)

## Цель

Предоставить доменные actions через MCP Streamable HTTP без дублирования
бизнес-логики.

## Tools

`list_queues`, `join_queue`, `get_current_position`, `cancel_queue_entry`,
`call_next_user`, `start_service`, `complete_service`.

## Объём

- Streamable HTTP transport;
- JWT authentication context;
- schemas входов/выходов и error codes;
- adapters к существующим actions;
- `idempotencyKey` для изменяющих tools;
- отсутствие административных tools;
- correlation id.

## Требования реализации

1. Tool args не определяют User.
2. Policies идентичны REST.
3. Ошибки структурированы для AI Agent.
4. Прямого repository/database tool нет.
5. Transport доступен за Nginx/HTTPS.

## Критерии приёмки

1. Все семь tools вызываются по Streamable HTTP.
2. REST/MCP parity подтверждена contract tests.
3. Изменяющий tool без ключа отклоняется.
4. Admin tools отсутствуют в discovery.
5. Неверный JWT не запускает action.

## Проверки

MCP protocol, schema, auth, parity и negative discovery tests.

## Definition of Done

Семь tools работают поверх общего прикладного слоя.

# TASK-020 — Реализовать сквозные REST/MCP acceptance tests

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-003–TASK-016, TASK-018, TASK-019
- **Результат:** автоматическое доказательство UC-001–UC-012

## Источники

- [PT-012](../1-business-tasks/planning/PT-012.md)
- [ACTOR specs](../2-specs/actors)
- [Event specs](../2-specs/events)
- [Use-case specs](../2-specs/use-cases)

## Цель

Покрыть позитивные, негативные и parity-сценарии всех продуктовых use case.

## Объём

- JWT permanent/guest;
- Queue lifecycle и assignments;
- join/position/cancel;
- call/start/complete;
- authorization matrix;
- idempotency replay/conflict;
- MCP tool discovery, schemas, errors и audit;
- retention;
- REST/MCP equivalence.

## Требования реализации

Каждый тест содержит ссылки/metadata на UC, EVT и PT. Fixtures не используют
production secrets.

## Критерии приёмки

1. UC-001–UC-012 имеют минимум позитивный и негативный тест.
2. Все переходы QueueEntry проверены.
3. Все роли проверены на разрешение и запрет.
4. Семь MCP tools имеют parity test.
5. Аудит и redaction проверены.

## Definition of Done

Результаты публикуются CI и могут быть включены в AcceptanceReport.

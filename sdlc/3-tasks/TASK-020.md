# TASK-020 — Реализовать сквозные REST/MCP acceptance tests

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-003–TASK-016, TASK-018, TASK-019
- **Результат:** автоматическая acceptance-матрица UC-001–UC-016

## Источники

- [PT-012](../1-business-tasks/planning/PT-012.md)
- [ACTOR specs](../2-specs/actors)
- [Event specs](../2-specs/events)
- [Use-case specs](../2-specs/use-cases)
- [MOD-001](../2-specs/modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

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
- REST/MCP equivalence;
- deployment contract/health и публичное табло;
- явные executable placeholders для UC, чья функциональность ещё не
  реализована.

## Требования реализации

Каждый тест содержит UC id в имени и трассируется к EVT, ENT, ACTOR и PT.
Fixtures не используют production secrets или production DB. Быстрый набор
использует SQLite `:memory:` и `RefreshDatabase`; специфичные для MariaDB/Redis
проверки относятся к TASK-021.

## Критерии приёмки

1. UC-001–UC-016 имеют минимум основной и релевантный негативный/граничный
   сценарий либо явный `INCOMPLETE` до реализации функции.
2. Все переходы QueueEntry проверены.
3. Все роли проверены на разрешение и запрет.
4. Семь MCP tools имеют parity test.
5. Аудит и redaction проверены.
6. `PASS`, `FAIL`, `INCOMPLETE` и `SKIPPED` различаются; только `PASS`
   считается прохождением.
7. Датированный отчёт соответствует `test_results/YYYY-MM-DD_tests.md`.

## Definition of Done

Результаты публикуются CI и включаются в AcceptanceReport. Наличие incomplete
UC не позволяет закрыть TASK-022 со статусом `READY`.

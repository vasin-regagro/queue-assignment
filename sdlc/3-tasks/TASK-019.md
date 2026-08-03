# TASK-019 — Настроить CI и базовые quality gates

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-001, TASK-018
- **Результат:** автоматическая проверка каждого изменения

## Источники

- [PT-011](../1-business-tasks/planning/PT-011.md)
- [PT-012](../1-business-tasks/planning/PT-012.md)
- [PRD NFR-06](../0-vibes/prd/prd.md)
- [MOD-001](../2-specs/modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Цель

Создать воспроизводимый CI pipeline для качества, тестов и безопасности
артефактов до evaluation.

## Объём

- dependency install с lockfile;
- formatter/linter;
- static analysis;
- отдельные jobs для unit, feature и UC acceptance tests;
- contract/parity tests REST/MCP;
- integration tests с MariaDB, Redis и Nginx;
- migration test на чистой DB;
- secret scan;
- dependency vulnerability scan;
- build Docker images;
- release jobs для concurrency/load и deployment smoke;
- публикация machine-readable и датированных test artifacts.

## Требования реализации

1. Pipeline использует MariaDB/MySQL и Redis совместимых версий.
2. Секреты CI не печатаются.
3. Неуспешная обязательная проверка блокирует результат.
4. Артефакт связан с commit/release id.
5. Команды совпадают с локальной документацией.
6. Быстрый job использует изолированную тестовую БД и не имеет production
   credentials.
7. `FAIL`, обязательный `INCOMPLETE` или `SKIPPED` блокирует release gate.

## Критерии приёмки

1. Pipeline проходит на корректном коде.
2. Намеренно падающий тест блокирует сборку.
3. Миграции проверяются с нуля.
4. Результаты доступны для AcceptanceReport.
5. Docker image строится один раз для проверяемого release.
6. Артефакты содержат commit/release id, окружение, команды и test totals.

## Definition of Done

Quality evidence автоматически сохраняется для TASK-022.

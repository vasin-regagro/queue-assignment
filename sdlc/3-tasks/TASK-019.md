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

## Цель

Создать воспроизводимый CI pipeline для качества, тестов и безопасности
артефактов до evaluation.

## Объём

- dependency install с lockfile;
- formatter/linter;
- static analysis;
- unit/feature/integration tests;
- migration test на чистой DB;
- secret scan;
- dependency vulnerability scan;
- build Docker images;
- публикация test artifacts.

## Требования реализации

1. Pipeline использует MariaDB/MySQL и Redis совместимых версий.
2. Секреты CI не печатаются.
3. Неуспешная обязательная проверка блокирует результат.
4. Артефакт связан с commit/release id.
5. Команды совпадают с локальной документацией.

## Критерии приёмки

1. Pipeline проходит на корректном коде.
2. Намеренно падающий тест блокирует сборку.
3. Миграции проверяются с нуля.
4. Результаты доступны для AcceptanceReport.
5. Docker image строится один раз для проверяемого release.

## Definition of Done

Quality evidence автоматически сохраняется для TASK-022.

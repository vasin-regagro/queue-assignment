# TASK-018 — Подготовить Docker Compose и HTTPS на Nginx

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-001, TASK-017
- **Результат:** воспроизводимый контейнерный контур

## Источники

- [PT-011](../1-business-tasks/planning/PT-011.md)
- [ACTOR-007](../2-specs/actors/ACTOR-007-DEPLOYMENT-OPERATOR-IN-PLATFORM.md)
- [ENT-008](../2-specs/entities/ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM.md)
- [EVT-014](../2-specs/events/EVT-014-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)
- [UC-014](../2-specs/use-cases/UC-014-ACTOR-007-EVT-014-ENT-008-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)
- [MOD-001](../2-specs/modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Цель

Запускать `app`, `nginx`, `db`, `redis` одной командой и предоставлять REST/MCP
через HTTPS с TLS termination на Nginx.

## Объём

- production-like Dockerfiles;
- Compose services, networks, volumes и healthchecks;
- Nginx REST/MCP proxy;
- TLS configuration;
- startup/migration/test commands;
- environment template;
- runbook запуска и остановки;
- автоматизированный clean-environment deployment smoke.

## Требования реализации

1. `docker compose up -d` запускает обязательный контур.
2. Образы не содержат секретов.
3. App стартует после готовности зависимостей либо корректно retry.
4. MCP Streamable HTTP поддерживает нужные proxy timeouts/streaming.
5. TLS завершается на Nginx.

## Критерии приёмки

1. Чистое окружение разворачивается по инструкции.
2. Все четыре сервиса healthy.
3. REST и MCP доступны по HTTPS.
4. Миграции и тесты выполняются документированно.
5. Сбой зависимости диагностируется.
6. Smoke-тест проверяет миграции, idempotent seeder, `/api/health`, HTTPS REST,
   оба MCP transport, публичное табло и restart контейнеров.
7. Проверка выполняется на одноразовых volumes без production credentials.

## Проверки

Compose config validation, build, image secret scan, clean-volume deployment
smoke и негативная проверка недоступной зависимости. Отчёт содержит версии
образов, команды, release/commit id и endpoint results.

## Definition of Done

UC-014 проходит в чистом окружении.

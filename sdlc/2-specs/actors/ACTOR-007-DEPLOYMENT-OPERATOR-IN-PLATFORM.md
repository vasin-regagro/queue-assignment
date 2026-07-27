# ACTOR-007-DEPLOYMENT-OPERATOR-IN-PLATFORM

## Назначение

Инженер или CI-процесс, который разворачивает воспроизводимый пилотный контур,
проверяет зависимости и предоставляет REST/MCP через HTTPS.

## Источники

- [PRD NFR-07](../../0-vibes/prd/prd.md)
- [PT-011](../../1-business-tasks/planning/PT-011.md)

## Права и ограничения

- управляет конфигурацией запуска, но не предметными данными;
- использует секреты из окружения, не включая их в репозиторий и образы;
- запускает `app`, `nginx`, `db`, `redis`;
- выполняет миграции и тесты документированным способом;
- завершает TLS на Nginx;
- не считается администратором предметной области.

## Связи через сущности

- [ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM](../entities/ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM.md) —
  проверяемый контур.
- [ENT-007-OBSERVATION-IN-OBSERVABILITY](../entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md) —
  сигнал неготовности.

## События

- [EVT-014-DEPLOYMENT-VERIFIED-IN-PLATFORM](../events/EVT-014-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)
- [EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY](../events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)

## Use cases

- [UC-014-ACTOR-007-EVT-014-ENT-008-DEPLOYMENT-VERIFIED-IN-PLATFORM](../use-cases/UC-014-ACTOR-007-EVT-014-ENT-008-DEPLOYMENT-VERIFIED-IN-PLATFORM.md)

## Критерии корректности актора

1. Чистое окружение запускается после настройки переменных.
2. Секреты не фиксируются в артефактах.
3. Недоступная зависимость видна через health-check.
4. MCP Streamable HTTP доступен через HTTPS/Nginx.

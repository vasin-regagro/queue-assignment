# TASK-021 — Провести конкурентные и нагрузочные проверки

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-008–TASK-018, TASK-019
- **Результат:** доказательство целостности и производительности

## Источники

- [PT-009](../1-business-tasks/planning/PT-009.md)
- [PT-012](../1-business-tasks/planning/PT-012.md)
- [PRD NFR-01–NFR-03](../0-vibes/prd/prd.md)
- [ENT-008](../2-specs/entities/ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM.md)
- [MOD-001](../2-specs/modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Цель

Подтвердить инварианты при гонках и целевой объём MVP.

## Объём

- параллельный join одного User;
- параллельная выдача талонов;
- конкурентный call-next;
- cancel против call;
- start-service для одного Operator;
- idempotency replay;
- REST/MCP parity под реальными зависимостями;
- Streamable HTTP и legacy SSE через Nginx;
- профиль до 100 Queue, 1000 активных Entry/Queue, 20 rps;
- измерение p95 без внешнего AI provider.

## Критерии приёмки

1. Нет дублирующих активных записей.
2. Нет одинаковых талонов.
3. Одна Entry не вызывается дважды.
4. Один Operator не имеет две `SERVING`.
5. При 20 rps p95 не превышает 500 ms в согласованном тестовом контуре.
6. Ошибки и saturation наблюдаемы.
7. Проверки выполняются на одноразовых MariaDB/Redis без production данных.
8. Отчёт фиксирует версии образов, число workers, длительность, latency
   percentiles, throughput и error rate.

## Проверки

Повторяемый load profile, конкурентные integration tests, проверка инвариантов
после нагрузки и отчёт с параметрами окружения.

## Definition of Done

Отчёт привязан к release и пригоден для TASK-022.

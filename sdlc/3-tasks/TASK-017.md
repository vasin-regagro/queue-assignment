# TASK-017 — Реализовать наблюдаемость приложения

## Карточка

- **Статус:** Ready
- **Приоритет:** P1
- **Зависимости:** TASK-001, TASK-015, TASK-016
- **Результат:** health-check, метрики и структурированные журналы

## Источники

- [PT-010](../1-business-tasks/planning/PT-010.md)
- [PT-013](../1-business-tasks/planning/PT-013.md)
- [ENT-007](../2-specs/entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md)
- [EVT-013](../2-specs/events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)

## Цель

Сделать состояние приложения, зависимостей и критических бизнес-инвариантов
измеримым без раскрытия секретов.

## Объём

- liveness/readiness;
- отдельные статусы DB и Redis;
- structured logs с correlation id;
- latency/error metrics;
- счётчики auth failures, MCP audit completeness, idempotency conflicts;
- сигналы нарушения FIFO/дублей;
- redaction.

## Требования реализации

1. Health не раскрывает credentials и внутренние трассировки.
2. Метрики не содержат высококардинальные персональные labels.
3. Ошибка retention/audit создаёт диагностируемый сигнал.
4. Источники метрик сопоставимы с PRD section 17.

## Критерии приёмки

1. Недоступная DB/Redis видна в readiness.
2. Один запрос трассируется по correlation id.
3. JWT не появляется в логах.
4. Метрики позволяют рассчитать продуктовые показатели.
5. Пороговый сигнал может стать Observation.

## Проверки

Dependency failure, log redaction, metrics existence и correlation tests.

## Definition of Done

Сервис предоставляет минимальный эксплуатационный контракт для PT-013.

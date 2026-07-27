# ACTOR-006-OPERATIONS-OBSERVER-IN-OBSERVABILITY

## Назначение

Ответственный сотрудник или автоматизированный контур наблюдения, который
получает сигналы пилота, классифицирует их и возвращает подтверждённые проблемы
в бизнес-планирование.

## Источники

- [PRD NFR-04 и раздел 17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [PT-013](../../1-business-tasks/planning/PT-013.md)

## Права и ограничения

- читает разрешённые метрики, health-check и безопасные журналы;
- классифицирует сигнал как `ERROR`, `WARNING` или `INFO`;
- связывает сигнал с релизом и PT;
- не использует наблюдение для прямого изменения предметных данных;
- не переносит секреты и лишние персональные данные в артефакт;
- создаёт вход следующего цикла только после триажа.

## Связи через сущности

- [ENT-006-MCP-TOOL-CALL-IN-MCP](../entities/ENT-006-MCP-TOOL-CALL-IN-MCP.md) —
  один из источников.
- [ENT-007-OBSERVATION-IN-OBSERVABILITY](../entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md) —
  создаваемый и классифицируемый сигнал.

## События

- [EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY](../events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)

## Use cases

- [UC-013-ACTOR-006-EVT-013-ENT-007-OBSERVATION-TRIAGED-IN-OBSERVABILITY](../use-cases/UC-013-ACTOR-006-EVT-013-ENT-007-OBSERVATION-TRIAGED-IN-OBSERVABILITY.md)

## Критерии корректности актора

1. У сигнала есть severity, источник, влияние и доказательство.
2. Сигнал трассируется к релизу и задачам.
3. Для реакции назначен владелец.
4. Подтверждённая проблема поступает в следующий цикл SDLC.

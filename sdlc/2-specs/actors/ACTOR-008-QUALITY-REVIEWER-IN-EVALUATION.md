# ACTOR-008-QUALITY-REVIEWER-IN-EVALUATION

## Назначение

Ответственный за приёмку сотрудник или CI-контур, который сопоставляет
реализацию с PRD и спецификациями и формирует решение о готовности MVP.

## Источники

- [PRD, разделы 16–17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)

## Права и ограничения

- читает результаты тестов и безопасные диагностические данные;
- не изменяет критерии задним числом;
- трассирует каждую проверку к PRD, PT и spec;
- не исправляет дефекты внутри процесса оценки;
- не допускает пилот при открытом критическом дефекте;
- фиксирует остаточные риски и доказательства.

## Связи через сущности

- [ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION](../entities/ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION.md) —
  итог оценки.
- [ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM](../entities/ENT-008-DEPLOYMENT-ENVIRONMENT-IN-PLATFORM.md) —
  проверяемый контур.
- [ENT-007-OBSERVATION-IN-OBSERVABILITY](../entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md) —
  источник следующего цикла при выявленном риске.

## События

- [EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION](../events/EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION.md)

## Use cases

- [UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION](../use-cases/UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION.md)

## Критерии корректности актора

1. Все 22 критерия PRD имеют доказательство.
2. Критические дефекты блокируют допуск.
3. REST/MCP, безопасность, нагрузка и развёртывание включены в оценку.
4. Решение и остаточные риски воспроизводимы.

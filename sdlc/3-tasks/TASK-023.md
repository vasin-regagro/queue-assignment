# TASK-023 — Организовать цикл производственных наблюдений

## Карточка

- **Статус:** Ready
- **Приоритет:** P2
- **Зависимости:** TASK-017, TASK-018, TASK-022
- **Результат:** замкнутый путь observation → business planning

## Источники

- [PT-013](../1-business-tasks/planning/PT-013.md)
- [ACTOR-006](../2-specs/actors/ACTOR-006-OPERATIONS-OBSERVER-IN-OBSERVABILITY.md)
- [ENT-007](../2-specs/entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md)
- [EVT-013](../2-specs/events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)
- [UC-013](../2-specs/use-cases/UC-013-ACTOR-006-EVT-013-ENT-007-OBSERVATION-TRIAGED-IN-OBSERVABILITY.md)

## Цель

Превращать эксплуатационные сигналы в классифицированные наблюдения и входы
следующего цикла SDLC.

## Объём

- шаблоны `ERROR`, `WARNING`, `INFO`;
- источник, release, PT, влияние и evidence;
- правила redaction;
- владелец реакции;
- периодичность обзора;
- создание входа в `1-business-tasks/observation/`;
- проверка пути из этапа наблюдения обратно в planning.

## Критерии приёмки

1. Каждая метрика PRD имеет источник и способ расчёта.
2. Severity классифицируется однозначно.
3. Подтверждённый ERROR имеет владельца.
4. Observation связан с release и PT.
5. Секреты и лишние персональные данные отсутствуют.
6. Тестовый сигнал проходит полный цикл этап 10 → этап 1.

## Definition of Done

UC-013 воспроизводим, а команда имеет документированный cadence обзора.

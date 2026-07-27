# EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY

## Смысл

Сигнал работающего пилота классифицирован и подготовлен для возврата в
бизнес-планирование.

## Источники

- [PRD NFR-04 и раздел 17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [PT-013](../../1-business-tasks/planning/PT-013.md)

## Инициатор и сущность

- актор: [ACTOR-006](../actors/ACTOR-006-OPERATIONS-OBSERVER-IN-OBSERVABILITY.md);
- сущность: [ENT-007](../entities/ENT-007-OBSERVATION-IN-OBSERVABILITY.md).

## Источники сигнала

Метрики, структурированные журналы, MCP-аудит, health-check, результаты
проверок FIFO, авторизации, идемпотентности и производительности.

## Классификация

- `ERROR`: нарушение критического сценария, безопасности или доступности;
- `WARNING`: деградация, рост ошибок или приближение к пределу;
- `INFO`: ожидаемая продуктовая или операционная метрика.

## Полезная нагрузка

`observation_id`, `severity`, `source`, `release_id`, `business_task_refs`,
`impact`, `evidence_refs`, `occurred_at`, `correlation_id`.

## Постусловия

- сигнал сохранён в соответствующей категории этапа наблюдения;
- назначен владелец реакции;
- подтверждённый сигнал может создать вход в
  `1-business-tasks/observation/`;
- секреты и лишние персональные данные исключены.

## Use case

[UC-013](../use-cases/UC-013-ACTOR-006-EVT-013-ENT-007-OBSERVATION-TRIAGED-IN-OBSERVABILITY.md)

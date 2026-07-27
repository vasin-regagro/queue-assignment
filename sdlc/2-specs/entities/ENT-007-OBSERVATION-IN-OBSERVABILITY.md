# ENT-007-OBSERVATION-IN-OBSERVABILITY

## Назначение

Структурированный сигнал работающего пилота, который связывает наблюдаемое
поведение с релизом и бизнес-задачей и возвращает его в следующий цикл SDLC.

## Источники

- [PRD 0.2](../../0-vibes/prd/prd.md), NFR-04 и раздел 17
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [PT-013](../../1-business-tasks/planning/PT-013.md)

## Атрибуты

| Поле | Тип | Правило |
|---|---|---|
| `id` | string | Стабильный идентификатор сигнала |
| `severity` | enum | `ERROR`, `WARNING`, `INFO` |
| `source` | string | Метрика, журнал или health-check |
| `release_id` | string | Наблюдаемый релиз |
| `business_task_refs` | list | Затронутые PT |
| `occurred_at` | datetime | Время события |
| `impact` | text | Пользовательское/операционное влияние |
| `evidence` | references | Метрики и безопасные журналы |
| `status` | enum | `NEW`, `TRIAGED`, `PLANNED`, `CLOSED` |

## Инварианты

- каждый сигнал имеет severity, источник, влияние и доказательство;
- секреты и избыточные персональные данные не включаются;
- `ERROR` означает нарушение критического сценария или доступности;
- `WARNING` означает деградацию или приближение к порогу;
- `INFO` фиксирует ожидаемый факт или продуктовую метрику;
- подтверждённый сигнал создаёт артефакт в следующем цикле планирования.

## Связанные события и use cases

- [EVT-013](../events/EVT-013-OBSERVATION-RECORDED-IN-OBSERVABILITY.md)
- [UC-013](../use-cases/UC-013-ACTOR-006-EVT-013-ENT-007-OBSERVATION-TRIAGED-IN-OBSERVABILITY.md)

# ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION

## Назначение

Неизменяемый итог оценки конкретного результата MVP против требований.

## Источники

- [PRD, разделы 16–17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [MOD-001](../modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Атрибуты

| Поле | Правило |
|---|---|
| `report_id` | Уникальный идентификатор |
| `release_id` | Неизменяемый release или commit id проверяемого результата |
| `criteria_results` | Результат всех 24 критериев PRD |
| `spec_refs` | Ссылки ACTOR/ENT/EVT/UC |
| `test_evidence` | Unit, feature, UC acceptance, contract/parity, integration, concurrency, load, deployment smoke, security/privacy и UI evidence |
| `environment` | Версии runtime, БД, Redis, контейнеров и существенные параметры без секретов |
| `commands` | Точные воспроизводимые команды проверки |
| `test_totals` | Passed, failed, incomplete, skipped tests и assertions |
| `critical_defects` | Открытые блокеры |
| `residual_risks` | Принятые некритические риски |
| `decision` | `READY`, `NOT_READY` |
| `decided_at` | Время решения |
| `decided_by` | Владелец решения |

## Инварианты

- нет критерия без результата;
- `READY` невозможно при открытом критическом дефекте;
- доказательства относятся к тому же release;
- только `PASS` считается прохождением; `INCOMPLETE` и `SKIPPED` блокируют
  затронутый обязательный критерий;
- production данные и credentials не используются;
- отчёт хранится как `test_results/YYYY-MM-DD_tests.md` и не утверждает
  coverage без фактического измерения;
- изменение требований требует новой версии отчёта;
- отчёт не заменяет security gate.

## Связанные события и use cases

- [EVT-015](../events/EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION.md)
- [UC-015](../use-cases/UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION.md)

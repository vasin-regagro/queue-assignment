# EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION

## Смысл

Проверяемый релиз получил формальное решение о готовности или неготовности к
следующему quality/security/release gate.

## Источники

- [PRD, разделы 16–17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [MOD-001](../modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Инициатор и сущность

- актор: [ACTOR-008](../actors/ACTOR-008-QUALITY-REVIEWER-IN-EVALUATION.md);
- сущность: [ENT-009](../entities/ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION.md).

## Предусловия

Результат реализации существует; тесты выполнены; deployment environment
проверен; критерии PRD и specs имеют трассировку.

## Проверки

Static quality, unit, feature, UC acceptance, REST/MCP contract/parity,
integration с MariaDB/Redis/Nginx, конкурентность, нагрузка, deployment smoke,
security/privacy, UI, FIFO, состояния, роли, JWT, гостевой доступ,
идемпотентность, аудит, сроки хранения и документация.

## Полезная нагрузка

`report_id`, `release_id`, `passed_count`, `failed_count`, `incomplete_count`,
`skipped_count`, `assertion_count`, `evidence_refs`, `critical_defect_count`,
`decision`, `residual_risk_refs`, `decided_at`.

## Постусловия

- `READY`: нет критических дефектов, все обязательные проверки имеют `PASS` и
  все 24 критерия доказаны;
- `NOT_READY`: зафиксированы блокеры и владельцы;
- решение не заменяет security check.

## Use case

[UC-015](../use-cases/UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION.md)

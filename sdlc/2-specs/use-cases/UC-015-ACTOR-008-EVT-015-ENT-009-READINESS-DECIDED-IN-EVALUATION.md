# UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION

## Цель

Сформировать доказуемое решение о готовности MVP к следующим gate.

## Трассировка

- [PRD, разделы 16–17](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [ACTOR-008](../actors/ACTOR-008-QUALITY-REVIEWER-IN-EVALUATION.md)
- [EVT-015](../events/EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION.md)
- [ENT-009](../entities/ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION.md)
- [MOD-001](../modules/MOD-001-TEST-STRATEGY-IN-EVALUATION.md)

## Предусловия

Определён release id; доступны результаты реализации, тестов, deployment
verification и все спецификации.

## Основной поток

1. Построить матрицу 24 критериев PRD.
2. Связать каждый критерий с PT, spec и тестом.
3. Выполнить static quality, unit, feature и UC-001–UC-016 acceptance tests.
4. Выполнить REST/MCP contract/parity и integration tests на MariaDB, Redis,
   Nginx, Streamable HTTP и legacy SSE.
5. Проверить конкурентность, идемпотентность, FIFO и load profile.
6. Выполнить deployment smoke, security/privacy и UI checks.
7. Проверить сроки хранения, миграции, seeder и документацию.
8. Зафиксировать команды, окружение, test totals, assertions и evidence для
   release/commit id.
9. Классифицировать дефекты и остаточные риски.
10. При нуле критических дефектов и полном покрытии принять `READY`, иначе
   `NOT_READY`.
11. Сохранить AcceptanceReport и EVT-015.

## Альтернативы

Отсутствующее доказательство, `INCOMPLETE` или обязательный `SKIPPED` считаются
непрохождением критерия. Изменение требования требует нового отчёта, а не
редактирования доказательств прошлого релиза.

## Результат

Получено воспроизводимое решение с блокерами или остаточными рисками.

## Критерии приёмки

1. Все критерии имеют результат.
2. `READY` невозможен с критическим дефектом.
3. Отчёт относится к конкретному release.
4. Следующим шагом остаётся отдельный security gate.
5. Все уровни MOD-001 имеют evidence, а production DB не использовалась.
